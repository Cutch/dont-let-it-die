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

class Game extends \Table
{
    protected $actions;
    private $cards;
    private static array $decksData;
    private static array $charactersData;
    private static array $tokensData;
    private static array $boardsData;
    private static array $itemsData;
    private static array $upgradesData;
    private static array $expansionData;
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

        $this->initGameStateLabels([]);

        include dirname(__DIR__) . '/data/boards.php';
        include dirname(__DIR__) . '/data/characters.php';
        include dirname(__DIR__) . '/data/decks.php';
        include dirname(__DIR__) . '/data/expansion.php';
        include dirname(__DIR__) . '/data/items.php';
        include dirname(__DIR__) . '/data/tokens.php';
        include dirname(__DIR__) . '/data/upgrades.php';

        self::$decksData = $decksData;
        self::$charactersData = $charactersData;
        self::$tokensData = $tokensData;
        self::$boardsData = $boardsData;
        self::$itemsData = $itemsData;
        self::$upgradesData = $upgradesData;
        self::$expansionData = $expansionData;

        $_this = $this;
        $this->actions = [
            'actInvestigateFire' => [
                'stamina' => 3,
            ],
            'actCraft' => [
                'stamina' => 3,
            ],
            'actDrawGather' => [
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'stamina' => 3,
            ],
            'actDrawHunt' => [
                'stamina' => 3,
            ],
            'actSpendFKP' => [
                'stamina' => 0,
                'requires' => function () use ($_this) {
                    $fkp = $_this->globals->get('fkp');
                    return $fkp > 0;
                },
            ],
            'actAddWood' => [
                'stamina' => 0,
                'requires' => function () use ($_this) {
                    $wood = $_this->globals->get('wood');
                    return $wood > 0;
                },
            ],
            'actEat' => [
                'stamina' => 0,
                'requires' => function () use ($_this) {
                    $variables = $_this->globals->getAll();
                    $array = array_filter(
                        self::$tokensData,
                        function ($v, $k) use ($variables) {
                            if (isset($variables[$k])) {
                                return array_key_exists('cooked', $v['options']) && $v['options']['actEat']['count'] >= $variables[$k];
                            }
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    return sizeof($array) > 0;
                },
            ],
            'actCook' => [
                'stamina' => 1,
                'requires' => function () use ($_this) {
                    $array = array_filter(
                        self::$tokensData,
                        function ($v, $k) {
                            return array_key_exists('cookable', $v['options']);
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    $variables = $_this->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count >= 3;
                },
            ],
            'actTrade' => [
                'stamina' => 1,
                'requires' => function () use ($_this) {
                    $array = array_filter(
                        self::$tokensData,
                        function ($v, $k) {
                            return $v['options']['type'] === 'resource';
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    $variables = $_this->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count >= 3;
                },
            ],
        ];
        // automatically complete notification args when needed
        $this->notify->addDecorator(function (string $message, array $args) {
            if (isset($args['player_id']) && !isset($args['player_name']) && str_contains($message, '${player_name}')) {
                $args['player_name'] = $this->getPlayerNameById($args['player_id']);
            }
            // if (isset($args['character_id']) && !isset($args['character_name']) && str_contains($message, '${character_name}')) {
            //     $args['character_name'] = self::$charactersData[$args['character_id']]['name'];
            // }

            return $args;
        });
    }
    public function getActiveCharacter()
    {
        $playerId = (int) $this->getActivePlayerId();
    }
    public function actCharacterClicked(
        string $character1 = null,
        string $character2 = null,
        string $character3 = null,
        string $character4 = null
    ): void {
        $characters = [$character1, $character2, $character3, $character4];
        $this->validateCharacterCount(false, $characters);
        $playerId = $this->getCurrentPlayerId();
        // Check if already selected
        $escapedCharacterList = join(
            ', ',
            array_map(function ($char) use ($playerId) {
                $char = self::escapeStringForDB($char);
                return "'$char'";
            }, array_filter($characters))
        );
        if (
            $escapedCharacterList &&
            sizeof(
                array_values(
                    $this->getCollectionFromDb(
                        "SELECT 1 FROM `character` WHERE player_id != $playerId AND character_name in (" . $escapedCharacterList . ')'
                    )
                )
            ) > 0
        ) {
            throw new BgaUserException($this->_('Character Selected By Another Player'));
        }
        // Remove player's previous selected
        self::DbQuery("DELETE FROM `character` WHERE player_id = $playerId");
        // Add player's current selected
        if ($character1) {
            $values = join(
                ', ',
                array_map(function ($char) use ($playerId) {
                    $char = self::escapeStringForDB($char);
                    return "('$char', $playerId)";
                }, array_filter($characters))
            );
            self::DbQuery("INSERT INTO `character` (`character_name`, `player_id`) VALUES $values");
        }
        // Notify Players
        $results = [];
        $this->getAllCharacters($results);
        $this->notify->all('characterClicked', '', $results);
    }
    private function validateCharacterCount(bool $checkIfNotEnough, array $characters)
    {
        // Check for bad character name
        foreach ($characters as $index => $char) {
            if ($char) {
                if (!preg_match('/^[A-Z][a-z]+$/', $char)) {
                    throw new Exception('Bad value for character');
                }
            }
        }
        // Check how many characters the player can select
        $playerId = $this->getCurrentPlayerId();
        $players = $this->loadPlayersBasicInfos();
        $playerCount = sizeof($players);
        $count = 0;
        if ($playerCount == 3) {
            $count = ((string) $players[$playerId]['player_no']) == '1' ? 2 : 1;
        } elseif ($playerCount == 1) {
            $count = 4;
        } elseif ($playerCount == 2) {
            $count = 2;
        } elseif ($playerCount == 4) {
            $count = 1;
        }
        if (sizeof(array_filter($characters)) > $count) {
            throw new BgaUserException($this->_('Too many characters selected'));
        }
        if ($checkIfNotEnough && sizeof(array_filter($characters)) != $count) {
            throw new BgaUserException($this->_('Not enough characters selected'));
        }
    }
    private function initCharacters($playerId)
    {
        $selectedCharacters = array_map(function ($char) {
            return $char['character_name'];
        }, array_values($this->getCollectionFromDb("SELECT character_name FROM `character` WHERE `player_id` = '$playerId'")));

        foreach ($selectedCharacters as $index => $value) {
            extract($charactersData[$value]);
            self::DbQuery(
                "UPDATE `character` SET `stamina`=$stamina, `health`=$health, `max_health`=$health, `max_stamina`=$stamina VALUES character_name = $value"
            );
        }
    }
    public function actChooseCharacters(): void
    {
        $playerId = $this->getCurrentPlayerId();
        $selectedCharacters = array_map(function ($char) {
            return $char['character_name'];
        }, array_values($this->getCollectionFromDb("SELECT character_name FROM `character` WHERE `player_id` = '$playerId'")));

        $this->validateCharacterCount(true, $selectedCharacters);

        self::DbQuery("UPDATE `character` set `confirmed`=1 WHERE `player_id` = $playerId");
        $selectedCharactersArgs = [];
        $message = '${player_name} selected ';
        foreach ($selectedCharacters as $index => $value) {
            $selectedCharactersArgs['character' . ($index + 1)] = $value;
            if ($index == sizeof($selectedCharacters)) {
                $message = $message . ', and ';
            } elseif ($index > 0) {
                $message = $message . ', ';
            }
            $message = $message . '${character' . ($index + 1) . '}';
        }
        $results = ['player_id' => $playerId];
        $this->getAllCharacters($results);
        $this->initCharacters($playerId);
        $this->notify->all('chooseCharacters', clienttranslate($message), array_merge($results, $selectedCharactersArgs));

        // $waiting = sizeof(array_values($this->getCollectionFromDb('SELECT 1 FROM `character` WHERE `confirmed` = 0'))) > 0;
        // if ($waiting) {
        //     $this->gamestate->nextState('start');
        // }
        // Deactivate player, and move to next state if none are active
        $this->gamestate->setPlayerNonMultiactive($playerId, 'start');
    }
    public function actEat(): void
    {
        $playerId = (int) $this->getActivePlayerId();

        $this->notify->all('tokenUsed', clienttranslate('${player_name}(${character_name}) ate'), [
            'player_id' => $playerId,
            'character_name' => 'Gronk',
            'i18n' => ['card_name'], // remove this line if you uncomment notification decorator
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
        $playerId = (int) $this->getActivePlayerId();
        $card = $this->cards->pickCards(1, $deck, $playerId);

        $this->notify->all('cardDrawn', clienttranslate('${player_name}(${character_name}) drew from the ${deck} deck'), [
            'player_id' => $playerId,
            'character_name' => '',
            'deck' => $deck,
            'i18n' => ['card_name'], // remove this line if you uncomment notification decorator
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState('evaluateCard');
    }
    public function actPass(): void
    {
        // Retrieve the active player ID.
        $playerId = (int) $this->getActivePlayerId();

        // Notify all players about the choice to pass.
        $this->notify->all('pass', clienttranslate('${player_name} passes'), [
            'player_id' => $playerId,
            'player_name' => $this->getActivePlayerName(), // remove this line if you uncomment notification decorator
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState('pass');
    }

    /**
     * Get character stamina
     * @return int
     * @see ./states.inc.php
     */
    public function getStamina(): int
    {
        return 30;
    }
    /**
     * Get character stamina cost
     * @return int
     * @see ./states.inc.php
     */
    public function getStaminaCost($action): int
    {
        return $this->actions[$action]['stamina'];
    }
    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `playerTurn` game state.
     *
     * @return array
     * @see ./states.inc.php
     */
    public function argSelectionCount(): array
    {
        $result = [];
        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        return $result;
    }
    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `playerTurn` game state.
     *
     * @return array
     * @see ./states.inc.php
     */
    public function argPlayableActions(): array
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter(
            $this->actions,
            function ($v, $k) {
                return (!array_key_exists('requires', $v) || $v['requires']()) && $this->getStaminaCost($k) <= $this->getStamina();
            },
            ARRAY_FILTER_USE_BOTH
        );
        $validActions = array_column(
            array_map(
                function ($k, $v) {
                    return [$k, $this->getStaminaCost($k)];
                },
                array_keys($validActionsFiltered),
                $validActionsFiltered
            ),
            1,
            0
        );
        return [
            'actions' => $validActions,
        ];
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
     * Game state action, example content.
     *
     * The action method of state `nextPlayer` is called everytime the current game state is set to `nextPlayer`.
     */
    public function stNextPlayer(): void
    {
        // Retrieve the active player ID.
        $playerId = (int) $this->getActivePlayerId();

        // Give some extra time to the active player when he completed an action
        $this->giveExtraTime($playerId);

        $this->activeNextPlayer();

        // Go to another gamestate
        // Here, we would detect if the game is over, and in this case use "endGame" transition instead
        $this->gamestate->nextState('nextPlayer');
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
    protected function getAllCharacters(&$result): void
    {
        $result['characters'] = Array_map(function ($char) {
            return [
                'name' => $char['character_name'],
                'equipment' => array_filter([$char['item_1_name'], $char['item_2_name']]),
                'playerColor' => $char['player_color'],
                'playerId' => $char['player_id'],
                'stamina' => $char['stamina'],
                'maxStamina' => $char['max_stamina'],
                'health' => $char['health'],
                'maxHealth' => $char['max_health'],
            ];
        }, array_values(
            $this->getCollectionFromDb('SELECT c.*, player_color FROM `character` c INNER JOIN `player` p ON p.player_id = c.player_id')
        ));
    }
    protected function getDecksCharacters(&$result): void
    {
        $result['decks'] = $this->getCollectionFromDb(
            'SELECT `card_type` `type`, `card_location` `loc`, count(1) `count` FROM `card` GROUP BY card_type, card_location'
        );
        $result['discards'] = $this->getCollectionFromDb(
            "SELECT `card_type` `type`, MAX(`card_name`) `name`
            FROM `card` a
            WHERE `card_location` = 'discard' AND `card_location_arg` = (SELECT MAX(`card_location_arg`) FROM `card` b WHERE `card_location` = 'discard' AND a.`card_type` = b.`card_type`)
            GROUP BY card_type, card_location"
        );
    }
    protected function getGameData(&$result): void
    {
        $result['game'] = $this->globals->getAll();
        $resourcesAvailable = [];
        array_walk(self::$tokensData, function ($v, $k) use ($result, &$resourcesAvailable) {
            if ($v['options']['type'] == 'resource' && isset($result['game'][$k])) {
                if (isset($v['options']['cooked'])) {
                    $cooked = $v['options']['cooked'];
                    $resourcesAvailable[$cooked] =
                        (isset($resourcesAvailable[$cooked]) ? $resourcesAvailable[$cooked] : 0) - $result['game'][$k];
                } else {
                    $resourcesAvailable[$k] =
                        (isset($resourcesAvailable[$k]) ? $resourcesAvailable[$k] : 0) + $v['options']['count'] - $result['game'][$k];
                }
            }
        });
        $result['resourcesAvailable'] = $resourcesAvailable;
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
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        $this->getAllCharacters($result);
        $this->getAllPlayers($result);
        $this->getDecksCharacters($result);
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

    protected function createDeck($type)
    {
        $filtered_cards = array_filter(
            self::$decksData,
            function ($v, $k) use ($type) {
                return $v['options']['deck'] == $type;
            },
            ARRAY_FILTER_USE_BOTH
        );
        $cards = array_map(
            function ($k, $v) {
                return [
                    'type' => $v['options']['deck'],
                    'card_name' => $k,
                    'card_location' => 'deck',
                    'type_arg' => 0,
                    'nbr' => $v['count'] ?? 1,
                ];
            },
            array_keys($filtered_cards),
            $filtered_cards
        );
        $this->cards->createCards($cards, 'deck');
    }
    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        // global $decksData;
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        // echo $decksData;
        $default_colors = $gameinfos['player_colors'];

        $this->cards = $this->getNew('module.common.deck');
        $this->cards->autoreshuffle = true;
        $this->cards->init('card');

        $this->createDeck('harvest');
        $this->createDeck('hunt');
        $this->createDeck('gather');
        $this->createDeck('explore');
        $this->createDeck('day-event');
        $this->createDeck('night-event');
        $this->createDeck('hindrance');
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
        $this->globals->set('day', 1);
        $this->globals->set('firstPlayerId', 0);
        $this->globals->set('wood', 0);
        $this->globals->set('bone', 0);
        $this->globals->set('meat', 0);
        $this->globals->set('meat-cooked', 0);
        $this->globals->set('fish', 0);
        $this->globals->set('fish-cooked', 0);
        $this->globals->set('dino-egg', 0);
        $this->globals->set('dino-egg-cooked', 0);
        $this->globals->set('berry', 0);
        $this->globals->set('berry-cooked', 0);
        $this->globals->set('stone', 0);
        $this->globals->set('stew', 0);
        $this->globals->set('fiber', 0);
        $this->globals->set('hide', 0);
        $this->globals->set('trap', 0);
        $this->globals->set('herbs', 0);
        $this->globals->set('fkp', 0);
        $this->globals->set('gem', 0);

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
