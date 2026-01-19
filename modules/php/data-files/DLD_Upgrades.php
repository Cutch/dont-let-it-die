<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
use BgaUserException;
class DLD_UpgradesData
{
    public function getData(): array
    {
        return [
            '1-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Charcoal Writing'),
                'unlockCost' => 6,
                'onGetUnlockCost' => function (Game $game, $unlock, &$data) {
                    $data['unlockCost'] -= 1;
                },
            ],
            '1-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Tracking'),
                'unlockCost' => 5,
                'onEncounterPre' => function (Game $game, $unlock, &$data) {
                    $data['willReceiveMeat'] += 1;
                },
            ],
            '10-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Flint'),
                'unlockCost' => 7,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Understand Flint'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'perDay' => 1,
                        'global' => true,
                        'onInvestigateFire' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getTurnCharacterId();
                            if (getUsePerDay($char . 'flint', $game) < 1 && $game->gameData->getResource('rock') > 0) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                // $interruptState = $game->actInterrupt->getState('actInvestigateFire');
                                $game->adjustResource('rock', -1);
                                $char = $game->character->getTurnCharacterId();
                                $data['data']['roll'] = $data['data']['roll'] + $game->rollFireDie($skill['parentName'], $char) - 1;
                                usePerDay($char . 'flint', $game);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getTurnCharacterId();
                            return getUsePerDay($char . 'flint', $game) < 1;
                        },
                    ],
                ],
            ],
            '10-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Fire Stoking'),
                'unlockCost' => 8,
                'onAddFireWood' => function (Game $game, $unlock, &$data) {
                    $char = $game->character->getTurnCharacterId();
                    if ($game->gamestate->state(true, false, true)['name'] === 'dinnerPhase') {
                        $dinnerCharacters = $game->character->getAllCharacterDataForPlayer($game->getCurrentPlayerId());
                        foreach ($dinnerCharacters as $checkChar) {
                            if (getUsePerDay($checkChar['id'] . $unlock['id'], $game) == 0) {
                                $char = $checkChar['id'];
                                break;
                            }
                        }
                    }
                    if (getUsePerDay($char . $unlock['id'], $game) < 1) {
                        usePerDay($char . $unlock['id'], $game);
                        $count = $game->adjustResource('fkp', 2)['changed'];
                        if ($count > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $unlock['name'],
                                'count' => $count,
                                'resource_type' => 'fkp',
                            ]);
                        }
                    }
                },
            ],
            '11-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Haggle'),
                'unlockCost' => 4,
                'onGetTradeRatio' => function (Game $game, $unlock, &$data) {
                    $char = $game->character->getTurnCharacterId();
                    if (getUsePerDay($char . $unlock['id'], $game) < 1) {
                        if (!$data['checkOnly']) {
                            usePerDay($char . $unlock['id'], $game);
                        }
                        $data['ratio'] = 2;
                    }
                },
            ],
            '11-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Trade Routes'),
                'unlockCost' => 5,
                'onGetActionCost' => function (Game $game, $unlock, &$data) {
                    if ($data['action'] == 'actTrade') {
                        $data['stamina'] = min($data['stamina'], 0);
                    }
                },
            ],
            '12-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Planning'),
                'unlockCost' => 6,
                'onEndTurn' => function (Game $game, $unlock, &$data) {
                    if ($game->gameData->get('lastAction') == 'actInvestigateFire') {
                        if ($game->adjustResource('fkp', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $unlock['name'],
                                'count' => 1,
                                'resource_type' => 'fkp',
                            ]);
                        }
                    }
                },
            ],
            '12-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Focus'),
                'unlockCost' => 6,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Focus'),
                        'state' => ['playerTurn'],
                        'perDay' => 1,
                        'global' => true,
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getTurnCharacterId();
                            return getUsePerDay($char . '12-B', $game) < 1;
                        },
                    ],
                ],
                'onInvestigateFirePost' => function (Game $game, $obj, &$data) {
                    $char = $game->character->getTurnCharacterId();
                    if (getUsePerDay($char . '12-B', $game) < 1 && array_key_exists('focus', $data) && $data['focus']) {
                        usePerDay($char . '12-B', $game);
                        $data['roll'] += $data['originalRoll'] + (array_key_exists('originalRoll2', $data) ? $data['originalRoll2'] : 0);
                    }
                },
            ],
            '13-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Bone Efficiency'),
                'unlockCost' => 4,
                'onResolveDrawPost' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'bone') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                            'action_name' => $obj['name'],
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
                        ]);
                    }
                },
            ],
            '13-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Hide Efficiency'),
                'unlockCost' => 4,
                'onResolveDrawPost' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'hide') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                            'action_name' => $obj['name'],
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
                        ]);
                    }
                },
            ],
            '14-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Medicinal Herb Efficiency'),
                'unlockCost' => 5,
                'onResolveDrawPost' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'herb') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                            'action_name' => $obj['name'],
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
                        ]);
                    }
                },
            ],
            '14-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Dino Egg Efficiency'),
                'unlockCost' => 5,
                'onResolveDrawPost' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'dino-egg') {
                        $resourceChange = $game->adjustResource($card['resourceType'], 1);
                        $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                            'action_name' => $obj['name'],
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
                        ]);
                    }
                },
            ],
            '15-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Jewelry'),
                'unlockCost' => 5,
                'onGetActionSelectable' => function (Game $game, $skill, &$data) {
                    if ($data['action'] == 'actCraft') {
                        if (getUsePerDay($game->character->getSubmittingCharacterId() . 'craftjewlery', $game) == 0) {
                            array_push(
                                $data['selectable'],
                                $game->data->getItems()['gem-y-necklace'],
                                $game->data->getItems()['gem-b-necklace'],
                                $game->data->getItems()['gem-p-necklace']
                            );
                        }
                    }
                },
            ],
            '15-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Recycling'),
                'unlockCost' => 4,
                'disabled' => true,
                // TODO: Decrease item crafting cost after destroying it
            ],
            '16-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Tinder'),
                'unlockCost' => 6,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Tinder'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn', 'morningPhase'],
                        'global' => true,
                        'onMorning' => function (Game $game, $skill, &$data) {
                            $game->actInterrupt->addSkillInterrupt($skill);
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->gameData->destroyResource('fiber');
                                $data['data']['woodNeeded'] -= 1;
                            }
                        },
                    ],
                ],
            ],
            '16-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Fire Watch'),
                'unlockCost' => 8,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Fire Watch'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn', 'morningPhase'],
                        'global' => true,
                        'onMorning' => function (Game $game, $skill, &$data) {
                            $game->actInterrupt->addSkillInterrupt($skill);
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['data']['health'] += 1;
                                $data['data']['woodNeeded'] -= 1;
                            }
                        },
                    ],
                ],
            ],
            '2-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Smoke Cover'),
                'unlockCost' => 4,
                'onNightDrawCard' => function (Game $game, $unlock, &$data) {
                    if (array_key_exists('eventType', $data['state']['card']) && $data['state']['card']['eventType'] == 'rival-tribe') {
                        $roll = min($game->rollFireDie($unlock['name']), $game->rollFireDie($unlock['name']));
                        rivalTribe($game, $data['state']['card'], $roll);

                        $data['onUse'] = false;
                    }
                },
            ],
            '2-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Revenge'),
                'unlockCost' => 8,
                'onNightDrawCard' => function (Game $game, $unlock, &$data) {
                    if (array_key_exists('eventType', $data['state']['card']) && $data['state']['card']['eventType'] == 'rival-tribe') {
                        $resourceType = $data['state']['card']['resourceType'];
                        $roll = $game->rollFireDie($unlock['name']);
                        if ($resourceType == 'gem') {
                            $left = $game->adjustResource('gem-y', $roll)['left'];
                            $left = $game->adjustResource('gem-p', $left)['left'];
                            $left = $game->adjustResource('gem-b', $left)['left'];
                            if ($roll != $left) {
                                $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                    'count' => $roll != $left,
                                    'resource_type' => 'gem-y',
                                ]);
                            }
                        } else {
                            $changed = $game->adjustResource($resourceType, $roll)['changed'];
                            if ($changed > 0) {
                                $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                    'count' => $changed,
                                    'resource_type' => 'meat-cooked',
                                ]);
                            }
                        }

                        $data['onUse'] = false;
                    }
                },
            ],
            '3-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Hot Rock Sauna'),
                'unlockCost' => 6,
                'onGetCharacterData' => function (Game $game, $unlock, &$data) {
                    $data['maxHealth'] = clamp($data['maxHealth'] + 3, 0, 10);
                    $data['maxStamina'] = clamp($data['maxStamina'] - 1, 0, 10);
                },
            ],
            '3-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Hot Rock Walking'),
                'unlockCost' => 7,
                'onGetCharacterData' => function (Game $game, $unlock, &$data) {
                    $data['maxHealth'] = clamp($data['maxHealth'] - 1, 0, 10);
                    $data['maxStamina'] = clamp($data['maxStamina'] + 2, 0, 10);
                },
            ],
            '4-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Smoked Food'),
                'unlockCost' => 4,
                'disabled' => true,
                'onAdjustHealth' => function (Game $game, $unlock, &$data) {
                    if ($data['change']) {
                        $aboveMax = $data['currentHealth'] + $data['change'] - $data['maxHealth'];
                        if ($aboveMax > 0) {
                            $currentCharacter = $game->character->getTurnCharacterId();
                            $characterIds = toId(
                                array_filter($game->character->getAllCharacterData(), function ($character) use ($currentCharacter) {
                                    return !$character['incapacitated'] && $character['id'] != $currentCharacter;
                                })
                            );
                            if (sizeof($characterIds) > 0) {
                                $game->selectionStates->initiateState(
                                    'characterSelection',
                                    [
                                        'selectableCharacters' => array_values($characterIds),
                                        'id' => $unlock['id'],
                                        'aboveMax' => $aboveMax,
                                    ],
                                    $currentCharacter,
                                    false
                                );
                            }
                            // TODO: we dont know what this will interrupt
                            // ideally we should track the next state and then redirect back there after selection
                        }
                    }
                },
                'onCharacterSelection' => function (Game $game, $unlock, &$data) {
                    $state = $game->selectionStates->getState('characterSelection');
                    if ($state && $state['id'] == $unlock['id']) {
                        $aboveMax = $state['aboveMax'];
                        $change = $game->character->adjustHealth($data['characterId'], $aboveMax);
                        $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                            'count' => $change,
                            'character_resource' => clienttranslate('Health'),
                        ]);
                    }
                },
            ],
            '4-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('First Aid'),
                'unlockCost' => 6,
                'onGetActionSelectable' => function (Game $game, $char, &$data) {
                    if ($data['action'] == 'actRevive') {
                        array_walk($data['selectable'], function (&$d) {
                            if ($d['id'] == 'meat-cooked') {
                                $d['actRevive']['count'] = 1;
                            }
                        });
                    }
                },
            ],
            '5-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Controlled Burn'),
                'unlockCost' => 6,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Controlled Burn'),
                        'state' => ['playerTurn'],
                        'perDay' => 1,
                        'global' => true,
                        'cost' => ['fkp' => 2],
                        'onUse' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getTurnCharacterId();
                            if (getUsePerDay($char . $skill['id'], $game) < 1) {
                                $game->adjustResource('fkp', -2);
                                usePerDay($char . $skill['id'], $game);
                                $game->gameData->destroyResource('fiber', 1);
                                $cooked = $game->adjustResource('meat-cooked', 3);
                                // Take from the raw stack if there is extra
                                $raw = $game->adjustResource('meat', -$cooked['left']);
                                $cooked2 = $game->adjustResource(
                                    'meat-cooked',
                                    -$raw['changed'],
                                    $game->gameData->getResource('meat-cooked')
                                );
                                if ($cooked['changed'] + $cooked2['changed'] > 0) {
                                    $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                        'count' => $cooked['changed'] + $cooked2['changed'],
                                        'resource_type' => 'meat-cooked',
                                    ]);
                                }
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getTurnCharacterId();
                            return $game->gameData->getResource('fkp') >= 2 && getUsePerDay($char . $skill['id'], $game) < 1;
                        },
                    ],
                ],
            ],
            '5-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Map Making'),
                'unlockCost' => 6,
                // TODO shuffle one card from any discard
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Map Making'),
                        'state' => ['playerTurn'],
                        'stamina' => 2,
                        'global' => true,
                        'onUse' => function (Game $game, $skill, &$data) {
                            return ['notify' => false, 'spendActionCost' => false];
                        },
                        'onUseSkill' => function (Game $game, $skill, &$data) {
                            if ($data['skillId'] == $skill['id']) {
                                $decksDiscards = $game->decks->listDeckDiscards(
                                    array_intersect(['explore', 'gather', 'forage', 'harvest', 'hunt'], $game->decks->getAllDeckNames())
                                );
                                $data['interrupt'] = true;
                                $game->selectionStates->initiateState(
                                    'cardSelection',
                                    [
                                        'cards' => $decksDiscards,
                                        'id' => $skill['id'],
                                    ],
                                    $game->character->getTurnCharacterId(),
                                    true
                                );
                            }
                        },
                        'onCardSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('cardSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $game->decks->shuffleInCard($game->data->getDecks()[$data['cardId']]['deck'], $data['cardId']);
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $decksDiscards = $game->decks->listDeckDiscards(
                                array_intersect(['explore', 'gather', 'forage', 'harvest', 'hunt'], $game->decks->getAllDeckNames())
                            );
                            return sizeof($decksDiscards) > 0;
                        },
                    ],
                ],
            ],
            '6-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Berry Farming'),
                'unlockCost' => 5,
                'onUse' => function (Game $game, $unlock) {
                    $game->gameData->destroyResource('berry', 1);
                },
                'onMorningAfter' => function (Game $game, $unlock) {
                    $count = $game->adjustResource('berry', 1)['changed'];
                    if ($count > 0) {
                        $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                            'count' => 1,
                            'resource_type' => 'berry',
                        ]);
                    }
                },
            ],
            '6-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Meditation'),
                'unlockCost' => 5,
                'onUse' => function (Game $game, $unlock) {
                    $game->character->adjustAllHealth(20);
                    $game->notify('tree', clienttranslate('Everyone gained ${count} ${character_resource}'), [
                        'count' => clienttranslate('full'),
                        'character_resource' => clienttranslate('Health'),
                    ]);
                },
            ],
            '7-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Rest'),
                'unlockCost' => 5,
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Rest'),
                        'state' => ['playerTurn'],
                        'perDay' => 1,
                        'stamina' => 4,
                        'global' => true,
                        'onUse' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getTurnCharacterId();
                            if (getUsePerDay($char . 'rest', $game) < 1) {
                                $game->selectionStates->initiateHindranceSelection($skill['id']);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getTurnCharacterId();
                            return sizeof($game->character->getTurnCharacter()['physicalHindrance']) > 0 &&
                                getUsePerDay($char . 'rest', $game) < 1;
                        },
                        'onUseHerbPre' => function (Game $game, $action, &$data) {
                            return ['notify' => false, 'nextState' => false, 'interrupt' => true];
                        },
                        'onHindranceSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('hindranceSelection');
                            $char = $game->character->getTurnCharacterId();
                            if ($state && $state['id'] == $skill['id']) {
                                usePerDay($char . 'rest', $game);
                                $game->character->adjustHealth($char, 1);

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
                                if ($count > 1) {
                                    throw new BgaUserException(clienttranslate('Only 1 hindrance can be removed'));
                                }
                                $data['nextState'] = 'playerTurn';
                            }
                        },
                    ],
                ],
            ],
            '7-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Clarity'),
                'unlockCost' => 7,
                'onUse' => function (Game $game, $unlock) {
                    $chars = $game->character->getAllCharacterData();
                    foreach ($chars as $char) {
                        foreach ($char['mentalHindrance'] as $card) {
                            $game->character->removeHindrance($char['character_name'], $card);
                        }
                    }
                },
                // Not affected by mental hindrance, can hold 4 physical
                'onMaxHindrance' => function (Game $game, $unlock, &$data) {
                    $data['maxPhysicalHindrance'] = 4;
                    $data['canDrawMentalHindrance'] = false;
                },
            ],
            '8-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Cooperation'),
                'unlockCost' => 5,
                'onMorningAfterPost' => function (Game $game, $unlock, &$data) {
                    $data['nextState'] = false;

                    $game->selectionStates->initiateState(
                        'characterSelection',
                        [
                            'selectableCharacters' => array_values(
                                array_diff($game->character->getAllCharacterIds(), [$game->character->getFirstCharacter()])
                            ),
                            'id' => $unlock['id'],
                        ],
                        $game->character->getFirstCharacter(),
                        false,
                        'tradePhase',
                        clienttranslate('Choose First Player')
                    );
                },
                'onCharacterSelection' => function (Game $game, $unlock, &$data) {
                    $state = $game->selectionStates->getState('characterSelection');
                    if ($state && $state['id'] == $unlock['id']) {
                        $game->character->setFirstTurnOrder($state['selectedCharacterId']);
                    }
                },
            ],
            '8-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Resourceful'),
                'unlockCost' => 4,
                'onDraw' => function (Game $game, $unlock, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'nothing') {
                        $game->character->adjustActiveStamina(1);
                    }
                },
            ],
            '9-A' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Torches'),
                'unlockCost' => 6,
                'onEncounterPre' => function (Game $game, $unlock, &$data) {
                    if ($data['willTakeDamage'] > 0) {
                        $data['willTakeDamage'] -= 1;
                    }
                },
            ],
            '9-B' => [
                'deck' => 'upgrade',
                'type' => 'deck',
                'name' => clienttranslate('Tempering'),
                'unlockCost' => 7,
                'onEncounterPre' => function (Game $game, $unlock, &$data) {
                    if ($data['characterDamage'] > 0) {
                        $data['characterDamage'] += 1;
                    }
                },
            ],
        ];
    }
}
