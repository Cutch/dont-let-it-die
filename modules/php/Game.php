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
    private GameData $gameData;
    public Hooks $hooks;
    public static array $expansionList = ['base', 'hindrance'];
    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `globals->set` or
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
            if (!isset($args['character_name']) && str_contains($message, '${character_name}')) {
                $args['character_name'] = $this->character->getActivateCharacter()['character_name'];
            }
            if (!isset($args['player_name']) && str_contains($message, '${player_name}')) {
                if (isset($args['player_id'])) {
                    $args['player_name'] = $this->getPlayerNameById($args['player_id']);
                } elseif (isset($args['character_name'])) {
                    $playerId = (int) $this->character->getCharacterData($args['character_name'])['player_id'];
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                } else {
                    $playerId = (int) $this->getActivePlayerId();
                    $args['player_name'] = $this->getPlayerNameById($playerId);
                }
            }
            if (isset($args['resource']) && !isset($args['resource_name']) && str_contains($message, '${resource_name}')) {
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
    public function nightEventLog($message, $arg = [])
    {
        $this->notify->all('nightEvent', clienttranslate($message), $arg);
    }
    public function adjustResource($resourceType, $change)
    {
        $currentCount = $this->globals->get($resourceType);
        $newValue = max(min($currentCount + $change, $this->data->tokens[$resourceType]['count']), 0);
        $this->globals->set($resourceType, $newValue);
        $difference = $currentCount - $newValue + $change;
        return $difference;
    }
    public function rollFireDie($character = null): array
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
            $this->notify->all('rollFireDie', clienttranslate('${player_name} - {character_name} fire die rolled a ${value}'), [
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
    public function actEat(): void
    {
        $this->actions->validateCanRunAction('actEat');
        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} ate'), [
            'gameData' => $this->getAllDatas(),
        ]);
    }
    public function actAddWood(): void
    {
        $this->actions->validateCanRunAction('actAddWood');
        extract($this->globals->getAll('fireWood', 'wood'));
        $this->globals->set('fireWood', min($fireWood + 1, $this->data->tokens['wood']['count']));
        $this->globals->set('wood', max($wood - 1, 0));

        $this->notify->all('tokenUsed', clienttranslate('${player_name} - ${character_name} added 1 wood to the fire'), [
            'gameData' => $this->getAllDatas(),
        ]);
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
    public function actDraw(string $deck): void
    {
        $this->actions->validateCanRunAction('actDraw' . ucfirst($deck));
        $staminaCost = $this->actions->getStaminaCost('actDraw' . ucfirst($deck));
        $character = $this->character->getActivateCharacter();
        $card = $this->decks->pickCard($deck);
        $this->character->updateCharacterData($character['character_name'], function (&$data) use ($staminaCost) {
            $data['stamina'] -= $staminaCost;
        });
        $this->notify->all('cardDrawn', clienttranslate('${player_name} - ${character_name} drew from the ${deck} deck'), [
            'player_id' => $character['player_id'],
            'character_name' => $character['character_name'],
            'deck' => str_replace('-', ' ', $deck),
            'gameData' => $this->getAllDatas(),
        ]);
        $this->globals->set('state', ['card' => $card, 'deck' => $deck]);
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

    public function argDrawCard()
    {
        return $this->globals->get('state');
    }
    public function stResolveEncounter()
    {
        $this->gamestate->setPlayersMultiactive([], 'playerTurn');
    }
    public function argResolveEncounter()
    {
        $validActions = $this->actions->getValidEncounterActions();
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
        extract($this->globals->get('state'));
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
        $result = [];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        return $result;
    }
    public function argPlayerState(): array
    {
        $validActions = $this->actions->getValidPlayerActions();
        // var_dump($validActions);
        $result = [
            'actions' => $validActions,
            'currentCharacter' => $this->character->getActivateCharacter()['character_name'],
        ];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        $this->getDecks($result);
        $this->getGameData($result);
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
        return $this->globals->get('day') / 14;
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
        // var_dump($this->gamestate->getActivePlayerList());
        foreach ($this->gamestate->getActivePlayerList() as $key => $playerId) {
            $this->giveExtraTime((int) $playerId, 500);
        }
    }
    public function stNightPhase()
    {
        $card = $this->decks->pickCard('night-event');
        $this->globals->set('lastNightCard', $card);
        $this->globals->set('state', ['card' => $card, 'deck' => 'night-event']);
        $this->gamestate->nextState('drawCard');
    }
    public function stMorningPhase()
    {
        $day = $this->globals->get('day');
        if ($day == 14) {
            $this->gamestate->nextState('endGame'); // Fail
        }
        $woodNeeded = (int) (($day - 1) / 3) + 1 - ($day >= 12 ? 1 : 0);
        if ($this->adjustResource('wood', -$woodNeeded) != 0) {
            $this->gamestate->nextState('endGame'); // Fail
        }
        $this->actions->resetTurnActions();
        $this->character->rotateTurnOrder();
        $this->gamestate->nextState('tradePhase');
    }
    public function stTradePhase()
    {
        $this->gamestate->setAllPlayersMultiactive();
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
    protected function getGameData(&$result): void
    {
        $result['game'] = $this->globals->getAll();
        $resourcesAvailable = [];
        array_walk($this->data->tokens, function ($v, $k) use ($result, &$resourcesAvailable) {
            if ($v['type'] == 'resource' && isset($result['game'][$k])) {
                if (isset($v['cooked'])) {
                    $cooked = $v['cooked'];
                    $resourcesAvailable[$cooked] =
                        (isset($resourcesAvailable[$cooked]) ? $resourcesAvailable[$cooked] : 0) - $result['game'][$k];
                } else {
                    $resourcesAvailable[$k] =
                        (isset($resourcesAvailable[$k]) ? $resourcesAvailable[$k] : 0) +
                        $v['count'] -
                        $result['game'][$k] -
                        ($k === 'wood' ? $result['game']['fireWood'] ?? 0 : 0);
                }
            }
        });
        // $result['game']['wood'] += $result['game']['fireWood'] ?? 0;

        $result['resourcesAvailable'] = $resourcesAvailable;
    }
    public function getExpansion()
    {
        $expansionMapping = self::$expansionList;
        return $expansionMapping[$this->getGameStateValue('expansion')];
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
    protected function getAllDatas(): array
    {
        $result = [
            'expansionList' => self::$expansionList,
            'expansion' => $this->getExpansion(),
            'difficulty' => $this->getDifficulty(),
            'trackDifficulty' => $this->getTrackDifficulty(),
        ];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        $this->getDecks($result);
        $this->getGameData($result);

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
        $this->character->setup();
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
