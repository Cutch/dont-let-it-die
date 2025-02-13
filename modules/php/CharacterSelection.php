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
        string $character1 = null,
        string $character2 = null,
        string $character3 = null,
        string $character4 = null
    ): void {
        $characters = [$character1, $character2, $character3, $character4];
        $this->validateCharacterCount(false, $characters);
        $playerId = $this->game->getCurrentPlayer();
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
                    $startsWith = null;
                    extract($this->game->data->characters[$char]);
                    $char = $this->game::escapeStringForDB($char);
                    return "('$char', $playerId, $stamina, $health, $health, $stamina, '$startsWith')";
                }, array_filter($characters))
            );
            $this->game::DbQuery(
                "INSERT INTO `character` (`character_name`, `player_id`, `stamina`, `health`, `max_health`, `max_stamina`, `item_1_name`) VALUES $values"
            );
        }
        // Notify Players
        $results = [];
        $this->game->getAllCharacters($results);
        $this->game->notify->all('characterClicked', '', $results);
    }
    private function validateCharacterCount(bool $checkIfNotEnough, array $characters)
    {
        // Check for bad character name
        foreach ($characters as $index => $char) {
            if ($char) {
                if (!isset($this->game->data->characters[$char])) {
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
        $turnOrder = $this->game->globals->get('turnOrder');
        $players = $this->game->loadPlayersBasicInfos();
        $playerNo = ((int) $players[$playerId]['player_no']) - 1;
        $playerCount = sizeof($players);
        if ($playerCount == 3) {
            foreach ($selectedCharacters as $index => $value) {
                $turnOrder[$playerNo + $index + ($playerNo > 0 ? 1 : 0)] = $value;
            }
        } elseif ($playerCount == 1) {
            foreach ($selectedCharacters as $index => $value) {
                $turnOrder[$playerNo + $index] = $value;
            }
        } elseif ($playerCount == 2) {
            foreach ($selectedCharacters as $index => $value) {
                $turnOrder[$playerNo * 2 + $index] = $value;
            }
        } elseif ($playerCount == 4) {
            foreach ($selectedCharacters as $index => $value) {
                $turnOrder[$playerNo + $index] = $value;
            }
        }
        $this->game->globals->set('turnOrder', $turnOrder);
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
            $selectedCharactersArgs['character' . ($index + 1)] = $value;
            if ($index + 1 == sizeof($selectedCharacters)) {
                $message = $message . ' and ';
            } elseif ($index > 0) {
                $message = $message . ', ';
            }
            $message = $message . '${character' . ($index + 1) . '}';
        }
        $results = ['player_id' => $playerId];
        $this->game->getAllCharacters($results);
        // $this->game->initCharacters($playerId);
        $this->game->notify->all('chooseCharacters', clienttranslate($message), array_merge($results, $selectedCharactersArgs));

        $this->setTurnOrder($playerId, $selectedCharacters);
        // $waiting = sizeof(array_values($this->game->getCollectionFromDb('SELECT 1 FROM `character` WHERE `confirmed` = 0'))) > 0;
        // if ($waiting) {
        //     $this->game->gamestate->nextState('start');
        // }
        // Deactivate player, and move to next state if none are active
        $this->game->gamestate->setPlayerNonMultiactive($playerId, 'start');
    }
}
