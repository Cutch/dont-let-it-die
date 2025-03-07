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
use Exception;

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
    public ActInterrupt $actInterrupt;
    public static array $expansionList = ['base', 'mini-expansion', 'hindrance'];
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
            if (
                array_key_exists('resource', $args) &&
                !array_key_exists('resource_name', $args) &&
                str_contains($message, '${resource_name}')
            ) {
                $args['resource_name'] = $this->getPlayerNameById($args['resource']);
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
    public function activeCharacterEventLog($message, $arg = [])
    {
        // $result = [
        //     'character_name' => $this->getCharacterHTML(),
        //     ...$arg,
        // ];
        // $this->getAllCharacters($result);
        // $this->getAllPlayers($result);
        $this->notify->all('activeCharacter', clienttranslate('${character_name} ' . $message), [
            ...$arg,
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function nightEventLog($message, $arg = [])
    {
        $this->notify->all('nightEvent', clienttranslate($message), $arg);
    }
    public function cardDrawEvent($card, $deck, $arg = [])
    {
        $result = [
            'card' => $card,
            'deck' => $deck,
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
        ];
        $this->getDecks($result);
        $this->notify->all('cardDrawn', '', $result);
    }
    public function getTradeRatio()
    {
        $data = ['ratio' => 3];
        $this->hooks->onGetTradeRatio($data);
        return $data['ratio'];
    }
    public function adjustResource($resourceType, int $change): int
    {
        $currentCount = (int) $this->gameData->getResource($resourceType);
        $maxCount = isset($this->data->tokens[$resourceType]['count']) ? $this->data->tokens[$resourceType]['count'] : 999;
        if ($resourceType == 'wood') {
            $maxCount -= $this->gameData->getResource('fireWood');
        }
        $newValue = max(min($currentCount + $change, $maxCount), 0);
        $this->gameData->setResource($resourceType, $newValue);
        $difference = $currentCount - $newValue + $change;
        return $difference;
    }
    public function rollFireDie(?string $characterName = null): int
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
        $value = max($this->hooks->onRollDie($value), 0);
        if ($characterName) {
            $this->notify->all('rollFireDie', clienttranslate('${character_name} rolled a ${value}'), [
                'value' => $value == 0 ? 'blank' : $value,
                'character_name' => $this->getCharacterHTML($characterName),
                'roll' => $rand,
            ]);
        } else {
            $this->notify->all('rollFireDie', clienttranslate('The fire die rolled a ${value}'), [
                'value' => $value == 0 ? 'blank' : $value,
                'roll' => $rand,
            ]);
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
    public function actCook(array $type): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actCook', null, $type);
        $this->adjustResource($type, -1);
        $this->adjustResource($type . '-cooked', 1);
        $this->actions->spendActionCost('actCook');

        $this->notify->all('tokenUsed', clienttranslate('${character_name} cooked ${amount} ${type}'), [
            'gameData' => $this->getAllDatas(),
            'amount' => 1,
            'type' => $type,
        ]);
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
        } elseif (in_array($knowledgeId, $this->getUnlockedKnowledge())) {
            throw new BgaUserException($this->translate('Already unlocked'));
        } elseif ($resourceCount < $availableUnlocks[$knowledgeId]['cost']) {
            throw new BgaUserException($this->translate('Not enough knowledge points'));
        }
        $cost = -$availableUnlocks[$knowledgeId]['cost'];
        foreach ($resources as $resource) {
            $cost = $this->adjustResource($resource, $cost);
        }

        $this->actions->spendActionCost('actSpendFKP');
        $this->unlockKnowledge($knowledgeId);
        // $this->log($availableUnlocks[$knowledgeId]);
        $knowledgeObj = $this->data->knowledgeTree[$knowledgeId];
        array_key_exists('onUse', $knowledgeObj) ? $knowledgeObj['onUse']($this, $knowledgeObj) : null;
        $this->notify->all('tokenUsed', clienttranslate('${character_name} unlocked ${knowledge_name}'), [
            'gameData' => $this->getAllDatas(),
            'knowledgeId' => $knowledgeId,
            'knowledge_name' => $this->data->knowledgeTree[$knowledgeId]['name'],
        ]);
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
                $_this->actions->validateCanRunAction('actCraft');
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
                    if ($_this->adjustResource($key, -$value) != 0) {
                        throw new BgaUserException($_this->translate('Missing resources'));
                    }
                }
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actCraft');
                }
                if ($itemType == 'building') {
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
                        $_this->gameData->set('state', [
                            'itemType' => $itemType,
                            'items' => [...$existingItems, ['name' => $itemName, 'itemId' => $itemId]],
                        ]);
                        $_this->gamestate->nextState('tooManyItems');
                    }
                }
                $_this->notify->all('tokenUsed', clienttranslate('${character_name} crafted a ${item_name}'), [
                    'gameData' => $_this->getAllDatas(),
                    'item_name' => $item['name'],
                ]);
            }
        );
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
    public function actSelectResource(?string $resourceType = null): void
    {
        // $this->character->addExtraTime();
        if (!$resourceType) {
            throw new BgaUserException($this->translate('Select a resource'));
        }
        $data = [
            'resourceType' => $resourceType,
        ];
        $this->hooks->onResourceSelection($data);
        $this->gamestate->nextState('playerTurn');
        // $this->actInterrupt->interruptableFunction(
        //     __FUNCTION__,
        //     func_get_args(),
        //     [$this->hooks, 'onResourceSelection'],
        //     function (Game $_this) use ($resourceType) {
        //         if (!$resourceType) {
        //             throw new BgaUserException($this->translate('Select a resource'));
        //         }
        //         return [
        //             'resourceType' => $resourceType,
        //         ];
        //     },
        //     function (Game $_this, bool $finalizeInterrupt, $data) {
        //         // $resourceType = $data['resourceType'];
        //         // var_dump('actSelectResource playerTurn');
        //         $this->gamestate->nextState('playerTurn');
        //     },
        //     'playerTurn'
        // );
    }
    public function actSelectResourceCancel(): void
    {
        // $this->character->addExtraTime();
        if (!$this->actInterrupt->onInterruptCancel()) {
            // var_dump('actSelectResourceCancel playerTurn');
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        // $this->character->addExtraTime();
        if (!$deckName) {
            throw new BgaUserException($this->translate('Select a deck'));
        }
        $this->hooks->onDeckSelection($deckName);
        $this->gamestate->nextState('playerTurn');
    }
    public function actSelectDeckCancel(): void
    {
        // $this->character->addExtraTime();
        if (!$this->actInterrupt->onInterruptCancel()) {
            $this->gamestate->nextState('playerTurn');
        }
    }
    public function actTradeItem(#[JsonParam] array $data): void
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onItemTrade'],
            function (Game $_this) use ($data) {
                if (sizeof($data['selection']) != 2) {
                    throw new BgaUserException($this->translate('You must select 2 items to trade'));
                }
                $selfId = $this->getCurrentPlayer();
                $hasSelf = false;
                $hasItem = false;
                $trade1 = [];
                $trade2 = [];
                $sendToCamp = false;
                array_walk($data['selection'], function (&$trade) use ($selfId, &$hasSelf, &$hasItem, &$trade1, &$trade2, &$sendToCamp) {
                    if (array_key_exists('character', $trade)) {
                        $trade['character'] = $this->character->getCharacterData($trade['character']);
                        $hasSelf = $hasSelf || $selfId == $trade['character']['player_id'];
                        if ($selfId == $trade['character']['player_id']) {
                            if (!$trade1) {
                                $trade1 = $trade;
                            } else {
                                $trade2 = $trade;
                            }
                        } else {
                            $trade2 = $trade;
                        }
                    } else {
                        $trade2 = $trade;
                        $sendToCamp = true;
                    }
                    if (array_key_exists('itemId', $trade)) {
                        $hasItem = $hasItem || true;
                    }
                });
                if (!$hasSelf) {
                    throw new BgaUserException($this->translate('Select one of your character\'s items to trade'));
                }
                if (!$hasItem) {
                    throw new BgaUserException($this->translate('Select one item to trade'));
                }
                if ($sendToCamp) {
                    $sendToCampId = $trade1['itemId'];

                    $character = $this->character->getCharacterData($trade1['character']);
                    $characterItems = array_map(
                        function ($d) {
                            return $d['itemId'];
                        },
                        array_filter($character['equipment'], function ($d) use ($sendToCampId) {
                            return $d['itemId'] != $sendToCampId;
                        })
                    );
                    $this->log('setCharacterEquipment', $characterItems);
                    $this->character->setCharacterEquipment($character['id'], $characterItems);

                    $campEquipment = $this->gameData->get('campEquipment');
                    $this->log('campEquipment', [...$campEquipment, $sendToCampId]);
                    $this->gameData->set('campEquipment', [...$campEquipment, $sendToCampId]);
                    return null;
                } else {
                    $this->log($trade1, $trade2);
                    $itemId1 = array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null;
                    $itemId2 = array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null;
                    $characterItems1 = array_map(
                        function ($d) {
                            return $d['itemId'];
                        },
                        array_filter($trade1['character']['equipment'], function ($d) use ($itemId1) {
                            return $d['itemId'] != $itemId1;
                        })
                    );
                    $characterItems2 = array_map(
                        function ($d) {
                            return $d['itemId'];
                        },
                        array_filter($trade2['character']['equipment'], function ($d) use ($itemId2) {
                            return $d['itemId'] != $itemId2;
                        })
                    );
                    if ($itemId1) {
                        $result = $this->character->getItemValidations($itemId1, $trade2['character'], $itemId2);
                        $hasOpenSlots = $result['hasOpenSlots'];
                        $hasDuplicateTool = $result['hasDuplicateTool'];
                        if (!$hasOpenSlots) {
                            throw new BgaUserException($this->translate('There is no open slot for that item type'));
                        }
                        if ($hasDuplicateTool) {
                            throw new BgaUserException($this->translate('Cannot of a duplicate tool'));
                        }
                        array_push($characterItems2, $itemId1);
                    }
                    if ($itemId2) {
                        $result = $this->character->getItemValidations($itemId2, $trade1['character'], $itemId1);
                        $hasOpenSlots = $result['hasOpenSlots'];
                        $hasDuplicateTool = $result['hasDuplicateTool'];

                        if (!$hasOpenSlots) {
                            throw new BgaUserException($this->translate('There is no open slot for that item type'));
                        }
                        if ($hasDuplicateTool) {
                            throw new BgaUserException($this->translate('Cannot of a duplicate tool'));
                        }
                        array_push($characterItems1, $itemId2);
                    }
                    return [
                        'characterItems1' => $characterItems1,
                        'characterItems2' => $characterItems2,
                        'trade1' => $trade1,
                        'trade2' => $trade2,
                    ];
                }
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if ($data) {
                    $characterItems1 = $data['characterItems1'];
                    $characterItems2 = $data['characterItems2'];
                    $trade1 = $data['trade1'];
                    $trade2 = $data['trade2'];
                    $this->character->setCharacterEquipment($trade1['character']['id'], array_values($characterItems1));
                    $this->character->setCharacterEquipment($trade2['character']['id'], array_values($characterItems2));

                    $items = $this->gameData->getItems();
                    $_this->notify->all('updateGameData', clienttranslate('${character_name_1} traded an item to ${character_name_2}'), [
                        'character_name_1' => $this->getCharacterHTML($trade1['character']['id']),
                        'character_name_2' => $this->getCharacterHTML($trade2['character']['id']),
                        'gameData' => $_this->getAllDatas(),
                        'itemId1' => array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null,
                        'itemId2' => array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null,
                        'itemName1' => array_key_exists('itemId', $trade1) ? $items[$trade1['itemId']] : null,
                        'itemName2' => array_key_exists('itemId', $trade2) ? $items[$trade2['itemId']] : null,
                        'character1' => $trade1['character']['id'],
                        'character2' => $trade2['character']['id'],
                    ]);
                }
            }
        );
        // $trade1 = $data['selection'][0];
        // $trade2 = $data['selection'][1];
        // if(array_key_exists('character', $trade1) || )
        // array_map(function($trade){ return $this->character->getCharacterData($trade['character']); }, $data['selection'])
    }
    public function actTrade(#[JsonParam] array $data): void
    {
        // $this->character->addExtraTime();
        extract($data);
        $this->actions->validateCanRunAction('actTrade');
        $offeredSum = 0;
        foreach ($offered as $key => $value) {
            $offeredSum += $value;
        }
        $requestedSum = 0;
        foreach ($requested as $key => $value) {
            $requestedSum += $value;
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
                $this->adjustResource($key, $value);
                array_push($requestedStr, $this->data->tokens[$key]['name'] . "($value)");
            }
        }
        // $this->hooks->onTrade($data);
        $this->notify->all('tokenUsed', clienttranslate('${character_name} traded ${offered} for ${requested}'), [
            'gameData' => $this->getAllDatas(),
            'offered' => join(', ', $offeredStr),
            'requested' => join(', ', $requestedStr),
        ]);
    }

    public function actEat(string $resourceType): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actEat', null, $resourceType);
        $tokenData = $this->data->tokens[$resourceType];
        $data = ['type' => $resourceType, ...$tokenData['actEat']];
        $this->hooks->onEat($data);
        if (array_key_exists('health', $data)) {
            $this->character->adjustActiveHealth($data['health']);
        }
        if (array_key_exists('stamina', $data)) {
            $this->character->adjustActiveStamina($data['stamina']);
        }
        $this->adjustResource($data['type'], -$data['count']);
        $this->notify->all(
            'tokenUsed',
            clienttranslate('${character_name} ate ${count} ${token_name} and gained ${health} health') .
                (array_key_exists('stamina', $data) ? clienttranslate(' and ${stamina} stamina') : ''),
            [
                'gameData' => $this->getAllDatas(),
                ...$data,
                'token_name' => $tokenData['name'],
            ]
        );
    }
    public function actAddWood(): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actAddWood');
        extract($this->gameData->getResources('fireWood', 'wood'));
        $this->gameData->setResource('fireWood', min($fireWood + 1, $this->data->tokens['wood']['count']));
        $this->gameData->setResource('wood', max($wood - 1, 0));

        $this->notify->all('tokenUsed', clienttranslate('${character_name} added 1 wood to the fire'), [
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function actUseSkill(string $skillId): void
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseSkill'],
            function (Game $_this) use ($skillId) {
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                // $this->character->addExtraTime();
                $_this->actions->validateCanRunAction('actUseSkill', $skillId);
                $res = $_this->character->getSkill($skillId);
                $skill = $res['skill'];
                $character = $res['character'];
                $_this->character->setSubmittingCharacter(null);
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
                $_this->hooks->reconnectHooks($skill, $_this->character->getSkill($skillId)['skill']);
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                if ($_this->gamestate->state()['name'] == 'interrupt') {
                    $_this->actInterrupt->actInterrupt($skillId);
                }
                // var_dump(json_encode(['onUseSkill', $skill, $this->gamestate->state()['name']]));
                if (!array_key_exists('interruptState', $skill) || (in_array('interrupt', $skill['state']) && $finalizeInterrupt)) {
                    $notificationSent = false;
                    $skill['sendNotification'] = function () use (&$skill, $_this, &$notificationSent) {
                        $_this->notify->all('updateGameData', clienttranslate('${character_name} used the skill ${skill_name}'), [
                            'gameData' => $_this->getAllDatas(),
                            'skill_name' => $skill['name'],
                        ]);
                        $notificationSent = true;
                    };
                    // var_dump(json_encode([array_key_exists('onUse', $skill)]));
                    $result = array_key_exists('onUse', $skill) ? $skill['onUse']($this, $skill, $character) : null;
                    if (!$result || !array_key_exists('spendActionCost', $result) || $result['spendActionCost'] != false) {
                        $_this->actions->spendActionCost('actUseSkill', $skillId);
                    }
                    if (!$notificationSent && (!$result || !array_key_exists('notify', $result) || $result['notify'] != false)) {
                        $skill['sendNotification']();
                    }
                }
                $_this->character->setSubmittingCharacter(null);
            }
        );
    }
    public function actUseItem(string $skillId): void
    {
        $this->character->setSubmittingCharacter('actUseItem', $skillId);
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actUseItem', $skillId);
        $character = $this->character->getSubmittingCharacter();
        $skills = $this->character->getActiveEquipmentSkills();
        $skill = $skills[$skillId];
        $result = $skill['onUse']($this, $skill, $character);
        if (!$result || (array_key_exists('spendActionCost', $result) && $result['spendActionCost'] != false)) {
            $this->actions->spendActionCost('actUseItem', $skillId);
        }
        if (!$result || (array_key_exists('notify', $result) && $result['notify'] != false)) {
            $this->notify->all('updateGameData', clienttranslate('${character_name} used the item\'s skill ${skill_name}'), [
                'gameData' => $this->getAllDatas(),
                'skill_name' => $skill['name'],
            ]);
        }
        $this->character->setSubmittingCharacter(null);
    }
    public function actDrawGather(): void
    {
        $this->actDraw('gather');
    }
    public function actDrawForage(): void
    {
        $this->actDraw('forage');
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
            throw new BgaUserException($this->translate('You need tool to harvest'));
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
            throw new BgaUserException($this->translate('You need weapon to hunt'));
        }
        $this->actDraw('hunt');
    }
    public function actDraw(string $deck): void
    {
        // $this->character->addExtraTime();
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onDraw'],
            function (Game $_this, $deck) {
                $_this->actions->validateCanRunAction('actDraw' . ucfirst($deck));
                $card = $_this->decks->pickCard($deck);
                $_this->activeCharacterEventLog('drew from the ${deck} deck', [
                    'deck' => str_replace('-', ' ', $deck),
                    'gameData' => $_this->getAllDatas(),
                ]);
                return ['deck' => $deck, 'card' => $card];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                extract($data);
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actDraw' . ucfirst($deck));
                }

                $_this->gameData->set('state', ['card' => $card, 'deck' => $deck]);
                $_this->gamestate->nextState('drawCard');
            }
        );
    }
    public function actInvestigateFire(): void
    {
        // $this->character->addExtraTime();
        // var_dump(json_encode(['actInvestigateFire']));
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onInvestigateFire'],
            function (Game $_this) {
                $_this->actions->validateCanRunAction('actInvestigateFire');
                $character = $_this->character->getSubmittingCharacter();
                $roll = $_this->rollFireDie($character['character_name']);
                return ['roll' => $roll];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                // var_dump(json_encode(['actInvestigateFire', $data]));
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actInvestigateFire');
                }
                $_this->adjustResource('fkp', $data['roll']);
                $this->notify->all('tokenUsed', '', [
                    'gameData' => $this->getAllDatas(),
                ]);
            }
        );
    }
    public function actEndTurn(): void
    {
        // Notify all players about the choice to pass.
        $this->activeCharacterEventLog('ends their turn');

        // at the end of the action, move to the next state
        $this->gamestate->nextState('endTurn');
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
        }
    }

    public function argTooManyItems()
    {
        return [...$this->gameData->get('state'), 'actions' => [], 'character_name' => $this->getCharacterHTML()];
    }
    public function argDeckSelection()
    {
        $result = ['actions' => [], 'character_name' => $this->getCharacterHTML()];
        $this->getDecks($result);

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
    public function argTradePhase()
    {
        $result = [...$this->getAllDatas()];
        return $result;
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
        $this->actInterrupt->checkForInterrupt();
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
                    $this->activeCharacterEventLog('did nothing', [
                        'deck' => str_replace('-', ' ', $deck),
                    ]);
                } elseif ($card['deckType'] == 'hindrance') {
                } else {
                }
                return $state;
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $deck = $data['deck'];
                $card = $data['card'];
                if ($card['deckType'] == 'resource') {
                    $this->gamestate->nextState('playerTurn');
                } elseif ($card['deckType'] == 'encounter') {
                    $this->gamestate->nextState('resolveEncounter');
                } elseif ($card['deckType'] == 'nothing') {
                    $this->gamestate->nextState('playerTurn');
                } elseif ($card['deckType'] == 'hindrance') {
                    $this->gamestate->nextState('playerTurn');
                } else {
                    $this->gamestate->nextState('playerTurn');
                }
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
                $result = array_key_exists('onUse', $card) ? $card['onUse']($this, $card) : null;

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
    public function log(...$args)
    {
        $this->trace('TRACE [' . $this->gamestate->state()['name'] . '] ' . json_encode($args));
    }
    public function argPlayerState(): array
    {
        $result = [...$this->getAllDatas()];
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
        return (($day - 1) * 4 + $turnNo) / (12 * 4);
    }

    /**
     * The action method of state `nextCharacter` is called every time the current game state is set to `nextCharacter`.
     */
    public function stNextCharacter(): void
    {
        // Retrieve the active player ID.
        $playerId = (int) $this->getActivePlayerId();
        while (true) {
            if ($this->character->isLastCharacter()) {
                $this->gamestate->nextState('nightPhase');
                break;
            } else {
                $playerId = $this->character->activateNextCharacter();
                if ($this->character->getActiveHealth() == 0) {
                    $this->notify->all('playerTurn', clienttranslate('${character_name} is incapacitated'), []);
                } else {
                    $this->giveExtraTime($playerId);
                    $this->gamestate->nextState('nextCharacter');
                    $this->notify->all('playerTurn', clienttranslate('${character_name} begins their turn'), []);
                    break;
                }
            }
        }
    }

    public function stSelectCharacter()
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
        $day = $this->gameData->get('day');
        $day += 1;
        $this->gameData->set('day', $day);
        if ($day == 14) {
            $this->lose();
        }
        $this->notify->all('morningPhase', clienttranslate('Morning has arrived (Day ${day})'), [
            'day' => $day,
        ]);
        $difficulty = $this->getTrackDifficulty();
        $health = -1;
        if ($difficulty == 'hard') {
            $health = -2;
        }
        $data = [
            'woodNeeded' => $this->getFirewoodCost(),
            'difficulty' => $difficulty,
            'health' => $health,
            'stamina' => 0,
            'skipMorningDamage' => [],
        ];
        $this->hooks->onMorning($data);
        extract($data);
        $this->character->updateAllCharacterData(function (&$data) use ($health, $stamina, $skipMorningDamage) {
            if (!in_array($data['id'], $skipMorningDamage)) {
                $data['health'] = max(min($data['health'] + $health, $data['maxHealth']), 0);
            }
            $data['stamina'] = $data['maxStamina'];
            $data['stamina'] = max(min($data['stamina'] + $stamina, $data['maxStamina']), 0);
        });
        if ($health != 0) {
            $this->notify->all('morningPhase', clienttranslate('Everyone lost ${amount} health'), [
                'amount' => -$health,
            ]);
        }

        $this->notify->all('morningPhase', clienttranslate('The fire pit used ${amount} wood'), [
            'amount' => $woodNeeded,
        ]);
        if ($this->adjustResource('fireWood', -$woodNeeded) != 0) {
            $this->lose();
        }
        $this->actions->resetTurnActions();
        $this->character->rotateTurnOrder();
        $this->gamestate->nextState('tradePhase');
    }
    public function stTradePhase()
    {
        if (sizeof($this->getCraftedItems()) == 0) {
            $this->gamestate->nextState('playerTurn');
        } else {
            $this->gameData->setAllMultiActiveCharacter();
        }
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
        foreach (array_keys($campEquipment + $equippedCounts) as $key) {
            $sums[$key] =
                (array_key_exists($key, $campEquipment) ? $campEquipment[$key] : 0) +
                (array_key_exists($key, $equippedCounts) ? $equippedCounts[$key] : 0);
        }
        return $sums;
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

        $resources = $this->gameData->getResources();
        $result['availableEquipmentWithCost'] = array_values(
            array_filter($availableEquipment, function ($itemName) use ($resources) {
                $item = $this->data->items[$itemName];
                $hasResources = true;
                foreach ($item['cost'] as $key => $value) {
                    if ($resources[$key] < $value) {
                        $hasResources = false;
                    }
                }
                return $hasResources;
            })
        );
    }
    protected function getGameData(&$result): void
    {
        $result['game'] = $this->gameData->getAll();
        $result['game']['prevResources'] = $this->gameData->getPreviousResources();

        $resourcesAvailable = [];
        array_walk($this->data->tokens, function ($v, $k) use ($result, &$resourcesAvailable) {
            if ($v['type'] == 'resource' && isset($result['game']['resources'][$k])) {
                if (array_key_exists('cooked', $v)) {
                    $cooked = $v['cooked'];
                    $resourcesAvailable[$cooked] =
                        (array_key_exists($cooked, $resourcesAvailable) ? $resourcesAvailable[$cooked] : 0) -
                        $result['game']['resources'][$k];
                } else {
                    $resourcesAvailable[$k] =
                        (array_key_exists($k, $resourcesAvailable) ? $resourcesAvailable[$k] : 0) +
                        $v['count'] -
                        $result['game']['resources'][$k] -
                        ($k === 'wood' ? $result['game']['resources']['fireWood'] ?? 0 : 0);
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
        $expansionList = $this::$expansionList;
        return array_search($expansion, $expansionList) <= $expansionI;
    }
    public function getBuildings(): array
    {
        $buildings = $this->gameData->get('buildings');
        return array_map(function ($building) {
            return $this->data->items[$building['name']];
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
    public function getUnlockedKnowledgeIds(): array
    {
        $unlocks = $this->gameData->get('unlocks');
        return $unlocks;
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
            'expansionList' => self::$expansionList,
            'expansion' => $this->getExpansion(),
            'difficulty' => $this->getDifficulty(),
            'trackDifficulty' => $this->getTrackDifficulty(),
            'fireWoodCost' => $this->getFirewoodCost(),
            'tradeRatio' => $this->getTradeRatio(),
            'availableUnlocks' => array_map(function ($id) use ($availableUnlocks) {
                return ['id' => $id, 'name' => $this->data->knowledgeTree[$id]['name'], 'cost' => $availableUnlocks[$id]['cost']];
            }, array_keys($availableUnlocks)),
            'resolving' => $this->actInterrupt->isStateResolving(),
        ];
        if ($this->gamestate->state()['name'] != 'characterSelect') {
            $result['character_name'] = $this->getCharacterHTML();
            $result['actions'] = $this->actions->getValidActions();
            $result['availableSkills'] = $this->actions->getAvailableCharacterSkills();
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
            'dino-egg' => 0,
            'dino-egg-cooked' => 0,
            'berry' => 4,
            'berry-cooked' => 4,
            'rock' => 6,
            'stew' => 0,
            'fiber' => 6,
            'hide' => 8,
            'trap' => 0,
            'herb' => 0,
            'fkp' => 40,
            'gem' => 0,
        ]);
    }
    public function giveClub()
    {
        $itemId = $this->gameData->createItem('club');
        $this->character->equipEquipment($this->character->getSubmittingCharacter()['id'], [$itemId]);
    }
    public function resetStamina()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['stamina'] = $data['maxStamina'];
        });
    }
    public function lowHealth()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['health'] = 2;
        });
    }
    public function maxCraftLevel()
    {
        $craftingLevel = $this->gameData->get('craftingLevel');
        $this->gameData->set('craftingLevel', max($craftingLevel, 3));
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
            'gem' => 3,
        ]);
        $this->adjustResource('fireWood', 2);
        $this->character->updateAllCharacterData(function (&$data) {
            $data['stamina'] = $data['maxStamina'];
            $data['health'] = $data['maxHealth'] - 2;
        });
        $this->gameData->set('day', 1);
        $this->gameData->set('turnNo', 3);
        $this->gamestate->nextState('endTurn');
        $this->notify->all('tokenUsed', '', [
            'gameData' => $this->getAllDatas(),
        ]);
    }
}
