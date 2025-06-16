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

class DLD_GameData
{
    private Game $game;
    private ?array $resourcesBeforeTransaction = null;
    private array $cachedGameData = [];
    private array $cachedGameItems = [];
    private static $defaults = [
        'expansion' => 0,
        'difficulty' => 0,
        'trackDifficulty' => 0,
        'dailyUseItems' => [],
        'foreverUseItems' => [],
        'pendingStates' => [],
        'buildings' => [],
        'lastAction' => null,
        'state' => [],
        'unlocks' => [],
        'upgrades' => [],
        'campEquipment' => [],
        'destroyedEquipment' => [],
        'activeNightCards' => [],
        'activeDayCards' => [],
        'day' => 1,
        'craftingLevel' => 0,
        'turnOrder' => [],
        'turnNo' => 0,
        'interruptState' => [],
        'activateCharacters' => [],
        'actInterruptState' => [],
        'partials' => [],
        'tokens' => [],
        'resources' => [
            'fireWood' => 3,
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
            'gem-y' => 0,
            'gem-b' => 0,
            'gem-p' => 0,
        ],
        'destroyedResources' => [],
    ];
    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->reload();

        $this->cachedGameItems = $this->game->getCollectionFromDb('SELECT item_id, item_name FROM `item`', true);
    }
    public function reload(): void
    {
        $this->cachedGameData = [...self::$defaults, ...$this->game->globals->getAll()];
        if (!$this->resourcesBeforeTransaction && array_key_exists('resources', $this->cachedGameData)) {
            $this->resourcesBeforeTransaction = $this->cachedGameData['resources'];
        }
    }
    public function getPreviousResources(): array
    {
        $resourcesBeforeTransaction = $this->resourcesBeforeTransaction;
        if (array_key_exists('resources', $this->cachedGameData)) {
            $this->resourcesBeforeTransaction = $this->cachedGameData['resources'];
        }
        return $resourcesBeforeTransaction;
    }
    public function set($name, $value)
    {
        $this->game->globals->set($name, $value);
        $this->cachedGameData[$name] = $value;
    }
    public function getCreatedItems(): array
    {
        return $this->cachedGameItems;
    }
    public function setItems($data): void
    {
        $newMax = 0;
        if (sizeof($data) > 0) {
            $newMax = max(array_keys($data));
        }
        // Only removing as this is used by the undo
        $this->game::DbQuery("DELETE FROM item WHERE item_id > $newMax");
        $this->cachedGameItems = $data;
    }
    public function createItem(string $itemName): int
    {
        $this->game::DbQuery("INSERT INTO item (item_name) VALUES ('$itemName')");
        $this->cachedGameItems[$this->game::DbGetLastId()] = $itemName;
        return $this->game::DbGetLastId();
    }
    public function get(string $name): mixed
    {
        return array_key_exists($name, $this->cachedGameData) ? $this->cachedGameData[$name] : null;
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
    public function setResources(array $resources): void
    {
        $this->cachedGameData['resources'] = [...$this->cachedGameData['resources'], ...$resources];
        $this->game->globals->set('resources', $this->cachedGameData['resources']);
        $this->game->markChanged('token');
    }
    public function setResource(string $name, int $value): void
    {
        $this->cachedGameData['resources'][$name] = max($value, 0);
        $this->game->globals->set('resources', $this->cachedGameData['resources']);
        $this->game->markChanged('token');
    }
    public function getResource(string $name): int
    {
        return $this->cachedGameData['resources'][$name];
    }
    public function destroyResource(string $resourceType, int $count = 1): void
    {
        $data = $this->get('destroyedResources');
        $data[$resourceType] = (array_key_exists($resourceType, $data) ? $data[$resourceType] : 0) + $count;
        $this->set('destroyedResources', $data);
        $this->game->adjustResource($resourceType, 0);
        $this->game->notify('notify', clienttranslate('${count} ${resource_type} removed from the game'), [
            'count' => 1,
            'resource_type' => $resourceType,
        ]);
    }
    public function getResourceMax(string $resourceType): int
    {
        $resourceType = str_replace('-cooked', '', $resourceType);
        if ($resourceType == 'fireWood') {
            $resourceType = 'wood';
        }
        $maxCount = $this->game->data->getTokens()[$resourceType]['count'];
        $data = $this->get('destroyedResources');
        $maxCount -= array_key_exists($resourceType, $data) ? $data[$resourceType] : 0;
        $hookData = [
            'resourceType' => $resourceType,
            'maxCount' => $maxCount,
        ];
        $this->game->hooks->onGetResourceMax($hookData);
        return $hookData['maxCount'];
    }
    public function getResourceLeft(string $name): int
    {
        return $this->getResourceMax($name) - $this->getResource($name);
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
    public function getAllMultiActiveCharacterIds(): array
    {
        return $this->get('activateCharacters');
    }
    public function getAllMultiActiveCharacter(): array
    {
        $activateCharacters = $this->getAllMultiActiveCharacterIds();
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
    // Used for zombie turns
    public function resetMultiActiveCharacter(): bool
    {
        $activateCharacters = $this->getAllMultiActiveCharacterIds();

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['playerId'];
            }, $activateCharacters)
        );
        if (sizeof($activePlayerIds) == 0) {
            $this->game->character->setSubmittingCharacter(null);
        }
        return $this->game->gamestate->setPlayersMultiactive($activePlayerIds, 'playerTurn', true);
    }
    public function addMultiActiveCharacter(string $characterId, bool $exclusive = false): bool
    {
        $activateCharacters = $this->getAllMultiActiveCharacterIds();
        if ($exclusive) {
            $activateCharacters = array_filter($activateCharacters, function ($d) use ($characterId) {
                return $d == $characterId;
            });
        }
        if (!in_array($characterId, $activateCharacters)) {
            array_push($activateCharacters, $characterId);
            $this->game->giveExtraTime($this->game->character->getCharacterData($characterId)['playerId']);
        }
        $this->set('activateCharacters', $activateCharacters);

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['playerId'];
            }, $activateCharacters)
        );
        $this->game->log('state 1', $activePlayerIds, 'playerTurn');
        if (sizeof($activePlayerIds) == 0) {
            $this->game->character->setSubmittingCharacter(null);
        }
        return $this->game->gamestate->setPlayersMultiactive($activePlayerIds, 'playerTurn', true);
    }
    public function setMultiActiveCharacter(array $characterIds, bool $exclusive = false): bool
    {
        $activateCharacters = $this->getAllMultiActiveCharacterIds();
        if ($exclusive) {
            $activateCharacters = array_filter($activateCharacters, function ($d) use ($characterIds) {
                return in_array($d, $characterIds);
            });
        }
        foreach ($characterIds as $k => $characterId) {
            if (!in_array($characterId, $activateCharacters)) {
                array_push($activateCharacters, $characterId);
                $this->game->giveExtraTime($this->game->character->getCharacterData($characterId)['playerId']);
            }
        }
        $this->set('activateCharacters', $activateCharacters);

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['playerId'];
            }, $activateCharacters)
        );

        if (sizeof($activePlayerIds) == 0) {
            $this->game->character->setSubmittingCharacter(null);
        }
        return $this->game->gamestate->setPlayersMultiactive($activePlayerIds, 'playerTurn', true);
    }
    public function removeMultiActiveCharacter(string $characterId, string $state): bool
    {
        $activateCharacters = $this->getAllMultiActiveCharacterIds();
        if (in_array($characterId, $activateCharacters)) {
            $activateCharacters = array_diff($activateCharacters, [$characterId]);
        } else {
            return false;
        }
        $this->set('activateCharacters', $activateCharacters);

        $activePlayerIds = array_unique(
            array_map(function ($c) {
                return $this->game->character->getCharacterData($c)['playerId'];
            }, $activateCharacters)
        );
        $this->game->log('state 2', $activePlayerIds, $state);
        if (sizeof($activePlayerIds) == 0) {
            $this->game->character->setSubmittingCharacter(null);
        }
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
