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
        'confirmed',
        'incapacitated',
    ];

    public function __construct($game)
    {
        $this->game = $game;
    }
    public function addExtraTime(?int $extraTime = null)
    {
        $this->game->giveExtraTime($this->getTurnCharacter()['player_id'], $extraTime);
    }
    public function updateCharacterData($name, $callback)
    {
        // Pull from db if needed
        $data = $this->getCharacterData($name, false);
        if (!$callback($data)) {
            // Update db
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
            $this->game->notify->all('updateGameData', '', [
                'gameData' => $this->game->getAllDatas(),
            ]);
        }
    }
    public function updateAllCharacterData($callback)
    {
        $turnOrder = $this->game->gameData->get('turnOrder');
        $turnOrder = array_values(array_filter($turnOrder));
        $hasUpdate = false;
        foreach ($turnOrder as $i => $name) {
            // Pull from db if needed
            $data = $this->getCharacterData($name, false);
            if (!$callback($data)) {
                $hasUpdate = true;
                // Update db
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
            }
        }
        if ($hasUpdate) {
            $this->game->notify->all('updateGameData', '', [
                'gameData' => $this->game->getAllDatas(),
            ]);
        }
    }
    public function getAllCharacterData($_skipHooks = false): array
    {
        $turnOrder = $this->game->gameData->get('turnOrder');
        $turnOrder = array_values(array_filter($turnOrder));
        return array_map(function ($char) use ($_skipHooks) {
            return $this->getCharacterData($char, $_skipHooks);
        }, $turnOrder);
    }
    public function getAllCharacterDataForPlayer($playerId): array
    {
        return array_values(
            array_filter($this->game->character->getAllCharacterData(), function ($char) use ($playerId) {
                return $char['player_id'] == $playerId;
            })
        );
    }
    public function getCalculatedData($characterData, $_skipHooks = false): array
    {
        extract($this->game->gameData->getAll('turnNo', 'turnOrder'));
        $turnOrder = array_values(array_filter($turnOrder));
        $characterName = $characterData['character_name'];
        $isActive = $turnOrder[$turnNo ?? 0] == $characterName;
        $characterData['isActive'] = $isActive;
        $characterData['isFirst'] = array_key_exists(0, $turnOrder) && $turnOrder[0] == $characterName;
        $characterData['id'] = $characterName;
        $underlyingCharacterData = $this->game->data->characters[$characterData['id']];
        $characterData['maxStamina'] = $underlyingCharacterData['stamina'];
        $characterData['maxHealth'] = $underlyingCharacterData['health'];

        array_walk($underlyingCharacterData, function ($v, $k) use (&$characterData) {
            if (str_starts_with($k, 'on') || in_array($k, ['slots', 'skills'])) {
                $characterData[$k] = $v;
            }
        });
        $_this = $this;
        $itemsLookup = $this->game->gameData->getItems();
        $characterData['equipment'] = array_map(function ($itemId) use ($_this, $isActive, $characterName, $itemsLookup) {
            $itemName = $itemsLookup[$itemId];
            $skills = [];
            if (array_key_exists('skills', $_this->game->data->items[$itemName])) {
                array_walk($_this->game->data->items[$itemName]['skills'], function ($v, $k) use (
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
                ...$_this->game->data->items[$itemName],
                'skills' => $skills,
                'character_name' => $characterName,
            ];
        }, array_values(array_filter([$characterData['item_1'], $characterData['item_2'], $characterData['item_3']])));
        if (!$_skipHooks) {
            $this->game->hooks->onGetCharacterData($characterData);
        }
        return $characterData;
    }
    public function getCharacterData($name, $_skipHooks = false): array
    {
        if (array_key_exists($name, $this->cachedData)) {
            return $this->getCalculatedData($this->cachedData[$name], $_skipHooks);
        } else {
            $characterData = $this->getCalculatedData(
                $this->game->getCollectionFromDb(
                    "SELECT c.*, player_color FROM `character` c INNER JOIN `player` p ON p.player_id = c.player_id WHERE character_name = '$name'"
                )[$name],
                $_skipHooks
            );
            $this->cachedData[$name] = $characterData;
            return $characterData;
        }
    }
    public function getItemValidations($itemId, array $character, $removingItemId = null)
    {
        $items = $this->game->gameData->getItems();
        $item = $items[$itemId];
        $itemName = $this->game->data->items[$item]['id'];
        $itemType = $this->game->data->items[$item]['itemType'];
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
            $data['item_1'] = array_key_exists(0, $equipment) ? $equipment[0] : null;
            $data['item_2'] = array_key_exists(1, $equipment) ? $equipment[1] : null;
            $data['item_3'] = array_key_exists(2, $equipment) ? $equipment[2] : null;
        });
    }
    public function unequipEquipment(string $characterName, array $items): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($items) {
            $equippedIds = array_map(function ($d) {
                return $d['itemId'];
            }, $data['equipment']);
            $equipment = array_diff($equippedIds, array_intersect($equippedIds, $items));
            $data['item_1'] = array_key_exists(0, $equipment) ? $equipment[0] : null;
            $data['item_2'] = array_key_exists(1, $equipment) ? $equipment[1] : null;
            $data['item_3'] = array_key_exists(2, $equipment) ? $equipment[2] : null;
        });
    }
    public function setCharacterEquipment($characterName, $equipment): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($equipment) {
            $data['item_1'] = array_key_exists(0, $equipment) ? $equipment[0] : null;
            $data['item_2'] = array_key_exists(1, $equipment) ? $equipment[1] : null;
            $data['item_3'] = array_key_exists(2, $equipment) ? $equipment[2] : null;
        });
    }
    public function setSubmittingCharacter(?string $action, ?string $subAction = null): void
    {
        if ($action == 'actUseSkill') {
            $this->submittingCharacter = $this->game->character->getSkill($subAction)['character']['id'];
        } elseif ($action == 'actUseItem') {
            $skill = $this->game->character->getSkill($subAction);
            if ($skill && array_key_exists('character', $skill)) {
                $this->submittingCharacter = $this->game->character->getSkill($subAction)['character']['id'];
            } else {
                $this->submittingCharacter = null;
            }
        } elseif ($action == null) {
            $this->submittingCharacter = null;
        }
    }
    public function getSkill($skillId): ?array
    {
        $characters = $this->game->character->getAllCharacterData(true);
        $currentCharacter = $this->game->character->getTurnCharacter(true);
        foreach ($characters as $k => $v) {
            if (array_key_exists('skills', $v)) {
                if (array_key_exists($skillId, $v['skills'])) {
                    return ['character' => $v, 'skill' => $v['skills'][$skillId]];
                }
            }
            foreach ($v['equipment'] as $k => $equipment) {
                if (array_key_exists('skills', $equipment)) {
                    if (array_key_exists($skillId, $equipment['skills'])) {
                        return ['character' => $v, 'skill' => $equipment['skills'][$skillId]];
                    }
                }
            }
        }
        $buildings = $this->game->gameData->get('buildings');
        foreach ($buildings as $k => $building) {
            $data = $this->game->data->items[$building['name']];
            if (array_key_exists('skills', $data)) {
                if (array_key_exists($skillId, $data['skills'])) {
                    return ['character' => $currentCharacter, 'skill' => $data['skills'][$skillId]];
                }
            }
        }
        return null;
    }
    // public function getItem($itemId): ?array
    // {
    //     $characters = $this->game->character->getAllCharacterData(true);
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
    public function getSubmittingCharacter($_skipHooks = false): array
    {
        return $this->submittingCharacter
            ? $this->getCharacterData($this->submittingCharacter, $_skipHooks)
            : $this->game->character->getTurnCharacter($_skipHooks);
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
    public function getTurnCharacter($_skipHooks = false): array
    {
        return $this->getCharacterData($this->getTurnCharacterId(), $_skipHooks);
    }
    public function getActiveEquipment(): array
    {
        $character = $this->getSubmittingCharacter();
        return $character['equipment'];
    }
    public function getActiveEquipmentSkills()
    {
        $character = $this->game->character->getSubmittingCharacter();
        $buildings = $this->game->getBuildings();
        $skills = array_merge(
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $buildings),
            ...array_map(function ($item) {
                if (!array_key_exists('skills', $item)) {
                    return [];
                }
                return $item['skills'];
            }, $character['equipment'])
        );
        return $skills;
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
            $this->game->character->addExtraTime();
        }
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
        $this->game->log('turn order', $turnOrder);
    }

    public function getActiveStamina(): int
    {
        return (int) $this->getSubmittingCharacter()['stamina'];
    }
    public function adjustAllStamina(int $stamina): void
    {
        $this->updateAllCharacterData(function (&$data) use ($stamina) {
            $data['stamina'] = max(min($data['stamina'] + $stamina, $data['maxStamina']), 0);
        });
    }
    public function adjustStamina(string $characterName, int $stamina): int
    {
        $prev = 0;
        $_this = $this;
        $this->updateCharacterData($characterName, function (&$data) use ($_this, $stamina, &$prev) {
            $_this->game->hooks->onAdjustStamina($stamina);
            $prev = $data['stamina'];
            $data['stamina'] = max(min($data['stamina'] + $stamina, $data['maxStamina']), 0);
            $prev = $data['stamina'] - $prev;
            $this->game->log('stamina', $data['stamina']);
            return $prev == 0;
        });
        return $prev;
    }
    public function adjustActiveStamina(int $stamina): int
    {
        $characterName = $this->getSubmittingCharacter()['character_name'];
        $this->game->log('$cost', $characterName, $stamina);
        $this->game->hooks->onAdjustStamina($stamina);
        return $this->adjustStamina($characterName, $stamina);
    }
    public function getActiveHealth(): int
    {
        return (int) $this->getSubmittingCharacter()['health'];
    }

    public function adjustAllHealth(int $health): void
    {
        $this->updateAllCharacterData(function (&$data) use ($health) {
            $data['health'] = max(min($data['health'] + $health, $data['maxHealth']), 0);
        });
    }
    public function adjustHealth(string $characterName, int $health): int
    {
        $prev = 0;
        $this->updateCharacterData($characterName, function (&$data) use ($health, &$prev, $characterName) {
            if ($data['incapacitated'] && $health > 0) {
                return;
            }
            $prev = $data['health'];
            $data['health'] = max(min($data['health'] + $health, $data['maxHealth']), 0);
            $prev = $data['health'] - $prev;
            if ($data['health'] == 0 && !$data['incapacitated']) {
                $this->game->activeCharacterEventLog('has been incapacitated', [
                    'character_name' => $this->game->getCharacterHTML($characterName),
                ]);
                $data['incapacitated'] = true;
                $data['stamina'] = 0;
                if ($data['isActive'] && $this->game->gamestate->state()['name'] == 'playerTurn') {
                    $this->game->gamestate->nextState('endTurn');
                }
                $hookData = [
                    'characterId' => $characterName,
                ];
                $this->game->hooks->onIncapacitation($hookData);
            } else {
                return $prev == 0;
            }
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
                'health' => $char['health'],
                'incapacitated' => !!$char['incapacitated'],
                'slotsUsed' => $slotsUsed,
                'slotsAllowed' => $slotsAllowed,
            ];
        }, $this->getAllCharacterData());
    }
}
