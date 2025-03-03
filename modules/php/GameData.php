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

class GameData
{
    private Game $game;
    private ?array $resourcesBeforeTransaction = null;
    private array $cachedGameData = [];
    private array $cachedGameItems = [];
    private static $defaults = [
        'dailyUseItems' => [],
        'buildings' => [],
        'state' => [],
        'unlocks' => [],
        'campEquipment' => [],
        'activeNightCards' => [],
        'day' => 1,
        'craftingLevel' => 0,
        'turnOrder' => [],
        'turnNo' => 0,
        'turnActions' => [],
        'interruptState' => [],
        'activateCharacters' => [],
        'actInterruptState' => [],
        'resources' => [
            'fireWood' => 0,
            'wood' => 0,
            'bone' => 0,
            'meat' => 0,
            'meat-cooked' => 0,
            'fish' => 0,
            'fish-cooked' => 0,
            'dino-egg' => 0,
            'dino-egg-cooked' => 0,
            'berry' => 0,
            'berry-cooked' => 0,
            'rock' => 0,
            'stew' => 0,
            'fiber' => 0,
            'hide' => 0,
            'trap' => 0,
            'herb' => 0,
            'fkp' => 0,
            'gem' => 0,
        ],
    ];
    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->reload();

        $this->cachedGameItems = $this->game->getCollectionFromDb('SELECT item_id, item_name FROM `item`', true);
    }
    public function reload(): void
    {
        $this->cachedGameData = $this->game->globals->getAll();
        if (sizeof($this->cachedGameData) == 0) {
            $this->cachedGameData = self::$defaults;
        }
        if (!$this->resourcesBeforeTransaction && array_key_exists('resources', $this->cachedGameData)) {
            $this->resourcesBeforeTransaction = $this->cachedGameData['resources'];
        }
    }
    public function getPreviousResources(): array
    {
        return $this->resourcesBeforeTransaction;
    }
    public function set($name, $value)
    {
        $this->game->globals->set($name, $value);
        $this->cachedGameData[$name] = $value;
    }
    public function getItems(): array
    {
        // if (!$this->cachedGameItems) {
        //     $this->cachedGameItems = $this->game->getCollectionFromDb('SELECT item_id, item_name FROM `item`', true);
        // }
        return $this->cachedGameItems;
    }
    public function createItem(string $itemName): int
    {
        $this->game::DbQuery("INSERT INTO item (item_name) VALUES ('$itemName')");
        $this->cachedGameItems[$this->game::DbGetLastId()] = $itemName;
        return $this->game::DbGetLastId();
    }
    public function get($name): mixed
    {
        return $this->cachedGameData[$name];
    }
    public function getAll(...$names): array
    {
        if (sizeof($names) == 0) {
            return $this->cachedGameData;
        }
        return array_filter(
            $this->cachedGameData,
            function ($key) use ($names) {
                return in_array($key, $names);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
    public function setResource($name, $value): void
    {
        $this->cachedGameData['resources'][$name] = $value;
        $this->game->globals->set('resources', $this->cachedGameData['resources']);
    }
    public function getResource($name): int
    {
        return $this->cachedGameData['resources'][$name];
    }
    public function getResources(...$names): array
    {
        if (sizeof($names) == 0) {
            return $this->cachedGameData['resources'];
        }
        return array_filter(
            $this->cachedGameData['resources'],
            function ($key) use ($names) {
                return in_array($key, $names);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
    public function getAllMultiActiveCharacter(): array
    {
        $activateCharacters = $this->get('activateCharacters');
        return array_map(function ($c) {
            return $this->game->character->getCharacterData($c);
        }, $activateCharacters);
    }
    public function setAllMultiActiveCharacter()
    {
        $turnOrder = $this->game->gameData->get('turnOrder');
        $turnOrder = array_values(array_filter($turnOrder));
        foreach ($turnOrder as $k => $id) {
            $this->addMultiActiveCharacter($id);
        }
    }
    public function addMultiActiveCharacter(string $characterId): bool
    {
        $activateCharacters = $this->get('activateCharacters');
        if (!in_array($characterId, $activateCharacters)) {
            array_push($activateCharacters, $characterId);
        }
        $this->set('activateCharacters', $activateCharacters);

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['player_id'];
            }, $activateCharacters)
        );
        $this->game->log('state 1', $activePlayerIds, 'playerTurn');
        return $this->game->gamestate->setPlayersMultiactive($activePlayerIds, 'playerTurn', true);
    }
    public function removeMultiActiveCharacter(string $characterId, string $state): bool
    {
        $activateCharacters = $this->get('activateCharacters');
        if (in_array($characterId, $activateCharacters)) {
            $activateCharacters = array_diff($activateCharacters, [$characterId]);
        }
        $this->set('activateCharacters', $activateCharacters);

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['player_id'];
            }, $activateCharacters)
        );
        $this->game->log('state 2', $activePlayerIds, $state);
        return $this->game->gamestate->setPlayersMultiactive($activePlayerIds, $state, true);
    }
    public function setup()
    {
        foreach (self::$defaults as $k => $v) {
            $this->game->globals->set($k, $v);
        }
        $this->reload();
    }
}
