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
        $isInterrupt = array_key_exists('isInterrupt', $data) && $data['isInterrupt'];

        if ($data['nextState']) {
            $this->game->character->setSubmittingCharacterById(null);
            $this->game->nextState($data['nextState']);
        }
        if ($isInterrupt) {
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
    public function actSelectButton(?string $buttonValue = null): void
    {
        // $this->game->character->addExtraTime();
        if (!$buttonValue) {
            throw new BgaUserException(clienttranslate('Selection is required'));
        }
        $stateData = $this->getState(null);
        $stateData['selectedButtonValue'] = $buttonValue;
        $this->setState(null, $stateData);
        $data = [
            'buttonValue' => $buttonValue,
            'nextState' => $stateData['nextState'],
            'isInterrupt' => $stateData['isInterrupt'],
            'characterId' => $stateData['characterId'],
        ];
        $this->game->hooks->onButtonSelection($data);
        $this->completeSelectionState($data);
    }
    public function actSelectEat(?string $resourceType = null): void
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
            'characterId' => $stateData['characterId'],
        ];
        $this->game->hooks->onEatSelection($data);
        $this->_actSelectEat($resourceType, $data);
    }
    public function _actSelectEat(?string $resourceType = null, array $selectionState)
    {
        $this->game->character->setSubmittingCharacterById($selectionState['characterId']);
        $this->game->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->game->hooks, 'onEat'],
            function (Game $_this) use ($resourceType, $selectionState) {
                $this->game->actions->validateCanRunAction('actEat', null, $resourceType);

                $tokenData = $this->game->data->getTokens()[$resourceType];
                $data = [
                    'type' => $resourceType,
                    ...$tokenData['actEat'],
                    'tokenName' => $tokenData['name'],
                    'selectionState' => $selectionState,
                ];
                return $data;
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if (array_key_exists('health', $data)) {
                    $data['health'] = $this->game->character->adjustActiveHealth($data['health']);
                }
                if (array_key_exists('stamina', $data)) {
                    $data['stamina'] = $this->game->character->adjustActiveStamina($data['stamina']);
                }
                $left = $this->game->adjustResource($data['type'], -$data['count'])['left'];
                if (!$data || !array_key_exists('notify', $data) || $data['notify'] != false) {
                    if ($left == 0) {
                        $this->game->notify(
                            'notify',
                            !array_key_exists('stamina', $data)
                                ? clienttranslate('${character_name} ate ${count} ${token_name} and gained ${health} health')
                                : clienttranslate(
                                    '${character_name} ate ${count} ${token_name} and gained ${health} health and ${stamina} stamina'
                                ),
                            [...$data, 'token_name' => $data['tokenName']]
                        );
                    }
                }
                $this->completeSelectionState($data['selectionState']);
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
        } elseif ($stateName == 'eatSelection') {
            return 'eatSelectionState';
        } elseif ($stateName == 'buttonSelection') {
            return 'buttonSelectionState';
        }
        return null;
    }
    public function argSelectionState(): array
    {
        $stateName = $this->stateToStateNameMapping();
        $state = $this->getState();
        $result = [
            'actions' => [],
            'selectionState' => $this->game->gameData->get($stateName),
            'character_name' => $this->game->getCharacterHTML($state['characterId']),
        ];
        $this->game->getGameData($result);
        $this->game->getResources($result);
        if ($stateName === 'deckSelectionState') {
            $this->game->getDecks($result);
        }
        if ($stateName === 'eatSelection') {
            $this->game->getItemData($result);
        }
        return $result;
    }
    public function actCancel(): void
    {
        $stateName = $this->stateToStateNameMapping();
        $this->cancelState($stateName);
    }
    public function getState(?string $stateName = null): array
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
        bool $isInterrupt = false,
        bool $isPendingState = false
    ): void {
        if ($this->stateChanged || $this->stateToStateNameMapping() != null) {
            $pendingStates = $this->game->gameData->get('pendingStates') ?? [];
            // WARNING: Update if args change
            $args = [$stateName, $state, $characterId, $cancellable, $nextState, $title, $isInterrupt, $isPendingState];
            $args[sizeof($args) - 1] = true;
            array_push($pendingStates, $args);
            $this->game->gameData->set('pendingStates', $pendingStates);
        } else {
            $this->stateChanged = true;
            $stateNameState = $this->stateToStateNameMapping($stateName);

            $playerId = $this->game->getCurrentPlayer();
            $newState = [
                'cancellable' => $cancellable,
                'title' => $title,
                'currentPlayerId' => $playerId,
                'characterId' => $characterId,
                'nextState' => $nextState,
                'isInterrupt' => $isInterrupt,
                'isPendingState' => $isPendingState,
                ...$state,
            ];
            $this->game->gameData->addMultiActiveCharacter($characterId, true);

            $this->game->gameData->set($stateNameState, $newState);
            $this->game->nextState($stateName);
        }
    }
    public function initiateDeckSelection(
        string $id,
        ?array $decks = null,
        ?string $title = null,
        $cancellable = true,
        array $extraArgs = []
    ) {
        if ($decks == null) {
            $decks = $this->game->decks->getAllDeckNames();
        }
        $this->initiateState(
            'deckSelection',
            [...$extraArgs, 'id' => $id, 'decks' => array_values($decks)],
            $this->game->character->getTurnCharacterId(),
            $cancellable,
            'playerTurn',
            $title
        );
    }
    public function initiateHindranceSelection(
        string $id,
        ?array $characters = null,
        ?string $button = null,
        ?bool $cancellable = true,
        ?string $nextState = 'playerTurn'
    ) {
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
            $cancellable,
            $nextState
        );
    }
}
