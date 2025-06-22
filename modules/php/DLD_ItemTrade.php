<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
use Bga\GameFramework\Actions\Types\JsonParam;

use BgaUserException;

class DLD_ItemTrade
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function actConfirmTradeItem(): void
    {
        $state = $this->game->gameData->get('tradeState');
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];

        $responseData = [...$this->tradeProcess($trade1, $trade2), 'trade1' => $trade1, 'trade2' => $trade2];
        $this->completeTrade($responseData);
        foreach ($this->game->gamestate->getActivePlayerList() as $playerId) {
            $this->game->gamestate->nextPrivateState($playerId, 'tradePhaseActions');
        }
        // $this->game->gamestate->nextPrivateState($trade1['character']['playerId'], 'tradePhaseActions');
        // $this->game->gamestate->nextPrivateState($trade2['character']['playerId'], 'tradePhaseActions');
    }
    public function actCancelTrade(): void
    {
        $state = $this->game->gameData->get('tradeState');
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];

        foreach ($this->game->gamestate->getActivePlayerList() as $playerId) {
            $this->game->gamestate->nextPrivateState($playerId, 'tradePhaseActions');
        }
        // $this->game->gamestate->nextPrivateState($trade1['character']['playerId'], 'tradePhaseActions');
        // $this->game->gamestate->nextPrivateState($trade2['character']['playerId'], 'tradePhaseActions');
        $this->game->gameData->set('tradeState', null);
    }
    public function actTradeDone(): void
    {
        $selfId = $this->game->getCurrentPlayer();

        $this->game->gamestate->setPlayerNonMultiactive($selfId, 'nextCharacter');
        // $this->game->gamestate->unsetPrivateState($selfId);
    }
    public function actTradeYield(): void
    {
        $selfId = $this->game->getCurrentPlayer();

        $this->game->gamestate->setPlayerNonMultiactive($selfId, 'nextCharacter');
        $this->game->gameData->set('tradeYield', [(int) $selfId, ...$this->game->gameData->get('tradeYield') ?? []]);
        // $this->game->gamestate->unsetPrivateState($selfId);
    }
    public function actForceSkip(): void
    {
        $this->game->gamestate->unsetPrivateStateForAllPlayers();
        $this->game->nextState('nextCharacter');
    }
    public function actUnBack(): void
    {
        $selfId = $this->game->getCurrentPlayer();
        if (!$this->game->gamestate->isPlayerActive($selfId)) {
            $this->game->gameData->set(
                'tradeYield',
                array_values(
                    array_filter($this->game->gameData->get('tradeYield') ?? [], function ($d) use ($selfId) {
                        return $d != $selfId;
                    })
                )
            );
            $this->game->gamestate->setPlayersMultiactive([$selfId], 'playerTurn', false);
        }
    }
    public function actTradeItem(#[JsonParam] array $data): void
    {
        if (sizeof($data['selection']) != 2) {
            throw new BgaUserException(clienttranslate('You must make 2 selections'));
        }
        $selfId = $this->game->getCurrentPlayer();
        $hasSelf = false;
        $hasItem = false;
        $trade1 = [];
        $trade2 = [];
        $sendToCamp = false;
        $responseData = [];
        array_walk($data['selection'], function (&$trade) use ($selfId, &$hasSelf, &$hasItem, &$trade1, &$trade2, &$sendToCamp) {
            if (array_key_exists('character', $trade)) {
                $trade['character'] = $this->game->character->getCharacterData($trade['character']);
                $hasSelf = $hasSelf || $selfId == $trade['character']['playerId'];
                if ($sendToCamp) {
                    $trade1 = $trade;
                } else {
                    if (!$trade1) {
                        $trade1 = $trade;
                    } else {
                        $trade2 = $trade;
                    }
                }
            } else {
                if ($trade2) {
                    $trade1 = $trade2;
                }
                $trade2 = $trade;
                $sendToCamp = true;
            }
            if (array_key_exists('itemId', $trade)) {
                $hasItem = $hasItem || true;
            }
        });
        // if (!$hasSelf) {
        //     throw new BgaUserException(clienttranslate('Select one of your character\'s items to trade'));
        // }
        if (!$hasItem) {
            throw new BgaUserException(clienttranslate('Select one item to trade'));
        }
        $hookData = [
            'sendToCamp' => $sendToCamp,
            'trade1' => $trade1,
            'trade2' => $trade2,
        ];
        $this->game->hooks->onItemTrade($hookData);
        $activatePlayer = function ($toPlayerId) {
            if (!in_array((int) $toPlayerId, $this->game->gameData->get('tradeYield') ?? [])) {
                if (!$this->game->gamestate->isPlayerActive($toPlayerId)) {
                    $this->game->gamestate->setPlayersMultiactive([$toPlayerId], 'playerTurn', false);
                }
            }
        };
        if ($sendToCamp) {
            $itemId1 = array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null;
            $itemId2 = array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null;

            $character = $this->game->character->getCharacterData($trade1['character']['character_name']);
            $characterItems = array_map(
                function ($d) {
                    return $d['itemId'];
                },
                array_filter($character['equipment'], function ($d) use ($itemId1) {
                    return $d['itemId'] != $itemId1;
                })
            );
            if ($itemId2) {
                $result = $this->game->character->getItemValidations((int) $itemId2, $trade1['character'], (int) $itemId1);
                $hasOpenSlots = $result['hasOpenSlots'];
                $hasDuplicateTool = $result['hasDuplicateTool'];

                if ($this->checkForTradableCharacters($trade1['character']['id'], $itemId2)) {
                    throw new BgaUserException(clienttranslate('Tribe member cannot obtain items from trade'));
                }
                if (!$hasOpenSlots) {
                    throw new BgaUserException(clienttranslate('There is no open slot for that item type'));
                }
                if ($hasDuplicateTool) {
                    throw new BgaUserException(clienttranslate('Cannot have a duplicate tool'));
                }
                array_push($characterItems, $itemId2);
            }
            $campEquipment = array_values(
                array_filter($this->game->gameData->get('campEquipment'), function ($d) use ($itemId2) {
                    return $d != $itemId2;
                })
            );

            if ($itemId1) {
                array_push($campEquipment, $itemId1);
            }

            $this->game->character->setCharacterEquipment($character['id'], $characterItems);

            $this->game->gameData->set('campEquipment', $campEquipment);

            $results = [];
            $items = $this->game->gameData->getCreatedItems();
            $this->game->getAllPlayers($results);
            $this->game->getItemData($results);
            $this->game->notify('tradeItem', clienttranslate('${character_name_1} traded an item with the camp'), [
                'character_name_1' => $this->game->getCharacterHTML($trade1['character']['id']),
                'itemId1' => array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null,
                'itemId2' => array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null,
                'itemName1' => array_key_exists('itemId', $trade1) ? $items[$trade1['itemId']] : null,
                'itemName2' => array_key_exists('itemId', $trade2) ? $items[$trade2['itemId']] : null,
                'character1' => $trade1['character']['id'],
                'character2' => null,
                'gameData' => $results,
            ]);
            $activatePlayer($trade1['character']['playerId']);
        } else {
            // var_dump($this->game->gamestate->getActivePlayerList(), $this->game->gamestate->isPlayerActive(2411502));
            $activatePlayer($trade1['character']['playerId']);
            $activatePlayer($trade2['character']['playerId']);
            // if ($trade1['character']['playerId'] != $trade2['character']['playerId']) {
            //     $this->game->gameData->set('tradeState', [
            //         'trade1' => $trade1,
            //         'trade2' => $trade2,
            //     ]);
            //     $toPlayerId = $trade2['character']['playerId'];
            //     if (!$this->game->gamestate->isPlayerActive($toPlayerId)) {
            //         $this->game->gamestate->setPlayersMultiactive([$toPlayerId], 'playerTurn', false);
            //         $this->game->gamestate->initializePrivateState($toPlayerId);
            //         $this->game->gamestate->nextPrivateState($toPlayerId, 'confirmTradePhase');
            //     } else {
            //         $this->game->gamestate->nextPrivateState($toPlayerId, 'confirmTradePhase');
            //     }
            //     foreach ($this->game->gamestate->getActivePlayerList() as $playerId) {
            //         // $this->game->gamestate->nextPrivateState($playerId, 'tradePhaseActions');
            //         if ($toPlayerId != $playerId) {
            //             $this->game->gamestate->nextPrivateState($playerId, 'waitTradePhase');
            //         }
            //     }
            // } else {
            $responseData = [...$this->tradeProcess($trade1, $trade2), 'trade1' => $trade1, 'trade2' => $trade2];
            $this->completeTrade($responseData);
            // }
        }
    }
    private function checkForTradableCharacters(string $characterName, string|int $itemId)
    {
        $tempLastItemOwners = $this->game->gameData->get('tempLastItemOwners');
        return in_array($characterName, ['Sig', 'Tooth']) &&
            (!array_key_exists($itemId, $tempLastItemOwners) || $tempLastItemOwners[$itemId] != $characterName);
    }

    private function tradeProcess($trade1, $trade2)
    {
        $itemId1 = array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null;
        $itemId2 = array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null;
        $characterItems1 = array_map(
            function ($d) {
                return $d['itemId'];
            },
            array_filter($trade1['character']['equipment'], function ($d) use ($itemId1) {
                return $d['itemId'] != $itemId1;
            })
        );
        $characterItems2 = array_map(
            function ($d) {
                return $d['itemId'];
            },
            array_filter($trade2['character']['equipment'], function ($d) use ($itemId2) {
                return $d['itemId'] != $itemId2;
            })
        );
        if ($itemId1) {
            $result = $this->game->character->getItemValidations((int) $itemId1, $trade2['character'], (int) $itemId2);
            $hasOpenSlots = $result['hasOpenSlots'];
            $hasDuplicateTool = $result['hasDuplicateTool'];
            if ($this->checkForTradableCharacters($trade2['character']['id'], $itemId1)) {
                throw new BgaUserException(clienttranslate('Tribe member cannot obtain items from trade'));
            }
            if (!$hasOpenSlots) {
                throw new BgaUserException(clienttranslate('There is no open slot for that item type'));
            }
            if ($hasDuplicateTool) {
                throw new BgaUserException(clienttranslate('Cannot of a duplicate tool'));
            }
            array_push($characterItems2, $itemId1);
        }
        if ($itemId2) {
            $result = $this->game->character->getItemValidations((int) $itemId2, $trade1['character'], (int) $itemId1);
            $hasOpenSlots = $result['hasOpenSlots'];
            $hasDuplicateTool = $result['hasDuplicateTool'];

            if ($this->checkForTradableCharacters($trade1['character']['id'], $itemId2)) {
                throw new BgaUserException(clienttranslate('Tribe member cannot obtain items from trade'));
            }
            if (!$hasOpenSlots) {
                throw new BgaUserException(clienttranslate('There is no open slot for that item type'));
            }
            if ($hasDuplicateTool) {
                throw new BgaUserException(clienttranslate('Cannot of a duplicate tool'));
            }
            array_push($characterItems1, $itemId2);
        }

        return [
            'characterItems1' => $characterItems1,
            'characterItems2' => $characterItems2,
        ];
    }
    private function completeTrade($data)
    {
        if ($data) {
            $characterItems1 = $data['characterItems1'];
            $characterItems2 = $data['characterItems2'];
            $trade1 = $data['trade1'];
            $trade2 = $data['trade2'];
            $this->game->character->setCharacterEquipment($trade1['character']['id'], array_values($characterItems1));
            $this->game->character->setCharacterEquipment($trade2['character']['id'], array_values($characterItems2));

            $results = [];
            $items = $this->game->gameData->getCreatedItems();
            $this->game->getAllPlayers($results);
            $this->game->getItemData($results);
            $this->game->notify('tradeItem', clienttranslate('${character_name_1} traded an item to ${character_name_2}'), [
                'character_name_1' => $this->game->getCharacterHTML($trade1['character']['id']),
                'character_name_2' => $this->game->getCharacterHTML($trade2['character']['id']),
                'itemId1' => array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null,
                'itemId2' => array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null,
                'itemName1' => array_key_exists('itemId', $trade1) ? $items[$trade1['itemId']] : null,
                'itemName2' => array_key_exists('itemId', $trade2) ? $items[$trade2['itemId']] : null,
                'character1' => $trade1['character']['id'],
                'character2' => $trade2['character']['id'],
                'gameData' => $results,
            ]);
        }
    }
    public function argTradePhaseActions($playerId)
    {
        $result = [
            'actions' => [
                [
                    'action' => 'actTradeItem',
                    'type' => 'action',
                ],
            ],
            'activeTurnPlayerId' => 0,
        ];
        // $result['character_name'] = $this->game->getCharacterHTML();
        // $this->game->getAllPlayers($result);
        // $this->game->getDecks($result);
        // $this->game->getGameData($result);
        // $this->game->getItemData($result);
        return $result;
    }
    public function argConfirmTradePhase($playerId)
    {
        $state = $this->game->gameData->get('tradeState');
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];
        $result = [
            'trade1' => $trade1,
            'trade2' => $trade2,
            'actions' => [
                [
                    'action' => 'actConfirmTradeItem',
                    'type' => 'action',
                ],
            ],
            'activeTurnPlayerId' => 0,
        ];
        return $result;
    }
    public function argWaitTradePhase($playerId)
    {
        $state = $this->game->gameData->get('tradeState');
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];
        $result = [
            'trade1' => $trade1,
            'trade2' => $trade2,
        ];
        return $result;
    }
    public function argTradePhase()
    {
        $result = [
            'actions' => [],
            'activeTurnPlayerId' => 0,
            'lastItemOwners' => $this->game->gameData->get('tempLastItemOwners'),
        ];
        $result['character_name'] = $this->game->getCharacterHTML();
        $this->game->getAllPlayers($result);
        $this->game->getDecks($result);
        $this->game->getGameData($result);
        $this->game->getResources($result);
        $this->game->getItemData($result);
        return $result;
    }
    public function stTradePhase()
    {
        $this->game->gameData->set('tradeYield', []);
        $this->game->gameData->set('tempLastItemOwners', $this->game->gameData->get('lastItemOwners'));
        if (sizeof($this->game->getCraftedItems()) == 0) {
            $this->game->nextState('nextCharacter');
        } else {
            $this->game->gamestate->setAllPlayersMultiactive();
            foreach ($this->game->gamestate->getActivePlayerList() as $playerId) {
                $this->game->giveExtraTime((int) $playerId);
            }
            $this->game->gamestate->initializePrivateStateForAllActivePlayers();
        }
    }
    public function stTradePhaseWait() {}
}
