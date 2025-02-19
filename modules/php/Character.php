<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Exception;

class Character
{
    private Game $game;
    private array $cachedData = [];
    private static array $characterColumns = [
        'character_name',
        'player_id',
        'item_1',
        'item_2',
        'item_3',
        'stamina',
        'max_stamina',
        'health',
        'max_health',
        'confirmed',
    ];

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
        if (!$callback($data)) {
            // Update db
            $values = [];
            foreach ($data as $key => $value) {
                if (in_array($key, self::$characterColumns)) {
                    $values[] = "`{$key}` = '{$value}'";
                    $this->cachedData[$name][$key] = $value;
                }
            }
            $values = implode(',', $values);
            $this->game::DbQuery("UPDATE `character` SET {$values} WHERE character_name = '$name'");
            $this->game->notify->all('updateGameData', '', [
                'gameData' => $this->game->getAllDatas(),
            ]);
        }
    }
    public function updateAllCharacterData($callback)
    {
        $turnOrder = $this->game->globals->get('turnOrder');
        $turnOrder = array_values(array_filter($turnOrder));
        $hasUpdate = false;
        foreach ($turnOrder as $i => $name) {
            // Pull from db if needed
            $data = $this->getCharacterData($name);
            if (!$callback($data)) {
                $hasUpdate = true;
                // Update db
                $values = [];
                foreach ($data as $key => $value) {
                    if (in_array($key, self::$characterColumns)) {
                        $values[] = "`{$key}` = '{$value}'";
                        $this->cachedData[$name][$key] = $value;
                    }
                }
                $values = implode(',', $values);
                $this->game::DbQuery("UPDATE `character` SET {$values} WHERE character_name = '$name'");
            }
        }
        if ($hasUpdate) {
            $this->game->notify->all('updateGameData', '', [
                'gameData' => $this->game->getAllDatas(),
            ]);
        }
    }
    public function getAllCharacterData(): array
    {
        $turnOrder = $this->game->globals->get('turnOrder');
        $turnOrder = array_values(array_filter($turnOrder));
        return array_map([$this, 'getCharacterData'], $turnOrder);
    }
    public function getCalculatedData($characterData): array
    {
        extract($this->game->globals->getAll('turnNo', 'turnOrder'));
        $turnOrder = array_values(array_filter($turnOrder));
        $isActive = $turnOrder[$turnNo] == $characterData['character_name'];
        $characterData['isActive'] = $isActive;
        $characterData['isFirst'] = isset($turnOrder[0]) && $turnOrder[0] == $characterData['character_name'];
        $characterData['id'] = $characterData['character_name'];
        array_walk($this->game->data->characters[$characterData['id']], function ($v, $k) use (&$characterData) {
            if (str_starts_with($k, 'on') || in_array($k, ['slots', 'skills'])) {
                // if ($k == 'skills') {
                //     var_dump(json_encode($v));
                // }
                $characterData[$k] = $v;
            }
        });
        $_this = $this;
        $characterData['equipment'] = array_map(function ($itemName) use ($_this, $isActive) {
            return ['id' => $itemName, 'isActive' => $isActive, ...$_this->game->data->items[$itemName]];
        }, array_filter([$characterData['item_1'], $characterData['item_2'], $characterData['item_3']]));

        // $this->game->hooks->onGetCharacterData($characterData);
        return $characterData;
    }
    public function getCharacterData($name): array
    {
        if (isset($this->cachedData[$name])) {
            return $this->getCalculatedData($this->cachedData[$name]);
        } else {
            $characterData = $this->getCalculatedData(
                $this->game->getCollectionFromDb(
                    "SELECT c.*, player_color FROM `character` c INNER JOIN `player` p ON p.player_id = c.player_id WHERE character_name = '$name'"
                )[$name]
            );
            $this->cachedData[$name] = $characterData;
            return $characterData;
        }
    }
    public function equipEquipment(string $characterName, array $items): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($items) {
            $equipment = [...$data['equipment'], ...$items];
            $data['item_1'] = isset($equipment[0]) ? $equipment[0] : null;
            $data['item_2'] = isset($equipment[1]) ? $equipment[1] : null;
            $data['item_3'] = isset($equipment[2]) ? $equipment[2] : null;
        });
    }
    public function unequipEquipment(string $characterName, array $items): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($items) {
            $equipment = array_diff($data['equipment'], array_intersect($data['equipment'], $items));
            $data['item_1'] = isset($equipment[0]) ? $equipment[0] : null;
            $data['item_2'] = isset($equipment[1]) ? $equipment[1] : null;
            $data['item_3'] = isset($equipment[2]) ? $equipment[2] : null;
        });
    }
    public function setCharacterEquipment($characterName, $equipment): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($equipment) {
            $data['item_1'] = isset($equipment[0]) ? $equipment[0] : null;
            $data['item_2'] = isset($equipment[1]) ? $equipment[1] : null;
            $data['item_3'] = isset($equipment[2]) ? $equipment[2] : null;
        });
    }
    public function getActivateCharacter(): array
    {
        extract($this->game->globals->getAll('turnNo', 'turnOrder'));
        $character = $turnOrder[$turnNo];
        return $this->getCharacterData($character);
    }
    public function getActiveEquipment(): array
    {
        $character = $this->getActivateCharacter();
        return $character['equipment'];
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
        return sizeof($turnOrder) == $turnNo + 1;
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
    public function adjustAllStamina(int $stamina): void
    {
        $this->updateAllCharacterData(function (&$data) use ($stamina) {
            $data['stamina'] = max(min($data['stamina'] + $stamina, $data['max_stamina']), 0);
        });
    }
    public function adjustStamina(string $characterName, int $stamina): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($stamina) {
            $data['stamina'] = max(min($data['stamina'] + $stamina, $data['max_stamina']), 0);
        });
    }
    public function adjustActiveStamina(int $stamina): void
    {
        $characterName = $this->getActivateCharacter()['character_name'];
        $this->adjustStamina($characterName, $stamina);
    }
    public function getActiveHealth(): int
    {
        return (int) $this->getActivateCharacter()['health'];
    }

    public function adjustAllHealth(int $health): void
    {
        $this->updateAllCharacterData(function (&$data) use ($health) {
            $data['health'] = max(min($data['health'] + $health, $data['max_health']), 0);
        });
    }
    public function adjustHealth(string $characterName, int $health): void
    {
        $this->updateCharacterData($characterName, function (&$data) use ($health) {
            $data['health'] = max(min($data['health'] + $health, $data['max_health']), 0);
        });
    }
    public function adjustActiveHealth(int $health): void
    {
        $characterName = $this->getActivateCharacter()['character_name'];
        $this->adjustHealth($characterName, $health);
    }
    public function getMarshallCharacters()
    {
        return array_map(function ($char) {
            return [
                'name' => $char['character_name'],
                'isFirst' => $char['isFirst'],
                'isActive' => $char['isActive'],
                'equipment' => array_filter([$char['item_1'], $char['item_2'], $char['item_3']]),
                'playerColor' => $char['player_color'],
                'playerId' => $char['player_id'],
                'stamina' => $char['stamina'],
                'maxStamina' => $char['max_stamina'],
                'health' => $char['health'],
                'maxHealth' => $char['max_health'],
            ];
        }, $this->getAllCharacterData());
    }
}
