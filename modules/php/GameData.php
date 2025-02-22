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
    private ?array $cachedGameItems = null;
    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->reload();
    }
    public function reload(): void
    {
        $this->cachedGameData = $this->game->globals->getAll();
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
        if (!$this->cachedGameItems) {
            $this->cachedGameItems = $this->game->getCollectionFromDb('SELECT item_id, item_name FROM `item`', true);
        }
        return $this->cachedGameItems;
    }
    public function createItem(string $itemName): int
    {
        $this->game::DbQuery("INSERT INTO item (item_name) VALUES ('$itemName')");
        $this->cachedGameItems[$this->game::DbGetLastId()] = $itemName;
        return $this->game::DbGetLastId();
    }
    public function getGlobals($name): mixed
    {
        return $this->cachedGameData[$name];
    }
    public function getGlobalsAll(...$names): array
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
    public function setup()
    {
        $this->game->globals->set('dailyUseItems', []);
        $this->game->globals->set('buildings', []);
        $this->game->globals->set('state', []);
        $this->game->globals->set('unlocks', []);
        $this->game->globals->set('campEquipment', []);
        $this->game->globals->set('activeNightCards', []);
        $this->game->globals->set('day', 1);
        $this->game->globals->set('craftingLevel', 0);
        $this->game->globals->set('turnOrder', []);
        $this->game->globals->set('turnNo', 0);
        $this->game->globals->set('turnActions', []);
        $this->game->globals->set('skillConfirmationState', []);
        $this->game->globals->set('resources', [
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
        ]);
        $this->reload();
    }
}
