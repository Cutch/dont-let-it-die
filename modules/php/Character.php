<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Exception;

class Character
{
    private Game $game;
    private array $cachedData = [];

    public function __construct($game)
    {
        $this->game = $game;
    }
    public function setup()
    {
        $this->game->globals->set('turnOrder', []);
        $this->game->globals->set('turnNo', 0);
    }
    public function updateCharacterData($name, $callback)
    {
        // Pull from db if needed
        $data = $this->getCharacterData($name);
        $callback($data);
        // Update db
        $values = [];
        foreach ($data as $key => $value) {
            $values[] = "`{$key}` = '{$value}'";
        }
        $values = implode(',', $values);
        $this->game::DbQuery("UPDATE `character` SET {$values} WHERE character_name = '$name'");
    }
    public function updateAllCharacterData($callback)
    {
        $turnOrder = $this->game->globals->get('turnOrder');
        foreach ($turnOrder as $i => $name) {
            // Pull from db if needed
            $data = $this->getCharacterData($name);
            $callback($data);
            // Update db
            $values = [];
            foreach ($data as $key => $value) {
                $values[] = "`{$key}` = '{$value}'";
            }
            $values = implode(',', $values);
            $this->game::DbQuery("UPDATE `character` SET {$values} WHERE character_name = '$name'");
        }
    }
    public function getCharacterData($name): array
    {
        if (isset($this->cachedData[$name])) {
            return $this->cachedData[$name];
        } else {
            $characterData = $this->game->getCollectionFromDb("SELECT * FROM `character` WHERE character_name = '$name'")[$name];
            $this->cachedData[$name] = $characterData;
            return $characterData;
        }
    }
    public function getActivateCharacter(): array
    {
        extract($this->game->globals->getAll('turnNo', 'turnOrder'));
        $character = $turnOrder[$turnNo];
        return $this->getCharacterData($character);
    }
    public function listActiveEquipmentTypes(): array
    {
        $character = $this->getActivateCharacter();
        $_this = $this;
        return array_map(function ($itemName) use ($_this) {
            return $_this->game->data->items[$itemName]['itemType'];
        }, array_filter([$character['item_1_name'], $character['item_2_name']]));
    }
    public function listUnusedEquipment(): array
    {
        $dailyUseItems = $this->game->globals->get('dailyUseItems');
        $character = $this->getActivateCharacter();
        $_this = $this;
        return array_map(function ($itemName) use ($_this) {
            return $_this->game->data->items[$itemName]['itemType'];
        }, array_filter([$character['item_1_name'], $character['item_2_name']]));
    }
    public function activateNextCharacter()
    {
        // Making the assumption that the functions are checking isLastCharacter()
        extract($this->game->globals->getAll('turnNo', 'turnOrder'));
        $this->game->globals->set('turnNo', $turnNo + 1);
        $character = $turnOrder[$turnNo + 1];
        $characterData = $this->getCharacterData($character);

        $playerId = (int) $this->game->getActivePlayerId();
        if ($playerId != $characterData['player_id']) {
            $this->game->gamestate->changeActivePlayer($characterData['player_id']);
        }
    }
    public function isLastCharacter()
    {
        extract($this->game->globals->getAll('turnNo', 'turnOrder'));
        return sizeof($turnOrder) == $turnNo - 1;
    }
    public function rotateTurnOrder(): void
    {
        $turnOrder = $this->game->globals->get('turnOrder');
        $temp = array_shift($turnOrder);
        array_push($turnOrder, $temp);
        $this->game->globals->set('turnOrder', $turnOrder);
    }

    public function getActiveStamina(): int
    {
        return (int) $this->getActivateCharacter()['stamina'];
    }
    public function setActiveStamina(int $stamina): void
    {
        $characterName = $this->getActivateCharacter()['character_name'];
        $this->updateCharacterData($characterName, function ($data) use ($stamina) {
            $data['stamina'] = min($stamina, $data['max_stamina']);
        });
    }
    public function getActiveHealth(): int
    {
        return (int) $this->getActivateCharacter()['health'];
    }

    public function setActiveHealth(int $health): void
    {
        $characterName = $this->getActivateCharacter()['character_name'];
        $this->updateCharacterData($characterName, function ($data) use ($health) {
            $data['health'] = min($health, $data['max_health']);
        });
    }
    public function getMarshallCharacters()
    {
        return array_map(function ($char) {
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
            $this->game->getCollectionFromDb(
                'SELECT c.*, player_color FROM `character` c INNER JOIN `player` p ON p.player_id = c.player_id'
            )
        ));
    }
}
