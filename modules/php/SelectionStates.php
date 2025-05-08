<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Bga\GameFramework\Actions\Types\JsonParam;
use BgaUserException;
use Exception;

class SelectionStates
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function actSelectCharacter(?string $characterId = null): void
    {
        if (!$characterId) {
            throw new BgaUserException($this->game->translate('Select a Character'));
        }
        $data = [
            'characterId' => $characterId,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onCharacterSelection($data);
        $this->game->gameData->set('characterSelectionState', null);
        if ($data['nextState'] != false) {
            $this->game->gamestate->nextState($data['nextState']);
        }
        $this->game->completeAction();
    }
    public function actSelectResource(?string $resourceType = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$resourceType) {
            throw new BgaUserException($this->game->translate('Select a Resource'));
        }
        $data = [
            'resourceType' => $resourceType,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onResourceSelection($data);
        if ($data['nextState'] != false) {
            $this->game->gamestate->nextState($data['nextState']);
        }
        $this->game->completeAction();
    }
    public function actSelectHindrance(#[JsonParam] array $data): void
    {
        if (sizeof($data) == 0) {
            throw new BgaUserException($this->game->translate('Select a Hindrance'));
        }
        $data = [
            'selections' => $data,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onHindranceSelection($data);

        if ($data['nextState'] != false) {
            $this->game->gamestate->nextState($data['nextState']);
        }
        $this->game->hooks->onHindranceSelectionAfter($data);
        $this->game->completeAction();
    }
    public function actSelectCard(?string $cardId = null): void
    {
        if (!$cardId) {
            throw new BgaUserException($this->game->translate('Select a Card'));
        }
        $data = [
            'cardId' => $cardId,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onCardSelection($data);
        if ($data['nextState'] != false) {
            $this->game->gamestate->nextState($data['nextState']);
        }
        $this->game->completeAction();
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$deckName) {
            throw new BgaUserException($this->game->translate('Select a Deck'));
        }
        $data = [
            'deckName' => $deckName,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onDeckSelection($data);
        if ($data['nextState'] != false) {
            $this->game->gamestate->nextState($data['nextState']);
        }
        $this->game->completeAction();
    }
    public function cancelState(?string $stateName): void
    {
        if ($stateName) {
            $state = $this->game->gameData->get($stateName);
            if (array_key_exists('cancellable', $state) && !$state['cancellable']) {
                throw new BgaUserException($this->game->translate('This action cannot be cancelled'));
            }
            $this->game->gameData->set($stateName, [...$state, 'cancelled' => true]);
        }

        if (!$this->game->actInterrupt->onInterruptCancel()) {
            $this->game->gamestate->nextState('playerTurn');
        }
        $this->game->completeAction();
    }
    public function stateToStateNameMapping(?string $stateName = null): ?string
    {
        $stateName = $stateName ?? $this->game->gamestate->state()['name'];
        if ($stateName == 'characterSelection') {
            return 'characterSelectionState';
        } elseif ($stateName == 'hindranceSelection') {
            return 'hindranceSelectionState';
        } elseif ($stateName == 'cardSelection') {
            return 'cardSelectionState';
        } elseif ($stateName == 'deckSelection') {
            return 'deckSelectionState';
        } elseif ($stateName == 'tooManyItems') {
            return 'tooManyItemsState'; // Check
        } elseif ($stateName == 'tradeSelection') {
            return 'tradeSelectionState'; // Check
        }
        return null;
    }
    public function argSelectionState()
    {
        $stateName = $this->stateToStateNameMapping();
        $result = [
            'actions' => [],
            'selectionState' => $this->game->gameData->get($stateName),
            'character_name' => $this->game->getCharacterHTML(),
        ];
        $this->game->getGameData($result);
        $this->game->getResources($result);
        if ($stateName === 'deckSelectionState') {
            $this->game->getDecks($result);
        }
    }
    public function actCancel(): void
    {
        $stateName = $this->stateToStateNameMapping();
        $this->cancelState($stateName);

        $this->game->completeAction();
    }
    public function getState(string $stateName): array
    {
        $stateNameState = $this->stateToStateNameMapping($stateName);
        return $this->game->gameData->get($stateNameState);
    }
    public function initiateState(string $stateName, array $state, bool $cancellable = true, ?string $characterId = null): void
    {
        $stateNameState = $this->stateToStateNameMapping($stateName);

        $playerId = $this->game->getCurrentPlayer();
        $newState = [...$state, 'cancellable' => $cancellable, 'currentPlayerId' => $playerId];
        if ($characterId) {
            $newPlayerId = $this->game->character->getCharacterData($characterId)['player_id'];
            if ($newPlayerId != $playerId) {
                $this->game->gamestate->changeActivePlayer($newPlayerId);
                $newState['newPlayerId'] = $newPlayerId;
            }
        }
        $this->game->gameData->set($stateNameState, $newState);
        $this->game->gamestate->nextState($stateName);
    }
    public function initiateDeckSelection(?array $decks = null, ?string $title = null, $cancellable = true)
    {
        if ($decks == null) {
            $decks = $this->game->decks->getAllDeckNames();
        }
        $this->initiateState(
            'deckSelection',
            [
                'decks' => array_values($decks),
                'title' => $title,
            ],
            $cancellable
        );
    }
    public function initiateHindranceSelection(string $id, ?array $characters = null, ?string $button = null)
    {
        if ($characters == null) {
            $characters = [$this->game->character->getTurnCharacterId()];
        }
        $characters = array_values(
            array_map(
                function ($d) {
                    return ['physicalHindrance' => $d['physicalHindrance'], 'characterId' => $d['character_name']];
                },
                array_filter($this->game->character->getAllCharacterData(), function ($d) use ($characters) {
                    return in_array($d['id'], $characters);
                })
            )
        );
        $this->initiateState('hindranceSelection', ['id' => $id, 'characters' => $characters, 'button' => $button], false);
    }
}
