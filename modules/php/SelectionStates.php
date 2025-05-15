<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Bga\GameFramework\Actions\Types\JsonParam;
use BgaUserException;
use Exception;

class SelectionStates
{
    private Game $game;
    private bool $stateChanged = false;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function completeSelectionState(string|bool $nextState): void
    {
        if ($nextState) {
            $this->game->nextState($nextState);
        }
        $this->initiatePendingState();
    }
    public function actSelectCharacter(?string $characterId = null): void
    {
        $this->game->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->game->hooks, 'onCharacterSelection'],
            function (Game $_this) use ($characterId) {
                if (!$characterId) {
                    throw new BgaUserException($_this->translate('Select a Character'));
                }
                $stateData = $this->getState(null);
                $stateData['selectedCharacterId'] = $characterId;
                $this->setState(null, $stateData);
                return [
                    'characterId' => $characterId,
                    'nextState' => 'playerTurn',
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if ($data['nextState'] != false) {
                    $_this->nextState($data['nextState']);
                }
                $this->initiatePendingState();
            }
        );
    }
    public function actSelectResource(?string $resourceType = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$resourceType) {
            throw new BgaUserException($this->game->translate('Select a Resource'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedResourceType'] = $resourceType;
        $this->setState(null, $stateData);
        $data = [
            'resourceType' => $resourceType,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onResourceSelection($data);
        if ($data['nextState'] != false) {
            $this->game->nextState($data['nextState']);
        }
        $this->initiatePendingState();
    }
    public function actSelectHindrance(#[JsonParam] array $data): void
    {
        if (sizeof($data) == 0) {
            throw new BgaUserException($this->game->translate('Select a Hindrance'));
        }
        $stateData = $this->getState(null);
        $stateData['selections'] = $data;
        $this->setState(null, $stateData);
        $data = [
            'selections' => $data,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onHindranceSelection($data);

        if ($data['nextState'] != false) {
            $this->game->nextState($data['nextState']);
        }
        $this->game->hooks->onHindranceSelectionAfter($data);
        $this->initiatePendingState();
    }
    public function actSelectCard(?string $cardId = null): void
    {
        if (!$cardId) {
            throw new BgaUserException($this->game->translate('Select a Card'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedCardId'] = $cardId;
        $this->setState(null, $stateData);
        $data = [
            'cardId' => $cardId,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onCardSelection($data);
        if ($data['nextState'] != false) {
            $this->game->nextState($data['nextState']);
        }
        $this->initiatePendingState();
    }
    public function actSelectItem(?string $itemId = null): void
    {
        if (!$itemId) {
            throw new BgaUserException($this->game->translate('Select an item'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedItemId'] = $itemId;
        $this->setState(null, $stateData);
        $data = [
            'itemId' => $itemId,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onItemSelection($data);
        if ($data['nextState'] != false) {
            $this->game->nextState($data['nextState']);
        }
        $this->initiatePendingState();
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$deckName) {
            throw new BgaUserException($this->game->translate('Select a Deck'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedDeckName'] = $deckName;
        $this->setState(null, $stateData);
        $data = [
            'deckName' => $deckName,
            'nextState' => 'playerTurn',
        ];
        $this->game->hooks->onDeckSelection($data);
        if ($data['nextState'] != false) {
            $this->game->nextState($data['nextState']);
        }
        $this->initiatePendingState();
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
            $this->game->nextState('playerTurn');
        }
        $this->initiatePendingState();
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
        } elseif ($stateName == 'itemSelection') {
            return 'itemSelectionState'; // Check
        }
        return null;
    }
    public function argSelectionState(): array
    {
        $stateName = $this->stateToStateNameMapping();
        $result = [
            'actions' => [],
            'selectionState' => $this->game->gameData->get($stateName),
            'character_name' => $this->game->getCharacterHTML(),
        ];
        $this->game->log($this->game->gamestate->state()['name'], $stateName, $result);
        $this->game->getGameData($result);
        $this->game->getResources($result);
        if ($stateName === 'deckSelectionState') {
            $this->game->getDecks($result);
        }
        return $result;
    }
    public function actCancel(): void
    {
        $stateName = $this->stateToStateNameMapping();
        $this->cancelState($stateName);
    }
    public function getState(?string $stateName): array
    {
        $stateNameState = $this->stateToStateNameMapping($stateName);
        return $this->game->gameData->get($stateNameState);
    }
    public function setState(?string $stateName, ?array $data): void
    {
        $stateNameState = $this->stateToStateNameMapping($stateName);
        $this->game->gameData->set($stateNameState, $data);
    }
    public function initiatePendingState(): void
    {
        $pendingStates = $this->game->gameData->get('pendingStates') ?? [];
        if (sizeof($pendingStates) > 0) {
            $this->initiateState(...$pendingStates[0]);
            array_shift($pendingStates);
            $this->game->gameData->set('pendingStates', $pendingStates);
        }
        $this->game->completeAction();
    }
    public function initiateState(
        string $stateName,
        array $state,
        string $characterId,
        bool $cancellable = true,
        ?string $title = null
    ): void {
        if ($this->stateChanged) {
            $pendingStates = $this->game->gameData->get('pendingStates') ?? [];
            array_push($pendingStates, func_get_args());
            $this->game->gameData->set('pendingStates', $pendingStates);
        } else {
            $this->stateChanged = true;
            $stateNameState = $this->stateToStateNameMapping($stateName);

            $playerId = $this->game->getCurrentPlayer();
            $newState = ['cancellable' => $cancellable, 'title' => $title, 'currentPlayerId' => $playerId, ...$state];
            $this->game->gameData->addMultiActiveCharacter($characterId, $stateName, true);

            $this->game->gameData->set($stateNameState, $newState);
            $this->game->nextState($stateName);
        }
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
            ],
            $this->game->character->getTurnCharacterId(),
            $cancellable,
            $title
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
        $this->initiateState(
            'hindranceSelection',
            ['id' => $id, 'characters' => $characters, 'button' => $button],
            $this->game->character->getTurnCharacterId(),
            false
        );
    }
}
