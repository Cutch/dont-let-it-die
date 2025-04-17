<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DontLetItDie implementation : © Cutch <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
use Bga\GameFramework\Actions\Types\JsonParam;

use BgaUserException;
use ErrorException;
use Exception;
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});
require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';
include_once dirname(__DIR__) . '/php/Data.php';
include_once dirname(__DIR__) . '/php/Actions.php';
include_once dirname(__DIR__) . '/php/CharacterSelection.php';
include_once dirname(__DIR__) . '/php/Character.php';
include_once dirname(__DIR__) . '/php/GameData.php';
class Game extends \Table
{
    public Character $character;
    public Actions $actions;
    private CharacterSelection $characterSelection;
    public Data $data;
    public Decks $decks;
    public GameData $gameData;
    public Hooks $hooks;
    public Encounter $encounter;
    public ItemTrade $itemTrade;
    public ActInterrupt $actInterrupt;
    public static array $expansionList = ['base', 'mini-expansion', 'hindrance', 'death-valley'];
    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `gameData->set` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels([
            'expansion' => 100,
            'difficulty' => 101,
            'trackDifficulty' => 102,
        ]);
        $this->actions = new Actions($this);
        $this->data = new Data($this);
        $this->decks = new Decks($this);
        $this->character = new Character($this);
        $this->characterSelection = new CharacterSelection($this);
        $this->gameData = new GameData($this);
        $this->hooks = new Hooks($this);
        $this->encounter = new Encounter($this);
        $this->itemTrade = new ItemTrade($this);
        $this->actInterrupt = new ActInterrupt($this);
        // automatically complete notification args when needed
        $this->notify->addDecorator(function (string $message, array $args) {
            $args['gamestate'] = ['name' => $this->gamestate->state()['name']];
            if (!array_key_exists('character_name', $args) && str_contains($message, '${character_name}')) {
                $args['character_name'] = $this->getCharacterHTML();
            }
            if (!array_key_exists('player_name', $args) && str_contains($message, '${player_name}')) {
                if (array_key_exists('player_id', $args)) {
                    $args['player_name'] = $this->getPlayerNameById($args['player_id']);
                } elseif (array_key_exists('character_name', $args)) {
                    $playerId = (int) $this->character->getCharacterData($args['character_name'])['player_id'];
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                } else {
                    $playerId = (int) $this->getActivePlayerId();
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                }
            }
            if (str_contains($message, '${resource_type}')) {
                $args['resource_type'] = $this->data->tokens[$args['resource_type']]['name'];
            }
            if (array_key_exists('character_resource', $args)) {
                $args['character_resource'] = clienttranslate($args['character_resource']);
            }
            return $args;
        });
    }
    public function getCharacterHTML(?string $name = null)
    {
        if ($name) {
            $char = $this->character->getCharacterData($name);
        } else {
            $char = $this->character->getSubmittingCharacter();
            $name = $char['character_name'];
        }
        $playerName = $this->getPlayerNameById($char['player_id']);
        $playerColor = $char['player_color'];
        return "<!--PNS--><span class=\"playername\" style=\"color:#$playerColor;\">$name ($playerName)</span><!--PNE-->";
    }
    function costToString($cost): string
    {
        return join(
            ', ',
            array_values(
                array_map(
                    function ($k, $v) {
                        return $v . ' ' . $this->data->tokens[$k]['name'];
                    },
                    array_keys($cost),
                    $cost
                )
            )
        );
    }
    public function initDeck($type = 'card')
    {
        $deck = $this->getNew('module.common.deck');
        $deck->autoreshuffle = true;
        $deck->init($type);
        return $deck;
    }
    public function getCurrentPlayer(bool $bReturnNullIfNotLogged = false): string|int
    {
        return parent::getCurrentPlayerId($bReturnNullIfNotLogged);
    }
    public function translate(string $str)
    {
        return $this->_($str);
    }
    public function activeCharacterEventLog($message = '', $arg = [], $translatedMessage = '')
    {
        // $result = [
        //     'character_name' => $this->getCharacterHTML(),
        //     ...$arg,
        // ];
        // $this->getAllCharacters($result);
        // $this->getAllPlayers($result);
        $this->notify->all('activeCharacter', clienttranslate('${character_name} ' . $message) . $translatedMessage, [
            ...$arg,
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function nightEventLog($message, $arg = [])
    {
        $this->notify->all('nightEvent', clienttranslate($message), $arg);
    }
    public function checkHindrance($drawPhysical = true, ?string $char = null): bool
    {
        $data = ['maxPhysicalHindrance' => 3, 'maxMentalHindrance' => 1, 'canDrawMentalHindrance' => true];
        if (!$char) {
            $char = $this->character->getSubmittingCharacter(true);
        } else {
            $char = $this->character->getCharacterData($char, true);
        }
        $this->hooks->onMaxHindrance($data);
        $deckType = 'physical-hindrance';
        if (sizeof($char['physicalHindrance']) == $data['maxPhysicalHindrance']) {
            // Skip removal and hindrance draw if mental hindrance is maxed out
            if (!$data['canDrawMentalHindrance'] || sizeof($char['mentalHindrance']) >= $data['maxMentalHindrance']) {
                return true;
            }
            $deckType = 'mental-hindrance';
            foreach ($char['physicalHindrance'] as $card) {
                $this->character->removeHindrance($char['character_name'], $card);
            }
        }
        if ($deckType == 'physical-hindrance' && !$drawPhysical) {
            return true;
        }
        if ($deckType != 'mental-hindrance' || $data['canDrawMentalHindrance']) {
            $card = $this->decks->pickCard($deckType);
            if ($card) {
                $this->character->addHindrance($char['character_name'], $card);
            } else {
                return true;
            }
        } else {
            return true;
        }
        return false;
    }
    public function deckSelection(?array $decks = null, ?string $title = null, $cancellable = true)
    {
        if ($decks == null) {
            $decks = $this->decks->getAllDeckNames();
        }
        $this->gameData->set('deckSelection', ['decks' => array_values($decks), 'title' => $title, 'cancellable' => $cancellable]);
        $this->gamestate->nextState('deckSelection');
    }
    // $type = #, each, any
    public function hindranceSelection(string $id, ?array $characters = null, ?string $button = null)
    {
        if ($characters == null) {
            $characters = [$this->character->getTurnCharacterId()];
        }
        $characters = array_values(
            array_map(
                function ($d) {
                    return ['physicalHindrance' => $d['physicalHindrance'], 'characterId' => $d['character_name']];
                },
                array_filter($this->character->getAllCharacterData(), function ($d) use ($characters) {
                    return in_array($d['id'], $characters);
                })
            )
        );
        $this->gameData->set('hindranceSelectionState', ['id' => $id, 'characters' => $characters, 'button' => $button]);
        $this->gamestate->nextState('hindranceSelection');
    }
    public function cardDrawEvent($card, $deck, $arg = [])
    {
        $result = [
            'card' => $card,
            'deck' => $deck,
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
            ...$arg,
        ];
        $this->getDecks($result);
        $this->notify->all('cardDrawn', '', $result);
        $partials = $this->gameData->get('partials');
        if (array_key_exists('partial', $arg) && $arg['partial']) {
            $partials[$deck] = $card;
            $this->gameData->set('partials', $partials);
        } elseif (array_key_exists($deck, $partials)) {
            unset($partials[$deck]);
            $this->gameData->set('partials', $partials);
        }
    }
    public function getMaxBuildingCount()
    {
        $data = ['count' => 1];
        $this->hooks->onGetMaxBuildingCount($data);
        return $data['count'];
    }
    public function getTradeRatio($checkOnly = true)
    {
        $data = ['ratio' => 3, 'checkOnly' => $checkOnly];
        $this->hooks->onGetTradeRatio($data);
        return $data['ratio'];
    }
    public function adjustResource($resourceType, int $change): array
    {
        $currentCount = (int) $this->gameData->getResource($resourceType);
        $maxCount = $this->gameData->getResourceMax($resourceType);
        $rawResourceType = str_replace('-cooked', '', $resourceType);
        if (array_key_exists($resourceType . '-cooked', $this->data->tokens)) {
            $maxCount -= (int) $this->gameData->getResource($resourceType . '-cooked');
        } elseif ($rawResourceType != $resourceType && array_key_exists($rawResourceType, $this->data->tokens)) {
            $maxCount -= (int) $this->gameData->getResource($rawResourceType);
        }
        if ($resourceType == 'wood') {
            $maxCount -= $this->gameData->getResource('fireWood');
        }
        $newValue = clamp($currentCount + $change, 0, $maxCount);
        $this->gameData->setResource($resourceType, $newValue);
        $difference = $currentCount - $newValue + $change;
        return ['left' => $difference, 'changed' => $newValue - $currentCount];
    }
    public function rollFireDie(string $actionName, ?string $characterName = null): int
    {
        $rand = rand(1, 6);
        $value = 0;
        if ($rand == 6) {
            $value = 3;
        } elseif ($rand == 5) {
            $value = 2;
        } elseif ($rand > 1) {
            $value = 1;
        }
        $notificationSent = false;
        $data = [
            'value' => $value,
        ];
        $data['sendNotification'] = function () use ($data, $characterName, &$notificationSent, $actionName) {
            $sideNum = $data['value'] == 0 ? 1 : ($data['value'] == 3 ? 6 : ($data['value'] == 2 ? 5 : 2));
            if ($characterName) {
                $this->notify->all('rollFireDie', clienttranslate('${character_name} rolled a ${value} ${action_name}'), [
                    'value' => $data['value'] == 0 ? clienttranslate('blank') : $data['value'],
                    'character_name' => $this->getCharacterHTML($characterName),
                    'roll' => $sideNum,
                    'action_name' => '(' . $actionName . ')',
                ]);
            } else {
                $this->notify->all('rollFireDie', clienttranslate('The fire die rolled a ${value} ${action_name}'), [
                    'value' => $data['value'] == 0 ? clienttranslate('blank') : $data['value'],
                    'roll' => $sideNum,
                    'action_name' => '(' . $actionName . ')',
                ]);
            }
            $notificationSent = true;
        };
        $this->hooks->onRollDie($data);
        $data['value'] = max($data['value'], 0);
        if (!$notificationSent) {
            $data['sendNotification']();
        }
        return $value;
    }
    public function addExtraTime(?int $extraTime = null)
    {
        $this->giveExtraTime($this->getCurrentPlayer(), $extraTime);
    }
    public function actCharacterClicked(
        ?string $character1 = null,
        ?string $character2 = null,
        ?string $character3 = null,
        ?string $character4 = null
    ): void {
        $this->characterSelection->actCharacterClicked($character1, $character2, $character3, $character4);
    }
    public function actChooseCharacters(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->addExtraTime();
        }
        $this->characterSelection->actChooseCharacters();
    }
    public function actMoveDiscovery(string $upgradeId, string $upgradeReplaceId): void
    {
        $selectableUpgrades = array_keys(
            array_filter($this->data->boards['knowledge-tree-' . $this->getDifficulty()]['track'], function ($v) {
                return !array_key_exists('upgradeType', $v);
            })
        );
        $upgrades = $this->gameData->get('upgrades');
        // This is a swap
        if (in_array($upgradeId, $selectableUpgrades) && in_array($upgradeReplaceId, $selectableUpgrades)) {
            $keys = [];
            array_walk($upgrades, function ($upgrade, $k) use ($upgradeId, $upgradeReplaceId, &$keys) {
                if ($upgrade['replace'] == $upgradeId || $upgrade['replace'] == $upgradeReplaceId) {
                    array_push($keys, $k);
                }
            });
            if (sizeof($keys) == 2) {
                $temp = $upgrades[$keys[0]];
                $upgrades[$keys[0]] = $upgrades[$keys[1]];
                $upgrades[$keys[1]] = $temp;
            } else {
                $temp = $upgrades[$keys[0]]['replace'];
                if ($temp == $upgradeId) {
                    $upgrades[$keys[0]]['replace'] = $upgradeReplaceId;
                } else {
                    $upgrades[$keys[0]]['replace'] = $upgradeId;
                }
            }
        } else {
            array_walk($upgrades, function (&$upgrade) use ($upgradeReplaceId) {
                if ($upgrade['replace'] == $upgradeReplaceId) {
                    $upgrade['replace'] = null;
                }
            });
            $upgrades[$upgradeId]['replace'] = $upgradeReplaceId;
        }
        $this->gameData->set('upgrades', $upgrades);
        $result = [...$this->getAllDatas(), 'selectableUpgrades' => $selectableUpgrades];
        $this->notify->all('updateGameData', '', [
            'gameData' => $result,
        ]);
    }
    public function actCook(string $resourceType): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actCook', null, $resourceType);
        $data = [
            'resourceType' => $resourceType,
        ];
        $this->hooks->onCook($data);
        $this->adjustResource($resourceType, -1);
        $this->adjustResource($resourceType . '-cooked', 1);
        $this->actions->spendActionCost('actCook');

        $this->notify->all('tokenUsed', clienttranslate('${character_name} cooked ${amount} ${type}'), [
            'gameData' => $this->getAllDatas(),
            'amount' => 1,
            'type' => $resourceType,
        ]);
        $this->hooks->onCookAfter($data);
        $this->gameData->set('lastAction', 'actCook');
    }
    public function actRevive(?string $character, ?string $food): void
    {
        if (!$character) {
            throw new BgaUserException($this->translate('Select a character'));
        }
        if (!$food) {
            throw new BgaUserException($this->translate('Select a food'));
        }
        if (!$this->character->getCharacterData($character)['incapacitated']) {
            throw new BgaUserException($this->translate('That character is not incapacitated'));
        }
        $this->actions->validateCanRunAction('actRevive');
        $requireCount = array_values(
            array_filter($this->actions->getActionSelectable('actRevive'), function ($d) use ($food) {
                return $d['id'] == $food;
            })
        )[0]['actRevive']['count'];
        if ($food == 'meat-cooked') {
            $left = $this->adjustResource('fish-cooked', -$requireCount)['left'];
            $left = $this->adjustResource('meat-cooked', $left)['left'];
        } else {
            $left = $this->adjustResource('fish-cooked', -$requireCount);
        }
        if ($left != 0) {
            throw new BgaUserException($this->translate('Not enough resources'));
        }

        $this->character->updateCharacterData($character, function (&$data) {
            $data['health'] = clamp(3, 0, $data['maxHealth']);
            $this->log('actRevive', $data['health'], $data['incapacitated'], $data['maxHealth']);
        });
        $this->notify->all(
            'tokenUsed',
            clienttranslate('${character_name} revived ${character_name_2} they should be recovered by the morning'),
            [
                'gameData' => $this->getAllDatas(),
                'character_name_2' => $this->getCharacterHTML($character),
            ]
        );
        $this->gameData->set('lastAction', 'actRevive');
    }
    public function actSpendFKP(string $knowledgeId): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actSpendFKP', null);

        $resources = $this->actions->getActionSelectable('actSpendFKP');
        $variables = $this->gameData->getResources(...$resources);
        $resourceCount = array_sum(
            array_map(function ($type) use ($variables) {
                return $variables[$type];
            }, $resources)
        );
        $availableUnlocks = $this->data->getValidKnowledgeTree();

        if (!array_key_exists($knowledgeId, $availableUnlocks)) {
            throw new BgaUserException($this->translate('Requirements not met for this unlock'));
        } elseif (in_array($knowledgeId, $this->getUnlockedKnowledgeIds())) {
            throw new BgaUserException($this->translate('Already unlocked'));
        } elseif ($resourceCount < $availableUnlocks[$knowledgeId]['unlockCost']) {
            throw new BgaUserException($this->translate('Not enough knowledge points'));
        }
        $cost = -$availableUnlocks[$knowledgeId]['unlockCost'];
        foreach ($resources as $resource) {
            $cost = $this->adjustResource($resource, $cost)['left'];
        }

        $this->actions->spendActionCost('actSpendFKP');
        $this->unlockKnowledge($knowledgeId);
        $knowledgeObj = $this->data->knowledgeTree[$knowledgeId];
        array_key_exists('onUse', $knowledgeObj) ? $knowledgeObj['onUse']($this, $knowledgeObj) : null;
        $this->hooks->onUnlock($knowledgeObj);
        $this->notify->all('tokenUsed', clienttranslate('${character_name} unlocked ${knowledge_name}'), [
            'gameData' => $this->getAllDatas(),
            'knowledgeId' => $knowledgeId,
            'knowledge_name' => $this->data->knowledgeTree[$knowledgeId]['name'],
        ]);
        $this->gameData->set('lastAction', 'actSpendFKP');
    }
    public function actCraft(?string $itemName = null): void
    {
        // $this->character->addExtraTime();
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onCraft'],
            function (Game $_this) use ($itemName) {
                if (!$itemName) {
                    throw new BgaUserException($_this->translate('Select an item'));
                }
                $_this->actions->validateCanRunAction('actCraft', $itemName);
                if (!array_key_exists($itemName, $_this->data->items)) {
                    throw new BgaUserException($_this->translate('Invalid Item'));
                }
                $itemType = $_this->data->items[$itemName]['itemType'];
                $currentBuildings = $_this->gameData->get('buildings');
                if ($itemType == 'building' && sizeof($currentBuildings) > 0) {
                    throw new BgaUserException($_this->translate('A building has already been crafted'));
                }
                $result = [];
                $_this->getItemData($result);
                if (!isset($result['availableEquipment'][$itemName]) || $result['availableEquipment'][$itemName] == 0) {
                    throw new BgaUserException($_this->translate('All of those available items have been crafted'));
                }
                return [
                    'itemName' => $itemName,
                    'item' => $_this->data->items[$itemName],
                    'itemType' => $itemType,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $itemName = $data['itemName'];
                $item = $data['item'];
                $itemType = $data['itemType'];
                foreach ($item['cost'] as $key => $value) {
                    if ($_this->adjustResource($key, -$value)['left'] > 0) {
                        throw new BgaUserException($_this->translate('Missing resources'));
                    }
                }
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actCraft', $itemName);
                }
                if ($itemType == 'necklace') {
                    $itemId = $_this->gameData->createItem($itemName);
                    $_this->character->updateCharacterData($_this->character->getSubmittingCharacterId(), function (&$data) use ($itemId) {
                        array_push($data['necklaces'], ['itemId' => $itemId]);
                    });
                } elseif ($itemType == 'building') {
                    $currentBuildings = $_this->gameData->get('buildings');
                    $itemId = $_this->gameData->createItem($itemName);
                    array_push($currentBuildings, ['name' => $itemName, 'itemId' => $itemId]);
                    $_this->gameData->set('buildings', $currentBuildings);
                } else {
                    $itemId = $_this->gameData->createItem($itemName);
                    $character = $_this->character->getSubmittingCharacter();

                    $result = $this->character->getItemValidations($itemId, $character);
                    $hasOpenSlots = $result['hasOpenSlots'];
                    $hasDuplicateTool = $result['hasDuplicateTool'];
                    if ($hasOpenSlots && !$hasDuplicateTool) {
                        $_this->character->equipEquipment($character['id'], [$itemId]);
                    } else {
                        $existingItems = array_map(
                            function ($d) {
                                return ['name' => $d['id'], 'itemId' => $d['itemId']];
                            },
                            array_filter($character['equipment'], function ($d) use ($itemType, $hasDuplicateTool, $itemName) {
                                if ($hasDuplicateTool) {
                                    return $d['id'] == $itemName;
                                } else {
                                    return $d['itemType'] == $itemType;
                                }
                            })
                        );
                        $_this->gameData->set('tooManyItemsState', [
                            'itemType' => $itemType,
                            'items' => [...$existingItems, ['name' => $itemName, 'itemId' => $itemId]],
                        ]);
                        $_this->gamestate->nextState('tooManyItems');
                    }
                }
                $this->hooks->onCraftAfter($data);
                $_this->notify->all('tokenUsed', clienttranslate('${character_name} crafted a ${item_name}'), [
                    'gameData' => $_this->getAllDatas(),
                    'item_name' => $item['name'],
                ]);
            }
        );
        $this->gameData->set('lastAction', 'actCraft');
    }
    public function actSendToCamp(?int $sendToCampId = null): void
    {
        // $this->character->addExtraTime();
        if (!$sendToCampId) {
            throw new BgaUserException($this->translate('Select an item'));
        }
        $items = $this->gameData->get('state')['items'];
        if (
            !in_array(
                $sendToCampId,
                array_map(function ($d) {
                    return $d['itemId'];
                }, $items)
            )
        ) {
            throw new BgaUserException($this->translate('Invalid Item'));
        }
        $items = array_map(function ($d) {
            return $d['itemId'];
        }, $items);
        $character = $this->character->getSubmittingCharacter();
        $characterItems = array_map(
            function ($d) {
                return $d['itemId'];
            },
            array_filter($character['equipment'], function ($d) use ($items) {
                return !in_array($d['itemId'], $items);
            })
        );
        $items = array_filter($items, function ($d) use ($sendToCampId) {
            return $d != $sendToCampId;
        });

        $this->log('setCharacterEquipment', [...$characterItems, ...$items]);
        $this->character->setCharacterEquipment($character['id'], [...$characterItems, ...$items]);

        $campEquipment = $this->gameData->get('campEquipment');
        $this->log('campEquipment', [...$campEquipment, $sendToCampId]);
        $this->gameData->set('campEquipment', [...$campEquipment, $sendToCampId]);
        $this->gamestate->nextState('playerTurn');
    }

    public function destroyItem(int $itemId): void
    {
        $destroyedEquipment = $this->gameData->get('destroyedEquipment');
        array_push($destroyedEquipment, $itemId);
        $this->gameData->set('destroyedEquipment', $destroyedEquipment);

        $campEquipment = $this->gameData->get('campEquipment');
        if (in_array($itemId, $campEquipment)) {
            $this->gameData->set(
                'campEquipment',
                array_filter($campEquipment, function ($id) use ($itemId) {
                    return $id != $itemId;
                })
            );
        } else {
            foreach ($this->character->getAllCharacterData() as $k => $v) {
                $equippedIds = array_map(function ($d) {
                    return $d['itemId'];
                }, $v['equipment']);
                if (in_array($itemId, $equippedIds)) {
                    $this->character->unequipEquipment($v['character_name'], [$itemId]);
                    break;
                }
            }
        }
    }
    public function actDestroyItem(int $itemId): void
    {
        $this->destroyItem($itemId);
    }
    public function actSelectCharacter(?string $characterId = null): void
    {
        if (!$characterId) {
            throw new BgaUserException($this->translate('Select a Character'));
        }
        $data = [
            'characterId' => $characterId,
            'nextState' => 'playerTurn',
        ];
        $this->hooks->onCharacterSelection($data);
        $this->gameData->set('characterSelectionState', null);
        if ($data['nextState'] != false) {
            $this->gamestate->nextState($data['nextState']);
        }
    }
    public function actSelectCharacterCancel(): void
    {
        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actSelectCard(?string $cardId = null): void
    {
        if (!$cardId) {
            throw new BgaUserException($this->translate('Select a Card'));
        }
        $data = [
            'cardId' => $cardId,
            'nextState' => 'playerTurn',
        ];
        $this->hooks->onCardSelection($data);
        if ($data['nextState'] != false) {
            $this->gamestate->nextState($data['nextState']);
        }
    }
    public function actSelectCardCancel(): void
    {
        $state = $this->gameData->get('cardSelectionState');
        if (array_key_exists('cancellable', $state) && !$state['cancellable']) {
            throw new BgaUserException($this->translate('This action cannot be cancelled'));
        }

        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actSelectResource(?string $resourceType = null): void
    {
        // $this->character->addExtraTime();
        if (!$resourceType) {
            throw new BgaUserException($this->translate('Select a Resource'));
        }
        $data = [
            'resourceType' => $resourceType,
            'nextState' => 'playerTurn',
        ];
        $this->hooks->onResourceSelection($data);
        if ($data['nextState'] != false) {
            $this->gamestate->nextState($data['nextState']);
        }
    }
    public function actSelectResourceCancel(): void
    {
        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actSelectHindrance(#[JsonParam] array $data): void
    {
        if (sizeof($data) == 0) {
            throw new BgaUserException($this->translate('Select a Hindrance'));
        }
        $data = [
            'selections' => $data,
            'nextState' => 'playerTurn',
        ];
        $this->hooks->onHindranceSelection($data);

        if ($data['nextState'] != false) {
            $this->gamestate->nextState($data['nextState']);
        }
        $this->hooks->onHindranceSelectionAfter($data);
    }
    public function actSelectHindranceCancel(): void
    {
        $this->gameData->set('hindranceSelectionState', [...$this->gameData->get('hindranceSelectionState'), 'cancelled' => true]);
        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        // $this->character->addExtraTime();
        if (!$deckName) {
            throw new BgaUserException($this->translate('Select a Deck'));
        }
        $data = [
            'deckName' => $deckName,
            'nextState' => 'playerTurn',
        ];
        $this->hooks->onDeckSelection($data);
        if ($data['nextState'] != false) {
            $this->gamestate->nextState($data['nextState']);
        }
    }
    public function actSelectDeckCancel(): void
    {
        // $this->character->addExtraTime();
        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actConfirmTradeItem(): void
    {
        $this->itemTrade->actConfirmTradeItem();
    }
    public function actCancelTrade(): void
    {
        $this->itemTrade->actCancelTrade();
    }
    public function actTradeDone(): void
    {
        $this->itemTrade->actTradeDone();
    }
    public function actTradeItem(#[JsonParam] array $data): void
    {
        $this->itemTrade->actTradeItem($data);
    }
    public function actTrade(#[JsonParam] array $data): void
    {
        $offered = $data['offered'];
        $requested = $data['requested'];
        $this->actions->validateCanRunAction('actTrade');
        $offeredSum = 0;
        foreach ($offered as $key => $value) {
            $offeredSum += $value * (str_starts_with($key, 'gem') ? 2 : 1);
        }
        $requestedSum = 0;
        foreach ($requested as $key => $value) {
            $requestedSum += $value * (str_starts_with($key, 'gem') ? 2 : 1);
        }
        if ($offeredSum != $this->getTradeRatio()) {
            throw new BgaUserException(
                str_replace('${amount}', (string) $this->getTradeRatio(), $this->translate('You must offer ${amount} resources'))
            );
        }
        if ($requestedSum != 1) {
            throw new BgaUserException($this->translate('You must request only one resource'));
        }
        $this->actions->spendActionCost('actTrade');
        $offeredStr = [];
        $requestedStr = [];
        foreach ($offered as $key => $value) {
            if ($value > 0) {
                $this->adjustResource($key, -$value);
                array_push($offeredStr, $this->data->tokens[$key]['name'] . "($value)");
            }
        }
        foreach ($requested as $key => $value) {
            if ($value > 0) {
                if (str_contains($key, '-cooked')) {
                    throw new BgaUserException($this->translate('You cannot trade for a cooked resource'));
                }
                if (str_contains($key, 'gem-')) {
                    throw new BgaUserException($this->translate('You cannot trade for gems'));
                }
                $this->adjustResource($key, $value);
                array_push($requestedStr, $this->data->tokens[$key]['name'] . "($value)");
            }
        }
        // Finalize the trade hooks
        $this->getTradeRatio(false);
        // $this->hooks->onTrade($data);
        $this->notify->all('tokenUsed', clienttranslate('${character_name} traded ${offered} for ${requested}'), [
            'gameData' => $this->getAllDatas(),
            'offered' => join(', ', $offeredStr),
            'requested' => join(', ', $requestedStr),
        ]);
        $this->gameData->set('lastAction', 'actTrade');
    }
    public function actUseHerb(): void
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseHerb'],
            function (Game $_this) {
                // $this->log('onUseHerb');
                // $this->actions->validateCanRunAction('actUseHerb');
                return ['herb' => 1];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {}
        );

        $this->gameData->set('lastAction', 'actUseHerb');
    }
    public function actEat(string $resourceType): void
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onEat'],
            function (Game $_this) use ($resourceType) {
                $this->actions->validateCanRunAction('actEat', null, $resourceType);
                $tokenData = $this->data->tokens[$resourceType];
                $data = ['type' => $resourceType, ...$tokenData['actEat'], 'tokenName' => $tokenData['name']];
                $this->hooks->onEatBefore($data);
                return $data;
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if (array_key_exists('health', $data)) {
                    $this->character->adjustActiveHealth($data['health']);
                }
                if (array_key_exists('stamina', $data)) {
                    $this->character->adjustActiveStamina($data['stamina']);
                }
                $left = $this->adjustResource($data['type'], -$data['count'])['left'];
                if (!$data || !array_key_exists('notify', $data) || $data['notify'] != false) {
                    if ($left == 0) {
                        $this->notify->all(
                            'tokenUsed',
                            clienttranslate('${character_name} ate ${count} ${token_name} and gained ${health} health') .
                                (array_key_exists('stamina', $data) ? clienttranslate(' and ${stamina} stamina') : ''),
                            [
                                'gameData' => $this->getAllDatas(),
                                ...$data,
                                'token_name' => $data['tokenName'],
                            ]
                        );
                    }
                }
            }
        );

        $this->gameData->set('lastAction', 'actEat');
    }
    public function actAddWood(): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actAddWood');
        $data = $this->gameData->getResources('fireWood', 'wood');
        $this->hooks->onAddFireWood($data);
        $this->gameData->setResource('fireWood', min($data['fireWood'] + 1, $this->gameData->getResourceMax('wood')));
        $this->gameData->setResource('wood', max($data['wood'] - 1, 0));

        $this->notify->all('tokenUsed', clienttranslate('${character_name} added ${count} ${token_name} to the fire'), [
            'gameData' => $this->getAllDatas(),
            'token_name' => 'wood',
            'count' => 1,
        ]);
        $this->gameData->set('lastAction', 'actAddWood');
    }
    public function actUseSkill(string $skillId, ?string $optionValue = null): void
    {
        if ($this->gamestate->state()['name'] == 'playerTurn') {
            $this->gameData->set('lastAction', 'actUseSkill');
        }
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseSkill'],
            function (Game $_this) use ($skillId, $optionValue) {
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                // $this->character->addExtraTime();
                // $this->log('$skillId', $skillId);
                $_this->actions->validateCanRunAction('actUseSkill', $skillId);
                $res = $_this->character->getSkill($skillId);
                // $this->log('$res', $res);
                $skill = $res['skill'];
                $character = $res['character'];
                $_this->character->setSubmittingCharacter(null);
                $skill['optionValue'] = $optionValue;
                return [
                    'skillId' => $skillId,
                    'skill' => $skill,
                    'character' => $character,
                    'turnCharacter' => $this->character->getTurnCharacter(),
                    'nextState' => $this->gamestate->state()['name'] == 'dayEvent' ? 'playerTurn' : false,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) use ($optionValue) {
                $skill = $data['skill'];
                $character = $data['character'];
                $skillId = $data['skillId'];
                $skill['optionValue'] = $optionValue;
                $_this->hooks->reconnectHooks($skill, $_this->character->getSkill($skillId)['skill']);
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                $notificationSent = false;
                $skill['sendNotification'] = function () use (&$skill, $_this, &$notificationSent) {
                    $_this->notify->all('updateGameData', clienttranslate('${character_name} used the skill ${skill_name}'), [
                        'gameData' => $_this->getAllDatas(),
                        'skill_name' => $skill['name'],
                    ]);
                    $notificationSent = true;
                };
                if ($_this->gamestate->state()['name'] == 'interrupt') {
                    $_this->actInterrupt->actInterrupt($skillId, $optionValue);
                    $skill['sendNotification']();
                }
                if (!array_key_exists('interruptState', $skill) || (in_array('interrupt', $skill['state']) && $finalizeInterrupt)) {
                    // var_dump(json_encode([array_key_exists('onUse', $skill)]));
                    $result = array_key_exists('onUse', $skill) ? $skill['onUse']($this, $skill, $character) : null;
                    if (!$result || !array_key_exists('spendActionCost', $result) || $result['spendActionCost'] != false) {
                        // $this->log('$spendActionCost', $skillId);
                        $_this->actions->spendActionCost('actUseSkill', $skillId);
                    }
                    if (!$notificationSent && (!$result || !array_key_exists('notify', $result) || $result['notify'] != false)) {
                        $skill['sendNotification']();
                    }
                    if ($result && array_key_exists('nextState', $result)) {
                        $data['nextState'] = $result['nextState'];
                    }
                }
                $_this->character->setSubmittingCharacter(null);
                if ($data['nextState']) {
                    $this->gamestate->nextState($data['nextState']);
                }
            }
        );
    }
    public function actUseItem(string $skillId): void
    {
        if ($this->gamestate->state()['name'] == 'playerTurn') {
            $this->gameData->set('lastAction', 'actUseItem');
        }
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseSkill'],
            function (Game $_this) use ($skillId) {
                $_this->character->setSubmittingCharacter('actUseItem', $skillId);
                // $this->character->addExtraTime();
                $_this->actions->validateCanRunAction('actUseItem', $skillId);
                // $this->log('validateCanRunAction', $skillId);
                $character = $this->character->getSubmittingCharacter();

                $skills = $this->actions->getActiveEquipmentSkills();
                $skill = $skills[$skillId];
                return [
                    'skillId' => $skillId,
                    'skill' => $skill,
                    'character' => $character,
                    'turnCharacter' => $this->character->getTurnCharacter(),
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $skill = $data['skill'];
                $character = $data['character'];
                $skillId = $data['skillId'];
                $this->log('end', $skillId);

                $skills = $this->actions->getActiveEquipmentSkills();
                $_this->hooks->reconnectHooks($skill, $skills[$skillId]);
                $_this->character->setSubmittingCharacter('actUseItem', $skillId);
                $notificationSent = false;
                $skill['sendNotification'] = function () use (&$skill, $_this, &$notificationSent) {
                    $_this->notify->all('updateGameData', clienttranslate('${character_name} used the item\'s skill ${skill_name}'), [
                        'gameData' => $_this->getAllDatas(),
                        'skill_name' => $skill['name'],
                    ]);
                    $notificationSent = true;
                };
                if ($_this->gamestate->state()['name'] == 'interrupt') {
                    $_this->actInterrupt->actInterrupt($skillId);
                    $skill['sendNotification']();
                }
                if (!array_key_exists('interruptState', $skill) || (in_array('interrupt', $skill['state']) && $finalizeInterrupt)) {
                    // var_dump(json_encode([array_key_exists('onUse', $skill)]));
                    $result = array_key_exists('onUse', $skill) ? $skill['onUse']($this, $skill, $character) : null;
                    if (!$result || !array_key_exists('spendActionCost', $result) || $result['spendActionCost'] != false) {
                        $_this->actions->spendActionCost('actUseItem', $skillId);
                    }
                    if (!$notificationSent && (!$result || !array_key_exists('notify', $result) || $result['notify'] != false)) {
                        $skill['sendNotification']();
                    } else {
                        // $_this->notify->all('updateGameData', '', [
                        //     'gameData' => $_this->getAllDatas(),
                        // ]);
                    }
                } else {
                    // $_this->notify->all('updateGameData', '', [
                    //     'gameData' => $_this->getAllDatas(),
                    // ]);
                }
                $_this->character->setSubmittingCharacter(null);
            }
        );
    }
    public function actDrawGather(): void
    {
        $this->actDraw('gather');
        $this->gameData->set('lastAction', 'actDrawGather');
    }
    public function actDrawForage(): void
    {
        $this->actDraw('forage');
        $this->gameData->set('lastAction', 'actDrawForage');
    }
    public function actDrawHarvest(): void
    {
        if (
            sizeof(
                array_filter($this->character->getActiveEquipment(), function ($data) {
                    return $data['itemType'] == 'tool';
                })
            ) == 0
        ) {
            throw new BgaUserException($this->translate('You need a tool to harvest'));
        }
        if (
            sizeof(
                array_filter($this->character->getActiveEquipment(), function ($data) {
                    return $data['itemType'] == 'tool' && !in_array($data['id'], ['mortar-and-pestle', 'bandage']);
                })
            ) == 0
        ) {
            throw new BgaUserException($this->translate('The equipped tool can\'t be used for harvesting'));
        }
        $this->actDraw('harvest');
        $this->gameData->set('lastAction', 'actDrawHarvest');
    }
    public function actDrawHunt(): void
    {
        if (
            sizeof(
                array_filter($this->character->getActiveEquipment(), function ($data) {
                    return $data['itemType'] == 'weapon';
                })
            ) == 0
        ) {
            throw new BgaUserException($this->translate('You need a weapon to hunt'));
        }
        $this->actDraw('hunt');
        $this->gameData->set('lastAction', 'actDrawHunt');
    }
    public function actDrawExplore(): void
    {
        $this->actDraw('explore');
        $this->gameData->set('lastAction', 'actDrawExplore');
    }
    public function actDraw(string $deck): void
    {
        // $this->character->addExtraTime();
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onDraw'],
            function (Game $_this, $deck) {
                $this->actions->validateCanRunAction('actDraw' . ucfirst($deck));
                $data = ['deck' => $deck, 'cancel' => false];
                $this->hooks->onActDraw($data);
                $card = [];
                if (!$data['cancel']) {
                    $card = $this->decks->pickCard($deck);
                    $this->activeCharacterEventLog('draws from the ${deck} deck', [
                        'deck' => str_replace('-', ' ', $deck),
                        'gameData' => $this->getAllDatas(),
                    ]);
                }
                return ['deck' => $deck, 'card' => [...$card], ...$data];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                extract($data);
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actDraw' . ucfirst($deck));
                }

                if (!$data['cancel']) {
                    $_this->gameData->set('state', ['card' => $card, 'deck' => $deck]);
                    $_this->gamestate->nextState('drawCard');
                }
            }
        );
    }
    public function actInvestigateFire(?int $guess = null): void
    {
        // $this->character->addExtraTime();
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onInvestigateFire'],
            function (Game $_this) use ($guess) {
                $_this->actions->validateCanRunAction('actInvestigateFire');
                $character = $_this->character->getSubmittingCharacter();
                $_this->activeCharacterEventLog('investigated the fire');
                $roll = $_this->rollFireDie(clienttranslate('Investigate Fire'), $character['character_name']);
                return ['roll' => $roll, 'originalRoll' => $roll, 'guess' => $guess];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actInvestigateFire');
                }
                $_this->adjustResource('fkp', $data['roll']);
                $this->notify->all('tokenUsed', clienttranslate('${character_name} received ${count} ${resource_type}'), [
                    'count' => $data['roll'],
                    'resource_type' => 'fkp',
                    'gameData' => $this->getAllDatas(),
                ]);
            }
        );
        $this->gameData->set('lastAction', 'actInvestigateFire');
    }
    public function actEndTurn(): void
    {
        // Notify all players about the choice to pass.
        $this->activeCharacterEventLog('ends their turn');

        // at the end of the action, move to the next state
        $this->endTurn();
    }
    public function actDone(): void
    {
        // $this->character->addExtraTime();
        $stateName = $this->gamestate->state()['name'];
        if ($stateName == 'postEncounter') {
            $this->gamestate->nextState('playerTurn');
        } elseif ($stateName == 'tradePhase') {
            $this->gamestate->nextState('playerTurn');
        } elseif ($stateName == 'interrupt') {
            $this->actInterrupt->onInterruptCancel();
        } elseif ($stateName == 'dinnerPhase') {
            $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayer(), 'nightPhase');
        } elseif ($stateName == 'startHindrance') {
            if (
                sizeof(
                    array_filter($this->gameData->get('upgrades'), function ($v) {
                        return $v['replace'] == null;
                    })
                ) > 0
            ) {
                throw new BgaUserException($this->translate('All discoveries must replace an existing track discovery'));
            }
            $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayer(), 'start');
        }
    }

    public function argTooManyItems()
    {
        return [...$this->gameData->get('tooManyItemsState'), 'actions' => [], 'character_name' => $this->getCharacterHTML()];
    }
    public function argDeckSelection()
    {
        $result = [
            'actions' => [],
            'deckSelection' => $this->gameData->get('deckSelection'),
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getGameData($result);
        $this->getDecks($result);

        return $result;
    }
    public function argCardSelection()
    {
        $result = [
            'cardSelection' => $this->gameData->get('cardSelectionState'),
            'actions' => [],
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getGameData($result);
        return $result;
    }
    public function argHindranceSelection()
    {
        $result = [
            'hindranceSelection' => $this->gameData->get('hindranceSelectionState'),
            'actions' => [],
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getGameData($result);
        return $result;
    }
    public function argCharacterSelection()
    {
        $result = [...$this->gameData->get('characterSelectionState'), 'actions' => [], 'character_name' => $this->getCharacterHTML()];
        $this->getGameData($result);
        return $result;
    }
    public function argResourceSelection()
    {
        $resources = array_filter(
            $this->gameData->getResources(),
            function ($v, $k) {
                return $v > 0;
            },
            ARRAY_FILTER_USE_BOTH
        );
        $this->hooks->onResourceSelectionOptions($resources);
        $result = [
            ...$this->gameData->get('state'),
            'actions' => [],
            'character_name' => $this->getCharacterHTML(),
            'tokenSelection' => $resources,
        ];
        $this->getGameData($result);
        return $result;
    }
    public function argDrawCard()
    {
        $result = [
            ...$this->gameData->get('state'),
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getDecks($result);
        return $result;
    }
    public function argNightDrawCard()
    {
        $result = [
            ...$this->gameData->get('state'),
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getDecks($result);
        return $result;
    }
    public function argTradePhaseActions($playerId)
    {
        return $this->itemTrade->argTradePhaseActions($playerId);
    }
    public function argConfirmTradePhase($playerId)
    {
        return $this->itemTrade->argConfirmTradePhase($playerId);
    }
    public function argWaitTradePhase($playerId)
    {
        return $this->itemTrade->argWaitTradePhase($playerId);
    }
    public function argTradePhase()
    {
        return $this->itemTrade->argTradePhase();
    }

    public function actChooseWeapon(string $weaponId)
    {
        return $this->encounter->actChooseWeapon($weaponId);
    }
    public function argWhichWeapon()
    {
        return $this->encounter->argWhichWeapon();
    }
    public function argPostEncounter()
    {
        return $this->encounter->argPostEncounter();
    }
    public function stPostEncounter()
    {
        $this->encounter->stPostEncounter();
    }
    public function stResolveEncounter()
    {
        $this->encounter->stResolveEncounter();
    }
    public function argResolveEncounter()
    {
        return $this->encounter->argResolveEncounter();
    }
    public function stPlayerTurn()
    {
        $this->actions->clearDayEvent();
        // if (!$this->actInterrupt->checkForInterrupt()) {
        $char = $this->character->getTurnCharacter();
        $this->hooks->onPlayerTurn($char);
        if ($char['isActive'] && $char['incapacitated']) {
            $this->activeCharacterEventLog('is incapacitated');
            $this->endTurn();
        }
        // }
    }
    public function stDrawCard()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onResolveDraw'],
            function (Game $_this) {
                // $character = $this->character->getSubmittingCharacter();
                // deck,card
                $state = $this->gameData->get('state');
                $deck = $state['deck'];
                $card = $state['card'];
                $this->cardDrawEvent($card, $deck);
                if ($card['deckType'] == 'resource') {
                    $this->adjustResource($card['resourceType'], $card['count']);

                    $this->activeCharacterEventLog('found ${count} ${name}', [...$card, 'deck' => str_replace('-', ' ', $deck)]);
                } elseif ($card['deckType'] == 'encounter') {
                    // Change state and check for health/damage modifications
                    $this->activeCharacterEventLog('encountered a ${name} (${health} health, ${damage} damage)', [
                        ...$card,
                        'deck' => str_replace('-', ' ', $deck),
                    ]);
                } elseif ($card['deckType'] == 'nothing') {
                    if (!$this->isValidExpansion('mini-expansion')) {
                        $this->activeCharacterEventLog('did nothing', [
                            'deck' => str_replace('-', ' ', $deck),
                            ...$card,
                        ]);
                    }
                } elseif ($card['deckType'] == 'physical-hindrance' || $card['deckType'] == 'mental-hindrance') {
                } else {
                }
                return [...$state, 'discard' => false];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) use (&$moveToDrawCardState) {
                $deck = $data['deck'];
                $card = $data['card'];
                if ($data['discard']) {
                    $this->gamestate->nextState('playerTurn');
                } elseif ($card['deckType'] == 'resource') {
                    $this->gamestate->nextState('playerTurn');
                } elseif ($card['deckType'] == 'encounter') {
                    $this->gamestate->nextState('resolveEncounter');
                } elseif ($card['deckType'] == 'nothing') {
                    if ($this->isValidExpansion('mini-expansion')) {
                        $card = $this->decks->pickCard('day-event');
                        $this->gameData->set('state', ['card' => $card, 'deck' => 'day-event']);
                        $this->actions->addDayEvent($card['id']);
                        $moveToDrawCardState = true;
                    } else {
                        $this->gamestate->nextState('playerTurn');
                    }
                } elseif (
                    $card['deck'] != $card['deckType'] &&
                    ($card['deckType'] == 'physical-hindrance' || $card['deckType'] == 'mental-hindrance')
                ) {
                    $card = $this->decks->pickCard($card['deckType']);
                    $this->checkHindrance(true, $this->character->getSubmittingCharacterId());
                    // $this->character->addHindrance($this->character->getSubmittingCharacterId(), $card);
                    $this->gamestate->nextState('playerTurn');
                } elseif ($card['deckType'] == 'day-event') {
                    $this->gamestate->nextState('dayEvent');
                } else {
                    $this->gamestate->nextState('playerTurn');
                }
            }
        );
        if ($moveToDrawCardState) {
            $this->gamestate->nextState('drawCard');
        }
    }
    public function stDayEvent()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onDayEvent'],
            function (Game $_this) {
                $state = $this->gameData->get('state');
                $deck = $state['deck'];
                $card = $state['card'];
                return ['card' => $card, 'deck' => 'day-event'];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $deck = $data['deck'];
                $this->nightEventLog('Something unexpected happens, drawing a day event');
                // $this->gamestate->nextState('playerTurn');
            }
        );
    }
    public function stNightPhase()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onNight'],
            function (Game $_this) {
                $card = $this->decks->pickCard('night-event');
                $this->gameData->set('state', ['card' => $card, 'deck' => 'night-event']);
                return ['card' => $card, 'deck' => 'night-event'];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $deck = $data['deck'];
                $this->nightEventLog('It\'s night, drawing from the night deck');
                $this->gamestate->nextState('nightDrawCard');
            }
        );
    }
    public function stNightDrawCard()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onNightDrawCard'],
            function (Game $_this) {
                // deck,card
                $state = $this->gameData->get('state');
                $deck = $state['deck'];
                $card = $state['card'];
                $this->cardDrawEvent($card, $deck);
                return ['state' => $state];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $card = $data['state']['card'];
                $_this->hooks->reconnectHooks($card, $_this->decks->getCard($card['id']));

                $this->setActiveNightCard($card['id']);

                if (!$data || !array_key_exists('onUse', $data) || $data['onUse'] != false) {
                    $result = array_key_exists('onUse', $card) ? $card['onUse']($this, $card) : null;
                }

                $this->gamestate->nextState('morningPhase');
            }
        );
    }

    public function argSelectionCount(): array
    {
        $result = ['actions' => []];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        return $result;
    }
    public function argStartHindrance(): array
    {
        $selectableUpgrades = array_keys(
            array_filter($this->data->boards['knowledge-tree-' . $this->getDifficulty()]['track'], function ($v) {
                return !array_key_exists('upgradeType', $v);
            })
        );
        $result = [...$this->getAllDatas(), 'selectableUpgrades' => $selectableUpgrades];
        return $result;
    }
    public function log(...$args)
    {
        if ($this->gamestate == null) {
            $this->trace('TRACE [__init] ' . json_encode($args));
        } else {
            $this->trace('TRACE [' . $this->gamestate->state()['name'] . '] ' . json_encode($args));
        }
    }
    public function argPlayerState(): array
    {
        $result = [...$this->getAllDatas()];
        return $result;
    }
    public function argDayEvent(): array
    {
        $state = $this->gameData->get('state');
        $card = $state['card'];
        $result = [
            ...$this->getAllDatas(),
            'character_name' => $this->getCharacterHTML(),
            //'actions' => [],//array_values($this->data->expansion[$card['id']]['skills']),
            'actions' => [
                [
                    'action' => 'actUseSkill',
                    'type' => 'action',
                ],
                [
                    'action' => 'actUseItem',
                    'type' => 'action',
                ],
            ],
            // 'availableSkills' => array_values(
            //     $this->actions->wrapSkills(
            //         array_filter($this->data->expansion[$card['id']]['skills'], function ($skill) {
            //             return $skill['type'] == 'skill';
            //         }),
            //         'actUseSkill'
            //     )
            // ),
            // 'availableItemSkills' => array_values(
            //     $this->actions->wrapSkills(
            //         array_filter($this->data->expansion[$card['id']]['skills'], function ($skill) {
            //             return $skill['type'] == 'item-skill';
            //         }),
            //         'actUseItem'
            //     )
            // ),
        ];
        $result['availableSkills'] = $this->actions->getAvailableSkills();
        $result['availableItemSkills'] = $this->actions->getAvailableItemSkills();
        return $result;
    }
    public function argInterrupt(): array
    {
        return $this->actInterrupt->argInterrupt();
    }
    public function stInterrupt(): void
    {
        $this->actInterrupt->stInterrupt();
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression
        extract($this->gameData->getAll('day', 'turnNo'));
        return (($day - 1) * 4 + ($turnNo ?? 0)) / (12 * 4);
    }
    public function endTurn()
    {
        $data = [
            'characterId' => $this->character->getTurnCharacterId(),
        ];
        $this->hooks->onEndTurn($data);
        $this->gameData->set('lastAction', null);
        $this->gamestate->nextState('endTurn');
    }
    /**
     * The action method of state `nextCharacter` is called every time the current game state is set to `nextCharacter`.
     */
    public function stNextCharacter(): void
    {
        // Retrieve the active player ID.
        while (true) {
            if ($this->character->isLastCharacter()) {
                $this->gamestate->nextState('dinnerPhase');
                $this->actions->clearDayEvent();
                break;
            } else {
                $this->character->activateNextCharacter();
                $this->actions->clearDayEvent();
                if ($this->character->getActiveHealth() == 0) {
                    $this->notify->all('playerTurn', clienttranslate('${character_name} is incapacitated'), []);
                } else {
                    $this->gamestate->nextState('playerTurn');
                    $this->notify->all('playerTurn', clienttranslate('${character_name} begins their turn'), []);
                    break;
                }
            }
        }
    }
    public function argDinnerPhase($playerId)
    {
        $actions = array_map(function ($char) {
            return [
                'action' => 'actEat',
                'character' => $char['character_name'],
                'type' => 'action',
            ];
        }, $this->character->getAllCharacterDataForPlayer($playerId));
        $result = [
            'actions' => $actions,
        ];
        $this->getItemData($result);
        $this->getGameData($result);
        return $result;
    }
    public function stDinnerPhase()
    {
        $action = $this->actions->getAction('actEat');
        $hasFood = $action['requires']($this, $action);
        if ($hasFood) {
            $this->gamestate->setAllPlayersMultiactive();
            foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
                $this->giveExtraTime((int) $playerId);
            }
            $this->gamestate->initializePrivateStateForAllActivePlayers();
        } else {
            $this->notify->all('playerTurn', clienttranslate('The tribe skipped dinner as there is nothing to eat'));
            $this->gamestate->nextState('nightPhase');
        }
    }
    public function stSelectCharacter()
    {
        $this->gamestate->setAllPlayersMultiactive();
        foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
            $this->giveExtraTime((int) $playerId);
        }
        if ($this->isValidExpansion('hindrance')) {
            $upgrades = array_keys($this->data->upgrades);
            shuffle($upgrades);
            $count = 5;
            if ($this->getDifficulty() == 'easy') {
                $count = 4;
            } elseif ($this->getDifficulty() == 'hard') {
                $count = 6;
            }
            $upgrades = array_slice($upgrades, 0, $count);
            $upgrades = array_column(
                array_map(function ($k) {
                    return [$k, ['replace' => null]];
                }, $upgrades),
                1,
                0
            );
            $this->gameData->set('upgrades', $upgrades);
        }
    }
    public function stStartHindrance()
    {
        $this->gamestate->setAllPlayersMultiactive();
        foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
            $this->giveExtraTime((int) $playerId);
        }
    }
    public function getFirewoodCost()
    {
        $day = $this->gameData->get('day');
        if ($this->getTrackDifficulty() == 'normal') {
            return (int) (($day - 1) / 3) + 1 + ($day == 12 ? 1 : 0);
        } else {
            return (int) ($day / 3) + 1 + ($day >= 11 ? 1 : 0) - ($day == 12 ? 1 : 0);
        }
    }
    public function win()
    {
        $eloMapping = [5, 10, 15, 25];

        $trackEloMapping = [10, 20];
        $score = $eloMapping[$this->getGameStateValue('difficulty')] + $trackEloMapping[$this->getGameStateValue('trackDifficulty')];
        $this->DbQuery("UPDATE player SET player_score={$score} WHERE 1=1");
        $this->gamestate->nextState('endGame');
    }
    public function lose()
    {
        $this->DbQuery('UPDATE player SET player_score=0 WHERE 1=1');
        $this->gamestate->nextState('endGame');
    }
    public function stMorningPhase()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onMorning'],
            function (Game $_this) {
                $day = $this->gameData->get('day');
                $day += 1;
                $this->gameData->set('day', $day);
                $woodNeeded = $this->getFirewoodCost();
                $fireWood = $this->gameData->get('fireWood');
                if (array_key_exists('allowFireWoodAddition', $this->gameData->get('morningState') ?? []) && $fireWood < $woodNeeded) {
                    $missingWood = $woodNeeded - $fireWood;
                    $wood = $this->gameData->get('wood');
                    $this->gameData->setResource('fireWood', min($fireWood + $missingWood, $this->gameData->getResourceMax('wood')));
                    $this->gameData->setResource('wood', max($wood - $missingWood, 0));
                    $this->notify->all(
                        'tokenUsed',
                        clienttranslate('During the night the tribe quickly added ${woodNeeded} ${token_name} to the fire'),
                        [
                            'gameData' => $this->getAllDatas(),
                            'woodNeeded' => $woodNeeded,
                            'token_name' => 'wood',
                        ]
                    );
                }

                $this->notify->all('morningPhase', clienttranslate('Morning has arrived (Day ${day})'), [
                    'day' => $day,
                ]);
                if ($day == 14) {
                    $this->lose();
                }
                $difficulty = $this->getTrackDifficulty();
                $health = -1;
                if ($difficulty == 'hard') {
                    $health = -2;
                }
                return [
                    'difficulty' => $difficulty,
                    'health' => $health,
                    'stamina' => 0,
                    'skipMorningDamage' => [],
                    'woodNeeded' => $woodNeeded,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                // extract($data);
                $health = $data['health'];
                $stamina = $data['stamina'];
                $skipMorningDamage = $data['skipMorningDamage'];
                $woodNeeded = $data['woodNeeded'];
                $this->character->updateAllCharacterData(function (&$data) use ($health, $stamina, $skipMorningDamage) {
                    if (!in_array($data['id'], $skipMorningDamage)) {
                        $prev = 0;
                        $this->character->_adjustHealth($data, $health, $prev, $data['id']);
                    }
                    if ($data['incapacitated'] && $data['health'] > 0) {
                        $data['incapacitated'] = false;
                    }

                    if (!$data['incapacitated']) {
                        $data['stamina'] = $data['maxStamina'];
                        $data['stamina'] = clamp($data['stamina'] + $stamina, 0, $data['maxStamina']);
                    }
                });
                if ($health != 0) {
                    $this->notify->all('morningPhase', clienttranslate('Everyone lost ${amount} ${character_resource}'), [
                        'amount' => -$health,
                        'character_resource' => 'health',
                    ]);
                }

                $this->notify->all('morningPhase', clienttranslate('The fire pit used ${amount} wood'), [
                    'amount' => $woodNeeded,
                ]);
                if ($this->adjustResource('fireWood', -$woodNeeded)['left'] > 0) {
                    $this->lose();
                }
                $this->hooks->onMorningAfter($data);
                $this->actions->resetTurnActions();
                $this->character->rotateTurnOrder();
                $this->gamestate->nextState('tradePhase');
            }
        );
    }
    public function stTradePhase()
    {
        $this->itemTrade->stTradePhase();
    }
    public function stTradePhaseWait()
    {
        $this->itemTrade->stTradePhaseWait();
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
        //       if ($from_version <= 1404301345)
        //       {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
        //
        //       if ($from_version <= 1405061421)
        //       {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
    }
    public function getAllPlayers(&$result): void
    {
        $result['players'] = $this->getCollectionFromDb('SELECT `player_id` `id`, player_no FROM `player`');

        // $characters = $this->gameData->getAllMultiActiveCharacter();
        // $html = [];
        // foreach ($characters as $k => $v) {
        //     $color = $v['player_color'];
        //     $playerName = $this->getPlayerNameById($v['player_id']);
        //     $name = $v['character_name'];
        //     array_push($html, "<!--PNS--><span class=\"playername\" style=\"color:#$color;\">$name ($playerName)</span><!--PNE-->");
        // }
        // $result['playersString'] = join(', ', $html);
    }
    public function getAllCharacters(&$result): void
    {
        $result['characters'] = $this->character->getMarshallCharacters();
    }
    public function getDecks(&$result): void
    {
        $data = $this->decks->getDecksData();
        $result['decks'] = $data['decks'];
        $result['decksDiscards'] = $data['decksDiscards'];
    }
    public function getCraftedItems(): array
    {
        $items = $this->gameData->getItems();
        $campEquipment = array_count_values(
            array_map(function ($d) use ($items) {
                return $items[$d];
            }, $this->gameData->get('campEquipment'))
        );
        $destroyedEquipment = array_count_values(
            array_map(function ($d) use ($items) {
                return $items[$d];
            }, $this->gameData->get('destroyedEquipment'))
        );

        $equippedEquipment = array_merge(
            [],
            ...array_map(function ($data) {
                return array_map(function ($d) {
                    return $d['id'];
                }, $data['equipment']);
            }, $this->character->getAllCharacterData())
        );
        $equippedCounts = array_count_values(array_values($equippedEquipment));
        $sums = [];
        foreach (array_keys($campEquipment + $destroyedEquipment + $equippedCounts) as $key) {
            $sums[$key] =
                (array_key_exists($key, $campEquipment) ? $campEquipment[$key] : 0) +
                (array_key_exists($key, $destroyedEquipment) ? $destroyedEquipment[$key] : 0) +
                (array_key_exists($key, $equippedCounts) ? $equippedCounts[$key] : 0);
        }
        return $sums;
    }
    public function hasResourceCost($cost)
    {
        $resources = $this->gameData->getResources();

        $hasResources = true;
        foreach ($cost as $key => $value) {
            if ($resources[$key] < $value) {
                $hasResources = false;
            }
        }
        return $hasResources;
    }
    public function getItemData(&$result): void
    {
        $result['builtEquipment'] = $this->getCraftedItems();
        $items = $this->gameData->getItems();
        $result['campEquipmentCounts'] = array_count_values(
            array_map(function ($d) use ($items) {
                return $items[$d];
            }, $this->gameData->get('campEquipment'))
        );
        $result['campEquipment'] = array_map(function ($d) use ($items) {
            return ['name' => $items[$d], 'itemId' => $d];
        }, $this->gameData->get('campEquipment'));

        $result['eatableFoods'] = array_map(function ($eatable) {
            $data = [...$eatable['actEat'], 'id' => $eatable['id']];
            $this->hooks->onGetEatData($data);
            return $data;
        }, $this->actions->getActionSelectable('actEat'));
        $result['revivableFoods'] = array_map(function ($eatable) {
            $data = [...$eatable['actRevive'], 'id' => $eatable['id']];
            return $data;
        }, $this->actions->getActionSelectable('actRevive'));
        $selectable = $this->actions->getActionSelectable('actCraft');

        $result['availableEquipment'] = array_combine(
            array_map(function ($d) {
                return $d['id'];
            }, $selectable),
            array_map(function ($d) use ($result) {
                return $d['count'] - (array_key_exists($d['id'], $result['builtEquipment']) ? $result['builtEquipment'][$d['id']] : 0);
            }, $selectable)
        );
        $availableEquipment = array_keys($result['availableEquipment']);

        $result['availableEquipmentWithCost'] = array_values(
            array_filter($availableEquipment, function ($itemName) {
                $item = $this->data->items[$itemName];
                return $this->hasResourceCost($item['cost']);
            })
        );
    }
    public function getValidTokens(): array
    {
        return array_filter($this->data->tokens, function ($v) {
            return $v['type'] == 'resource' &&
                (!array_key_exists('requires', $v) || $v['requires']($this, $v)) &&
                (!array_key_exists('expansion', $v) || $this->isValidExpansion($v['expansion']));
        });
    }
    public function getGameData(&$result): void
    {
        $result['game'] = $this->gameData->getAll();
        $result['game']['prevResources'] = $this->gameData->getPreviousResources();
        // Need to remove these otherwise the response is too big
        foreach (array_keys($result['game']) as $key) {
            if (str_contains($key, 'State')) {
                unset($result['game'][$key]);
            }
        }

        $tokens = $this->gameData->get('tokens') ?? [];
        $trapCount = sizeof(
            array_filter(array_keys($tokens ?? []), function ($deck) use ($tokens) {
                return in_array('trap', $tokens[$deck]);
            })
        );
        $result['game']['resources']['trap'] += $trapCount;

        unset($result['game']['state']);
        $resourcesAvailable = [];
        array_walk($this->data->tokens, function ($v, $k) use (&$result, &$resourcesAvailable) {
            if ($v['type'] == 'resource' && isset($result['game']['resources'][$k])) {
                if (
                    (!array_key_exists('requires', $v) || $v['requires']($this, $v)) &&
                    (!array_key_exists('expansion', $v) || $this->isValidExpansion($v['expansion']))
                ) {
                    if (array_key_exists('cooked', $v)) {
                        $cooked = $v['cooked'];
                        $resourcesAvailable[$cooked] =
                            (array_key_exists($cooked, $resourcesAvailable) ? $resourcesAvailable[$cooked] : 0) -
                            $result['game']['resources'][$k];
                    } else {
                        $resourcesAvailable[$k] =
                            (array_key_exists($k, $resourcesAvailable) ? $resourcesAvailable[$k] : 0) +
                            $this->gameData->getResourceMax($k) -
                            $result['game']['resources'][$k] -
                            ($k === 'wood' ? $result['game']['resources']['fireWood'] ?? 0 : 0);
                    }
                } else {
                    unset($result['game']['resources'][$k]);
                    unset($result['game']['prevResources'][$k]);
                }
            }
        });

        $result['resourcesAvailable'] = $resourcesAvailable;
    }
    public function getExpansion()
    {
        $expansionMapping = self::$expansionList;
        return $expansionMapping[$this->getGameStateValue('expansion')];
    }
    public function isValidExpansion(string $expansion)
    {
        $expansionI = array_search($this->getExpansion(), $this::$expansionList);
        if ($expansionI === false) {
            throw new Exception('Can\'t find expansion ' . $this->getExpansion() . ' in ' . json_encode($this::$expansionList));
        }
        $expansionList = $this::$expansionList;
        return array_search($expansion, $expansionList) <= $expansionI;
    }
    public function getBuildings(): array
    {
        $buildings = $this->gameData->get('buildings');
        $characterId = $this->character->getTurnCharacterId();
        if (!$characterId) {
            return [];
        }
        return array_map(function ($building) use ($characterId) {
            $data = $this->data->items[$building['name']];
            if (array_key_exists('skills', $data)) {
                array_walk($data['skills'], function (&$v, $k) use ($building, $characterId) {
                    $v['itemId'] = $building['itemId'];
                    $v['itemName'] = $building['name'];
                    $v['characterId'] = $characterId;
                });
            }
            return $data;
        }, $buildings);
    }
    // public function addBuilding($buildingId): void
    // {
    //     $array = $this->gameData->get('buildings');
    //     array_push($array, $buildingId);
    //     $this->gameData->set('buildings', $array);
    // }
    public function getActiveNightCards(): array
    {
        $activeNightCards = $this->getActiveNightCardIds();
        return array_map(function ($cardId) {
            $card = $this->data->decks[$cardId];
            return $card;
        }, $activeNightCards);
    }
    public function getActiveNightCardIds(): array
    {
        return $this->gameData->get('activeNightCards');
    }
    public function setActiveNightCard($cardId): void
    {
        $this->gameData->set('activeNightCards', [$cardId]);
    }
    public function getUnlockedKnowledge(): array
    {
        $unlocks = $this->getUnlockedKnowledgeIds();
        return array_map(function ($unlock) {
            return $this->data->knowledgeTree[$unlock];
        }, $unlocks);
    }
    public function getUnlockedKnowledgeIds(bool $withReplacements = true): array
    {
        $unlocks = $this->gameData->get('unlocks');
        if ($withReplacements) {
            return $unlocks;
        }
        $upgrades = $this->gameData->get('upgrades');
        $mapping = [];
        array_walk($upgrades, function ($v, $k) use (&$mapping) {
            $mapping[$k] = $v['replace'];
        });
        return array_map(function ($v) use ($mapping) {
            if (array_key_exists($v, $mapping)) {
                return $mapping[$v];
            }
            return $v;
        }, $unlocks);
    }
    public function unlockKnowledge($knowledgeId): void
    {
        $array = $this->gameData->get('unlocks');
        array_push($array, $knowledgeId);
        $this->gameData->set('unlocks', $array);
    }
    public function getDifficulty()
    {
        $difficultyMapping = ['easy', 'normal', 'normal+', 'hard'];
        return $difficultyMapping[$this->getGameStateValue('difficulty')];
    }
    public function getTrackDifficulty()
    {
        $difficultyMapping = ['normal', 'hard'];
        return $difficultyMapping[$this->getGameStateValue('trackDifficulty')];
    }
    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    public function getAllDatas(): array
    {
        $availableUnlocks = $this->data->getValidKnowledgeTree();
        $result = [
            'activeCharacter' => $this->character->getTurnCharacterId(),
            'expansionList' => self::$expansionList,
            'expansion' => $this->getExpansion(),
            'difficulty' => $this->getDifficulty(),
            'trackDifficulty' => $this->getTrackDifficulty(),
            'fireWoodCost' => $this->getFirewoodCost(),
            'tradeRatio' => $this->getTradeRatio(),
            'upgrades' => $this->gameData->get('upgrades'),
            'availableUnlocks' => array_map(function ($id) use ($availableUnlocks) {
                return [
                    'id' => $id,
                    'name' => $this->data->knowledgeTree[$id]['name'],
                    'unlockCost' => $availableUnlocks[$id]['unlockCost'],
                ];
            }, array_keys($availableUnlocks)),
            'resolving' => $this->actInterrupt->isStateResolving(),
        ];
        if ($this->gamestate->state()['name'] != 'characterSelect') {
            $result['character_name'] = $this->getCharacterHTML();
            $result['actions'] = array_values($this->actions->getValidActions());
            $result['availableSkills'] = $this->actions->getAvailableSkills();
            $result['availableItemSkills'] = $this->actions->getAvailableItemSkills();
        }
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        $this->getDecks($result);
        $this->getGameData($result);
        $this->getItemData($result);

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return 'dontletitdie';
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        $this->gameData->setup();
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];
        // Create players based on generic information.
        //
        foreach ($players as $playerId => $player) {
            // Now you can access both $playerId and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $playerId,
                array_shift($default_colors),
                $player['player_canal'],
                addslashes($player['player_name']),
                addslashes($player['player_avatar']),
            ]);
        }
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s',
                implode(',', $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        $this->reloadPlayersBasicInfos();
        $this->decks->setup();

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
    }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state['name'];

        if ($state['type'] === 'activeplayer') {
            switch ($state_name) {
                default:
                    $this->gamestate->nextState('zombiePass');
                    break;
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state['type'] === 'multipleactiveplayer') {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
    // TEST FUNCTIONS START HERE
    public function giveResources()
    {
        $this->globals->set('resources', [
            ...$this->gameData->getResources(),
            'fireWood' => 4,
            'wood' => 4,
            'bone' => 6,
            'meat' => 4,
            'meat-cooked' => 4,
            'fish' => 0,
            'fish-cooked' => 0,
            'herb' => 4,
            'dino-egg' => 4,
            'dino-egg-cooked' => 4,
            'berry' => 4,
            'berry-cooked' => 4,
            'rock' => 6,
            'stew' => 3,
            'fiber' => 6,
            'hide' => 8,
            'trap' => 0,
            'fkp' => 40,
            'gem-y' => 1,
            'gem-b' => 1,
            'gem-p' => 1,
        ]);
        $this->notify->all('tokenUsed', '', [
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function giveClub()
    {
        $itemId = $this->gameData->createItem('club');
        $this->character->equipEquipment($this->character->getSubmittingCharacter()['id'], [$itemId]);
    }
    public function give($item)
    {
        $itemType = $this->data->items[$item]['itemType'];
        if ($itemType == 'building') {
            $currentBuildings = $this->gameData->get('buildings');
            $itemId = $this->gameData->createItem($item);
            array_push($currentBuildings, ['name' => $item, 'itemId' => $itemId]);
            $this->gameData->set('buildings', $currentBuildings);
        } else {
            $itemId = $this->gameData->createItem($item);
            $this->character->equipEquipment($this->character->getSubmittingCharacter()['id'], [$itemId]);
        }
    }
    public function giveItems()
    {
        $craftingLevel = $this->gameData->get('craftingLevel');
        $this->gameData->set('craftingLevel', max($craftingLevel, 3));

        extract($this->gameData->getAll('turnNo', 'turnOrder'));

        $itemId = $this->gameData->createItem('hide-armor');
        $this->character->equipEquipment($turnOrder[0], [$itemId]);
        $itemId = $this->gameData->createItem('spear');
        $this->character->equipEquipment($turnOrder[1], [$itemId]);
        $itemId = $this->gameData->createItem('sharp-stick');
        $this->character->equipEquipment($turnOrder[2], [$itemId]);
        $itemId = $this->gameData->createItem('bag');
        $this->character->equipEquipment($turnOrder[2], [$itemId]);
        $itemId = $this->gameData->createItem('hatchet');
        $this->character->equipEquipment($turnOrder[3], [$itemId]);
    }

    public function drawDayEvent()
    {
        $this->gameData->set('state', ['card' => $this->data->decks['gather-7_15'], 'deck' => 'gather']);
        $this->gamestate->nextState('drawCard');
    }
    public function setNightCard()
    {
        $cards = array_values($this->decks->getDeck('night-event')->getCardsInLocation('deck'));
        $firstCard = null;
        $max = 0;
        foreach ($cards as $k => $v) {
            if ($max < $v['location_arg']) {
                $max = max($max, $v['location_arg']);
                $firstCard = $v;
            }
        }
        foreach ($cards as $k => $v) {
            if ($v['type_arg'] == 'night-event-7_15' && $firstCard['type_arg'] != 'night-event-7_15') {
                $this->decks->getDeck('night-event')->moveCard($firstCard['id'], 'deck', $v['location_arg']);
                $this->decks->getDeck('night-event')->moveCard($v['id'], 'deck', $max);
            }
        }
        $this->globals->set('resources', [...$this->gameData->getResources(), 'fireWood' => 1, 'wood' => 1]);
    }
    public function resetStamina()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['stamina'] = $data['maxStamina'];
        });
    }
    public function lowHealth(?string $char = null)
    {
        if (!$char) {
            $char = $this->character->getSubmittingCharacter()['id'];
        }
        $this->character->updateCharacterData($char, function (&$data) {
            $data['health'] = 1;
        });
    }
    public function maxCraftLevel()
    {
        $craftingLevel = $this->gameData->get('craftingLevel');
        $this->gameData->set('craftingLevel', max($craftingLevel, 3));
    }
    public function kill()
    {
        $this->character->adjustActiveHealth(-10);
    }
    public function drawNightCard()
    {
        $this->globals->set('resources', [
            ...$this->gameData->getResources(),
            'fireWood' => 5,
            'wood' => 3,
            'bone' => 4,
            'meat' => 4,
            'meat-cooked' => 4,
            'fish' => 4,
            'fish-cooked' => 4,
            'dino-egg' => 4,
            'dino-egg-cooked' => 4,
            'berry' => 4,
            'berry-cooked' => 4,
            'rock' => 4,
            'fiber' => 4,
            'hide' => 4,
            'herb' => 4,
            'fkp' => 1,
            'gem-y' => 1,
            'gem-b' => 1,
            'gem-p' => 1,
        ]);
        $this->adjustResource('fireWood', 2);
        $this->character->updateAllCharacterData(function (&$data) {
            $data['stamina'] = $data['maxStamina'];
            $data['health'] = $data['maxHealth'] - 2;
        });
        $this->gameData->set('day', 1);
        $this->gameData->set('turnNo', 3);
        $this->endTurn();
        $this->notify->all('tokenUsed', '', [
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function shuffle()
    {
        $this->decks->shuffleInDiscard('gather', true);
    }
    public function unlockAll()
    {
        $this->gameData->set('unlocks', array_keys($this->data->knowledgeTree));
    }
    public function swapCharacter(string $char)
    {
        $this->characterSelection->test_swapCharacter($char);
    }
}
