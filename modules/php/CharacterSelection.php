<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Exception;

class CharacterSelection
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function actCharacterClicked(
        ?string $character1 = null,
        ?string $character2 = null,
        ?string $character3 = null,
        ?string $character4 = null
    ): void {
        $characters = [$character1, $character2, $character3, $character4];
        $this->validateCharacterCount(false, $characters);
        $playerId = $this->game->getCurrentPlayer();
        $this->setTurnOrder($playerId, array_filter($characters));
        // Check if already selected
        $escapedCharacterList = join(
            ', ',
            array_map(function ($char) use ($playerId) {
                $char = $this->game::escapeStringForDB($char);
                return "'$char'";
            }, array_filter($characters))
        );
        if (
            $escapedCharacterList &&
            sizeof(
                array_values(
                    $this->game->getCollectionFromDb(
                        "SELECT 1 FROM `character` WHERE player_id != $playerId AND character_name in (" . $escapedCharacterList . ')'
                    )
                )
            ) > 0
        ) {
            throw new BgaUserException($this->game->translate('Character Selected By Another Player'));
        }
        // Remove player's previous selected
        $this->game::DbQuery("DELETE FROM `character` WHERE player_id = $playerId");
        // Add player's current selected
        if ($character1) {
            $values = join(
                ', ',
                array_map(function ($char) use ($playerId) {
                    extract($this->game->data->getCharacters()[$char]);
                    $char = $this->game::escapeStringForDB($char);
                    return "('$char', $playerId, $stamina, $health)";
                }, array_filter($characters))
            );
            $this->game::DbQuery("INSERT INTO `character` (`character_name`, `player_id`, `stamina`, `health`) VALUES $values");
        }
        $characterIds = $this->game->character->getAllCharacterIds();
        if (in_array('Atouk', $characterIds) && in_array('Yurt', $characterIds)) {
            throw new BgaUserException($this->game->translate('Atouk and Yurt cannot be in the same tribe'));
        }
        // Notify Players
        $results = [];
        $this->game->getAllPlayers($results);
        $this->game->notify('characterClicked', '', ['gameData' => $results]);
    }
    private function validateCharacterCount(bool $checkIfNotEnough, array $characters)
    {
        // Check for bad character name
        foreach ($characters as $index => $char) {
            if ($char) {
                if (!array_key_exists($char, $this->game->data->getCharacters())) {
                    throw new Exception('Bad value for character');
                }
            }
        }
        // Check how many characters the player can select
        $playerId = $this->game->getCurrentPlayer();
        $players = $this->game->loadPlayersBasicInfos();
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
            throw new BgaUserException($this->game->translate('Too many characters selected'));
        }
        if ($checkIfNotEnough && sizeof(array_filter($characters)) != $count) {
            throw new BgaUserException($this->game->translate('Not enough characters selected'));
        }
    }
    private function setTurnOrder($playerId, $selectedCharacters)
    {
        // Set the character turn order
        $turnOrder = $this->game->gameData->get('turnOrder');
        $players = $this->game->loadPlayersBasicInfos();
        $playerNo = ((int) $players[$playerId]['player_no']) - 1;
        $playerCount = sizeof($players);
        if ($playerCount == 3) {
            for ($i = 0; $i < ($playerNo > 0 ? 2 : 1); $i++) {
                $turnOrder[$playerNo + $i + ($playerNo > 0 ? 1 : 0)] = array_key_exists($i, $selectedCharacters)
                    ? $selectedCharacters[$i]
                    : null;
            }
        } elseif ($playerCount == 1) {
            for ($i = 0; $i < 4; $i++) {
                $turnOrder[$playerNo + $i] = array_key_exists($i, $selectedCharacters) ? $selectedCharacters[$i] : null;
            }
        } elseif ($playerCount == 2) {
            for ($i = 0; $i < 2; $i++) {
                $turnOrder[$playerNo * 2 + $i] = array_key_exists($i, $selectedCharacters) ? $selectedCharacters[$i] : null;
            }
        } elseif ($playerCount == 4) {
            for ($i = 0; $i < 1; $i++) {
                $turnOrder[$playerNo + $i] = array_key_exists($i, $selectedCharacters) ? $selectedCharacters[$i] : null;
            }
        }
        $this->game->gameData->set('turnOrder', $turnOrder);
    }
    public function actChooseCharacters(): void
    {
        $playerId = $this->game->getCurrentPlayer();
        $selectedCharacters = array_map(function ($char) {
            return $char['character_name'];
        }, array_values($this->game->getCollectionFromDb("SELECT character_name FROM `character` WHERE `player_id` = '$playerId'")));

        $this->validateCharacterCount(true, $selectedCharacters);

        $this->game::DbQuery("UPDATE `character` set `confirmed`=1 WHERE `player_id` = $playerId");
        $selectedCharactersArgs = [];
        $message = '${player_name} selected ';
        foreach ($selectedCharacters as $index => $value) {
            $characterObject = $this->game->data->getCharacters()[$value];
            if (array_key_exists('startsWith', $characterObject)) {
                $itemId = $this->game->gameData->createItem($characterObject['startsWith']);
                $this->game->character->equipEquipment($value, [$itemId]);
            }
            $this->game->hooks->onCharacterChoose($characterObject);

            $selectedCharactersArgs['character' . ($index + 1)] = $value;
            if ($index + 1 == sizeof($selectedCharacters)) {
                $message = $message . ' and ';
            } elseif ($index > 0) {
                $message = $message . ', ';
            }
            $message = $message . '${character' . ($index + 1) . '}';
        }
        $this->game->character->adjustAllHealth(10);
        $this->game->character->adjustAllStamina(10);

        $this->setTurnOrder($playerId, $selectedCharacters);
        $results = ['player_id' => $playerId];
        $this->game->getAllPlayers($results);
        // $this->game->initCharacters($playerId);
        $this->game->notify('chooseCharacters', clienttranslate($message), array_merge(['gameData' => $results], $selectedCharactersArgs));

        // Deactivate player, and move to next state if none are active
        $this->game->gamestate->setPlayerNonMultiactive(
            $playerId,
            $this->game->isValidExpansion('hindrance') ? 'startHindrance' : 'playerTurn'
        );
    }
    public function test_swapCharacter($character)
    {
        $oldChar = $this->game->character->getTurnCharacterId();
        $playerId = $this->game->getCurrentPlayer();
        // Remove player's previous selected
        $this->game::DbQuery('DELETE FROM `character` WHERE character_name = "' . $oldChar . '"');
        // Add player's current selected
        $data = $this->game->data->getCharacters()[$character];
        $health = $data['health'];
        $stamina = $data['stamina'];
        $char = $this->game::escapeStringForDB($character);
        $this->game::DbQuery(
            "INSERT INTO `character` (`character_name`, `player_id`, `stamina`, `health`) VALUES ('$char', $playerId, $stamina, $health)"
        );
        $turnOrder = $this->game->gameData->get('turnOrder');
        $this->game->gameData->set(
            'turnOrder',
            array_map(function ($charId) use ($oldChar, $character) {
                return $charId == $oldChar ? $character : $charId;
            }, $turnOrder)
        );
        if (array_key_exists('startsWith', $data)) {
            $itemId = $this->game->gameData->createItem($data['startsWith']);
            $this->game->character->equipEquipment($character, [$itemId]);
        }
        $this->game->hooks->onCharacterChoose($data);

        $this->game->character->adjustAllHealth(10);
        $this->game->character->adjustAllStamina(10);
    }
}
