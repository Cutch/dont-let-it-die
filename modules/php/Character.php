<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Exception;

class Character
{
    private Game $game;
    private ?string $submittingCharacter = null;
    private array $cachedData = [];
    private static array $characterColumns = [
        'character_name',
        'player_id',
        'item_1',
        'item_2',
        'item_3',
        'stamina',
        'health',
        'modifiedMaxStamina',
        'modifiedMaxHealth',
        'confirmed',
        'incapacitated',
        'hindrance',
        'day_event',
        'necklace',
    ];

    public function __construct($game)
    {
        $this->game = $game;
    }
    public function addExtraTime(?int $extraTime = null)
    {
        $this->game->giveExtraTime($this->getTurnCharacter()['player_id'], $extraTime);
    }

    public function _updateCharacterData(string $name, array $data)
    {
        // Update db
        for ($i = 0; $i < 3; $i++) {
            $data['item_' . ($i + 1)] = array_key_exists($i, $data['equipment']) ? $data['equipment'][$i] : null;
            if ($data['item_' . ($i + 1)]) {
                $data['item_' . ($i + 1)] = is_array($data['item_' . ($i + 1)])
                    ? (array_key_exists('itemId', $data['item_' . ($i + 1)])
                        ? $data['item_' . ($i + 1)]['itemId']
                        : null)
                    : $data['item_' . ($i + 1)];
            }
        }
        $data['hindrance'] =
            join(
                ',',
                array_map(function ($hindrance) {
                    return $hindrance['id'];
                }, $data['physicalHindrance'] + $data['mentalHindrance'])
            ) ?? '';
        $data['day_event'] =
            join(
                ',',
                array_map(function ($dayEvent) {
                    return $dayEvent['id'];
                }, $data['dayEvent'])
            ) ?? '';
        $data['necklace'] =
            join(
                ',',
                array_map(function ($item) {
                    return $item['itemId'];
                }, $data['necklaces'])
            ) ?? '';
        $data['health'] = clamp($data['health'], 0, $data['maxHealth']);
        $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, self::$characterColumns)) {
                $sqlValue = $value ? "'{$value}'" : 'NULL';
                $values[] = "`{$key}` = {$sqlValue}";
                $this->cachedData[$name][$key] = $value;
            }
        }
        $values = implode(',', $values);
        $this->game::DbQuery("UPDATE `character` SET {$values} WHERE character_name = '$name'");
        $this->game->markChanged('player');
    }
    public function updateCharacterData(string $name, $callback)
    {
        // Pull from db if needed
        $data = $this->getCharacterData($name, false);
        if (!$callback($data)) {
            $this->_updateCharacterData($name, $data);
        }
    }
    public function updateAllCharacterData($callback)
    {
        $turnOrder = $this->getAllCharacterIds();
        foreach ($turnOrder as $name) {
            // Pull from db if needed
            $data = $this->getCharacterData($name, false);
            if (!$callback($data)) {
                $this->_updateCharacterData($name, $data);
            }
        }
    }
    public function getAllCharacterIds(): array
    {
        $turnOrder = $this->game->gameData->get('turnOrder');
        return array_values(array_filter($turnOrder));
    }
    public function getAllCharacterData(bool $_skipHooks = false): array
    {
        $turnOrder = $this->getAllCharacterIds();
        return array_map(function ($char) use ($_skipHooks) {
            return $this->getCharacterData($char, $_skipHooks);
        }, $turnOrder);
    }
    public function getAllCharacterDataForPlayer(int $playerId): array
    {
        return array_values(
            array_filter($this->getAllCharacterData(), function ($char) use ($playerId) {
                return $char['player_id'] == $playerId;
            })
        );
    }
    public function getCalculatedData(array $characterData, bool $_skipHooks = false): array
    {
        extract($this->game->gameData->getAll('turnNo', 'turnOrder'));
        $turnOrder = array_values(array_filter($turnOrder));
        $characterName = $characterData['character_name'];
        $isActive = $turnOrder[$turnNo ?? 0] == $characterName;
        $characterData['isActive'] = $isActive;
        $characterData['isFirst'] = array_key_exists(0, $turnOrder) && $turnOrder[0] == $characterName;
        $characterData['id'] = $characterName;
        $underlyingCharacterData = $this->game->data->getCharacters()[$characterData['id']];
        $characterData['maxStamina'] = $underlyingCharacterData['stamina'] + $characterData['modifiedMaxStamina'];
        $characterData['maxHealth'] = $underlyingCharacterData['health'] + $characterData['modifiedMaxHealth'];

        array_walk($underlyingCharacterData, function ($v, $k) use (&$characterData) {
            if (str_starts_with($k, 'on') || in_array($k, ['slots', 'skills'])) {
                $characterData[$k] = $v;
            }
        });
        $itemsLookup = $this->game->gameData->getItems();
        $characterData['dayEvent'] = array_map(function ($itemId) {
            return $this->game->data->getExpansion()[$itemId];
        }, array_filter(explode(',', $characterData['day_event'] ?? '')));

        $characterData['necklaces'] = array_map(function ($itemId) use ($itemsLookup, $characterName) {
            $itemName = $itemsLookup[$itemId];
            $skills = [];
            if (array_key_exists('skills', $this->game->data->getItems()[$itemName])) {
                array_walk($this->game->data->getItems()[$itemName]['skills'], function ($v, $k) use (
                    $itemId,
                    $itemName,
                    $characterName,
                    &$skills
                ) {
                    $skillId = $k . '_' . $itemId;
                    $v['id'] = $skillId;
                    $v['itemId'] = $itemId;
                    $v['itemName'] = $itemName;
                    $v['characterId'] = $characterName;
                    $skills[$skillId] = $v;
                });
            }
            return [
                ...$this->game->data->getItems()[$itemName],
                'character_name' => $characterName,
                'characterId' => $characterName,
                'itemId' => $itemId,
                'skills' => $skills,
            ];
        }, array_filter(explode(',', $characterData['necklace'] ?? '')));

        $hindrances = array_map(function ($itemId) use ($characterName) {
            return [...$this->game->data->getExpansion()[$itemId], 'character_name' => $characterName, 'characterId' => $characterName];
        }, array_filter(explode(',', $characterData['hindrance'] ?? '')));
        $characterData['mentalHindrance'] = array_values(
            array_filter($hindrances, function ($hindrance) {
                return $hindrance['deck'] == 'mental-hindrance';
            })
        );
        $characterData['physicalHindrance'] = array_values(
            array_filter($hindrances, function ($hindrance) {
                return $hindrance['deck'] == 'physical-hindrance';
            })
        );

        $characterData['equipment'] = array_map(function ($itemId) use ($isActive, $characterName, $itemsLookup) {
            $itemName = $itemsLookup[$itemId];
            $skills = [];
            if (array_key_exists('skills', $this->game->data->getItems()[$itemName])) {
                array_walk($this->game->data->getItems()[$itemName]['skills'], function ($v, $k) use (
                    $itemId,
                    $itemName,
                    $characterName,
                    &$skills
                ) {
                    $skillId = $k . '_' . $itemId;
                    $v['id'] = $skillId;
                    $v['itemId'] = $itemId;
                    $v['itemName'] = $itemName;
                    $v['characterId'] = $characterName;
                    $skills[$skillId] = $v;
                });
            }

            return [
                'itemId' => $itemId,
                'isActive' => $isActive,
                ...$this->game->data->getItems()[$itemName],
                'skills' => $skills,
                'character_name' => $characterName,
                'characterId' => $characterName,
            ];
        }, array_values(array_filter([$characterData['item_1'], $characterData['item_2'], $characterData['item_3']])));
        if (!$_skipHooks) {
            $this->game->hooks->onGetCharacterData($characterData);
        }
        $characterData['maxStamina'] = clamp($characterData['maxStamina'], 0, 10);
        $characterData['maxHealth'] = clamp($characterData['maxHealth'], 0, 10);
        $characterData['health'] = clamp($characterData['health'], 0, $characterData['maxHealth']);
        $characterData['stamina'] = clamp($characterData['stamina'], 0, $characterData['maxStamina']);
        return $characterData;
    }
    public function getCharacterData(string $name, $_skipHooks = false): array
    {
        if (array_key_exists($name, $this->cachedData)) {
            return $this->getCalculatedData($this->cachedData[$name], $_skipHooks);
        } else {
            $this->cachedData[$name] = $this->game->getCollectionFromDb(
                "SELECT c.*, player_color FROM `character` c INNER JOIN `player` p ON p.player_id = c.player_id WHERE character_name = '$name'"
            )[$name];
            return $this->getCalculatedData($this->cachedData[$name], $_skipHooks);
        }
    }
    public function getItemValidations(int $itemId, array $character, ?int $removingItemId = null)
    {
        $items = $this->game->gameData->getItems();
        $item = $items[$itemId];
        $itemName = $this->game->data->getItems()[$item]['id'];
        $itemType = $this->game->data->getItems()[$item]['itemType'];
        $slotsAllowed = array_count_values($character['slots']);
        $equipment = array_values(
            array_filter($character['equipment'], function ($d) use ($removingItemId) {
                return $d['itemId'] != $removingItemId;
            })
        );
        $slotsUsed = array_count_values(
            array_map(function ($d) {
                return $d['itemType'];
            }, $equipment)
        );
        $hasOpenSlots =
            (array_key_exists($itemType, $slotsAllowed) ? $slotsAllowed[$itemType] : 0) -
                (array_key_exists($itemType, $slotsUsed) ? $slotsUsed[$itemType] : 0) >
            0;
        $hasDuplicateTool =
            sizeof(
                array_filter($equipment, function ($d) use ($itemName) {
                    return $d['id'] == $itemName;
                })
            ) > 0;
        return ['hasOpenSlots' => $hasOpenSlots, 'hasDuplicateTool' => $hasDuplicateTool];
    }
    public function equipEquipment(string $characterName, array $items): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($items) {
            $equippedIds = array_map(function ($d) {
                return $d['itemId'];
            }, $data['equipment']);
            $equipment = [...$equippedIds, ...$items];
            $data['equipment'] = $equipment;
        });
    }
    public function unequipEquipment(string $characterName, array $items): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($items) {
            $equippedIds = array_map(function ($d) {
                return $d['itemId'];
            }, $data['equipment']);
            $equipment = array_diff($equippedIds, array_intersect($equippedIds, $items));
            $data['equipment'] = $equipment;
        });
    }
    public function setCharacterEquipment(string $characterName, array $equipment): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($equipment) {
            $data['equipment'] = $equipment;
        });
    }
    public function addHindrance(string $characterName, array $card): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($card) {
            array_push($data[$card['deck'] == 'physical-hindrance' ? 'physicalHindrance' : 'mentalHindrance'], $card);
        });
        $this->game->decks->removeFromDeck($card['deck'], $card['id']);
        $this->game->hooks->onAcquireHindrance($card);
        $this->game->activeCharacterEventLog('${acquireSentence} ${name}', [
            'acquireSentence' => $card['acquireSentence'],
            'name' => $card['name'],
            'character_name' => $this->game->getCharacterHTML($characterName),
        ]);
    }
    public function removeHindrance(string $characterName, array $card): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($card) {
            $data[$card['deck'] == 'physical-hindrance' ? 'physicalHindrance' : 'mentalHindrance'] = array_filter(
                $data[$card['deck'] == 'physical-hindrance' ? 'physicalHindrance' : 'mentalHindrance'],
                function ($hindrance) use ($card) {
                    return $hindrance['id'] != $card['id'];
                }
            );
        });
        $this->game->activeCharacterEventLog('no longer ${dropSentence} ${cardName}', [
            'dropSentence' => $card['dropSentence'],
            'cardName' => $card['name'],
            'character_name' => $this->game->getCharacterHTML($characterName),
        ]);
        $this->game->decks->addBackToDeck($card['deck'], $card['id']);
    }
    public function setSubmittingCharacter(?string $action, ?string $subAction = null): void
    {
        if ($action == 'actUseSkill') {
            $skillData = $this->getSkill($subAction);
            if ($skillData && !array_key_exists('global', $skillData['skill'])) {
                $this->submittingCharacter = $this->getSkill($subAction)['character']['id'];
            } else {
                $this->submittingCharacter = null;
            }
        } elseif ($action == 'actUseItem') {
            $skillData = $this->getSkill($subAction);
            if ($skillData && array_key_exists('character', $skillData)) {
                $this->submittingCharacter = $skillData['character']['id'];
            } else {
                $this->submittingCharacter = null;
            }
        } elseif ($action == null) {
            $this->submittingCharacter = null;
        }
    }
    public function setSubmittingCharacterById(string $characterId): void
    {
        $this->submittingCharacter = $characterId;
    }
    public function getSkill(string $skillId): ?array
    {
        $characters = $this->getAllCharacterData(true);
        $currentCharacter = $this->getTurnCharacter(true);
        foreach ($characters as $k => $v) {
            if (array_key_exists('skills', $v)) {
                if (array_key_exists($skillId, $v['skills'])) {
                    return ['character' => $v, 'skill' => $v['skills'][$skillId]];
                }
            }
            foreach ([...$v['equipment'], ...$v['necklaces']] as $k => $equipment) {
                if (array_key_exists('skills', $equipment)) {
                    if (array_key_exists($skillId, $equipment['skills'])) {
                        return ['character' => $v, 'skill' => $equipment['skills'][$skillId]];
                    }
                }
            }
        }
        $buildings = $this->game->gameData->get('buildings');
        foreach ($buildings as $k => $building) {
            $data = $this->game->data->getItems()[$building['name']];
            if (array_key_exists('skills', $data)) {
                if (array_key_exists($skillId, $data['skills'])) {
                    return ['character' => $currentCharacter, 'skill' => $data['skills'][$skillId]];
                }
            }
        }
        foreach ($this->game->data->getExpansion() as $k => $expansion) {
            if (array_key_exists('deckType', $expansion) && $expansion['deckType'] == 'day-event') {
                if (array_key_exists('skills', $expansion)) {
                    if (array_key_exists($skillId, $expansion['skills'])) {
                        return ['character' => $currentCharacter, 'skill' => $expansion['skills'][$skillId]];
                    }
                }
            }
        }
        foreach ($this->game->data->getKnowledgeTree() as $k => $knowledgeTree) {
            if (array_key_exists('skills', $knowledgeTree)) {
                if (array_key_exists($skillId, $knowledgeTree['skills'])) {
                    return ['character' => $currentCharacter, 'skill' => $knowledgeTree['skills'][$skillId]];
                }
            }
        }
        return null;
    }
    // public function getItem($itemId): ?array
    // {
    //     $characters = $this->getAllCharacterData(true);
    //     foreach ($characters as $k => $v) {
    //         $array = array_values(
    //             array_filter($v['equipment'], function ($item) use ($itemId) {
    //                 return $item['itemId'] == $itemId;
    //             })
    //         );
    //         if (sizeof($array) > 0) {
    //             return ['character' => $v, 'item' => $$array[0]];
    //         }
    //     }
    //     return null;
    // }
    public function getSubmittingCharacterId(): string
    {
        return $this->submittingCharacter ? $this->submittingCharacter : $this->getTurnCharacterId();
    }
    public function getSubmittingCharacter(bool $_skipHooks = false): array
    {
        return $this->submittingCharacter
            ? $this->getCharacterData($this->submittingCharacter, $_skipHooks)
            : $this->getTurnCharacter($_skipHooks);
    }
    public function getTurnCharacterId(): ?string
    {
        extract($this->game->gameData->getAll('turnNo', 'turnOrder'));
        if (sizeof($turnOrder) == 4) {
            return $turnOrder[$turnNo ?? 0];
        } else {
            return null;
        }
    }
    public function getTurnCharacter(bool $_skipHooks = false): array
    {
        return $this->getCharacterData($this->getTurnCharacterId(), $_skipHooks);
    }
    public function getActiveEquipment(): array
    {
        $character = $this->getSubmittingCharacter();
        return $character['equipment'];
    }
    public function activateNextCharacter(): void
    {
        // Making the assumption that the functions are checking isLastCharacter()
        extract($this->game->gameData->getAll('turnNo', 'turnOrder'));
        if ($turnNo !== null) {
            $this->game->gameData->set('turnNo', $turnNo + 1);
            $character = $turnOrder[$turnNo + 1];
            $turnNo = $turnNo + 1;
        } else {
            $this->game->gameData->set('turnNo', 0);
            $character = $turnOrder[0];
            $turnNo = 0;
        }
        $characterData = $this->getCharacterData($character);

        $playerId = (int) $this->game->getActivePlayerId();
        if ($playerId != $characterData['player_id']) {
            $this->game->gamestate->changeActivePlayer($characterData['player_id']);
            $this->addExtraTime();
        }
        $this->game->markChanged('player');
    }
    public function isLastCharacter()
    {
        extract($this->game->gameData->getAll('turnNo', 'turnOrder'));
        return sizeof($turnOrder) == ($turnNo ?? 0) + 1;
    }
    public function rotateTurnOrder(): void
    {
        $turnOrder = $this->game->gameData->get('turnOrder');
        $temp = array_shift($turnOrder);
        array_push($turnOrder, $temp);
        $this->game->gameData->set('turnOrder', $turnOrder);
        $this->game->gameData->set('turnNo', null);
        $this->game->markChanged('player');
    }

    public function getActiveStamina(): int
    {
        return (int) $this->getSubmittingCharacter()['stamina'];
    }
    public function _adjustStamina(array &$data, int $staminaChange, &$prev, $characterName): bool
    {
        $prev = $data['stamina'];
        $hookData = [
            'currentStamina' => $prev,
            'change' => $staminaChange,
            'characterId' => $characterName,
            'maxStamina' => $data['maxStamina'],
        ];
        $this->game->hooks->onAdjustStamina($hookData);
        $data['stamina'] = clamp($data['stamina'] + $hookData['change'], 0, $data['maxStamina']);
        $prev = $data['stamina'] - $prev;
        return $prev == 0;
    }
    public function adjustAllStamina(int $staminaChange): void
    {
        $prev = 0;
        $this->updateAllCharacterData(function (&$data) use ($staminaChange, &$prev) {
            return $this->_adjustStamina($data, $staminaChange, $prev, $data['id']);
        });
    }
    public function adjustStamina(string $characterName, int $staminaChange): int
    {
        $prev = 0;
        $this->updateCharacterData($characterName, function (&$data) use ($staminaChange, &$prev, $characterName) {
            return $this->_adjustStamina($data, $staminaChange, $prev, $characterName);
        });
        return $prev;
    }
    public function adjustActiveStamina(int $stamina): int
    {
        $characterName = $this->getSubmittingCharacter()['character_name'];
        return $this->adjustStamina($characterName, $stamina);
    }
    public function getActiveHealth(): int
    {
        return (int) $this->getSubmittingCharacter()['health'];
    }

    public function _adjustHealth(array &$data, $healthChange, &$prev, $characterName): bool
    {
        if ($data['incapacitated'] && $healthChange > 0) {
            return true;
        }
        $prev = $data['health'];
        $hookData = [
            'currentHealth' => $prev,
            'change' => $healthChange,
            'characterId' => $characterName,
            'maxHealth' => $data['maxHealth'],
        ];
        $this->game->hooks->onAdjustHealth($hookData);
        $data['health'] = clamp($data['health'] + $hookData['change'], 0, $data['maxHealth']);
        $prev = $data['health'] - $prev;
        if ($data['health'] == 0 && !$data['incapacitated']) {
            $this->game->activeCharacterEventLog('is incapacitated', [
                'character_name' => $this->game->getCharacterHTML($characterName),
            ]);
            $data['incapacitated'] = true;
            $data['stamina'] = 0;
            if ($data['isActive'] && $this->game->gamestate->state()['name'] == 'playerTurn') {
                $this->game->endTurn();
            }
            $hookData = [
                'characterId' => $characterName,
            ];
            $this->game->hooks->onIncapacitation($hookData);
            return false;
        } else {
            return $prev == 0;
        }
    }
    public function adjustAllHealth(int $healthChange): void
    {
        $prev = 0;
        $this->updateAllCharacterData(function (&$data) use ($healthChange, &$prev) {
            return $this->_adjustHealth($data, $healthChange, $prev, $data['id']);
        });
    }
    public function adjustHealth(string $characterName, int $healthChange): int
    {
        $prev = 0;
        $this->updateCharacterData($characterName, function (&$data) use ($healthChange, &$prev, $characterName) {
            return $this->_adjustHealth($data, $healthChange, $prev, $characterName);
        });
        return $prev;
    }
    public function adjustActiveHealth(int $health): int
    {
        $characterName = $this->getSubmittingCharacter()['character_name'];
        return $this->adjustHealth($characterName, $health);
    }
    public function getMarshallCharacters()
    {
        return array_map(function ($char) {
            $slotsAllowed = array_count_values($char['slots']);
            $slotsUsed = array_count_values(
                array_map(function ($d) {
                    return $d['itemType'];
                }, $char['equipment'])
            );
            return [
                'name' => $char['character_name'],
                'isFirst' => $char['isFirst'],
                'isActive' => $char['isActive'],
                'equipment' => $char['equipment'],
                'playerColor' => $char['player_color'],
                'playerId' => $char['player_id'],
                'stamina' => $char['stamina'],
                'maxStamina' => $char['maxStamina'],
                'maxHealth' => $char['maxHealth'],
                'dayEvent' => $char['dayEvent'],
                'mentalHindrance' => $char['mentalHindrance'],
                'physicalHindrance' => $char['physicalHindrance'],
                'necklaces' => $char['necklaces'],
                'health' => $char['health'],
                'incapacitated' => !!$char['incapacitated'],
                'slotsUsed' => $slotsUsed,
                'slotsAllowed' => $slotsAllowed,
            ];
        }, $this->getAllCharacterData());
    }
}
