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
    public static array $expansionList = ['base', 'hindrance'];
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
        // automatically complete notification args when needed
        $this->notify->addDecorator(function (string $message, array $args) {
            if (!array_key_exists('character_name', $args) && str_contains($message, '${character_name}')) {
                $args['character_name'] = $this->character->getActivateCharacter()['character_name'];
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
    public function initDeck($type = 'card')
    {
        $deck = $this->getNew('module.common.deck');
        $deck->autoreshuffle = true;
        $deck->init($type);
        return $deck;
    }
    public function getCurrentPlayer(bool $bReturnNullIfNotLogged = false): string|int
    {
        return parent::getCurrentPlayerId();
    }
    public function translate(string $str)
    {
        return $this->_($str);
    }
    public function activeCharacterEventLog($message, $arg = [])
    {
        $this->notify->all('activeCharacter', clienttranslate('${player_name} - ${character_name} ' . $message), [
            ...$arg,
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function nightEventLog($message, $arg = [])
    {
        $this->notify->all('nightEvent', clienttranslate($message), $arg);
    }
    public function getTradeRatio()
    {
        $data = ['ratio' => 3];
        $this->hooks->onGetTradeRatio($data);
        return $data['ratio'];
    }
    public function adjustResource($resourceType, $change): int
    {
        $currentCount = $this->gameData->getResource($resourceType);
        $maxCount = isset($this->data->tokens[$resourceType]['count']) ? $this->data->tokens[$resourceType]['count'] : 999;
        $newValue = max(min($currentCount + $change, $maxCount), 0);
        $this->gameData->set($resourceType, $newValue);
        $difference = $currentCount - $newValue + $change;
        return $difference;
    }
    public function rollFireDie($character = null): int
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
        if ($character) {
            $this->notify->all('rollFireDie', clienttranslate('${player_name} - ${character_name} rolled a ${value}'), [
                'value' => $value == 0 ? 'blank' : $value,
                'character_name' => $character['character_name'],
            ]);
        } else {
            $this->notify->all('rollFireDie', clienttranslate('The fire die rolled a ${value}'), [
                'value' => $value == 0 ? 'blank' : $value,
            ]);
        }
        return $value;
    }
    public function actCharacterClicked(
        string $character1 = null,
        string $character2 = null,
        string $character3 = null,
        string $character4 = null
    ): void {
        $this->characterSelection->actCharacterClicked($character1, $character2, $character3, $character4);
    }
    public function actChooseCharacters(): void
    {
        $this->characterSelection->actChooseCharacters();
    }
    public function actCook(array $type): void
    {
        $this->actions->validateCanRunAction('actCook', null, $type);
        $this->adjustResource($type, -1);
        $this->adjustResource($type . '-cooked', 1);
        $this->actions->spendActionCost('actCook');

        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} cooked ${amount} ${type}'), [
            'gameData' => $this->getAllDatas(),
            'amount' => 1,
            'type' => $type,
        ]);
    }
    public function actSpendFKP(array $resources, $knowledgeId): void
    {
        $this->actions->validateCanRunAction('actSpendFKP', null, $resources);
        $amount = 0;
        foreach ($resources as $type => $count) {
            $amount += $count;
            $this->adjustResource($type, -$count);
        }
        $this->actions->spendActionCost('actSpendFKP');
        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} spent ${amount} knowledge on'), [
            'gameData' => $this->getAllDatas(),
            'amount' => $amount,
            'knowledgeId' => $knowledgeId,
        ]);
    }
    public function actCraft(string $item = null): void
    {
        if (!$item) {
            throw new BgaUserException($this->translate('Select an item'));
        }
        $this->actions->validateCanRunAction('actCraft');
        if (!array_key_exists($item, $this->data->items)) {
            throw new BgaUserException($this->translate('Invalid Item'));
        }
        $itemType = $this->data->items[$item]['itemType'];
        $currentBuildings = $this->gameData->getGlobals('buildings');
        if ($itemType == 'building' && sizeof($currentBuildings) > 0) {
            throw new BgaUserException($this->translate('A building has already been crafted'));
        }
        $result = [];
        $this->getItemData($result);
        if (!isset($result['availableEquipment'][$item]) || $result['availableEquipment'][$item] == 0) {
            throw new BgaUserException($this->translate('All of those available items have been crafted'));
        }

        foreach ($this->data->items[$item]['cost'] as $key => $value) {
            if ($this->adjustResource($key, -$value) != 0) {
                throw new BgaUserException($this->translate('Missing resources'));
            }
        }
        $this->actions->spendActionCost('actCraft');
        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} crafted a ${item_name}'), [
            'gameData' => $this->getAllDatas(),
            'item_name' => $this->data->items[$item]['name'],
        ]);
        if ($itemType == 'building') {
            array_push($currentBuildings, $item);
            $this->gameData->set('buildings', $currentBuildings);
        } else {
            $character = $this->character->getActivateCharacter();
            $slotsAllowed = array_count_values($character['slots']);
            $slotsUsed = array_count_values(
                array_map(function ($d) {
                    return $d['itemType'];
                }, $character['equipment'])
            );
            if (
                (array_key_exists($itemType, $slotsAllowed) ? $slotsAllowed[$itemType] : 0) -
                    (array_key_exists($itemType, $slotsUsed) ? $slotsUsed[$itemType] : 0) >
                0
            ) {
                $this->character->equipEquipment($character['id'], [$item]);
            } else {
                $existingItems = array_map(
                    function ($d) {
                        return $d['id'];
                    },
                    array_filter($character['equipment'], function ($d) use ($itemType) {
                        return $d['itemType'] == $itemType;
                    })
                );
                $this->gameData->set('state', ['itemType' => $itemType, 'items' => [...$existingItems, $item]]);
                $this->gamestate->nextState('tooManyItems');
            }
        }
    }
    public function actSendToCamp(string $sendToCampId = null): void
    {
        if (!$sendToCampId) {
            throw new BgaUserException($this->translate('Select an item'));
        }
        extract($this->gameData->getGlobals('state'));
        if (!in_array($sendToCampId, $items)) {
            throw new BgaUserException($this->translate('Invalid Item'));
        }
        $character = $this->character->getActivateCharacter();
        $unchangedItems = array_map(
            function ($d) {
                return $d['id'];
            },
            array_filter($character['equipment'], function ($d) use ($itemType) {
                return $d['itemType'] != $itemType;
            })
        );

        $changed = array_map(
            function ($d) {
                return $d['id'];
            },
            array_filter($character['equipment'], function ($d) use ($itemType) {
                return $d['itemType'] == $itemType;
            })
        );
        foreach ($changed as $key) {
            unset($changed[$key]);
            break;
        }
        $this->character->setCharacterEquipment($character['id'], [...$unchangedItems, ...array_values($changed)]);

        $campEquipment = $this->gameData->getGlobals('campEquipment');
        $this->gameData->set('campEquipment', [...$campEquipment, $sendToCampId]);
        $this->gamestate->nextState('playerTurn');
    }

    public function actTrade(string $data): void
    {
        extract(json_decode($data, true));
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
        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} traded ${offered} for ${requested}'), [
            'gameData' => $this->getAllDatas(),
            'offered' => join(', ', $offeredStr),
            'requested' => join(', ', $requestedStr),
        ]);
    }
    public function actEat(array $resources): void
    {
        $this->actions->validateCanRunAction('actEat', null, $resources);
        $type = array_keys($resources)[0];
        $amount = $resources[$type];
        $data = ['amount' => $amount, 'type' => $type, 'health' => $this->data->tokens[$type]['health']];
        $this->hooks->onEat($data);
        $this->notify->all(
            'tokenUsed',
            clienttranslate('${player_name} - ${character_name} ate ${amount} ${type} and gained ${health} health'),
            [
                'gameData' => $this->getAllDatas(),
                ...$data,
            ]
        );
    }
    public function actAddWood(): void
    {
        $this->actions->validateCanRunAction('actAddWood');
        extract($this->gameData->getResources('fireWood', 'wood'));
        $this->gameData->set('fireWood', min($fireWood + 1, $this->data->tokens['wood']['count']));
        $this->gameData->set('wood', max($wood - 1, 0));

        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} added 1 wood to the fire'), [
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function actUseSkill(string $skillId): void
    {
        $this->actions->validateCanRunAction('actUseSkill', $skillId);
        $character = $this->character->getActivateCharacter();
        $skill = $character['skills'][$skillId];
        $this->notify->all('skillUsed', clienttranslate('${player_name} - ${character_name} used the skill ${skill_name}'), [
            'gameData' => $this->getAllDatas(),
            'skill_name' => $skill['name'],
        ]);
        $skill['onUse']($this, $skill);
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
        $this->actDraw('harvest');
    }
    public function actDrawHunt(): void
    {
        $this->actDraw('hunt');
    }
    public function actInvestigateFire(): void
    {
        $this->actions->validateCanRunAction('actInvestigateFire');
        $character = $this->character->getActivateCharacter();
        $roll = $this->rollFireDie($character);
        $this->adjustResource('fkp', $roll);
        $this->actions->spendActionCost('actInvestigateFire');
    }
    public function actDraw(string $deck): void
    {
        $this->actions->validateCanRunAction('actDraw' . ucfirst($deck));
        $character = $this->character->getActivateCharacter();
        $card = $this->decks->pickCard($deck);
        $this->actions->spendActionCost('actDraw' . ucfirst($deck));

        $this->notify->all('cardDrawn', clienttranslate('${player_name} - ${character_name} drew from the ${deck} deck'), [
            'player_id' => $character['player_id'],
            'character_name' => $character['character_name'],
            'deck' => str_replace('-', ' ', $deck),
            'gameData' => $this->getAllDatas(),
        ]);
        $this->gameData->set('state', ['card' => $card, 'deck' => $deck]);
        $this->gamestate->nextState('drawCard');
    }
    public function actEndTurn(): void
    {
        // Retrieve the active player ID.
        $playerId = (int) $this->getActivePlayerId();

        // Notify all players about the choice to pass.
        $this->notify->all('pass', clienttranslate('${player_name} - ${character_name} ends their turn'), [
            'player_id' => $playerId,
            'player_name' => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState('endTurn');
    }

    public function argTooManyItems()
    {
        return [...$this->gameData->getGlobals('state'), 'actions' => []];
    }
    public function argDrawCard()
    {
        return $this->gameData->getGlobals('state');
    }
    public function stPostEncounter()
    {
        $this->gamestate->nextState('playerTurn');
    }
    public function stResolveEncounter()
    {
        extract($this->gameData->getGlobals('state'));
        $tools = array_filter($this->character->getActiveEquipment(), function ($item) {
            return array_key_exists('onEncounter', $item) && !(!array_key_exists('requires', $item) || $item['requires']($item));
        });
        if (sizeof($tools) >= 2) {
            $weapon = $this->gameData->getGlobals('useTools');
            if ($weapon) {
                $this->gameData->set('chooseWeapon', null);
            } else {
                // TODO: Ask if want to use tools
                $this->gameData->set('useTools', $weapons);
                $this->gamestate->nextState('whichTool');
                return;
            }
        }
        $weapons = array_filter($this->character->getActiveEquipment(), function ($item) {
            return $item['type'] == 'weapon';
        });
        $weapon = null;
        if (sizeof($weapons) >= 2) {
            $weapon = $this->gameData->getGlobals('chooseWeapon');
            if ($weapon) {
                $this->gameData->set('chooseWeapon', null);
            } else {
                // TODO: Ask gronk if you want to combine two weapons or pick one
                // Highest range, lowest damage for combine
                $this->gameData->set('chooseWeapon', $weapons);
                $this->gamestate->nextState('whichWeapon');
                return;
            }
        } elseif (sizeof($weapons) >= 1) {
            $weapon = $weapons[0];
        } else {
            $weapon = [
                'damage' => 0,
                'range' => 1,
            ];
        }
        $data = [
            'name' => $card['name'],
            'encounterDamage' => $card['damage'], // Unused, maybe in logging
            'encounterHealth' => $card['health'],
            'escape' => false,
            'characterRange' => $weapon['range'],
            'characterDamage' => $weapon['damage'],
            'willTakeDamage' => $card['damage'],
            'willReceiveMeat' => $card['health'],
            'stamina' => 0,
        ];
        $this->hooks->onEncounter($data);
        if ($data['stamina'] != 0) {
            $this->character->adjustActiveStamina($data['stamina']);
        }
        if ($data['escape']) {
            $this->activeCharacterEventLog('escaped from a ${name}', $data);
        } elseif ($data['encounterHealth'] <= $data['characterDamage']) {
            $damageTaken = 0;
            if ($data['characterRange'] > 1) {
                $damageTaken = 0;
            } else {
                $damageTaken = max($data['willTakeDamage'], 1);
            }
            if ($damageTaken != 0) {
                $this->character->adjustActiveHealth(-$damageTaken);
            }
            $this->adjustResource('meat', $data['willReceiveMeat']);
            $this->activeCharacterEventLog('defeated a ${name}, took ${damageTaken} damage and gained ${willReceiveMeat} meat', [
                ...$data,
                'damageTaken' => $damageTaken,
            ]);
        } else {
            $this->character->adjustActiveHealth(-$data['willTakeDamage']);
            $this->activeCharacterEventLog('was attacked by a ${name} and lost ${willTakeDamage} health', $data);
        }

        $this->gamestate->nextState('postEncounter');
        // $this->gamestate->setPlayersMultiactive([], 'playerTurn');
    }
    public function argResolveEncounter()
    {
        $validActions = $this->actions->getValidActions('encounter');
        $result = [
            'actions' => $validActions,
        ];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        $this->getGameData($result);
        return $result;
    }
    public function stDrawCard()
    {
        $character = $this->character->getActivateCharacter();
        extract($this->gameData->getGlobals('state'));
        if ($card['deckType'] == 'resource') {
            $this->adjustResource($card['resourceType'], $card['count']);

            $this->notify->all('foundResource', clienttranslate('${player_name} - ${character_name} found ${count} ${name}'), [
                'player_id' => $character['player_id'],
                'character_name' => $character['character_name'],
                ...$card,
                'deck' => str_replace('-', ' ', $deck),
                'gameData' => $this->getAllDatas(),
            ]);
            $this->gamestate->nextState('playerTurn');
        } elseif ($card['deckType'] == 'encounter') {
            // Change state and check for health/damage modifications
            $this->notify->all(
                'cardDrawn',
                clienttranslate('${player_name} - ${character_name} encountered a ${name} (${health} health, ${damage} damage)'),
                [
                    'player_id' => $character['player_id'],
                    'character_name' => $character['character_name'],
                    ...$card,
                    'deck' => str_replace('-', ' ', $deck),
                ]
            );
            $this->gamestate->nextState('resolveEncounter');
        } elseif ($card['deckType'] == 'nothing') {
            $this->notify->all('cardDrawn', clienttranslate('${player_name} - ${character_name} did nothing'), [
                'player_id' => $character['player_id'],
                'character_name' => $character['character_name'],
                'deck' => str_replace('-', ' ', $deck),
            ]);
            $this->gamestate->nextState('playerTurn');
        } elseif ($card['deckType'] == 'hindrance') {
            $this->gamestate->nextState('playerTurn');
        } elseif ($card['deckType'] == 'night-event') {
            $this->notify->all('cardDrawn', clienttranslate('It\'s night, drawing from the night deck'), [
                'deck' => str_replace('-', ' ', $deck),
            ]);
            $this->gamestate->nextState('morning');
        } else {
            $this->gamestate->nextState('playerTurn');
        }
    }

    public function argSelectionCount(): array
    {
        $result = ['actions' => []];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        return $result;
    }
    public function argPlayerState(): array
    {
        $validActions = $this->actions->getValidActions('player');
        $result = [
            'actions' => $validActions,
            'currentCharacter' => $this->character->getActivateCharacter()['character_name'],
            ...$this->getAllDatas(),
        ];
        return $result;
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
        extract($this->gameData->getGlobalsAll('day', 'turnNo'));
        return (($day - 1) * 4 + $turnNo) / (12 * 4);
    }

    /**
     * The action method of state `nextCharacter` is called everytime the current game state is set to `nextCharacter`.
     */
    public function stNextCharacter(): void
    {
        // Retrieve the active player ID.
        $playerId = (int) $this->getActivePlayerId();
        if ($this->character->isLastCharacter()) {
            $this->gamestate->nextState('morningPhase');
        } else {
            $this->character->activateNextCharacter();
            $this->giveExtraTime($playerId);
            $this->gamestate->nextState('nextCharacter');

            $this->notify->all('playerTurn', clienttranslate('${player_name} - ${character_name} begins their turn'), []);
        }
    }

    public function stSelectCharacter()
    {
        $this->gamestate->setAllPlayersMultiactive();
        foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
            $this->giveExtraTime((int) $playerId, 500);
        }
    }
    public function stNightPhase()
    {
        $card = $this->decks->pickCard('night-event');
        $this->setActiveNightCard($card['id']);
        $this->gameData->set('state', ['card' => $card, 'deck' => 'night-event']);
        $this->gamestate->nextState('drawCard');
    }
    public function getFirewoodCost()
    {
        $day = $this->gameData->getGlobals('day');
        if ($this->getTrackDifficulty() == 'normal') {
            return (int) (($day - 1) / 3) + 1 + ($day == 12 ? 1 : 0);
        } else {
            return (int) ($day / 3) + 1 + ($day >= 11 ? 1 : 0) - ($day == 12 ? 1 : 0);
        }
    }
    public function win()
    {
        $this->DbQuery('UPDATE player SET player_score=1 WHERE 1=1');
        $this->gamestate->nextState('endGame');
    }
    public function lose()
    {
        $this->DbQuery('UPDATE player SET player_score=0 WHERE 1=1');
        $this->gamestate->nextState('endGame');
    }
    public function stMorningPhase()
    {
        $day = $this->gameData->getGlobals('day');
        if ($day == 14) {
            $this->lose(); // Fail
        }
        $woodNeeded = $this->getFirewoodCost();
        $difficulty = $this->getTrackDifficulty();

        $healthAmount = 1;
        if ($difficulty == 'hard') {
            $healthAmount = 2;
        }
        $this->character->adjustAllHealth(-$healthAmount);
        $this->notify->all('morningPhase', clienttranslate('Everyone lost ${amount} health'), [
            'amount' => $healthAmount,
        ]);

        $this->notify->all('morningPhase', clienttranslate('The fire pit used ${amount} wood'), [
            'amount' => $woodNeeded,
        ]);
        if ($this->adjustResource('fireWood', -$woodNeeded) != 0) {
            $this->lose(); // Fail
        }
        $this->actions->resetTurnActions();
        $this->character->rotateTurnOrder();
        $this->gamestate->nextState('tradePhase');
    }
    public function stTradePhase()
    {
        $this->gamestate->setPlayersMultiactive([], 'activePhase');
        // $this->gamestate->nextState('activePhase');
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
    protected function getAllPlayers(&$result): void
    {
        $result['players'] = $this->getCollectionFromDb('SELECT `player_id` `id`, `player_score` `score`, player_no FROM `player`');
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
        $campEquipment = array_count_values($this->gameData->getGlobals('campEquipment'));

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
        $result['campEquipment'] = array_count_values($this->gameData->getGlobals('campEquipment'));
        $selectable = $this->actions->getActionSelectable('actCraft');
        $result['availableEquipment'] = array_combine(
            array_map(function ($d) {
                return $d['id'];
            }, $selectable),
            array_map(function ($d) use ($result) {
                return $d['count'] - (array_key_exists($d['id'], $result['builtEquipment']) ? $result['builtEquipment'][$d['id']] : 0);
            }, $selectable)
        );
    }
    protected function getGameData(&$result): void
    {
        $result['game'] = $this->gameData->getGlobalsAll();
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
    public function getBuildings(): array
    {
        $buildings = $this->gameData->getGlobals('buildings');
        return array_map(function ($building) {
            return $this->data->items[$building];
        }, $buildings);
    }
    public function addBuilding($buildingId): void
    {
        $array = $this->gameData->getGlobals('buildings');
        array_push($array, $buildingId);
        $this->gameData->set('buildings', $array);
    }
    public function getActiveNightCards(): array
    {
        $activeNightCards = $this->gameData->getGlobals('activeNightCards');
        return array_map(function ($cardId) {
            return $this->data->decks[$cardId];
        }, $activeNightCards);
    }
    public function setActiveNightCard($cardId): void
    {
        $this->gameData->set('activeNightCards', [$cardId]);
    }
    public function getUnlockedKnowledge(): array
    {
        $unlocks = $this->gameData->getGlobals('unlocks');
        return array_map(function ($unlock) {
            return $this->data->knowledgeTree[$unlock];
        }, $unlocks);
    }
    public function unlockedKnowledge($knowledgeId): void
    {
        $array = $this->gameData->getGlobals('unlocks');
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
        $result = [
            'expansionList' => self::$expansionList,
            'expansion' => $this->getExpansion(),
            'difficulty' => $this->getDifficulty(),
            'trackDifficulty' => $this->getTrackDifficulty(),
            'fireWoodCost' => $this->getFirewoodCost(),
            'tradeRatio' => $this->getTradeRatio(),
        ];
        switch ($this->gamestate->state()['name']) {
            case 'playerTurn':
                $result['actions'] = $this->actions->getValidActions('player');
                $result['availableSkills'] = $this->actions->getAvailableCharacterSkills();
                break;
            case 'resolveEncounter':
                $result['actions'] = $this->actions->getValidActions('encounter');
                $result['availableSkills'] = $this->actions->getAvailableCharacterSkills();
                break;
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
        $this->gameData->setup();

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
}
