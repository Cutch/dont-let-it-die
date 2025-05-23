<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Bga\GameFramework\Actions\Types\JsonParam;
use BgaUserException;
use Exception;

class DLD_SelectionStates
{
    private Game $game;
    private bool $stateChanged = false;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function completeSelectionState(array $data): void
    {
        if ($data['nextState']) {
            $this->game->nextState($data['nextState']);
        }
        if (array_key_exists('isInterrupt', $data) && $data['isInterrupt']) {
            $this->game->actInterrupt->completeInterrupt();
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
                    throw new BgaUserException(clienttranslate('Select a Character'));
                }
                $stateData = $this->getState(null);
                $stateData['selectedCharacterId'] = $characterId;
                $this->setState(null, $stateData);
                return [
                    'characterId' => $characterId,
                    'nextState' => $stateData['nextState'],
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                $this->completeSelectionState($data);
            }
        );
    }
    public function actSelectResource(?string $resourceType = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$resourceType) {
            throw new BgaUserException(clienttranslate('Select a Resource'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedResourceType'] = $resourceType;
        $this->setState(null, $stateData);
        $data = [
            'resourceType' => $resourceType,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
        ];
        $this->game->log('actSelectResource1', $data);
        $this->game->hooks->onResourceSelection($data);
        $this->completeSelectionState($data);
    }
    public function actSelectHindrance(#[JsonParam] array $data): void
    {
        if (sizeof($data) == 0) {
            throw new BgaUserException(clienttranslate('Select a Hindrance'));
        }
        $stateData = $this->getState(null);
        $stateData['selections'] = $data;
        $this->setState(null, $stateData);
        $data = [
            'selections' => $data,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
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
            throw new BgaUserException(clienttranslate('Select a Card'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedCardId'] = $cardId;
        $this->setState(null, $stateData);
        $data = [
            'cardId' => $cardId,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
        ];
        $this->game->hooks->onCardSelection($data);
        $this->completeSelectionState($data);
    }
    public function actSelectItem(?string $itemId = null, ?string $characterId = null): void
    {
        if (!$itemId) {
            throw new BgaUserException(clienttranslate('Select an item'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedItemId'] = $itemId;
        $stateData['selectedCharacterId'] = $characterId;
        $this->setState(null, $stateData);
        $data = [
            'itemId' => $itemId,
            'characterId' => $characterId,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
        ];
        $this->game->hooks->onItemSelection($data);
        $this->completeSelectionState($data);
    }
    public function actSelectDeck(?string $deckName = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$deckName) {
            throw new BgaUserException(clienttranslate('Select a Deck'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedDeckName'] = $deckName;
        $this->setState(null, $stateData);
        $data = [
            'deck' => $deckName,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
        ];
        $this->game->hooks->onDeckSelection($data);
        $this->completeSelectionState($data);
    }
    public function actSendToCamp(?int $sendToCampId = null): void
    {
        // $this->character->addExtraTime();
        if (!$sendToCampId) {
            throw new BgaUserException(clienttranslate('Select an item'));
        }
        $state = $this->getState($this->game->gamestate->state()['name']);
        $items = $state['items'];
        if (
            !in_array(
                $sendToCampId,
                array_map(function ($d) {
                    return $d['itemId'];
                }, $items)
            )
        ) {
            throw new BgaUserException(clienttranslate('Invalid Item'));
        }
        $items = array_map(function ($d) {
            return $d['itemId'];
        }, $items);
        $character = $this->game->character->getCharacterData($state['characterId']);
        $characterItems = array_map(
            function ($d) {
                return $d['itemId'];
            },
            array_filter($character['equipment'], function ($d) use ($items) {
                return !in_array($d['itemId'], $items);
            })
        );
        $items = array_filter($items, function ($d) use ($sendToCampId) {
            return $d != $sendToCampId;
        });

        $this->game->log('setCharacterEquipment', [...$characterItems, ...$items]);
        $this->game->character->setCharacterEquipment($character['id'], [...$characterItems, ...$items]);

        $campEquipment = $this->game->gameData->get('campEquipment');
        $this->game->log('campEquipment', [...$campEquipment, $sendToCampId]);
        $this->game->gameData->set('campEquipment', [...$campEquipment, $sendToCampId]);
        $this->game->markChanged('player');
        $this->completeSelectionState([
            'nextState' => 'playerTurn',
        ]);
    }
    public function cancelState(?string $stateName): void
    {
        if ($stateName) {
            $state = $this->game->gameData->get($stateName);
            if (array_key_exists('cancellable', $state) && !$state['cancellable']) {
                throw new BgaUserException(clienttranslate('This action cannot be cancelled'));
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
        } elseif ($stateName == 'resourceSelection') {
            return 'resourceSelectionState';
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
        string $nextState = 'playerTurn',
        ?string $title = null,
        bool $isInterrupt = false
    ): void {
        if ($this->stateChanged || $this->stateToStateNameMapping() != null) {
            $pendingStates = $this->game->gameData->get('pendingStates') ?? [];
            array_push($pendingStates, func_get_args());
            $this->game->gameData->set('pendingStates', $pendingStates);
        } else {
            $this->stateChanged = true;
            $stateNameState = $this->stateToStateNameMapping($stateName);

            $playerId = $this->game->getCurrentPlayer();
            $newState = [
                'cancellable' => $cancellable,
                'title' => $title,
                'currentPlayerId' => $playerId,
                'nextState' => $nextState,
                'isInterrupt' => $isInterrupt,
                ...$state,
            ];
            $this->game->gameData->addMultiActiveCharacter($characterId, true);

            $this->game->gameData->set($stateNameState, $newState);
            $this->game->nextState($stateName);
        }
    }
    public function initiateDeckSelection(string $id, ?array $decks = null, ?string $title = null, $cancellable = true)
    {
        if ($decks == null) {
            $decks = $this->game->decks->getAllDeckNames();
        }
        $this->initiateState(
            'deckSelection',
            ['id' => $id, 'decks' => array_values($decks)],
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
