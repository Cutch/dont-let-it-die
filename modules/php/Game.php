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

use Bga\GameFramework\Actions\CheckAction;
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
include_once dirname(__DIR__) . '/php/DLD_Data.php';
include_once dirname(__DIR__) . '/php/DLD_Actions.php';
include_once dirname(__DIR__) . '/php/DLD_CharacterSelection.php';
include_once dirname(__DIR__) . '/php/DLD_Character.php';
include_once dirname(__DIR__) . '/php/DLD_GameData.php';
include_once dirname(__DIR__) . '/php/DLD_SelectionStates.php';
include_once dirname(__DIR__) . '/php/DLD_Undo.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Utils.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Boards.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Characters.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Decks.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Expansion.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_KnowledgeTree.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Items.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Tokens.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Upgrades.php';
class Game extends \Table
{
    public DLD_Character $character;
    public DLD_Actions $actions;
    private DLD_CharacterSelection $characterSelection;
    public DLD_Data $data;
    public DLD_Decks $decks;
    public DLD_GameData $gameData;
    public DLD_Hooks $hooks;
    public DLD_Encounter $encounter;
    public DLD_ItemTrade $itemTrade;
    public DLD_ActInterrupt $actInterrupt;
    public DLD_SelectionStates $selectionStates;
    public DLD_Undo $undo;
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
            'trusting' => 103,
            'randomUpgrades' => 104,
        ]);
        $this->gameData = new DLD_GameData($this);
        $this->actions = new DLD_Actions($this);
        $this->data = new DLD_Data($this);
        $this->decks = new DLD_Decks($this);
        $this->character = new DLD_Character($this);
        $this->characterSelection = new DLD_CharacterSelection($this);
        $this->hooks = new DLD_Hooks($this);
        $this->encounter = new DLD_Encounter($this);
        $this->itemTrade = new DLD_ItemTrade($this);
        $this->actInterrupt = new DLD_ActInterrupt($this);
        $this->selectionStates = new DLD_SelectionStates($this);
        $this->undo = new DLD_Undo($this);
        // automatically complete notification args when needed
        $this->notify->addDecorator(function (string $message, array $args) {
            $args['gamestate'] = ['name' => $this->gamestate->state(true, false, true)['name']];
            if (!array_key_exists('character_name', $args) && str_contains($message, '${character_name}')) {
                $args['character_name'] = $this->getCharacterHTML();
            }
            if (!array_key_exists('player_name', $args) && str_contains($message, '${player_name}')) {
                if (array_key_exists('playerId', $args)) {
                    $args['player_name'] = $this->getPlayerNameById($args['playerId']);
                } elseif (array_key_exists('character_name', $args)) {
                    $playerId = (int) $this->character->getCharacterData($args['character_name'])['playerId'];
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                } else {
                    $playerId = (int) $this->getActivePlayerId();
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                }
            }
            if (!array_key_exists('character_name', $args) && $this->character->getTurnCharacterId()) {
                $args['character_name'] = $this->getCharacterHTML();
            }
            if (str_contains($message, '${resource_type}')) {
                $args['resource_type'] = $this->data->getTokens()[$args['resource_type']]['name'];
            }
            return $args;
        });
    }
    public function actUndo()
    {
        $this->undo->actUndo();
    }
    public function getVersion(): int
    {
        return intval($this->gamestate->table_globals[300]);
    }
    protected function initTable(): void
    {
        $this->undo->loadInitialState();
    }
    public function nextState(string $transition)
    {
        if ($this->getBgaEnvironment() == 'studio') {
            $this->log('Transition to \'' . $transition . '\'');
        }
        $this->gamestate->nextState($transition);
    }
    public function notify(...$arg)
    {
        if ($this->getBgaEnvironment() == 'studio') {
            $this->log('notify', ...$arg);
        }
        $this->notify->all(...$arg);
    }
    public function notify_player($playerId, ...$arg)
    {
        if ($this->getBgaEnvironment() == 'studio') {
            $this->log('notify player', $playerId, ...$arg);
        }
        $this->notify->player($playerId, ...$arg);
    }
    public function getCharacterHTML(?string $name = null)
    {
        if ($name) {
            $char = $this->character->getCharacterData($name);
        } else {
            $char = $this->character->getSubmittingCharacter();
            $name = $char['character_name'];
        }
        $playerName = $this->getPlayerNameById($char['playerId']);
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
                        return $v . ' ' . $this->data->getTokens()[$k]['name'];
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
    public function getCurrentPlayer(bool $bReturnNullIfNotLogged = false): int
    {
        return (int) parent::getCurrentPlayerId($bReturnNullIfNotLogged);
    }
    public function getFromDB(string $str)
    {
        return $this->getObjectFromDB($str);
    }
    public function eventLog($message = '', $arg = [])
    {
        $this->notify('notify', $message, $arg);
    }
    public function skip(string $name): void
    {
        if (!in_array($name, $this->gameData->get('skip'))) {
            $this->gameData->set('skip', [...$this->gameData->get('skip'), $name]);
        }
    }
    public function checkSkip(string $name): bool
    {
        $check = in_array($name, $this->gameData->get('skip'));
        if ($check) {
            $this->gameData->set(
                'skip',
                array_filter($this->gameData->get('skip'), function ($d) use ($name) {
                    return $d != $name;
                })
            );
        }
        return $check;
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
    public function cardDrawEvent($card, $deck, $arg = [])
    {
        $gameData = [];
        $this->getDecks($gameData);
        $result = [
            'card' => $card,
            'deck' => $deck,
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
            'gameData' => $gameData,
            ...$arg,
        ];
        $this->notify('cardDrawn', '', $result);
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
        if (array_key_exists($resourceType . '-cooked', $this->data->getTokens())) {
            $maxCount -= (int) $this->gameData->getResource($resourceType . '-cooked');
        } elseif ($rawResourceType != $resourceType && array_key_exists($rawResourceType, $this->data->getTokens())) {
            $maxCount -= (int) $this->gameData->getResource($rawResourceType);
        }
        if ($resourceType == 'wood') {
            $maxCount -= $this->gameData->getResource('fireWood');
        }
        $newValue = clamp($currentCount + $change, 0, $maxCount);
        $this->gameData->setResource($resourceType, $newValue);
        $difference = $currentCount - $newValue + $change;
        if ($change > 0) {
            $this->incStat($change, 'resources_collected', $this->character->getSubmittingCharacter()['playerId']);
        }

        return ['left' => $difference, 'changed' => $newValue - $currentCount];
    }
    public function rollFireDie(string $actionName, ?string $characterName = null): int
    {
        $this->markRandomness();
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
        $data['sendNotification'] = function () use ($value, $characterName, &$notificationSent, $actionName) {
            $sideNum = $value == 0 ? 1 : ($value == 3 ? 6 : ($value == 2 ? 5 : 2));
            if ($characterName) {
                $this->notify('rollFireDie', clienttranslate('${character_name} rolled a ${value} ${action_name}'), [
                    'value' => $value == 0 ? clienttranslate('blank') : $value,
                    'character_name' => $this->getCharacterHTML($characterName),
                    'characterId' => $characterName,
                    'roll' => $sideNum,
                    'action_name' => '(' . $actionName . ')',
                ]);
            } else {
                $this->notify('rollFireDie', clienttranslate('The fire die rolled a ${value} ${action_name}'), [
                    'value' => $value == 0 ? clienttranslate('blank') : $value,
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
        return $data['value'];
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
        $this->completeAction(false);
    }
    public function actChooseCharacters(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->addExtraTime();
        }
        $this->characterSelection->actChooseCharacters();
        $this->completeAction(false);
    }
    public function actMoveDiscovery(string $upgradeId, string $upgradeReplaceId): void
    {
        $selectableUpgrades = array_keys(
            array_filter($this->data->getBoards()['knowledge-tree-' . $this->getDifficulty()]['track'], function ($v) {
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
        $this->markChanged('knowledge');
        $this->completeAction();
    }
    public function actCook(string $resourceType): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actCook', null, $resourceType);
        $this->actions->validateSelectable(
            $resourceType,
            function ($d) {
                return $d;
            },
            'actCook'
        );

        $data = [
            'resourceType' => $resourceType,
        ];
        $this->hooks->onCook($data);
        $this->adjustResource($resourceType, -1);
        $this->adjustResource($resourceType . '-cooked', 1);
        $this->actions->spendActionCost('actCook');

        $this->notify('notify', clienttranslate('${character_name} cooked ${amount} ${type}'), [
            'amount' => 1,
            'type' => $resourceType,
            'usedActionId' => 'actCook',
        ]);
        $this->hooks->onCookAfter($data);
        $this->setLastAction('actCook');
        $this->completeAction();
    }
    public function actRevive(?string $character, ?string $food): void
    {
        if (!$character) {
            throw new BgaUserException(clienttranslate('Select a character'));
        }
        if (!$food) {
            throw new BgaUserException(clienttranslate('Select a food'));
        }
        if (!$this->character->getCharacterData($character)['incapacitated']) {
            throw new BgaUserException(clienttranslate('That character is not incapacitated'));
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
        } elseif ($food == 'berry-cooked') {
            $left = $this->adjustResource('berry-cooked', -$requireCount)['left'];
        } else {
            $left = $this->adjustResource('fish-cooked', -$requireCount);
        }
        if ($left != 0) {
            throw new BgaUserException(clienttranslate('Not enough resources'));
        }

        $this->character->updateCharacterData($character, function (&$data) {
            $data['health'] = clamp(3, 0, $data['maxHealth']);
            $this->log('actRevive', $data['health'], $data['incapacitated'], $data['maxHealth']);
        });
        $this->notify('notify', clienttranslate('${character_name} revived ${character_name_2} they should be recovered by the morning'), [
            'character_name_2' => $this->getCharacterHTML($character),
            'usedActionId' => 'actRevive',
        ]);
        $this->setLastAction('actRevive');
        $this->completeAction();
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
            throw new BgaUserException(clienttranslate('Requirements not met for this unlock'));
        } elseif (in_array($knowledgeId, $this->getUnlockedKnowledgeIds())) {
            throw new BgaUserException(clienttranslate('Already unlocked'));
        } elseif ($resourceCount < $availableUnlocks[$knowledgeId]['unlockCost']) {
            throw new BgaUserException(clienttranslate('Not enough knowledge points'));
        }
        $cost = -$availableUnlocks[$knowledgeId]['unlockCost'];
        foreach ($resources as $resource) {
            $cost = $this->adjustResource($resource, $cost)['left'];
        }

        $this->actions->spendActionCost('actSpendFKP');
        $this->unlockKnowledge($knowledgeId);
        $knowledgeObj = $this->data->getKnowledgeTree()[$knowledgeId];
        array_key_exists('onUse', $knowledgeObj) ? $knowledgeObj['onUse']($this, $knowledgeObj) : null;
        $this->notify('notify', clienttranslate('${character_name} unlocked ${knowledge_name}${knowledge_name_suffix}'), [
            'knowledgeId' => $knowledgeId,
            'knowledge_name' => $knowledgeObj['name'],
            'knowledge_name_suffix' => array_key_exists('name_suffix', $knowledgeObj) ? $knowledgeObj['name_suffix'] : '',
            'usedActionId' => 'actSpendFKP',
        ]);
        $this->hooks->onUnlock($knowledgeObj);
        $this->setLastAction('actSpendFKP');
        $this->completeAction();
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
                    throw new BgaUserException(clienttranslate('Select an item'));
                }
                $_this->actions->validateCanRunAction('actCraft', $itemName);
                if (!array_key_exists($itemName, $_this->data->getItems())) {
                    throw new BgaUserException(clienttranslate('Invalid Item'));
                }
                $itemType = $_this->data->getItems()[$itemName]['itemType'];
                $currentBuildings = $_this->gameData->get('buildings');
                if ($itemType == 'building' && sizeof($currentBuildings) >= $this->getMaxBuildingCount()) {
                    throw new BgaUserException(clienttranslate('A building has already been crafted'));
                }
                $result = [];
                $_this->getItemData($result);
                if (!isset($result['availableEquipment'][$itemName]) || $result['availableEquipment'][$itemName] == 0) {
                    throw new BgaUserException(clienttranslate('All of those available items have been crafted'));
                }
                return [
                    'itemName' => $itemName,
                    'item' => $_this->data->getItems()[$itemName],
                    'itemType' => $itemType,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $itemName = $data['itemName'];
                $item = $data['item'];
                $itemType = $data['itemType'];
                foreach ($item['cost'] as $key => $value) {
                    if ($_this->adjustResource($key, -$value)['left'] != 0) {
                        throw new BgaUserException(clienttranslate('Missing resources'));
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
                    usePerDay($_this->character->getSubmittingCharacterId() . 'craftjewlery', $this);
                } elseif ($itemType == 'building') {
                    $currentBuildings = $_this->gameData->get('buildings');
                    $itemId = $_this->gameData->createItem($itemName);
                    array_push($currentBuildings, ['name' => $itemName, 'itemId' => $itemId]);
                    $_this->gameData->set('buildings', $currentBuildings);
                } else {
                    $itemId = $_this->gameData->createItem($itemName);
                    $character = $_this->character->getSubmittingCharacter();
                    $this->character->equipAndValidateEquipment($character['id'], $itemId);
                }
                $this->hooks->onCraftAfter($data);
                $_this->eventLog(clienttranslate('${character_name} crafted a ${item_name}'), [
                    'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                    'usedActionId' => 'actCraft',
                ]);
            }
        );
        $this->setLastAction('actCraft');
        $this->completeAction();
    }
    public function actSendToCamp(?int $sendToCampId = null): void
    {
        $this->selectionStates->actSendToCamp($sendToCampId);
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
                array_values(
                    array_filter($campEquipment, function ($id) use ($itemId) {
                        return $id != $itemId;
                    })
                )
            );
            $this->markChanged('player');
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
        $items = $this->gameData->getCreatedItems();

        $this->notify('notify', clienttranslate('${item_name} destroyed'), [
            'item_name' => notifyTextButton([
                'name' => $this->data->getItems()[$items[$itemId]]['name'],
                'dataId' => $itemId,
                'dataType' => 'item',
            ]),
        ]);
    }
    public function actDestroyItem(int $itemId): void
    {
        $this->destroyItem($itemId);
        // $this->completeAction();
    }
    public function actConfirmTradeItem(): void
    {
        $this->itemTrade->actConfirmTradeItem();
        // $this->completeAction();
    }
    public function actCancelTrade(): void
    {
        $this->itemTrade->actCancelTrade();
        // $this->completeAction();
    }
    public function actTradeDone(): void
    {
        $this->itemTrade->actTradeDone();
        // $this->completeAction();
    }
    public function actTradeYield(): void
    {
        $this->itemTrade->actTradeYield();
        // $this->completeAction();
    }
    public function actTradeItem(#[JsonParam] array $data): void
    {
        $this->itemTrade->actTradeItem($data);
        // $this->completeAction();
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
                str_replace('${amount}', (string) $this->getTradeRatio(), clienttranslate('You must offer ${amount} resources'))
            );
        }
        if ($requestedSum != 1) {
            throw new BgaUserException(clienttranslate('You must request only one resource'));
        }
        $this->actions->spendActionCost('actTrade');
        $offeredStr = [];
        $requestedStr = [];
        foreach ($offered as $key => $value) {
            if ($value > 0) {
                $this->adjustResource($key, -$value);
                array_push($offeredStr, $this->data->getTokens()[$key]['name'] . "($value)");
            }
        }
        foreach ($requested as $key => $value) {
            if ($value > 0) {
                if (str_contains($key, '-cooked')) {
                    throw new BgaUserException(clienttranslate('You cannot trade for a cooked resource'));
                }
                if (str_contains($key, 'gem-')) {
                    throw new BgaUserException(clienttranslate('You cannot trade for gems'));
                }
                $this->adjustResource($key, $value);
                array_push($requestedStr, $this->data->getTokens()[$key]['name'] . "($value)");
            }
        }
        // Finalize the trade hooks
        $this->getTradeRatio(false);
        // $this->hooks->onTrade($data);
        $this->notify('notify', clienttranslate('${character_name} traded ${offered} for ${requested}'), [
            'offered' => join(', ', $offeredStr),
            'requested' => join(', ', $requestedStr),
            'usedActionId' => 'actTrade',
        ]);
        $this->setLastAction('actTrade');
        $this->completeAction();
    }
    public function actUseHerb(): void
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseHerb'],
            function (Game $_this) {
                return ['herb' => 1];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $this->notify('notify', '', [
                    'usedActionId' => 'actUseHerb',
                ]);
            }
        );

        $this->setLastAction('actUseHerb');
        $this->completeAction();
    }
    public function actEat(?string $resourceType = null, ?string $characterId = null): void
    {
        if (!$resourceType) {
            throw new BgaUserException(clienttranslate('Select a Resource'));
        }
        if ($characterId) {
            $this->character->setSubmittingCharacterById($characterId);
        }
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onEat'],
            function (Game $_this) use ($resourceType) {
                $this->actions->validateCanRunAction('actEat', null, $resourceType);
                $this->actions->validateSelectable(
                    $resourceType,
                    function ($d) {
                        return $d['id'];
                    },
                    'actEat'
                );

                $tokenData = $this->data->getTokens()[$resourceType];
                $data = [
                    'type' => $resourceType,
                    ...$tokenData['actEat'],
                    'tokenName' => $tokenData['name'],
                    'characterId' => $this->character->getSubmittingCharacterId(),
                ];
                return $data;
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if (array_key_exists('health', $data)) {
                    $data['health'] = $this->character->adjustActiveHealth($data['health']);
                }
                if (array_key_exists('stamina', $data)) {
                    $data['stamina'] = $this->character->adjustActiveStamina($data['stamina']);
                }
                $_this->actions->spendActionCost('actEat');
                $left = $this->adjustResource($data['type'], -$data['count'])['left'];
                if (!$data || !array_key_exists('notify', $data) || $data['notify'] != false) {
                    $this->notify(
                        'notify',
                        !array_key_exists('stamina', $data)
                            ? clienttranslate('${character_name} ate ${count} ${token_name} and gained ${health} health')
                            : (array_key_exists('health', $data)
                                ? clienttranslate(
                                    '${character_name} ate ${count} ${token_name} and gained ${health} health and ${stamina} stamina'
                                )
                                : clienttranslate('${character_name} ate ${count} ${token_name} and gained ${stamina} stamina')),
                        [...$data, 'token_name' => $data['tokenName'], 'usedActionId' => 'actEat']
                    );
                }
            }
        );

        $this->setLastAction('actEat');
        $this->completeAction();
    }
    public function actAddWood(): void
    {
        // $this->character->addExtraTime();
        $this->actions->validateCanRunAction('actAddWood');
        $data = $this->gameData->getResources('fireWood', 'wood');
        $this->hooks->onAddFireWood($data);
        $this->gameData->setResource('fireWood', min($data['fireWood'] + 1, $this->gameData->getResourceMax('wood')));
        $this->gameData->setResource('wood', max($data['wood'] - 1, 0));

        $this->notify('notify', clienttranslate('${character_name} added ${count} ${token_name} to the fire'), [
            'token_name' => 'wood',
            'count' => 1,
            'usedActionId' => 'actAddWood',
        ]);
        $this->setLastAction('actAddWood');
        $this->completeAction();
    }
    public function actUseSkill(string $skillId, ?string $skillSecondaryId = null): void
    {
        if ($this->gamestate->state(true, false, true)['name'] == 'playerTurn') {
            $this->setLastAction('actUseSkill');
        }
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseSkill'],
            function (Game $_this) use ($skillId, $skillSecondaryId) {
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                // $this->character->addExtraTime();
                $_this->actions->validateCanRunAction('actUseSkill', $skillId);
                $res = $_this->character->getSkill($skillId);
                $skill = $res['skill'];
                $character = $res['character'];
                $_this->character->setSubmittingCharacter(null);
                return [
                    'skillId' => $skillId,
                    'skillSecondaryId' => $skillSecondaryId,
                    'skill' => $skill,
                    'character' => $character,
                    'turnCharacter' => $this->character->getTurnCharacter(),
                    'nextState' => $this->gamestate->state(true, false, true)['name'] == 'dayEvent' ? 'playerTurn' : false,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $skill = $data['skill'];
                $character = $data['character'];
                $skillId = $data['skillId'];
                $skillSecondaryId = array_key_exists('skillSecondaryId', $data) ? $data['skillSecondaryId'] : null;
                $_this->hooks->reconnectHooks($skill, $_this->character->getSkill($skillId)['skill']);
                $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                $notificationSent = false;
                $skill['sendNotification'] = function () use (&$skill, $_this, &$notificationSent) {
                    $_this->notify('notify', clienttranslate('${character_name} used the skill ${skill_name}'), [
                        'skill_name' => $skill['name'],
                        'usedActionId' => 'actUseSkill',
                        'usedActionName' => $skill['name'],
                    ]);
                    $notificationSent = true;
                };
                if ($_this->gamestate->state(true, false, true)['name'] == 'interrupt') {
                    // Only applies to skills from an interrupt state
                    $skill['sendNotification']();
                    $_this->actInterrupt->actInterrupt($skillId, $skillSecondaryId);
                    $_this->actions->spendActionCost('actUseSkill', $skillId);
                    $_this->character->setSubmittingCharacter('actUseSkill', $skillId);
                }
                if (!array_key_exists('interruptState', $skill) || (in_array('interrupt', $skill['state']) && $finalizeInterrupt)) {
                    $result = array_key_exists('onUse', $skill) ? $skill['onUse']($this, $skill, $character) : null;
                    if (!$result || !array_key_exists('spendActionCost', $result) || $result['spendActionCost'] != false) {
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
                    $this->nextState($data['nextState']);
                }
            }
        );
        $this->completeAction();
    }
    public function actUseItem(string $skillId): void
    {
        if ($this->gamestate->state(true, false, true)['name'] == 'playerTurn') {
            $this->setLastAction('actUseItem');
        }
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onUseSkill'],
            function (Game $_this) use ($skillId) {
                $_this->character->setSubmittingCharacter('actUseItem', $skillId);
                // $this->character->addExtraTime();
                $_this->actions->validateCanRunAction('actUseItem', $skillId);
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

                $skills = $this->actions->getActiveEquipmentSkills();
                $_this->hooks->reconnectHooks($skill, $skills[$skillId]);
                $_this->character->setSubmittingCharacter('actUseItem', $skillId);
                $notificationSent = false;
                $skill['sendNotification'] = function () use (&$skill, $_this, &$notificationSent) {
                    $_this->notify('notify', clienttranslate('${character_name} used the item\'s skill ${skill_name}'), [
                        'skill_name' => $skill['name'],
                        'usedActionId' => 'actUseSkill',
                        'usedActionName' => $skill['name'],
                    ]);
                    $notificationSent = true;
                };
                if ($_this->gamestate->state(true, false, true)['name'] == 'interrupt') {
                    $_this->actInterrupt->actInterrupt($skillId);
                    $_this->character->setSubmittingCharacter('actUseItem', $skillId);
                    $skill['sendNotification']();
                }
                if (!array_key_exists('interruptState', $skill) || (in_array('interrupt', $skill['state']) && $finalizeInterrupt)) {
                    $result = array_key_exists('onUse', $skill) ? $skill['onUse']($this, $skill, $character) : null;
                    if (!$result || !array_key_exists('spendActionCost', $result) || $result['spendActionCost'] != false) {
                        $_this->actions->spendActionCost('actUseItem', $skillId);
                    }
                    if (!$notificationSent && (!$result || !array_key_exists('notify', $result) || $result['notify'] != false)) {
                        $skill['sendNotification']();
                    }
                }
                $_this->character->setSubmittingCharacter(null);
            }
        );
        $this->completeAction();
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
            throw new BgaUserException(clienttranslate('You need a tool to harvest'));
        }
        if (
            sizeof(
                array_filter($this->character->getActiveEquipment(), function ($data) {
                    return $data['itemType'] == 'tool' && !in_array($data['id'], ['mortar-and-pestle', 'bandage']);
                })
            ) == 0
        ) {
            throw new BgaUserException(clienttranslate('The equipped tool can\'t be used for harvesting'));
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
            throw new BgaUserException(clienttranslate('You need a weapon to hunt'));
        }
        $this->actDraw('hunt');
    }
    public function actDrawExplore(): void
    {
        $this->actDraw('explore');
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
                    $this->gameData->set('tempDeckDiscard', $this->decks->getDecksData()['decksDiscards']);
                    $this->incStat(1, 'cards_drawn', $this->character->getSubmittingCharacter()['playerId']);
                    $card = $this->decks->pickCard($deck);
                    $this->eventLog(clienttranslate('${character_name} draws from the ${deck} deck'), [
                        'deck' => $this->decks->getDeckName($deck),
                        'usedActionId' => 'actDraw' . ucfirst($deck),
                    ]);
                }

                return ['deck' => $deck, 'card' => [...$card], ...$data];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $deck = $data['deck'];
                $card = $data['card'];
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actDraw' . ucfirst($deck));
                }

                if (!$data['cancel']) {
                    $_this->gameData->set('state', ['card' => $card, 'deck' => $deck]);
                    $_this->nextState('drawCard');
                }
            }
        );
        $this->setLastAction($deck);
        $this->completeAction();
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
                $roll = $_this->rollFireDie(clienttranslate('Investigate Fire'), $character['character_name']);
                return ['roll' => $roll, 'originalRoll' => $roll, 'guess' => $guess];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if (!array_key_exists('spendActionCost', $data) || $data['spendActionCost'] != false) {
                    $_this->actions->spendActionCost('actInvestigateFire');
                }
                $_this->adjustResource('fkp', $data['roll']);
                if ($data['roll'] > 0) {
                    $this->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                        'count' => $data['roll'],
                        'resource_type' => 'fkp',
                        'usedActionId' => 'actInvestigateFire',
                    ]);
                }
            }
        );
        $this->setLastAction('actInvestigateFire');
        $this->completeAction();
    }
    public function actEndTurn(): void
    {
        // Notify all players about the choice to pass.
        $this->eventLog(clienttranslate('${character_name} ends their turn'), [
            'usedActionId' => 'actEndTurn',
        ]);

        // at the end of the action, move to the next state
        $this->endTurn();
        $this->completeAction(false);
    }
    #[CheckAction(false)]
    public function actForceSkip(): void
    {
        $this->gamestate->checkPossibleAction('actForceSkip');
        $stateName = $this->gamestate->state(true, false, true)['name'];
        if ($stateName == 'interrupt') {
            if (!$this->actInterrupt->onInterruptCancel(true)) {
                $this->nextState('playerTurn');
            }
            $this->completeAction();
        } elseif ($stateName == 'dinnerPhase') {
            $this->gamestate->unsetPrivateStateForAllPlayers();
            $this->nextState('nightPhase');
        } elseif ($stateName == 'tradePhase') {
            $privateState = $this->gamestate->getPrivateState($this->getCurrentPlayer());
            if ($privateState && $privateState['name'] == 'waitTradePhase') {
                $this->itemTrade->actCancelTrade();
            } else {
                $this->itemTrade->actForceSkip();
            }
        }
    }
    #[CheckAction(false)]
    public function actUnBack(): void
    {
        $this->gamestate->checkPossibleAction('actUnBack');
        $stateName = $this->gamestate->state(true, false, true)['name'];
        if ($stateName == 'characterSelect') {
            $this->characterSelection->actUnBack();
        } elseif ($stateName == 'dinnerPhase') {
            $playerId = $this->getCurrentPlayer();
            $this->gamestate->setPlayersMultiactive([$playerId], '');
            $this->gamestate->initializePrivateState($playerId);
        } elseif ($stateName == 'tradePhase') {
            $this->itemTrade->actUnBack();
        }
    }
    public function actDone(): void
    {
        // $this->character->addExtraTime();
        $saveState = true;
        $stateName = $this->gamestate->state(true, false, true)['name'];
        if ($stateName == 'postEncounter') {
            $this->nextState('playerTurn');
        } elseif ($stateName == 'tradePhase') {
            $this->nextState('playerTurn');
        } elseif ($stateName == 'interrupt') {
            if (!$this->actInterrupt->onInterruptCancel()) {
                $this->nextState('playerTurn');
            }
        } elseif ($stateName == 'dinnerPhase') {
            $saveState = false;
            $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayer(), 'nightPhase');
        } elseif ($stateName == 'startHindrance') {
            $this->markChanged('token');
            // TODO Depreciated Can be removed
            if (!$this->gameData->get('upgradesCount')) {
                if (
                    sizeof(
                        array_filter($this->gameData->get('upgrades'), function ($v) {
                            return $v['replace'] == null;
                        })
                    ) > 0
                ) {
                    throw new BgaUserException(
                        sprintf(self::_('%d discoveries must replace existing track discoveries'), sizeof($this->gameData->get('upgrades')))
                    );
                }
            } else {
                if (
                    sizeof(
                        array_filter($this->gameData->get('upgrades'), function ($v) {
                            return $v['replace'] != null;
                        })
                    ) != $this->gameData->get('upgradesCount')
                ) {
                    throw new BgaUserException(
                        sprintf(self::_('%d discoveries must replace existing track discoveries'), $this->gameData->get('upgradesCount'))
                    );
                }
                $this->gameData->set(
                    'upgrades',
                    array_filter($this->gameData->get('upgrades'), function ($v) {
                        return $v['replace'] != null;
                    })
                );
            }

            $saveState = false;
            $this->character->addExtraTime();
            $this->gamestate->setPlayerNonMultiactive($this->getCurrentPlayer(), 'playerTurn');
        }
        $this->completeAction($saveState);
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
        $this->getResources($result);
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
            ...$this->gameData->get('drawNightState') ?? $this->gameData->get('state'),
            'resolving' => $this->actInterrupt->isStateResolving(),
            'character_name' => $this->getCharacterHTML(),
            'activeTurnPlayerId' => 0,
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
        $resp = $this->encounter->actChooseWeapon($weaponId);
        $this->completeAction();
        return $resp;
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
    public function actCancel(): void
    {
        $this->selectionStates->actCancel();
    }
    public function argSelectionState(): array
    {
        return $this->selectionStates->argSelectionState();
    }
    public function actSelectCharacter(?string $characterId = null): void
    {
        $this->selectionStates->actSelectCharacter($characterId);
    }
    public function actSelectButton(?string $buttonValue = null): void
    {
        $this->selectionStates->actSelectButton($buttonValue);
    }
    public function actSelectEat(?string $resourceType = null): void
    {
        $this->selectionStates->actSelectEat($resourceType);
    }
    public function _actSelectEat(?string $resourceType = null, array $selectionState): void
    {
        $this->selectionStates->_actSelectEat($resourceType, $selectionState);
    }
    public function actSelectResource(?string $resourceType = null): void
    {
        $this->selectionStates->actSelectResource($resourceType);
    }
    public function actTokenReduceSelection(#[JsonParam] array $data): void
    {
        $this->selectionStates->actTokenReduceSelection($data);
    }
    public function actSelectHindrance(#[JsonParam] array $data): void
    {
        $this->selectionStates->actSelectHindrance($data);
    }
    public function actSelectCard(?string $cardId = null): void
    {
        $this->selectionStates->actSelectCard($cardId);
    }
    public function actSelectItem(?string $itemId = null): void
    {
        $this->selectionStates->actSelectItem($itemId);
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        $this->selectionStates->actSelectDeck($deckName);
    }
    public function stPlayerTurn()
    {
        $this->gameData->set('tempLastItemOwners', []);
        $this->actions->clearDayEvent();
        // if (!$this->actInterrupt->checkForInterrupt()) {
        $char = $this->character->getTurnCharacter();
        // $this->hooks->onPlayerTurn($char);
        if ($char['isActive'] && $char['incapacitated']) {
            $this->eventLog(clienttranslate('${character_name} is incapacitated'));
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

                    $this->eventLog(clienttranslate('${character_name} found ${count} ${name}(s) ${buttons}'), [
                        ...$card,
                        'buttons' => notifyButtons([
                            ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'card'],
                        ]),
                    ]);
                } elseif ($card['deckType'] == 'encounter') {
                    // Change state and check for health/damage modifications
                    $this->eventLog(clienttranslate('${character_name} encountered a ${name} (${health} health, ${damage} damage)'), [
                        ...$card,
                        'name' => notifyTextButton([
                            'name' => $card['name'],
                            'dataId' => $card['id'],
                            'dataType' => 'card',
                        ]),
                    ]);
                } elseif ($card['deckType'] == 'nothing') {
                    if (!$this->isValidExpansion('mini-expansion')) {
                        $this->eventLog(clienttranslate('${character_name} did nothing ${buttons}'), [
                            'buttons' => notifyButtons([
                                ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'card'],
                            ]),
                        ]);
                    }
                } elseif ($card['deckType'] == 'physical-hindrance') {
                    $this->eventLog(clienttranslate('${character_name} must draw a ${deck} ${buttons}'), [
                        'deck' => clienttranslate('Physical Hindrance'),
                        'buttons' => notifyButtons([
                            ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'card'],
                        ]),
                    ]);
                } elseif ($card['deckType'] == 'mental-hindrance') {
                    $this->eventLog(clienttranslate('${character_name} must draw a ${deck} ${buttons}'), [
                        'deck' => clienttranslate('Mental Hindrance'),
                        'buttons' => notifyButtons([
                            ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'card'],
                        ]),
                    ]);
                } else {
                }
                return [...$state, 'discard' => false];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) use (&$moveToDrawCardState) {
                $deck = $data['deck'];
                $card = $data['card'];
                if ($data['discard']) {
                    $this->nextState('playerTurn');
                } elseif ($card['deckType'] == 'resource') {
                    $this->nextState('playerTurn');
                } elseif ($card['deckType'] == 'encounter') {
                    $this->nextState('resolveEncounter');
                } elseif ($card['deckType'] == 'nothing') {
                    if ($this->isValidExpansion('mini-expansion')) {
                        $card = $this->decks->pickCard('day-event');
                        $this->gameData->set('state', ['card' => $card, 'deck' => 'day-event']);
                        $this->actions->addDayEvent($card['id']);
                        $moveToDrawCardState = true;
                    } else {
                        $this->nextState('playerTurn');
                    }
                } elseif (
                    $card['deck'] != $card['deckType'] &&
                    ($card['deckType'] == 'physical-hindrance' || $card['deckType'] == 'mental-hindrance')
                ) {
                    $this->checkHindrance(true, $this->character->getSubmittingCharacterId());
                    $this->nextState('playerTurn');
                } elseif ($card['deckType'] == 'day-event') {
                    $this->nextState('dayEvent');
                } else {
                    $this->nextState('playerTurn');
                }
            }
        );
        if ($moveToDrawCardState) {
            $this->nextState('drawCard');
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
                $card = $data['card'];
                $this->eventLog(clienttranslate('Something unexpected happens, drawing a ${day_event}'), [
                    'day_event' => notifyButtons([
                        ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'day-event'],
                    ]),
                ]);
                // $this->nextState('playerTurn');
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
                $this->gameData->set('drawNightState', ['card' => $card, 'deck' => 'night-event']);
                return ['card' => $card, 'deck' => 'night-event'];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $deck = $data['deck'];
                $this->eventLog(clienttranslate('It\'s night, drawing from the night deck'));
                $this->nextState('nightDrawCard');
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
                $state = $this->gameData->get('drawNightState') ?? $this->gameData->get('state');
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
                if (
                    (!$data || !array_key_exists('notify', $data) || $data['notify'] != false) &&
                    (!$result || !array_key_exists('notify', $result) || $result['notify'] != false)
                ) {
                    $this->eventLog('${buttons}', [
                        'buttons' => notifyButtons([
                            ['name' => $this->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'night-event'],
                        ]),
                    ]);
                }
                if (
                    (!$data || !array_key_exists('nextState', $data) || $data['nextState'] != false) &&
                    (!$result || !array_key_exists('nextState', $result) || $result['nextState'] != false)
                ) {
                    $this->nextState('morningPhase');
                }
            }
        );
    }

    public function argSelectionCount(): array
    {
        $result = ['actions' => []];
        $this->getAllPlayers($result);
        return $result;
    }
    public function argStartHindrance(): array
    {
        $selectableUpgrades = array_keys(
            array_filter($this->data->getBoards()['knowledge-tree-' . $this->getDifficulty()]['track'], function ($v) {
                return !array_key_exists('upgradeType', $v);
            })
        );
        $result = [...$this->getArgsData(), 'selectableUpgrades' => $selectableUpgrades];
        return $result;
    }
    public function log(...$args)
    {
        $e = new \Exception();
        $stack = preg_split('/[\r\n]+/', $e->getTraceAsString());
        $stackString = join(
            PHP_EOL,
            array_map(
                function ($d) {
                    return '&nbsp;&nbsp;&nbsp;&nbsp;' .
                        preg_replace('/Bga\\\\Games\\\\[^\\\\]+\\\\/', '', preg_replace('/#.*modules\\/(.*\d\\)): (.*)/', '$1: $2', $d));
                },
                array_filter($stack, function ($d) {
                    return str_contains($d, '/games/');
                })
            )
        );
        // preg_match('/#0.*modules\/(.*\d\)):/', $e->getTraceAsString(), $m);  . (array_key_exists(1, $m) ? ' [' . $m[1] . ']' : '')
        if ($this->gamestate == null) {
            $this->trace('TRACE [__init] ' . json_encode($args) . PHP_EOL . $stackString);
        } else {
            $this->trace(
                'TRACE [' . $this->gamestate->state(true, false, true)['name'] . '] ' . json_encode($args) . PHP_EOL . $stackString
            );
        }
    }
    public function argPlayerState(): array
    {
        $result = [...$this->getArgsData()];

        $decksDiscards = $this->gameData->get('tempDeckDiscard');
        if ($decksDiscards) {
            unset($result['decksDiscards']);
            $this->gameData->set('tempDeckDiscard', null);
        }
        return $result;
    }
    public function argDayEvent(): array
    {
        $state = $this->gameData->get('state');
        $card = $state['card'];
        $result = [
            ...$this->getArgsData(),
            'character_name' => $this->getCharacterHTML(),
            //'actions' => [],//array_values($this->data->getExpansion()[$card['id']]['skills']),
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
            'activeTurnPlayerId' => 0,
            // 'availableSkills' => array_values(
            //     $this->actions->wrapSkills(
            //         array_filter($this->data->getExpansion()[$card['id']]['skills'], function ($skill) {
            //             return $skill['type'] == 'skill';
            //         }),
            //         'actUseSkill'
            //     )
            // ),
            // 'availableItemSkills' => array_values(
            //     $this->actions->wrapSkills(
            //         array_filter($this->data->getExpansion()[$card['id']]['skills'], function ($skill) {
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
        // Compute and return the game progression
        extract($this->gameData->getAll('day', 'turnNo'));
        return ((($day - 1) * 4 + ($turnNo ?? 0)) / (12 * 4)) * 100;
    }
    public function endTurn()
    {
        $data = [
            'characterId' => $this->character->getTurnCharacterId(),
        ];
        $this->hooks->onEndTurn($data);
        $this->gameData->set('lastAction', null);
        $this->nextState('endTurn');
        $this->undo->clearUndoHistory();
    }
    /**
     * The action method of state `nextCharacter` is called every time the current game state is set to `nextCharacter`.
     */
    public function stNextCharacter(): void
    {
        // Retrieve the active player ID.
        while (true) {
            if ($this->character->isLastCharacter()) {
                $this->nextState('dinnerPhase');
                $this->actions->clearDayEvent();
                break;
            } else {
                $this->character->activateNextCharacter();
                $this->actions->clearDayEvent();
                if ($this->character->getActiveHealth() == 0) {
                    $this->notify('playerTurn', clienttranslate('${character_name} is incapacitated'), []);
                } else {
                    $this->nextState('playerTurn');
                    $this->notify('playerTurn', clienttranslate('${character_name} begins their turn'), []);
                    break;
                }
            }
        }
    }
    public function getDinnerPhaseActions($playerId)
    {
        $characters = $this->character->getAllCharacterDataForPlayer($playerId);
        $actAddWood = $this->actions->getAction('actAddWood');
        $hasWood = $actAddWood['requires']($this, $actAddWood);
        $actSpendFKP = $this->actions->getAction('actSpendFKP');
        $hasFKP = $actSpendFKP['requires']($this, $actSpendFKP);
        $actions = array_values(
            array_map(
                function ($char) {
                    return [
                        ...$this->actions->getActionCost('actEat', null, $char['character_name']),
                        'action' => 'actEat',
                        'character' => $char['character_name'],
                        'type' => 'action',
                    ];
                },
                array_filter($characters, function ($char) {
                    return !$char['incapacitated'] || $char['recovering'];
                })
            )
        );
        return [...$actions, ...$hasWood ? [$actAddWood] : [], ...$hasFKP ? [$actSpendFKP] : []];
    }
    public function argDinnerPhase($playerId)
    {
        $characters = $this->character->getAllCharacterDataForPlayer($playerId);
        $result = [
            'version' => $this->getVersion(),
            'actions' => $this->getDinnerPhaseActions($playerId),
            'dinnerEatableFoods' => [],
            'activeTurnPlayerId' => 0,
        ];
        $this->getItemData($result);
        $this->getGameData($result);
        $this->getResources($result);

        foreach ($characters as $char) {
            if (!$char['incapacitated'] || $char['recovering']) {
                $result['dinnerEatableFoods'][$char['id']] = array_map(function ($eatable) use ($char) {
                    $data = [...$eatable['actEat'], 'id' => $eatable['id'], 'characterId' => $char['id']];
                    $this->hooks->onGetEatData($data);
                    return $data;
                }, $this->actions->getActionSelectable('actEat', null, $char['id']));
            }
        }
        return $result;
    }
    public function stDinnerPhase()
    {
        $action = $this->actions->getAction('actEat');
        $hasFood = $action['requires']($this, $action);
        $actAddWood = $this->actions->getAction('actAddWood');
        $hasWood = $actAddWood['requires']($this, $actAddWood);
        $actSpendFKP = $this->actions->getAction('actSpendFKP');
        $hasFKP = $actSpendFKP['requires']($this, $actSpendFKP);
        if ($hasFood || $hasWood || $hasFKP) {
            $this->gamestate->setAllPlayersMultiactive();
            foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
                $this->giveExtraTime((int) $playerId);
            }
            $this->gamestate->initializePrivateStateForAllActivePlayers();
        } else {
            $this->notify('playerTurn', clienttranslate('The tribe skipped dinner as there is nothing to do'));
            $this->nextState('nightPhase');
        }
    }
    public function stSelectCharacter()
    {
        $this->gamestate->setAllPlayersMultiactive();
        foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
            $this->giveExtraTime((int) $playerId);
        }
        if ($this->isValidExpansion('hindrance')) {
            $randomUpgrades = $this->useRandomUpgrades();
            $upgrades = $this->data->getUpgrades();
            $count = 5;
            if ($this->getDifficulty() == 'easy') {
                $count = 4;
            } elseif ($this->getDifficulty() == 'hard') {
                $count = 6;
            }
            if ($randomUpgrades) {
                shuffle($upgrades);
                $upgrades = array_slice($upgrades, 0, $count);
                $upgrades = array_column(
                    array_map(function ($k) {
                        return [$k['id'], ['replace' => null]];
                    }, $upgrades),
                    1,
                    0
                );
            } else {
                array_orderby($upgrades, 'name', SORT_ASC);
                $upgrades = array_column(
                    array_map(function ($k) {
                        return [$k['id'], ['replace' => null]];
                    }, $upgrades),
                    1,
                    0
                );
            }
            $this->gameData->set('upgrades', $upgrades);
            $this->gameData->set('upgradesCount', $count);
        }
    }
    public function stStartHindrance()
    {
        $players = array_values(
            array_filter($this->loadPlayersBasicInfos(), function ($d) {
                return $d['player_no'] == 1;
            })
        );
        $this->gamestate->setPlayersMultiactive([$players[0]['player_id']], 'playerTurn');
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
        $eloMapping = [5, 10, 12, 15];

        $trackEloMapping = [0, 5];
        $score = $eloMapping[$this->gameData->get('difficulty')] + $trackEloMapping[$this->gameData->get('trackDifficulty')];
        $this->DbQuery("UPDATE player SET player_score={$score} WHERE 1=1");
        $this->nextState('endGame');
    }
    public function lose()
    {
        $this->DbQuery('UPDATE player SET player_score=0 WHERE 1=1');
        $this->nextState('endGame');
    }
    public function stMorningPhase()
    {
        $this->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->hooks, 'onMorning'],
            function (Game $_this) {
                $woodNeeded = $this->getFirewoodCost();
                $day = $this->gameData->get('day');
                $day += 1;
                $this->gameData->set('day', $day);
                $fireWood = $this->gameData->getResource('fireWood');
                if (array_key_exists('allowFireWoodAddition', $this->gameData->get('morningState') ?? [])) {
                    $this->gameData->set('morningState', [...$this->gameData->get('morningState') ?? [], 'allowFireWoodAddition' => false]);
                    if ($fireWood < $woodNeeded + 1) {
                        $missingWood = $woodNeeded + 1 - $fireWood;
                        $wood = $this->gameData->getResource('wood');
                        if ($wood >= $missingWood) {
                            $this->gameData->setResource(
                                'fireWood',
                                min($fireWood + $missingWood, $this->gameData->getResourceMax('wood'))
                            );
                            $this->gameData->setResource('wood', max($wood - $missingWood, 0));
                            $this->notify(
                                'notify',
                                clienttranslate('During the night the tribe quickly added ${woodNeeded} ${token_name} to the fire'),
                                [
                                    'woodNeeded' => $woodNeeded,
                                    'token_name' => 'wood',
                                ]
                            );
                        }
                    }
                }

                $this->setStat($day, 'day_number');
                resetPerDay($this);
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
                    'changeOrder' => true,
                    'nextState' => 'tradePhase',
                    'day' => $day,
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
                    if ($data['incapacitated'] && $data['recovering']) {
                        $data['incapacitated'] = false;
                    }

                    if (!$data['incapacitated']) {
                        $data['stamina'] = $data['maxStamina'];
                        $data['stamina'] = clamp($data['stamina'] + $stamina, 0, $data['maxStamina']);
                    }
                });
                if ($health != 0) {
                    $this->notify('morningPhase', clienttranslate('Everyone lost ${amount} ${character_resource}'), [
                        'amount' => -$health,
                        'character_resource' => clienttranslate('Health'),
                    ]);
                }

                $this->notify('morningPhase', clienttranslate('The fire pit used ${amount} wood'), [
                    'amount' => $woodNeeded,
                ]);
                $this->adjustResource('fireWood', -$woodNeeded);
                if ($this->gameData->getResource('fireWood') <= 0) {
                    $this->lose();
                }
                $this->notify('morningPhase', clienttranslate('Morning has arrived (Day ${day})'), [
                    'day' => $data['day'],
                ]);
                $this->hooks->onMorningAfter($data);
                if ($data['changeOrder']) {
                    $this->character->rotateTurnOrder();
                }
                if ($data['nextState'] != false) {
                    $this->nextState('tradePhase');
                }
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
        // if ($from_version <= 2506201717) {
        //     // ! important ! Use DBPREFIX_<table_name> for all tables
        //     try {
        //         $sql = 'ALTER TABLE DBPREFIX_item ADD  `last_owner` varchar(10)';
        //         $this->applyDbUpgradeToAllDB($sql);
        //     } catch (Exception $e) {
        //     }
        // }
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
        $result['characters'] = $this->character->getMarshallCharacters();
        $result['players'] = $this->getCollectionFromDb('SELECT `player_id` `id`, player_no FROM `player`');
    }
    public function getDecks(&$result): void
    {
        $data = $this->decks->getDecksData();
        $result['decks'] = $data['decks'];
        $result['decksDiscards'] = $data['decksDiscards'];
    }
    public function getCraftedItems(): array
    {
        $items = $this->gameData->getCreatedItems();
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
        $buildings = array_count_values(
            array_map(function ($d) use ($items) {
                return $d['name'];
            }, $this->gameData->get('buildings'))
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
        foreach (array_keys($campEquipment + $destroyedEquipment + $equippedCounts + $buildings) as $key) {
            $sums[$key] =
                (array_key_exists($key, $campEquipment) ? $campEquipment[$key] : 0) +
                (array_key_exists($key, $destroyedEquipment) ? $destroyedEquipment[$key] : 0) +
                (array_key_exists($key, $buildings) ? $buildings[$key] : 0) +
                (array_key_exists($key, $equippedCounts) ? $equippedCounts[$key] : 0);
        }
        return $sums;
    }
    public function hasResourceCost(array $item)
    {
        $resources = $this->gameData->getResources();

        $hasResources = true;
        foreach ($item['cost'] as $key => $value) {
            if ($resources[$key] < $value) {
                $hasResources = false;
            }
        }
        $results = [
            'itemId' => $item['id'],
            'cost' => $item['cost'],
            'resources' => $resources,
            'hasResources' => $hasResources,
        ];
        $this->hooks->onHasResourceCost($results);
        return $results['hasResources'];
    }
    public function getItemData(&$result): void
    {
        $result['builtEquipment'] = $this->getCraftedItems();
        $result['buildings'] = $this->gameData->get('buildings');
        $items = $this->gameData->getCreatedItems();
        $result['campEquipmentCounts'] = array_count_values(
            array_map(function ($d) use ($items) {
                return $items[$d];
            }, $this->gameData->get('campEquipment'))
        );
        $result['campEquipment'] = array_values(
            array_map(function ($d) use ($items) {
                return ['name' => $items[$d], 'itemId' => $d];
            }, $this->gameData->get('campEquipment'))
        );

        $result['cookableFoods'] = $this->actions->getActionSelectable('actCook');

        $result['eatableFoods'] = array_map(function ($eatable) {
            $data = [...$eatable['actEat'], 'id' => $eatable['id'], 'characterId' => $this->character->getTurnCharacterId()];
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
                $item = $this->data->getItems()[$itemName];
                return $this->hasResourceCost($item);
            })
        );

        $craftingLevel = $this->gameData->get('craftingLevel');
        $buildings = $this->gameData->get('buildings');
        $allBuildableEquipment = array_values(
            array_filter(
                $this->data->getItems(),
                function ($v, $k) use ($craftingLevel) {
                    return $v['type'] == 'item' && $v['craftingLevel'] <= $craftingLevel;
                },
                ARRAY_FILTER_USE_BOTH
            )
        );
        $result['availableEquipmentCount'] = array_combine(
            array_map(function ($d) {
                return $d['id'];
            }, $allBuildableEquipment),
            array_map(function ($d) use ($result, $buildings) {
                if ($d['itemType'] == 'building' && sizeof($buildings) >= $this->getMaxBuildingCount()) {
                    return 0;
                }
                return $d['count'] - (array_key_exists($d['id'], $result['builtEquipment']) ? $result['builtEquipment'][$d['id']] : 0);
            }, $allBuildableEquipment)
        );

        $result['foreverUseItems'] = getUsePerForeverItems($this);
    }
    public function getValidTokens(): array
    {
        return array_filter($this->data->getTokens(), function ($v) {
            return $v['type'] == 'resource' &&
                (!array_key_exists('requires', $v) || $v['requires']($this, $v)) &&
                (!array_key_exists('expansion', $v) || $this->isValidExpansion($v['expansion']));
        });
    }
    public function getResources(&$result): void
    {
        $result['destroyedResources'] = $this->gameData->get('destroyedResources');
        $result['resources'] = $this->gameData->get('resources');
        $result['prevResources'] = $this->gameData->getPreviousResources();
        $tokens = $this->gameData->get('tokens') ?? [];
        $trapCount = sizeof(
            array_filter(array_keys($tokens ?? []), function ($deck) use ($tokens) {
                return in_array('trap', $tokens[$deck]);
            })
        );
        $result['resources']['trap'] += $trapCount;

        $resourcesAvailable = [];
        $tokensData = $this->data->getTokens();
        array_walk($tokensData, function ($v, $k) use (&$result, &$resourcesAvailable) {
            if ($v['type'] == 'resource' && isset($result['resources'][$k])) {
                if (
                    (!array_key_exists('requires', $v) || $v['requires']($this, $v)) &&
                    (!array_key_exists('expansion', $v) || $this->isValidExpansion($v['expansion']))
                ) {
                    if (array_key_exists('cooked', $v)) {
                        $cooked = $v['cooked'];
                        $resourcesAvailable[$cooked] =
                            (array_key_exists($cooked, $resourcesAvailable) ? $resourcesAvailable[$cooked] : 0) - $result['resources'][$k];
                    } else {
                        $resourcesAvailable[$k] =
                            (array_key_exists($k, $resourcesAvailable) ? $resourcesAvailable[$k] : 0) +
                            $this->gameData->getResourceMax($k) -
                            $result['resources'][$k] -
                            ($k === 'wood' ? $result['resources']['fireWood'] ?? 0 : 0);
                    }
                } else {
                    unset($result['resources'][$k]);
                    unset($result['prevResources'][$k]);
                }
            }
        });

        $result['resourcesAvailable'] = $resourcesAvailable;
    }
    public function getGameData(&$result): void
    {
        $result['game'] = $this->gameData->getAll();
        // Need to remove these otherwise the response is too big
        foreach (array_keys($result['game']) as $key) {
            if (str_contains($key, 'State')) {
                unset($result['game'][$key]);
            }
        }

        unset($result['game']['state']);
        unset($result['game']['resources']);
        unset($result['game']['destroyedResources']);
    }
    public function getExpansion()
    {
        $expansionMapping = self::$expansionList;
        return $expansionMapping[$this->gameData->get('expansion')];
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
            $data = $this->data->getItems()[$building['name']];
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
            $card = $this->data->getDecks()[$cardId];
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
            return $this->data->getKnowledgeTree()[$unlock];
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
        $this->markChanged('knowledge');
    }
    public function getDifficulty()
    {
        $difficultyMapping = ['easy', 'normal', 'normal+', 'hard'];
        return $difficultyMapping[$this->gameData->get('difficulty')];
    }
    public function getTrackDifficulty()
    {
        $difficultyMapping = ['normal', 'hard'];
        return $difficultyMapping[$this->gameData->get('trackDifficulty')];
    }
    public function useRandomUpgrades()
    {
        return $this->gameData->get('randomUpgrades') == '0';
    }
    public function getIsTrusting()
    {
        return $this->gameData->get('trusting') == '1';
    }
    private array $changed = ['token' => false, 'player' => false, 'knowledge' => false, 'actions' => false];
    public function markChanged(string $type)
    {
        if (!array_key_exists($type, $this->changed)) {
            throw new Exception('Mark missing key ' . $type);
        }
        $this->changed[$type] = true;
    }
    public function markRandomness()
    {
        $this->undo->clearUndoHistory();
    }
    public function setLastAction(?string $actionName = null)
    {
        $lastAction = $this->gameData->get('lastAction');
        if ($lastAction == null) {
            $lastAction = [];
        }

        $this->gameData->set('lastAction', $actionName);
    }
    public function completeAction(bool $saveState = true)
    {
        if ($saveState) {
            $this->undo->saveState();
            $this->incStat(1, 'actions_used', $this->character->getSubmittingCharacter()['playerId']);
        }
        if ($this->changed['token']) {
            $result = [];
            $this->getResources($result);
            $this->getItemData($result);

            $this->notify('tokenUsed', '', ['gameData' => $result]);
        }
        if ($this->changed['player'] || $this->changed['knowledge']) {
            $result = [
                'activeCharacter' => $this->character->getTurnCharacterId(),
                'activePlayer' => $this->character->getTurnCharacterId(),
            ];
            $this->getAllPlayers($result);
            $this->getItemData($result);

            $this->notify('updateCharacterData', '', ['gameData' => $result]);
        }
        if ($this->changed['knowledge']) {
            $selectableUpgrades = array_keys(
                array_filter($this->data->getBoards()['knowledge-tree-' . $this->getDifficulty()]['track'], function ($v) {
                    return !array_key_exists('upgradeType', $v);
                })
            );
            $availableUnlocks = $this->data->getValidKnowledgeTree();
            $result = [
                'upgrades' => $this->gameData->get('upgrades'),
                'unlocks' => $this->gameData->get('unlocks'),
                'availableUnlocks' => array_map(function ($id) use ($availableUnlocks) {
                    $knowledgeObj = $this->data->getKnowledgeTree()[$id];
                    return [
                        'id' => $id,
                        'name' => $knowledgeObj['name'],
                        'name_suffix' => array_key_exists('name_suffix', $knowledgeObj) ? $knowledgeObj['name_suffix'] : '',
                        'unlockCost' => $availableUnlocks[$id]['unlockCost'],
                    ];
                }, array_keys($availableUnlocks)),
                'selectableUpgrades' => $selectableUpgrades,
            ];
            $this->getItemData($result);

            $result = [...$this->getArgsData(), 'selectableUpgrades' => $selectableUpgrades];

            $this->notify('updateKnowledgeTree', '', ['gameData' => $result]);
        }
        if (
            !in_array($this->gamestate->state(true, false, true)['name'], [
                'characterSelect',
                'interrupt',
                'dinnerPhasePrivate',
                'dinnerPhase',
            ])
        ) {
            $availableUnlocks = $this->data->getValidKnowledgeTree();
            $result = [
                'tradeRatio' => $this->getTradeRatio(),
                'actions' => array_values($this->actions->getValidActions()),
                'availableSkills' => $this->actions->getAvailableSkills(),
                'availableItemSkills' => $this->actions->getAvailableItemSkills(),
            ];
            if ($this->gamestate->state(true, false, true)['name'] == 'playerTurn') {
                $result['canUndo'] = $this->undo->canUndo();
            }
            $this->notify('updateActionButtons', '', ['gameData' => $result]);
        }
        if (in_array($this->gamestate->state(true, false, true)['name'], ['dinnerPhasePrivate', 'dinnerPhase'])) {
            foreach ($this->gamestate->getActivePlayerList() as $playerId) {
                $result = $this->argDinnerPhase($playerId);
                $this->notify_player((int) $playerId, 'updateActionButtons', '', ['gameData' => $result]);
            }
        }
    }

    public function getArgsData(): array
    {
        $availableUnlocks = $this->data->getValidKnowledgeTree();

        $allUnlocks = $this->data->getBoards()['knowledge-tree-' . $this->getDifficulty()]['track'];
        $upgrades = $this->gameData->get('upgrades');
        $mapping = [];
        array_walk($upgrades, function ($v, $k) use (&$mapping) {
            $mapping[$v['replace']] = $this->data->getUpgrades()[$k];
        });
        array_walk($allUnlocks, function ($v, $k) use ($mapping, &$allUnlocks) {
            if (array_key_exists($k, $mapping)) {
                unset($allUnlocks[$k]);
                $allUnlocks[$mapping[$k]['id']] = $mapping[$k];
            }
        });

        $result = [
            'version' => $this->getVersion(),
            'activeCharacter' => $this->character->getTurnCharacterId(),
            'activeCharacters' => $this->gameData->getAllMultiActiveCharacterIds(),
            'fireWoodCost' => $this->getFirewoodCost(),
            'tradeRatio' => $this->getTradeRatio(),
            'upgrades' => $this->gameData->get('upgrades'),
            'allUnlocks' => array_keys($allUnlocks),
            'unlocks' => $this->gameData->get('unlocks'),
            'availableUnlocks' => array_map(function ($id) use ($availableUnlocks) {
                $knowledgeObj = $this->data->getKnowledgeTree()[$id];
                return [
                    'id' => $id,
                    'name' => $knowledgeObj['name'],
                    'name_suffix' => array_key_exists('name_suffix', $knowledgeObj) ? $knowledgeObj['name_suffix'] : '',
                    'unlockCost' => $availableUnlocks[$id]['unlockCost'],
                ];
            }, array_keys($availableUnlocks)),
            'resolving' => $this->actInterrupt->isStateResolving(),
            'allBuildings' => array_keys(
                array_filter($this->data->getItems(), function ($d) {
                    return $d['type'] == 'item' && $d['itemType'] == 'building';
                })
            ),
            'maxBuildingCount' => $this->getMaxBuildingCount(),
        ];
        if ($this->gamestate->state(true, false, true)['name'] != 'characterSelect') {
            $result['character_name'] = $this->getCharacterHTML();
            $result['actions'] = array_values($this->actions->getValidActions());
            $result['availableSkills'] = $this->actions->getAvailableSkills();
            $result['availableItemSkills'] = $this->actions->getAvailableItemSkills();
            $result['activeTurnPlayerId'] = $this->character->getTurnCharacter(true)['player_id'];
            $this->getAllPlayers($result);
        }
        if ($this->gamestate->state(true, false, true)['name'] == 'playerTurn') {
            $result['canUndo'] = $this->undo->canUndo();
        }
        $this->getDecks($result);
        $this->getGameData($result);
        $this->getItemData($result);

        return $result;
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
        $stateName = $this->gamestate->state(true, false, true)['name'];
        // TODO remove this check after initial games are no longer in progress
        $turnOrder = $this->gameData->get('turnOrder');
        if (sizeof(array_filter($turnOrder)) != 4) {
            $players = $this->loadPlayersBasicInfos();

            $characters = array_values(
                $this->getCollectionFromDb('SELECT character_name, player_id FROM `character` order by character_name')
            );
            if (sizeof($characters) == 4) {
                $players = array_orderby($players, 'player_no', SORT_ASC);
                $turnOrder = [];
                array_walk($players, function ($player) use (&$turnOrder, $characters) {
                    $turnOrder = [
                        ...$turnOrder,
                        ...array_map(
                            function ($d) {
                                return $d['character_name'];
                            },
                            array_filter($characters, function ($char) use ($player) {
                                return $char['player_id'] == $player['player_id'];
                            })
                        ),
                    ];
                });
                $this->gameData->set('turnOrder', $turnOrder);
                if ($stateName !== 'characterSelect') {
                    $this->gameData->set('turnOrderStart', $this->gameData->get('turnOrder'));
                }
            }
        }
        if (
            (!$this->gameData->get('turnOrderStart') || sizeof($this->gameData->get('turnOrderStart')) < 4) &&
            $stateName !== 'characterSelect'
        ) {
            $players = $this->loadPlayersBasicInfos();

            $characters = array_values(
                $this->getCollectionFromDb('SELECT character_name, player_id FROM `character` order by character_name')
            );
            $players = array_orderby($players, 'player_no', SORT_ASC);
            $turnOrder = [];
            array_walk($players, function ($player) use (&$turnOrder, $characters) {
                $turnOrder = [
                    ...$turnOrder,
                    ...array_map(
                        function ($d) {
                            return $d['character_name'];
                        },
                        array_filter($characters, function ($char) use ($player) {
                            return $char['player_id'] == $player['player_id'];
                        })
                    ),
                ];
            });
            $this->gameData->set('turnOrderStart', $turnOrder);
        }
        if ($stateName == 'characterSelect' && $this->gameData->get('turnOrderStart')) {
            $this->gameData->set('turnOrderStart', null);
        }
        $equippedEquipment = array_merge(
            [],
            ...array_map(function ($data) {
                return array_map(function ($d) {
                    return $d['id'];
                }, $data['equipment']);
            }, $this->character->getAllCharacterData(false))
        );
        if (sizeof($this->gameData->get('lastItemOwners')) == 0 && sizeof($equippedEquipment) > 0) {
            foreach ($this->character->getAllCharacterData(false) as $char) {
                $ids = array_map(function ($d) {
                    return $d['itemId'];
                }, $char['equipment']);
                $this->character->updateItemLastOwner($char['id'], $ids);
            }
        }

        $result = [
            'version' => $this->getVersion(),
            'expansionList' => self::$expansionList,
            'expansion' => $this->getExpansion(),
            'difficulty' => $this->getDifficulty(),
            'trackDifficulty' => $this->getTrackDifficulty(),
            'isRealTime' => $this->isRealTime() || !$this->getIsTrusting(),
            'allItems' => array_values(
                array_map(
                    function ($d) {
                        return $d['id'];
                    },
                    array_filter($this->data->getItems(), function ($d) {
                        return $d['type'] == 'item';
                    })
                )
            ),
            ...$this->getArgsData(),
        ];
        $this->getAllPlayers($result);
        $this->getResources($result);

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

        $this->initStat('table', 'day_number', 1);
        $this->initStat('player', 'damage_done', 0);
        $this->initStat('player', 'health_lost', 0);
        $this->initStat('player', 'health_gained', 0);
        $this->initStat('player', 'actions_used', 0);
        $this->initStat('player', 'stamina_used', 0);
        $this->initStat('player', 'resources_collected', 0);
        $this->initStat('player', 'cards_drawn', 0);
        $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        $this->reloadPlayersBasicInfos();

        $this->gameData->set('expansion', $this->getGameStateValue('expansion'));
        $this->gameData->set('difficulty', $this->getGameStateValue('difficulty'));
        $this->gameData->set('trackDifficulty', $this->getGameStateValue('trackDifficulty'));
        $this->gameData->set('trusting', $this->getGameStateValue('trusting'));
        $this->gameData->set('randomUpgrades', $this->getGameStateValue('randomUpgrades'));

        $this->decks = new DLD_Decks($this);
        $this->decks->setup();

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
    }

    public function zombieBack(): void
    {
        $this->undo->clearUndoHistory();
        $returningPlayerId = $this->getCurrentPlayerId();
        $this->character->unZombiePlayer($returningPlayerId);
        $this->reloadPlayersBasicInfos();
        $this->character->clearCache();

        $stateName = $this->gamestate->state(true, false, true)['name'];
        $stateType = $this->gamestate->state()['type'];
        $this->log($stateName, $stateType, $returningPlayerId, $this->character->getTurnCharacter()['playerId']);
        if ($stateType === 'activeplayer') {
            if ($returningPlayerId == $this->character->getTurnCharacter()['playerId']) {
                $this->nextState('changeZombiePlayer');
                $this->gamestate->changeActivePlayer($returningPlayerId);
                $this->nextState($stateName);
            }
        } elseif ($stateType === 'multipleactiveplayer') {
            $this->gameData->resetMultiActiveCharacter();
        }
        $this->notify('zombieBackDLD', '', [
            'gameData' => $this->getAllDatas(),
        ]);
        $this->completeAction(false);
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
        $this->undo->clearUndoHistory();

        $stateName = $state['name'];
        $characters = $this->character->getAllCharacterData(true);
        $mapping = [];
        $charactersToMove = [];
        array_walk($characters, function ($char) use ($active_player, &$mapping, &$charactersToMove) {
            $charPId = (int) $char['playerId'];
            if ($charPId == (int) $active_player) {
                array_push($charactersToMove, $char['id']);
            } else {
                if (!array_key_exists($charPId, $mapping)) {
                    $mapping[$charPId] = [];
                }
                array_push($mapping[$charPId], $char['id']);
            }
        });

        array_walk($charactersToMove, function ($charId) use ($active_player, &$mapping, &$charactersToMove) {
            $minPlayerId = 0;
            $minCount = 99;
            array_walk($mapping, function ($v, $k) use (&$minCount, &$minPlayerId) {
                if (sizeof($v) < $minCount) {
                    $minPlayerId = $k;
                }
            });
            array_push($mapping[$minPlayerId], $charId);
            $this->character->assignNecromancer($minPlayerId, $charId);
        });
        $this->character->clearCache();

        if ($state['type'] === 'activeplayer') {
            $currentCharId = $this->character->getTurnCharacterId();
            $newPlayerId = array_keys(
                array_filter($mapping, function ($v) use ($currentCharId) {
                    return array_search($currentCharId, $v);
                })
            )[0];

            $this->nextState('changeZombiePlayer');
            $this->gamestate->changeActivePlayer($newPlayerId);
            $this->nextState($stateName);
        } elseif ($state['type'] === 'multipleactiveplayer') {
            $this->gameData->resetMultiActiveCharacter();
        }
        $this->notify('zombieChange', '', [
            'gameData' => $this->getAllDatas(),
        ]);
        $this->completeAction(false);
    }

    // TEST FUNCTIONS START HERE
    public function giveResources()
    {
        $this->gameData->setResources([
            'fireWood' => 3,
            'wood' => 2,
            'bone' => 6,
            'meat' => 4,
            'meat-cooked' => 4,
            'fish' => 0,
            'fish-cooked' => 0,
            'herb' => 4,
            'dino-egg' => 4,
            'dino-egg-cooked' => 4,
            'berry' => 3,
            'berry-cooked' => 2,
            'rock' => 6,
            'stew' => 1,
            'fiber' => 6,
            'hide' => 8,
            'trap' => 0,
            'fkp' => 20,
            'gem-y' => 1,
            'gem-b' => 1,
            'gem-p' => 1,
        ]);
        $this->completeAction();
    }
    // TEST FUNCTIONS START HERE
    public function noResources()
    {
        $this->gameData->setResources([
            'fireWood' => 0,
            'wood' => 0,
            'bone' => 0,
            'meat' => 0,
            'meat-cooked' => 0,
            'fish' => 0,
            'fish-cooked' => 0,
            'herb' => 0,
            'dino-egg' => 0,
            'dino-egg-cooked' => 0,
            'berry' => 0,
            'berry-cooked' => 0,
            'rock' => 0,
            'stew' => 0,
            'fiber' => 0,
            'hide' => 0,
            'trap' => 0,
            'fkp' => 20,
            'gem-y' => 0,
            'gem-b' => 0,
            'gem-p' => 0,
        ]);
        $this->completeAction();
    }
    public function giveClub()
    {
        $itemId = $this->gameData->createItem('club');
        $this->character->equipEquipment($this->character->getSubmittingCharacter()['id'], [$itemId]);
    }
    public function give($item)
    {
        $itemType = $this->data->getItems()[$item]['itemType'];
        if ($itemType == 'necklace') {
            $itemId = $this->gameData->createItem($item);
            $this->character->updateCharacterData($this->character->getSubmittingCharacterId(), function (&$data) use ($itemId) {
                array_push($data['necklaces'], ['itemId' => $itemId]);
            });
        } elseif ($itemType == 'building') {
            $currentBuildings = $this->gameData->get('buildings');
            $itemId = $this->gameData->createItem($item);
            array_push($currentBuildings, ['name' => $item, 'itemId' => $itemId]);
            $this->gameData->set('buildings', $currentBuildings);
        } else {
            $itemId = $this->gameData->createItem($item);
            $this->character->equipEquipment($this->character->getSubmittingCharacter()['id'], [$itemId]);
        }
        $this->completeAction();
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
        $this->completeAction();
    }

    public function drawDayEvent()
    {
        $this->gameData->set('state', ['card' => $this->data->getDecks()['gather-7_15'], 'deck' => 'gather']);
        $this->nextState('drawCard');
        $this->completeAction();
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
        $this->gameData->setResources(['fireWood' => 1, 'wood' => 1]);
        $this->completeAction();
    }
    public function resetStamina()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['stamina'] = $data['maxStamina'];
        });
        $this->completeAction();
    }
    public function noStamina()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['stamina'] = 0;
        });
        $this->completeAction();
    }
    public function resetHealth()
    {
        $this->character->updateCharacterData($this->character->getSubmittingCharacter()['id'], function (&$data) {
            $data['health'] = $data['maxHealth'];
        });
        $this->completeAction();
    }
    public function lowHealth(?string $char = null)
    {
        if (!$char) {
            $char = $this->character->getSubmittingCharacter()['id'];
        }
        $this->character->updateCharacterData($char, function (&$data) {
            $data['health'] = 1;
        });
        $this->completeAction();
    }
    public function maxCraftLevel()
    {
        $craftingLevel = $this->gameData->get('craftingLevel');
        $this->gameData->set('craftingLevel', max($craftingLevel, 3));
    }
    public function kill()
    {
        $this->character->adjustActiveHealth(-10);
        $this->completeAction();
    }
    public function killChar($character)
    {
        $this->character->adjustHealth($character, -10);
        $this->completeAction();
    }
    public function drawNightCard()
    {
        $this->gameData->setResources([
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
        $this->completeAction();
    }
    public function shuffle()
    {
        $this->decks->shuffleInDiscard('gather', true);
        $this->completeAction();
    }
    public function unlockAll()
    {
        $data = $this->data->getBoards()['knowledge-tree-' . $this->getDifficulty()]['track'];
        $unlocks = $this->getUnlockedKnowledgeIds(false);
        $upgrades = $this->gameData->get('upgrades');
        $mapping = [];
        array_walk($upgrades, function ($v, $k) use (&$mapping) {
            $mapping[$v['replace']] = $this->data->getUpgrades()[$k];
        });
        $notUnlocked = array_filter(
            $data,
            function ($v, $k) use ($unlocks) {
                return !in_array($k, $unlocks) && $k != 'fire-starter';
            },
            ARRAY_FILTER_USE_BOTH
        );
        array_walk($notUnlocked, function ($v, $k) use ($mapping, &$notUnlocked) {
            if (array_key_exists($k, $mapping)) {
                unset($notUnlocked[$k]);
                $notUnlocked[$mapping[$k]['id']] = $mapping[$k];
            }
        });

        foreach (array_keys($notUnlocked) as $knowledgeId) {
            $this->unlockKnowledge($knowledgeId);
            $knowledgeObj = $this->data->getKnowledgeTree()[$knowledgeId];
            array_key_exists('onUse', $knowledgeObj) ? $knowledgeObj['onUse']($this, $knowledgeObj) : null;
        }
        $this->completeAction();
    }
    public function swapCharacter(string $char)
    {
        $this->characterSelection->test_swapCharacter($char);
    }
    public function hinder()
    {
        $char = $this->character->getSubmittingCharacter()['id'];
        $this->checkHindrance(true, $char);
        $this->completeAction();
    }
}
