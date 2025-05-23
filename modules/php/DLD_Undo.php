<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Error;
use Exception;

class DLD_Undo
{
    private Game $game;
    private array $initialState;
    private ?int $savedMoveId = null;
    private bool $actionWasCleared = false;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function actUndo(): void
    {
        if ($this->game->gamestate->state()['name'] != 'playerTurn') {
            throw new BgaUserException(clienttranslate('Only player actions can be undone'));
        }
        if (!$this->canUndo()) {
            throw new BgaUserException(clienttranslate('Nothing left to undo, dice rolls, and deck pulls clear the undo history'));
        }
        $char = $this->game->character->getTurnCharacterId();
        $undoState = $this->game->getFromDB(
            'SELECT * FROM `undoState` a INNER JOIN (SELECT max(undo_id) max_last_id FROM `undoState`) b WHERE b.max_last_id = a.undo_id'
        );
        $storedCharacterId = $undoState['character_name'];
        if ($char != $storedCharacterId) {
            throw new BgaUserException(clienttranslate('Can\'t undo another player\'s action'));
        }
        $undoId = $undoState['undo_id'];
        $itemTable = json_decode($undoState['itemTable'], true);
        $characterTable = json_decode($undoState['characterTable'], true);
        $globalsTable = json_decode($undoState['globalsTable'], true);
        foreach ($characterTable as $k => $v) {
            $this->game->character->_updateCharacterData($v['id'], $v);
        }
        $this->resetNotifications($undoState['gamelog_move_id']);
        foreach ($globalsTable as $k => $v) {
            if ($k == 'resources') {
                $this->game->gameData->setResources($v);
            } else {
                $this->game->gameData->set($k, $v);
            }
        }
        $this->game->gameData->setItems($itemTable);
        $this->game->markChanged('token');
        $this->game->markChanged('player');
        $this->game->markChanged('knowledge');
        $this->game->markChanged('actions');
        $this->game::DbQuery("DELETE FROM `undoState` where undo_id = $undoId");
        $this->game->completeAction(false);
    }

    public function getLastMoveId(): int
    {
        $data = $this->game->getFromDB('SELECT max(gamelog_move_id) as last_move_id FROM `gamelog`');
        return array_key_exists('last_move_id', $data) ? (int) $data['last_move_id'] : 0;
    }
    public function resetNotifications($moveId): void
    {
        $this->game::DbQuery("DELETE FROM `gamelog` WHERE gamelog_move_id > $moveId");
        $this->game->notify('resetNotifications', '', ['moveId' => $moveId]);
    }

    public function loadInitialState(): void
    {
        $moveId = $this->getLastMoveId();
        $itemsData = json_encode($this->game->gameData->getItems());
        $characterData = json_encode($this->game->character->getAllCharacterData(true));
        $globalsData = json_encode($this->game->gameData->getAll());
        $stateName = '';
        try {
            $stateName = $this->game->gamestate->state()['name'];
        } catch (Exception $e) {
        }
        $this->initialState = [
            'moveId' => $moveId,
            'itemsData' => $itemsData,
            'characterData' => $characterData,
            'globalsData' => $globalsData,
            'stateName' => $stateName,
        ];
    }
    public function saveState(): void
    {
        $char = $this->game->character->getTurnCharacterId();
        if (
            !$this->actionWasCleared &&
            $this->initialState &&
            $this->initialState['stateName'] == 'playerTurn' &&
            $char == $this->game->character->getSubmittingCharacterId()
        ) {
            if ($this->savedMoveId != null) {
                $this->game::DbQuery('DELETE FROM `undoState` where gamelog_move_id=' . $this->savedMoveId);
            }
            $moveId = $this->initialState['moveId'];
            $itemsData = $this->initialState['itemsData'];
            $characterData = $this->initialState['characterData'];
            $globalsData = $this->initialState['globalsData'];
            $this->savedMoveId = $moveId;
            $this->game::DbQuery(
                "INSERT INTO `undoState` (`character_name`, `gamelog_move_id`, `itemTable`, `characterTable`, `globalsTable`) VALUES ('$char', $moveId, '$itemsData', '$characterData', '$globalsData')"
            );
        }
    }

    public function clearUndoHistory(): void
    {
        $this->game::DbQuery('DELETE FROM `undoState` WHERE undo_id > 0');
        $this->actionWasCleared = true;
    }
    public function canUndo(): bool
    {
        return $this->game->getFromDB('SELECT count(1) as `count` FROM `undoState`', true)['count'] > 0;
    }
}
