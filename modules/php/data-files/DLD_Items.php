<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
use BgaUserException;

if (!function_exists('getUsePerDay')) {
    function getUsePerDay(string $itemId, Game $game)
    {
        $dailyUseItems = $game->gameData->get('dailyUseItems');
        return array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] : 0;
    }
    function usePerDay(string $itemId, Game $game)
    {
        $dailyUseItems = $game->gameData->get('dailyUseItems');
        $dailyUseItems[$itemId] = array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] + 1 : 1;
        $game->gameData->set('dailyUseItems', $dailyUseItems);
    }
    function subtractPerDay(string $itemId, Game $game)
    {
        $dailyUseItems = $game->gameData->get('dailyUseItems');
        $dailyUseItems[$itemId] = array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] - 1 : 0;
        $game->gameData->set('dailyUseItems', $dailyUseItems);
        $game->markChanged('token');
        $game->markChanged('player');
    }
    function resetPerDay(Game $game)
    {
        $game->gameData->set('dailyUseItems', []);
        $game->markChanged('token');
        $game->markChanged('player');
    }
    function getUsePerForeverItems(Game $game)
    {
        return $game->gameData->get('foreverUseItems');
    }
    function getUsePerForever(string $itemId, Game $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        return array_key_exists($itemId, $foreverUseItems) ? $foreverUseItems[$itemId] : 0;
    }
    function usePerForever(string $itemId, Game $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        $foreverUseItems[$itemId] = array_key_exists($itemId, $foreverUseItems) ? $foreverUseItems[$itemId] + 1 : 1;
        $game->gameData->set('foreverUseItems', $foreverUseItems);
        $game->markChanged('token');
        $game->markChanged('player');
    }
    function subtractPerForever(string $itemId, Game $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        $foreverUseItems[$itemId] = min(0, array_key_exists($itemId, $foreverUseItems) ? $foreverUseItems[$itemId] - 1 : 0);
        $game->gameData->set('foreverUseItems', $foreverUseItems);
        $game->markChanged('token');
        $game->markChanged('player');
    }
    function clearUsePerForever(string $itemId, Game $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        $foreverUseItems[$itemId] = 0;
        $game->gameData->set('foreverUseItems', $foreverUseItems);
        $game->markChanged('token');
        $game->markChanged('player');
    }
    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field] ?? '0';
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
    function clearItemSkills(&$skills, $itemId)
    {
        array_walk($skills, function ($v, $k) use (&$skills, $itemId) {
            if (array_key_exists('itemId', $v) && $v['itemId'] == $itemId) {
                unset($skills[$k]);
            }
        });
    }
}
class DLD_ItemsData
{
    public function getData(): array
    {
        $data = [
            'bow-and-arrow' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 2,
                'name' => clienttranslate('Bow And Arrow'),
                'itemType' => 'weapon',
                'damage' => 3,
                'range' => 2,
                'cost' => [
                    'fiber' => 2,
                    'rock' => 2,
                    'wood' => 1,
                    'hide' => 1,
                ],
            ],
            'medical-hut' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Medical Hut'),
                'expansion' => 'hindrance',
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 3,
                    'bone' => 2,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Remove 2 Physical Hindrances'),
                        'state' => ['interrupt'],
                        'interruptState' => ['morningPhase'],
                        'perDay' => 1,
                        'onMorningAfter' => function (Game $game, $skill, &$data) {
                            $data['nextState'] = false;
                            $characters = array_values(
                                array_map(
                                    function ($d) {
                                        return ['physicalHindrance' => $d['physicalHindrance'], 'characterId' => $d['id']];
                                    },
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0 &&
                                            !in_array('hindrance_2_5', toId($d['physicalHindrance']));
                                    })
                                )
                            );

                            $game->selectionStates->initiateState(
                                'hindranceSelection',
                                ['id' => $skill['id'], 'characters' => $characters, 'button' => null],
                                $game->character->getFirstCharacter(),
                                false,
                                'tradePhase',
                                $skill['name']
                            );
                        },
                        'onHindranceSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('hindranceSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $characterCount = sizeof(
                                    array_unique(
                                        array_map(function ($d) {
                                            return $d['characterId'];
                                        }, $state['selections'])
                                    )
                                );
                                if ($characterCount > 1) {
                                    throw new BgaUserException(clienttranslate('Only 1 character\'s hindrances can be selected'));
                                }
                                $count = 0;
                                foreach ($state['characters'] as $char) {
                                    $cardIds = array_map(
                                        function ($d) {
                                            return $d['cardId'];
                                        },
                                        array_filter($data['selections'], function ($d) use ($char) {
                                            return $d['characterId'] == $char['characterId'];
                                        })
                                    );
                                    foreach ($char['physicalHindrance'] as $card) {
                                        if (in_array($card['id'], $cardIds)) {
                                            $count++;
                                            $game->character->removeHindrance($char['characterId'], $card);
                                        }
                                    }
                                }
                                if ($count > 2) {
                                    throw new BgaUserException(clienttranslate('Up to 2 hindrances can be removed'));
                                }
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getTurnCharacter(false);
                            return $char['isActive'] &&
                                sizeof(
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0 &&
                                            !in_array('hindrance_2_5', toId($d['physicalHindrance']));
                                    })
                                ) > 0;
                        },
                    ],
                ],
            ],
            'bone-club' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 2,
                'name' => clienttranslate('Bone Club'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 3,
                'cost' => [
                    'fiber' => 1,
                    'rock' => 1,
                    'wood' => 1,
                    'bone' => 2,
                ],
            ],
            'bone-scythe' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'name' => clienttranslate('Bone Scythe'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'bone' => 2,
                ],
                'onDraw' => function (Game $game, $item, &$data) {
                    $card = $data['card'];
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $card['deckType'] == 'resource' && $card['resourceType'] == 'fiber') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify(
                            'usedItem',
                            clienttranslate('${character_name} used ${item_name} and received ${count} ${resource_type}'),
                            [
                                'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                                'resource_type' => $card['resourceType'],
                                'count' => $resourceChange['changed'],
                                'i18n_suffix' =>
                                    $resourceChange['left'] == 0
                                        ? []
                                        : [
                                            'prefix' => ', ',
                                            'message' => clienttranslate('${left} could not be collected'),
                                            'args' => [
                                                'left' => $resourceChange['left'],
                                            ],
                                        ],
                            ]
                        );
                    }
                },
            ],
            'bag' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'name' => clienttranslate('Bag'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'hide' => 1,
                ],
                'onDraw' => function (Game $game, $item, &$data) {
                    $card = $data['card'];

                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $card['deckType'] == 'resource' && $card['resourceType'] == 'berry') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify(
                            'usedItem',
                            clienttranslate('${character_name} used ${item_name} and received ${count} ${resource_type}'),
                            [
                                'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                                'resource_type' => $card['resourceType'],
                                'count' => $resourceChange['changed'],
                                'i18n_suffix' =>
                                    $resourceChange['left'] == 0
                                        ? []
                                        : [
                                            'prefix' => ', ',
                                            'message' => clienttranslate('${left} could not be collected'),
                                            'args' => [
                                                'left' => $resourceChange['left'],
                                            ],
                                        ],
                            ]
                        );
                    }
                },
            ],
            'bone-armor' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'name' => clienttranslate('Bone Armor'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 1,
                    'bone' => 3,
                    'rock' => 1,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Ignore Damage'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'perDay' => 2,
                        'onGetActionCost' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            $interruptState = $game->actInterrupt->getState('actUseItem');
                            if (
                                !$char['isActive'] &&
                                $data['action'] == 'actUseItem' &&
                                $data['subAction'] == $skill['id'] &&
                                $interruptState &&
                                array_key_exists('data', $interruptState) &&
                                $interruptState['data']['skillId'] == $skill['id']
                            ) {
                                $data['perDay'] = 2 - getUsePerDay($char['id'] . $skill['id'], $game);
                            }
                        },
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            $damageTaken = $game->encounter->countDamageTaken($data);
                            $char = $game->character->getCharacterData($skill['characterId']);

                            if ($char['isActive'] && $damageTaken > 0 && !$data['noEscape']) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                usePerDay($char['id'] . $skill['id'], $game);
                                $data['data']['willTakeDamage'] = 0;
                                $game->eventLog(clienttranslate('${character_name} used ${item_name} to block the damage'), [
                                    'item_name' => notifyTextButton([
                                        'name' => $game->data->getItems()[$game->gameData->getCreatedItems()[$skill['itemId']]]['name'],
                                        'dataId' => $skill['itemId'],
                                        'dataType' => 'item',
                                    ]),
                                ]);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 2;
                        },
                    ],
                ],
            ],
            'camp-walls' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Camp Walls'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'bone' => 2,
                ],
                'onNightDrawCard' => function (Game $game, $item, &$data) {
                    if (array_key_exists('eventType', $data['state']['card']) && $data['state']['card']['eventType'] == 'rival-tribe') {
                        $game->eventLog(clienttranslate('${character_name} Camp walls protect against the rival tribe'));
                        $data['onUse'] = false;
                    }
                },
            ],
            'fire' => [
                'type' => 'game-piece',
                'name' => 'Fire',
            ],
            'hide-armor' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'name' => clienttranslate('Hide Armor'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 1,
                    'hide' => 2,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Ignore Damage'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'perDay' => 1,
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            $damageTaken = $game->encounter->countDamageTaken($data);
                            $char = $game->character->getCharacterData($skill['characterId']);

                            if ($char['isActive'] && $damageTaken > 0 && !$data['noEscape']) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                usePerDay($char['id'] . $skill['id'], $game);
                                $data['data']['willTakeDamage'] = 0;
                                $game->eventLog(clienttranslate('${character_name} used ${item_name} to block the damage'), [
                                    'item_name' => notifyTextButton([
                                        'name' => $game->data->getItems()[$game->gameData->getCreatedItems()[$skill['itemId']]]['name'],
                                        'dataId' => $skill['itemId'],
                                        'dataType' => 'item',
                                    ]),
                                ]);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                        },
                    ],
                ],
            ],
            'knowledge-hut' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Knowledge Hut'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 2,
                    'bone' => 2,
                ],
                'onUse' => function (Game $game, $item, $itemId) {
                    usePerDay($item, $game);
                },
                'requires' => function (Game $game, $item) {
                    return getUsePerDay($item['name'], $game) < 1;
                },
                'onInvestigateFire' => function (Game $game, $item, &$data) {
                    $char = $game->character->getTurnCharacter();
                    if (getUsePerDay($item['name'] . $char['id'] . 'investigateFire', $game) < 1) {
                        usePerDay($item['name'] . $char['id'] . 'investigateFire', $game);

                        if ($game->adjustResource('fkp', 1)['changed'] > 0) {
                            $game->notify('usedItem', clienttranslate('The ${item_name} grants an extra FKP ${buttons}'), [
                                'item_name' => $item['name'],
                                'buttons' => notifyButtons([['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']]),
                            ]);
                        }
                    }
                },
            ],
            'skull' => [
                'type' => 'game-piece',
                'name' => 'Skull',
            ],
            'hatchet' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 2,
                'name' => clienttranslate('Hatchet'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'wood' => 1,
                    'rock' => 2,
                ],
                'onDraw' => function (Game $game, $item, &$data) {
                    $card = $data['card'];
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $card['deckType'] == 'resource' && $card['resourceType'] == 'wood') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify(
                            'usedItem',
                            clienttranslate('${character_name} used ${item_name} and received ${count} ${resource_type}'),
                            [
                                'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                                'resource_type' => $card['resourceType'],
                                'count' => $resourceChange['changed'],
                                'i18n_suffix' =>
                                    $resourceChange['left'] == 0
                                        ? []
                                        : [
                                            'prefix' => ', ',
                                            'message' => clienttranslate('${left} could not be collected'),
                                            'args' => [
                                                'left' => $resourceChange['left'],
                                            ],
                                        ],
                            ]
                        );
                    }
                },
            ],
            'club' => [
                'type' => 'item',
                'craftingLevel' => 0,
                'count' => 2,
                'name' => clienttranslate('Club'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 1,
                'cost' => [
                    'wood' => 1,
                ],
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    if ($data['character_name'] == $item['character_name']) {
                        $data['maxStamina'] = clamp($data['maxStamina'] - 1, 0, 10);
                    }
                },
            ],
            'cooking-hut' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Cooking Hut'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 2,
                    'bone' => 2,
                ],
                'onEat' => function (Game $game, $item, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 2;
                    }
                },
                'onGetEatData' => function (Game $game, $item, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 2;
                    }
                },
            ],
            'carving-knife' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'name' => clienttranslate('Carving Knife'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'rock' => 2,
                    'bone' => 1,
                ],
                'onDraw' => function (Game $game, $item, &$data) {
                    $card = $data['card'];
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $card['deckType'] == 'resource' && $card['resourceType'] == 'meat') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify(
                            'usedItem',
                            clienttranslate('${character_name} used ${item_name} and received ${count} ${resource_type}'),
                            [
                                'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                                'resource_type' => $card['resourceType'],
                                'count' => $resourceChange['changed'],
                                'i18n_suffix' =>
                                    $resourceChange['left'] == 0
                                        ? []
                                        : [
                                            'prefix' => ', ',
                                            'message' => clienttranslate('${left} could not be collected'),
                                            'args' => [
                                                'left' => $resourceChange['left'],
                                            ],
                                        ],
                            ]
                        );
                    }
                },
            ],
            'item-back' => [
                'type' => 'item',
                'type' => 'back',
                'name' => 'Item Back',
            ],
            'sling-shot' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'name' => clienttranslate('Sling Shot'),
                'itemType' => 'weapon',
                'damage' => 3,
                'range' => 2,
                'useCost' => [
                    'rock' => 1,
                ],
                'cost' => [
                    'fiber' => 1,
                    'hide' => 1,
                    'wood' => 1,
                ],
            ],
            'pick-axe' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'name' => clienttranslate('Pick Axe'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'wood' => 1,
                    'rock' => 1,
                ],
                'onDraw' => function (Game $game, $item, &$data) {
                    $card = $data['card'];
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $card['deckType'] == 'resource' && $card['resourceType'] == 'rock') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify(
                            'usedItem',
                            clienttranslate('${character_name} used ${item_name} and received ${count} ${resource_type}'),
                            [
                                'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                                'resource_type' => $card['resourceType'],
                                'count' => $resourceChange['changed'],
                                'i18n_suffix' =>
                                    $resourceChange['left'] == 0
                                        ? []
                                        : [
                                            'prefix' => ', ',
                                            'message' => clienttranslate('${left} could not be collected'),
                                            'args' => [
                                                'left' => $resourceChange['left'],
                                            ],
                                        ],
                            ]
                        );
                    }
                },
            ],
            'planning-hut' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Planning Hut'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 2,
                    'bone' => 2,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Draw 2 Pick 1 (Planning Hut)'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'perDay' => 2,
                        'onGetActionCost' => function (Game $game, $skill, &$data) {
                            if ($data['action'] == 'actUseItem' && $data['subAction'] == $skill['id']) {
                                $data['perDay'] = 2 - getUsePerDay($skill['id'], $game);
                            }
                        },
                        'onDraw' => function (Game $game, $skill, &$data) {
                            $deck = $data['deck'];
                            if (
                                in_array($deck, ['explore', 'forage', 'gather', 'harvest', 'hunt']) &&
                                getUsePerDay($skill['id'], $game) < 2 &&
                                !$game->actInterrupt->getState('actDraw')
                            ) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onUseSkill' => function (Game $game, $skill, &$data) {
                            if ($data['skillId'] == $skill['id']) {
                                $existingData = $game->actInterrupt->getState('actDraw');
                                if (array_key_exists('data', $existingData)) {
                                    $deck = $existingData['data']['deck'];
                                    $card1 = $existingData['data']['card'];
                                    $card2 = $game->decks->pickCard($deck);
                                    $game->incStat(1, 'cards_drawn', $game->character->getSubmittingCharacter()['playerId']);
                                    $data['interrupt'] = true;
                                    $game->selectionStates->initiateState(
                                        'cardSelection',
                                        [
                                            'cards' => [$card1, $card2],
                                            'id' => $skill['id'],
                                        ],
                                        $game->character->getTurnCharacterId(),
                                        false
                                    );
                                }
                            }
                        },
                        'onCardSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('cardSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                usePerDay($skill['id'], $game);
                                $discardCard = array_values(
                                    array_filter($state['cards'], function ($card) use ($data) {
                                        return $card['id'] != $data['cardId'];
                                    })
                                );
                                if (sizeof($discardCard) > 0) {
                                    $discardCard = $discardCard[0];
                                    $game->cardDrawEvent($discardCard, $discardCard['deck']);
                                }

                                $drawState = $game->actInterrupt->getState('actDraw');
                                $drawState['data']['card'] = $game->decks->getCard($data['cardId']);
                                $game->actInterrupt->setState('actDraw', $drawState);
                                $game->actInterrupt->actInterrupt($skill['id']);
                                $data['nextState'] = false;
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && getUsePerDay($skill['id'], $game) < 2;
                        },
                    ],
                ],
            ],
            'spear' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'name' => clienttranslate('Spear'),
                'itemType' => 'weapon',
                'range' => 2,
                'damage' => 2,
                'cost' => [
                    'fiber' => 1,
                    'rock' => 2,
                    'wood' => 1,
                ],
            ],
            'sharp-stick' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'name' => clienttranslate('Sharp Stick'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 1,
                'cost' => [
                    'wood' => 1,
                    'rock' => 1,
                ],
            ],
            'shelter' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'name' => clienttranslate('Shelter'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 2,
                    'bone' => 2,
                ],
                'onMorning' => function (Game $game, $nightCard, &$data) {
                    $data['health'] = 0;
                    $game->eventLog(clienttranslate('No damage taken in the morning'));
                },
            ],
            'rock-knife' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'name' => clienttranslate('Rock Knife'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 2,
                'cost' => [
                    'fiber' => 1,
                    'rock' => 2,
                ],
            ],
            'stone-hammer' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Stone Hammer'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'rock' => 2,
                    'wood' => 1,
                ],
                'onGetActionCost' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $data['action'] == 'actCraft') {
                        $data['stamina'] = max($data['stamina'] - 2, 0);
                    }
                },
            ],
            'mortar-and-pestle' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Mortar And Pestle'),
                'itemType' => 'tool',
                'cost' => [
                    'rock' => 3,
                ],
                'onGetActionCost' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $data['action'] == 'actUseHerb') {
                        $data['stamina'] = min($data['stamina'], 0);
                    }
                },
            ],
            'bandage' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Bandage'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 1,
                    'hide' => 1,
                    'herb' => 1,
                ],
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    if ($item['characterId'] == $data['id']) {
                        $data['maxHealth'] = clamp($data['maxHealth'] + 1, 0, 10);
                    }
                },
                'onIncapacitation' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId'], true);
                    if ($char['isActive']) {
                        $game->eventLog(clienttranslate('${character_name} used their bandage to revive'));
                        $game->destroyItem($item['itemId']);
                    }
                },
            ],
            'skull-shield' => [
                'type' => 'item',
                'craftingLevel' => 4,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Skull Shield'),
                'itemType' => 'tool',
                // 'character' => 'Faye',
                'cost' => [],
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    if ($item['characterId'] == $data['id']) {
                        $data['maxHealth'] = clamp($data['maxHealth'] + 1, 0, 10);
                    }
                },
                'onEncounterPre' => function (Game $game, $item, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($item['characterId']);
                    if (
                        $char['isActive'] &&
                        $damageTaken > 0 &&
                        !$data['noEscape'] &&
                        $game->rollFireDie($item['name'], $item['characterId']) == 1
                    ) {
                        $data['willTakeDamage'] -= 1;
                    }
                },
            ],
            'cooking-pot' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Cooking Pot'),
                'itemType' => 'tool',
                'cost' => [
                    'fiber' => 2,
                    'rock' => 2,
                    'bone' => 2,
                ],
                'onGetActionCost' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $data['action'] == 'actCook' && getUsePerDay($char['id'] . $item['itemId'], $game) % 2 == 1) {
                        $data['stamina'] = min($data['stamina'], 0);
                    }
                },
                'onCook' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && getUsePerDay($char['id'] . $item['itemId'], $game) % 2 == 0) {
                        $game->eventLog(clienttranslate('${character_name} another cook action can be used for free'));
                    }
                    usePerDay($char['id'] . $item['itemId'], $game);
                },
            ],
            'bone-claws' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Bone Claws'),
                'itemType' => 'tool',
                'cost' => [
                    'rock' => 2,
                    'bone' => 2,
                ],
                'onGetActionCost' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive'] && $data['action'] == 'actExplore') {
                        $data['stamina'] = max($data['stamina'] - 2, 0);
                    }
                },
            ],
            'bone-flute' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Bone Flute'),
                'itemType' => 'tool',
                'cost' => [
                    'hide' => 1,
                    'bone' => 2,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Soothe'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'perDay' => 1,
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            $damageTaken = $game->encounter->countDamageTaken($data);
                            $char = $game->character->getCharacterData($skill['characterId']);

                            if ($char['isActive'] && $damageTaken > 0 && !$data['noEscape']) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                usePerDay($char['id'] . $skill['id'], $game);
                                $data['data']['soothe'] = true;
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                        },
                    ],
                ],
            ],
            'stock-hut' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Stock Hut'),
                'itemType' => 'building',
                'global' => true,
                'cost' => [
                    'fiber' => 3,
                    'rock' => 3,
                    'hide' => 2,
                    'bone' => 2,
                ],
                'onGetTradeRatio' => function (Game $game, $item, &$data) {
                    $data['ratio'] = 2;
                },
            ],
            'whip' => [
                'type' => 'item',
                'craftingLevel' => 2,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Whip'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 2,
                'cost' => [
                    'fiber' => 3,
                    'hide' => 2,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'state' => ['resolveEncounter'],
                        'name' => clienttranslate('Give 1 Health'),
                        'onEncounter' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive'] && in_array($skill['parentId'], $data['itemIdUsed'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                                $characterIds = toId(
                                    array_filter($game->character->getAllCharacterData(), function ($character) {
                                        return !$character['incapacitated'];
                                    })
                                );
                                $game->selectionStates->initiateState(
                                    'characterSelection',
                                    [
                                        'selectableCharacters' => array_values($characterIds),
                                        'id' => $skill['id'],
                                        'cardId' => $data['cardId'],
                                    ],
                                    $skill['characterId'],
                                    false,
                                    'resolveEncounter',
                                    true
                                );
                                $data['interrupt'] = true;
                            }
                        },
                        'onCharacterSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('characterSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $cardId = $state['cardId'];
                                $change = $game->character->adjustHealth($data['characterId'], 1);
                                $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                    'count' => $change,
                                    'character_resource' => clienttranslate('Health'),
                                    'character_name' => $game->getCharacterHTML($data['characterId']),
                                ]);
                                $game->decks->shuffleInCard($game->data->getDecks()[$cardId]['deck'], $cardId);
                            }
                        },
                    ],
                ],
            ],
            'fire-stick' => [
                'type' => 'item',
                'craftingLevel' => 4,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Fire Stick'),
                'itemType' => 'weapon',
                'range' => 1,
                'damage' => 0,
                // 'character' => 'Rex',
                'cost' => [],
                'onEncounterPre' => function (Game $game, $item, &$data) {
                    $char = $game->character->getCharacterData($item['characterId']);
                    if ($char['isActive']) {
                        $data['characterDamage'] += $game->rollFireDie($item['name'], $item['characterId']);
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Increase Attack (1 fkp)'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->adjustResource('fkp', -1);
                                $data['data']['characterDamage'] += 1;
                                clearItemSkills($data['skills'], $skill['itemId']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && $game->gameData->getResource('fkp') >= 1;
                        },
                    ],
                    'skill2' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Increase Attack (2 fkp)'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->adjustResource('fkp', -2);
                                $data['data']['characterDamage'] += 2;
                                clearItemSkills($data['skills'], $skill['itemId']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && $game->gameData->getResource('fkp') >= 2;
                        },
                    ],
                    'skill3' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Increase Attack (3 fkp)'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->adjustResource('fkp', -3);
                                $data['data']['characterDamage'] += 3;
                                clearItemSkills($data['skills'], $skill['itemId']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && $game->gameData->getResource('fkp') >= 3;
                        },
                    ],
                ],
            ],
            'rock-weapon' => [
                'type' => 'item',
                'craftingLevel' => 0,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Rock'),
                'itemType' => 'weapon',
                'range' => 2,
                'damage' => 1,
                'cost' => [
                    'rock' => 1,
                ],
                'useCost' => [], //Makes this optional
                'onUse' => function (Game $game, $item, $itemId) {
                    $game->character->unequipEquipment($item['characterId'], [$itemId]);
                    $game->notify('usedItem', clienttranslate('${character_name} used ${item_name} and lost their ${item_name}'), [
                        'item_name' => notifyTextButton(['name' => $item['name'], 'dataId' => $item['id'], 'dataType' => 'item']),
                    ]);
                },
            ],
            'bola' => [
                'type' => 'item',
                'craftingLevel' => 1,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Bola'),
                'itemType' => 'weapon',
                'range' => 2,
                'damage' => 2,
                'cost' => [
                    'fiber' => 2,
                    'rock' => 2,
                ],
                'useCost' => [], //Makes this optional
                // 'onEncounter' => function (Game $game, $item, &$data) {
                //     $char = $game->character->getCharacterData($item['characterId']);
                //     if ($char['isActive'] && ($data['soothe'] || $data['escape']) && in_array($item['itemId'], $data['itemIds'])) {
                //         $data['willTakeDamage'] -= 1;
                //     }
                // },
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Keep Bola'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'stamina' => 2,
                        'cancellable' => false,
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                clearItemSkills($data['skills'], $skill['itemId']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                    'skill2' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Discard Bola'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'cancellable' => false,
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->character->unequipEquipment($skill['characterId'], [$skill['itemId']]);
                                clearItemSkills($data['skills'], $skill['itemId']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                ],
            ],
            'boomerang' => [
                'type' => 'item',
                'craftingLevel' => 3,
                'count' => 2,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Boomerang'),
                'itemType' => 'weapon',
                'range' => 2,
                'damage' => 2,
                'cost' => [
                    'rock' => 2,
                    'hide' => 2,
                    'wood' => 1,
                ],
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Reduce Life By 1'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'perDay' => 1,
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            $game->actInterrupt->addSkillInterrupt($skill);
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                usePerDay($char['id'] . $skill['id'], $game);
                                $data['data']['encounterHealth'] -= 1;
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                        },
                    ],
                ],
            ],
            'gem-y-necklace' => [
                'type' => 'item',
                'craftingLevel' => 4,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Yellow Necklace'),
                'itemType' => 'necklace',
                'cost' => [
                    'gem-y' => 1,
                    'fiber' => 1,
                ],
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    if ($data['character_name'] == $item['character_name']) {
                        $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
                    }
                },
                'onCraftAfter' => function (Game $game, $unlock, &$data) {
                    $game->gameData->destroyResource('gem-y');
                },
            ],
            'gem-b-necklace' => [
                'type' => 'item',
                'craftingLevel' => 4,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Blue Necklace'),
                'itemType' => 'necklace',
                'cost' => [
                    'gem-b' => 1,
                    'fiber' => 1,
                ],
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    if ($data['character_name'] == $item['character_name']) {
                        $data['maxHealth'] = clamp($data['maxHealth'] + 1, 0, 10);
                    }
                },
                'onCraftAfter' => function (Game $game, $unlock, &$data) {
                    $game->gameData->destroyResource('gem-b');
                },
            ],
            'gem-p-necklace' => [
                'type' => 'item',
                'craftingLevel' => 4,
                'count' => 1,
                'expansion' => 'hindrance',
                'name' => clienttranslate('Purple Necklace'),
                'itemType' => 'necklace',
                'cost' => [
                    'gem-p' => 1,
                    'fiber' => 1,
                ],
                'onCraftAfter' => function (Game $game, $unlock, &$data) {
                    $game->gameData->destroyResource('gem-p');
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'item-skill',
                        'name' => clienttranslate('Re-Roll'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'perDay' => 1,
                        'onInvestigateFire' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if (getUsePerDay($char['id'] . 'gem-p-necklace', $game) < 1) {
                                // If kara is not the character, and the roll is not the max
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                $game->eventLog(clienttranslate('${character_name} is re-rolling ${active_character_name}\'s fire die'), [
                                    ...$char,
                                    'active_character_name' => $game->character->getTurnCharacter()['character_name'],
                                ]);
                                $data['data']['roll'] = $game->rollFireDie($skill['parentName'], $char['character_name']);
                                usePerDay($char['id'] . 'gem-p-necklace', $game);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return getUsePerDay($char['id'] . 'gem-p-necklace', $game) < 1;
                        },
                    ],
                ],
            ],
        ];
        array_walk($data, function (&$item) {
            $item['totalCost'] = array_sum(array_values(array_key_exists('cost', $item) ? $item['cost'] : []));
        });
        $itemsData = array_orderby($data, 'craftingLevel', SORT_ASC, 'itemType', SORT_DESC, 'totalCost', SORT_ASC);
        return $itemsData;
    }
}
