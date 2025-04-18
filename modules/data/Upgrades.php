<?php

use Bga\Games\DontLetItDie\Game;

$upgradesData = [
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
            if (getUsePerDay($char . $unlock['id'], $game) < 1) {
                usePerDay($char . $unlock['id'], $game);
                $count = $game->adjustResource('fkp', 2)['changed'];
                if ($count > 0) {
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
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
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
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
                'onUse' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getTurnCharacterId();
                    usePerDay($char . '12-B', $game);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getTurnCharacterId();
                    return getUsePerDay($char . '12-B', $game) < 1;
                },
            ],
        ],
        'onInvestigateFire' => function (Game $game, $skill, &$data) {
            $char = $game->character->getTurnCharacterId();
            if (getUsePerDay($char . '12-B', $game) == 1) {
                usePerDay($char . '12-B', $game);
                $data['roll'] += $data['originalRoll'];
            }
        },
    ],
    '13-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Bone Efficiency'),
        'unlockCost' => 4,
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'hide') {
                if ($game->adjustResource('hide', 1)['changed'] > 0) {
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'count' => 1,
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    '13-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Hide Efficiency'),
        'unlockCost' => 4,
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'bone') {
                if ($game->adjustResource('bone', 1)['changed'] > 0) {
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'count' => 1,
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    '14-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Medicinal Herb Efficiency'),
        'unlockCost' => 5,
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'herb') {
                if ($game->adjustResource('herb', 1)['changed'] > 0) {
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'count' => 1,
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    '14-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Dino Egg Efficiency'),
        'unlockCost' => 5,
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'dino-egg') {
                if ($game->adjustResource('dino-egg', 1)['changed'] > 0) {
                    $game->notify->all('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'count' => 1,
                        'resource_type' => $card['resourceType'],
                    ]);
                }
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
                array_push(
                    $data['selectable'],
                    $game->data->items['gem-y-necklace'],
                    $game->data->items['gem-b-necklace'],
                    $game->data->items['gem-p-necklace']
                );
            }
        },
    ],
    '15-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Recycling'),
        'unlockCost' => 4,
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
                'name' => clienttranslate('Fire Watch'),
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'global' => true,
                'onMorning' => function (Game $game, $skill, &$data) {
                    $game->actInterrupt->addSkillInterrupt($skill);
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $game->gameData->destroyResource('fiber');
                        $interruptState = $game->actInterrupt->getState('stMorningPhase');
                        $interruptState['woodNeeded'] -= 1;
                        $game->actInterrupt->setState('stMorningPhase', $interruptState);
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
                'interruptState' => ['playerTurn'],
                'global' => true,
                'onMorning' => function (Game $game, $skill, &$data) {
                    $game->actInterrupt->addSkillInterrupt($skill);
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $interruptState = $game->actInterrupt->getState('stMorningPhase');
                        $interruptState['health'] += 1;
                        $interruptState['woodNeeded'] -= 1;
                        $game->actInterrupt->setState('stMorningPhase', $interruptState);
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
            if (array_key_exists('eventType', $data['card']) && $data['card']['eventType'] == 'rival-tribe') {
                $roll = min($game->rollFireDie($unlock['name']), $game->rollFireDie($unlock['name']));
                rivalTribe($game, $data, $roll);

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
            if (array_key_exists('eventType', $data['card']) && $data['card']['eventType'] == 'rival-tribe') {
                $resourceType = $data['card']['resourceType'];
                $roll = $game->rollFireDie($unlock['name']);
                if ($resourceType == 'gem') {
                    $left = $game->adjustResource('gem-y', $roll)['left'];
                    $left = $game->adjustResource('gem-p', $left)['left'];
                    $left = $game->adjustResource('gem-b', $left)['left'];
                } else {
                    $changed = $game->adjustResource($resourceType, $roll)['changed'];
                    if ($changed > 0) {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', [
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
            $data['health'] = clamp($data['health'], 0, $data['maxHealth']);
            $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
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
            $data['health'] = clamp($data['health'], 0, $data['maxHealth']);
            $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
        },
    ],
    '4-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Smoked Food'),
        'unlockCost' => 4,
        'onAdjustHealth' => function (Game $game, $unlock, &$data) {
            if ($data['change']) {
                $aboveMax = $data['health'] + $data['change'] - $data['maxHealth'];
                if ($aboveMax > 0) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    $characters = array_filter($game->character->getAllCharacterIds(), function ($character) use ($currentCharacter) {
                        return $character != $currentCharacter;
                    });
                    $game->gameData->set('characterSelectionState', [
                        'selectableCharacters' => array_values($characters),
                        'cancellable' => false,
                        'id' => $unlock['id'],
                        'aboveMax' => $aboveMax,
                    ]);
                    $game->gamestate->nextState('characterSelection');
                    // TODO: we dont know what this will interrupt
                    // ideally we should track the next state and then redirect back there after selection
                }
            }
        },
        'onCharacterSelection' => function (Game $game, $unlock, &$data) {
            $state = $game->gameData->get('characterSelectionState');
            if ($state && $state['id'] == $unlock['id']) {
                $aboveMax = $state['aboveMax'];
                $game->character->adjustHealth($data['characterId'], $aboveMax);
                $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                    'count' => $aboveMax,
                    'character_resource' => 'health',
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
                        usePerDay($char . $skill['id'], $game);
                        $game->gameData->destroyResource('fiber', 1);
                        $cooked = $game->adjustResource('meat-cooked', 3);
                        // Take from the raw stack if there is extra
                        $raw = $game->adjustResource('meat', -$cooked['left']);
                        $cooked2 = $game->adjustResource('meat-cooked', -$raw['changed'], $game->gameData->getResource('meat-cooked'));
                        if ($cooked['changed'] + $cooked2['changed'] > 0) {
                            $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                                'count' => $cooked['changed'] + $cooked2['changed'],
                                'resource_type' => 'meat-cooked',
                            ]);
                        }
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getTurnCharacterId();
                    return getUsePerDay($char . $skill['id'], $game) < 1;
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
                // 'onUse' => function (Game $game, $skill, &$data) {
                //     $char = $game->character->getTurnCharacterId();
                //     array_intersect(['explore', 'gather', 'forage', 'harvest', 'hunt'], $game->decks->getAllDeckNames());
                // },
                // 'onUseSkill' => function (Game $game, $skill, &$data) {
                //     if ($data['skillId'] == $skill['id']) {
                //         $existingData = $game->actInterrupt->getState('actDraw');
                //         if (array_key_exists('data', $existingData)) {
                //             $deck = $existingData['data']['deck'];
                //             $card1 = $existingData['data']['card'];
                //             // $card2 = $game->decks->pickCard($deck);
                //             $game->gameData->set('cardSelectionState', [
                //                 'cards' => [$card1, $card2],
                //                 'deck' => $deck,
                //                 'cancellable' => false,
                //                 'id' => $skill['id'],
                //             ]);
                //             // $data['interrupt'] = true;
                //             $game->gamestate->nextState('cardSelection');
                //         }
                //     }
                // },
                // 'onCardSelection' => function (Game $game, $skill, &$data) {
                //     $state = $game->gameData->get('cardSelectionState');
                //     if ($state && $state['id'] == $skill['id']) {
                //         usePerDay($skill['id'], $game);
                //         $discardCard = array_values(
                //             array_filter($state['cards'], function ($card) use ($data) {
                //                 return $card['id'] != $data['cardId'];
                //             })
                //         )[0];
                //         $game->cardDrawEvent($discardCard, $state['deck']);

                //         $drawState = $game->actInterrupt->getState('actDraw');
                //         $drawState['data']['card'] = $game->decks->getCard($data['cardId']);
                //         $game->actInterrupt->setState('actDraw', $drawState);
                //         $game->actInterrupt->actInterrupt($skill['id']);
                //         $data['nextState'] = false;
                //     }
                // },
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
                $game->activeCharacterEventLog('received ${count} ${resource_type}', [
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
            $game->character->adjustAllHealth(10);
            $this->notify->all('tree', clienttranslate('Everyone gained ${count} ${character_resource}'), [
                'count' => clienttranslate('full'),
                'character_resource' => 'health',
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
                        $game->hindranceSelection($skill['id']);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getTurnCharacterId();
                    return sizeof($this->game->character->getTurnCharacter()['physicalHindrance']) > 0 &&
                        getUsePerDay($char . 'rest', $game) < 1;
                },
                'onUseHerbPre' => function (Game $game, $action, &$data) {
                    return ['notify' => false, 'nextState' => false, 'interrupt' => true];
                },
                'onHindranceSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('hindranceSelectionState');
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
                                    $this->game->character->removeHindrance($char['characterId'], $card);
                                }
                            }
                        }
                        if ($count > 1) {
                            throw new BgaUserException($this->game->translate('Only 1 hindrance can be removed'));
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
                    $this->game->character->removeHindrance($char['character_name'], $card);
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
        // TODO: On morning select the next character to go first
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
