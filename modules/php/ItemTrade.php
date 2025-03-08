<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
use Bga\GameFramework\Actions\Types\JsonParam;

use BgaUserException;

class ItemTrade
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function actConfirmTradeItem(): void
    {
        $selfId = $this->game->getCurrentPlayer();
        $state = $this->game->gameData->get('tradeState' . $selfId);
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];

        $responseData = [...$this->tradeProcess($trade1, $trade2), 'trade1' => $trade1, 'trade2' => $trade2];
        $this->completeTrade($responseData);
        $this->game->gamestate->nextPrivateState($trade1['character']['player_id'], 'tradePhaseActions');
        $this->game->gamestate->nextPrivateState($trade2['character']['player_id'], 'tradePhaseActions');
    }
    public function actCancelTrade(): void
    {
        $selfId = $this->game->getCurrentPlayer();
        $state = $this->game->gameData->get('tradeState' . $selfId);
        $trade1 = $state['trade1'];
        $trade2 = $state['trade2'];

        $this->game->gamestate->nextPrivateState($trade1['character']['player_id'], 'tradePhaseActions');
        $this->game->gamestate->nextPrivateState($trade2['character']['player_id'], 'tradePhaseActions');
        $this->game->gameData->set('tradeState' . $selfId, null);
    }
    public function actTradeDone(): void
    {
        $selfId = $this->game->getCurrentPlayer();

        $this->game->gamestate->setPlayerNonMultiactive($selfId, 'nextCharacter');
        // $this->game->gamestate->unsetPrivateState($selfId);
    }
    public function actTradeItem(#[JsonParam] array $data): void
    {
        if (sizeof($data['selection']) != 2) {
            throw new BgaUserException($this->game->translate('You must select 2 items to trade'));
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
                $hasSelf = $hasSelf || $selfId == $trade['character']['player_id'];
                if ($selfId == $trade['character']['player_id']) {
                    if (!$trade1) {
                        $trade1 = $trade;
                    } else {
                        $trade2 = $trade;
                    }
                } else {
                    $trade2 = $trade;
                }
            } else {
                $trade2 = $trade;
                $sendToCamp = true;
            }
            if (array_key_exists('itemId', $trade)) {
                $hasItem = $hasItem || true;
            }
        });
        if (!$hasSelf) {
            throw new BgaUserException($this->game->translate('Select one of your character\'s items to trade'));
        }
        if (!$hasItem) {
            throw new BgaUserException($this->game->translate('Select one item to trade'));
        }
        if ($sendToCamp) {
            $this->game->log($trade1, $trade2);
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
                $result = $this->game->character->getItemValidations($itemId2, $trade1['character'], $itemId1);
                $hasOpenSlots = $result['hasOpenSlots'];
                $hasDuplicateTool = $result['hasDuplicateTool'];

                if (!$hasOpenSlots) {
                    throw new BgaUserException($this->game->translate('There is no open slot for that item type'));
                }
                if ($hasDuplicateTool) {
                    throw new BgaUserException($this->game->translate('Cannot have a duplicate tool'));
                }
                array_push($characterItems, $itemId2);
            }
            $campEquipment = array_filter($this->game->gameData->get('campEquipment'), function ($d) use ($itemId2) {
                return $d != $itemId2;
            });

            if ($itemId1) {
                array_push($campEquipment, $itemId1);
            }

            $this->game->log('setCharacterEquipment', $characterItems);
            $this->game->character->setCharacterEquipment($character['id'], $characterItems);

            $this->game->log('campEquipment', $campEquipment);
            $this->game->gameData->set('campEquipment', $campEquipment);

            $items = $this->game->gameData->getItems();
            $this->game->notify->all('updateGameData', clienttranslate('${character_name_1} traded an item with the camp'), [
                'character_name_1' => $this->game->getCharacterHTML($trade1['character']['id']),
                'gameData' => $this->game->getAllDatas(),
                'itemId1' => array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null,
                'itemId2' => array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null,
                'itemName1' => array_key_exists('itemId', $trade1) ? $items[$trade1['itemId']] : null,
                'itemName2' => array_key_exists('itemId', $trade2) ? $items[$trade2['itemId']] : null,
                'character1' => $trade1['character']['id'],
                'character2' => null,
            ]);
        } else {
            $this->game->log($trade1, $trade2);
            if ($trade1['character']['player_id'] != $trade2['character']['player_id']) {
                $this->game->gameData->set('tradeState' . $trade2['character']['player_id'], [
                    'trade1' => $trade1,
                    'trade2' => $trade2,
                ]);
                if (!$this->game->gamestate->isPlayerActive($trade2['character']['player_id'])) {
                    $this->game->gamestate->setPlayersMultiactive([$trade2['character']['player_id']], 'playerTurn', false);
                    $this->game->gamestate->initializePrivateState($trade2['character']['player_id']);
                    $this->game->gamestate->nextPrivateState($trade2['character']['player_id'], 'confirmTradePhase');
                } else {
                    $this->game->gamestate->nextPrivateState($trade2['character']['player_id'], 'confirmTradePhase');
                }
                $this->game->gamestate->nextPrivateState($trade1['character']['player_id'], 'waitTradePhase');
            } else {
                $responseData = [...$this->tradeProcess($trade1, $trade2), 'trade1' => $trade1, 'trade2' => $trade2];
                $this->completeTrade($responseData);
            }
        }
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
            $result = $this->game->character->getItemValidations($itemId1, $trade2['character'], $itemId2);
            $hasOpenSlots = $result['hasOpenSlots'];
            $hasDuplicateTool = $result['hasDuplicateTool'];
            if (!$hasOpenSlots) {
                throw new BgaUserException($this->game->translate('There is no open slot for that item type'));
            }
            if ($hasDuplicateTool) {
                throw new BgaUserException($this->game->translate('Cannot of a duplicate tool'));
            }
            array_push($characterItems2, $itemId1);
        }
        if ($itemId2) {
            $result = $this->game->character->getItemValidations($itemId2, $trade1['character'], $itemId1);
            $hasOpenSlots = $result['hasOpenSlots'];
            $hasDuplicateTool = $result['hasDuplicateTool'];

            if (!$hasOpenSlots) {
                throw new BgaUserException($this->game->translate('There is no open slot for that item type'));
            }
            if ($hasDuplicateTool) {
                throw new BgaUserException($this->game->translate('Cannot of a duplicate tool'));
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

            $items = $this->game->gameData->getItems();
            $this->game->notify->all('updateGameData', clienttranslate('${character_name_1} traded an item to ${character_name_2}'), [
                'character_name_1' => $this->game->getCharacterHTML($trade1['character']['id']),
                'character_name_2' => $this->game->getCharacterHTML($trade2['character']['id']),
                'gameData' => $this->game->getAllDatas(),
                'itemId1' => array_key_exists('itemId', $trade1) ? $trade1['itemId'] : null,
                'itemId2' => array_key_exists('itemId', $trade2) ? $trade2['itemId'] : null,
                'itemName1' => array_key_exists('itemId', $trade1) ? $items[$trade1['itemId']] : null,
                'itemName2' => array_key_exists('itemId', $trade2) ? $items[$trade2['itemId']] : null,
                'character1' => $trade1['character']['id'],
                'character2' => $trade2['character']['id'],
            ]);
        }
    }
    public function actTrade(#[JsonParam] array $data): void
    {
        // $this->game->character->addExtraTime();
        extract($data);
        $this->game->actions->validateCanRunAction('actTrade');
        $offeredSum = 0;
        foreach ($offered as $key => $value) {
            $offeredSum += $value;
        }
        $requestedSum = 0;
        foreach ($requested as $key => $value) {
            $requestedSum += $value;
        }
        if ($offeredSum != $this->game->getTradeRatio()) {
            throw new BgaUserException(
                str_replace(
                    '${amount}',
                    (string) $this->game->getTradeRatio(),
                    $this->game->translate('You must offer ${amount} resources')
                )
            );
        }
        if ($requestedSum != 1) {
            throw new BgaUserException($this->game->translate('You must request only one resource'));
        }
        $this->game->actions->spendActionCost('actTrade');
        $offeredStr = [];
        $requestedStr = [];
        foreach ($offered as $key => $value) {
            if ($value > 0) {
                $this->game->adjustResource($key, -$value);
                array_push($offeredStr, $this->game->data->tokens[$key]['name'] . "($value)");
            }
        }
        foreach ($requested as $key => $value) {
            if ($value > 0) {
                if (str_contains($key, '-cooked')) {
                    throw new BgaUserException($this->game->translate('You cannot trade for a cooked resource'));
                }
                $this->game->adjustResource($key, $value);
                array_push($requestedStr, $this->game->data->tokens[$key]['name'] . "($value)");
            }
        }
        // $this->game->hooks->onTrade($data);
        $this->game->notify->all('tokenUsed', clienttranslate('${character_name} traded ${offered} for ${requested}'), [
            'gameData' => $this->game->getAllDatas(),
            'offered' => join(', ', $offeredStr),
            'requested' => join(', ', $requestedStr),
        ]);
    }
    public function argTradePhaseActions($playerId)
    {
        $result = [
            'actions' => [
                'actTradeItem' => [
                    'type' => 'action',
                ],
            ],
        ];
        // $result['character_name'] = $this->game->getCharacterHTML();
        // $this->game->getAllCharacters($result);
        // $this->game->getAllPlayers($result);
        // $this->game->getDecks($result);
        // $this->game->getGameData($result);
        // $this->game->getItemData($result);
        return $result;
    }
    public function argConfirmTradePhase($playerId)
    {
        $result = [
            'actions' => [
                'actConfirmTradeItem' => [
                    'type' => 'action',
                ],
            ],
        ];
        return $result;
    }
    public function argWaitTradePhase($playerId)
    {
        $result = [];
        return $result;
    }
    public function argTradePhase()
    {
        $result = [
            'actions' => [],
        ];
        $result['character_name'] = $this->game->getCharacterHTML();
        $this->game->getAllCharacters($result);
        $this->game->getAllPlayers($result);
        $this->game->getDecks($result);
        $this->game->getGameData($result);
        $this->game->getItemData($result);
        return $result;
    }
    public function stTradePhase()
    {
        if (sizeof($this->game->getCraftedItems()) == 0) {
            $this->game->gamestate->nextState('nextCharacter');
        } else {
            $this->game->gamestate->setAllPlayersMultiactive();
            foreach ($this->game->gamestate->getActivePlayerList() as $key => $playerId) {
                $this->game->giveExtraTime((int) $playerId);
            }
            $this->game->gamestate->initializePrivateStateForAllActivePlayers();
        }
    }
    public function stTradePhaseWait()
    {
    }
}
