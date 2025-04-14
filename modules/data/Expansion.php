<?php

use Bga\Games\DontLetItDie\Game;

$expansionData = [
    'day-event-back' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'back',
    ],
    'fish-rule' => [
        'type' => 'rule',
        'expansion' => 'mini-expansion',
    ],
    'day-event_1_0' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'name' => clienttranslate('Wolf Pup'),
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Tame the beast'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->activeCharacterEventLog('obtained a ${item_name}', ['item_name' => clienttranslate('Wolf Pup')]);
                    $game->character->updateCharacterData($game->character->getTurnCharacterId(), function (&$data) use ($skill, $game) {
                        array_push($data['dayEvent'], $game->data->expansion[$skill['parentId']]);
                    });
                    $game->decks->removeFromDeck('day-event', $skill['parentId']);
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Ask Wolf Pup to Fetch'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    if ($game->rollFireDie($skill['parentName'], $game->character->getTurnCharacterId()) == 0) {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 1, 'resource_type' => 'wood']);
                        $game->adjustResource('wood', 1);
                    } else {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 1, 'resource_type' => 'rock']);
                        $game->adjustResource('rock', 1);
                    }
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_1' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'damage' => 1,
        'health' => 3,
        'name' => clienttranslate('Shell Shield'),
        'rotate' => 180,
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Make a snappy comeback'),
                'state' => ['dayEvent', 'resolveEncounter'],
                'health' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->gameData->set('state', ['card' => $game->data->expansion[$skill['parentId']], 'deck' => 'day-event']);
                    return ['notify' => false, 'nextState' => 'resolveEncounter'];
                },
                'onEncounterPost' => function (Game $game, $skill, &$data) {
                    $game->log('encounter', $data);
                    if ($data['encounterHealth'] <= $data['characterDamage']) {
                        $game->activeCharacterEventLog('obtained a ${item_name}', ['item_name' => clienttranslate('Shell Shield')]);
                        $game->decks->removeFromDeck('day-event', $skill['parentId']);
                        $game->character->updateCharacterData($game->character->getTurnCharacterId(), function (&$data) use (
                            $skill,
                            $game
                        ) {
                            array_push($data['dayEvent'], $game->data->expansion[$skill['parentId']]);
                        });
                    }
                },
            ],
            'skill2' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Ignore Damage'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'perForever' => 2,
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($game->character->getTurnCharacterId());
                    if ($data['action'] == 'actUseItem' && $data['subAction'] == $skill['id']) {
                        $data['perForever'] = 2 - getUsePerForever($char['id'] . $skill['id'], $game);
                    }
                },
                'onEncounterPre' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($game->character->getTurnCharacterId());

                    if ($char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($game->character->getTurnCharacterId());
                        usePerForever($char['id'] . $skill['id'], $game);
                        $game->log('onInterrupt', $data);
                        $data['data']['willTakeDamage'] = 0;

                        $game->activeCharacterEventLog('used ${item_name} to block the damage', [
                            'item_name' => clienttranslate('Shell Shield'),
                        ]);
                        if (getUsePerForever($char['id'] . $skill['id'], $game) == 2) {
                            clearUsePerForever($char['id'] . $skill['id'], $game);
                            $game->decks->addBackToDeck('day-event', $skill['parentId']);
                            $game->character->updateCharacterData($game->character->getTurnCharacterId(), function (&$data) use ($skill) {
                                $data['dayEvent'] = array_filter($data['dayEvent'], function ($d) use ($skill) {
                                    return $d['id'] != $skill['parentId'];
                                });
                            });
                        }
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($game->character->getTurnCharacterId());
                    $game->log('requirement', $char['isActive'], getUsePerForever($char['id'] . $skill['id'], $game) < 2, $skill);
                    return $char['isActive'] && getUsePerForever($char['id'] . $skill['id'], $game) < 2;
                },
            ],
        ],
    ],
    'day-event_1_10' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Yoink!'),
                'state' => ['dayEvent'],
                'stamina' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('berry', 2);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 2, 'resource_type' => 'berry']);
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('We\'ll see about that'),
                'state' => ['dayEvent'],
                'health' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('berry', 3);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 5, 'resource_type' => 'berry']);
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_11' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Oops'),
                'state' => ['dayEvent'],
                'stamina' => 3,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('wood', 3);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 3, 'resource_type' => 'wood']);
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Who needs tools?'),
                'state' => ['dayEvent'],
                'health' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('wood', 1);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 1, 'resource_type' => 'wood']);
                    $game->activeCharacterEventLog('lost ${count} ${character_resource}', ['count' => 1, 'character_resource' => 'health']);
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_3' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('AaaaaaAAaaaAAAaaAAAAAaAAAA!!'),
                'state' => ['dayEvent'],
                'health' => 2,
                'onUse' => function (Game $game, $skill) {
                    $game->activeCharacterEventLog('lost ${count} ${character_resource}', ['count' => 2, 'character_resource' => 'health']);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return getUsePerDay($skill['parentId'], $game) == 0;
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('When in doubt, throw a rock'),
                'state' => ['dayEvent'],
                'cost' => ['rock' => 1],
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('rock', -1);
                    if (getUsePerDay($skill['parentId'], $game) == 0) {
                        usePerDay($skill['parentId'], $game);
                    }
                    if ($game->rollFireDie(clienttranslate('Day Event'), $game->character->getTurnCharacterId()) == 0) {
                        usePerDay($skill['parentId'], $game);
                        if (getUsePerDay($skill['parentId'], $game) == 3) {
                            $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 3, 'resource_type' => 'meat']);
                            return ['notify' => false, 'nextState' => 'playerTurn'];
                        } else {
                            $game->activeCharacterEventLog('hit the beast');
                        }
                    } else {
                        $game->activeCharacterEventLog('missed the beast');
                    }
                    return ['notify' => false, 'nextState' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return $game->gameData->getResource('rock') >= 1 && getUsePerDay($skill['parentId'], $game) < 3;
                },
            ],
            'skill3' => [
                'type' => 'skill',
                'name' => clienttranslate('Give Up'),
                'state' => ['dayEvent'],
                'health' => 2,
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveHealth(-2);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return getUsePerDay($skill['parentId'], $game) > 0 && getUsePerDay($skill['parentId'], $game) < 3;
                },
            ],
        ],
    ],
    'daylight-rule' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'rule',
    ],
    'day-event_1_5' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('The only good snake'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveStamina(2);
                    $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                        'count' => 2,
                        'character_resource' => 'stamina',
                    ]);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($equipment) {
                            return $equipment['itemType'] == 'weapon';
                        })
                    ) > 0;
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Maybe if I just move my foot'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->character->updateCharacterData($game->character->getTurnCharacterId(), function (&$data) {
                        $data['modifiedMaxStamina'] -= 1;
                    });
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_6' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Sorry about that!'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    $characters = array_filter($game->character->getAllCharacterIds(), function ($character) use ($currentCharacter) {
                        return $character != $currentCharacter;
                    });

                    $game->gameData->set('characterSelectionState', [
                        'selectableCharacters' => array_values($characters),
                        'cancellable' => false,
                        'id' => $skill['id'],
                    ]);
                    $data['interrupt'] = true;
                    $game->gamestate->nextState('characterSelection');
                    return ['notify' => false, 'nextState' => false];
                },
                'onCharacterSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('characterSelectionState');
                    if ($state && $state['id'] == $skill['id']) {
                        $game->character->adjustHealth($data['characterId'], -1);
                        $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                            'character_name' => $data['characterId'],
                        ]);
                        $data['nextState'] = 'playerTurn';
                    }
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Let\'s think about this'),
                'state' => ['dayEvent'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    $characters = array_filter($game->character->getAllCharacterIds(), function ($character) use ($currentCharacter) {
                        return $character != $currentCharacter;
                    });

                    $game->gameData->set('characterSelectionState', [
                        'selectableCharacters' => array_values($characters),
                        'cancellable' => false,
                        'id' => $skill['id'],
                    ]);
                    $data['interrupt'] = true;
                    $game->gamestate->nextState('characterSelection');
                    return ['notify' => false, 'nextState' => false];
                },
                'onCharacterSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('characterSelectionState');
                    if ($state && $state['id'] == $skill['id']) {
                        $game->character->adjustHealth($game->character->getTurnCharacterId(), 1);
                        $game->character->adjustHealth($data['characterId'], 1);
                        $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                        ]);
                        $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                            'character_name' => $data['characterId'],
                        ]);
                        $data['nextState'] = 'playerTurn';
                    }
                },
            ],
        ],
    ],
    'day-event_1_7' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Nope'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    if ($game->rollFireDie(clienttranslate('Day Event'), $currentCharacter) != 0) {
                        $game->character->adjustHealth($currentCharacter, -1);
                        $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                        ]);
                    } else {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                            'count' => 3,
                            'resource_type' => 'wood',
                        ]);
                    }
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('It doesn\'t look that tough'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    if (
                        $game->rollFireDie(clienttranslate('Day Event'), $currentCharacter) +
                            $game->rollFireDie(clienttranslate('Day Event'), $currentCharacter) +
                            $game->rollFireDie(clienttranslate('Day Event'), $currentCharacter) >=
                        5
                    ) {
                        $game->adjustResource('bone', 2);
                        $game->adjustResource('meat', 2);
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 2, 'resource_type' => 'meat']);
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 2, 'resource_type' => 'bone']);
                    } else {
                        $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                            'count' => 2,
                            'character_resource' => 'health',
                        ]);
                    }
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_8' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Climb a tree'),
                'state' => ['dayEvent'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Backtrack'),
                'state' => ['dayEvent'],
                'health' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                        'count' => 1,
                        'character_resource' => 'health',
                    ]);
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'day-event_1_9' => [
        'deck' => 'day-event',
        'deckType' => 'day-event',
        'expansion' => 'mini-expansion',
        'type' => 'deck',
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Sneak around'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $currentCharacter = $game->character->getTurnCharacterId();
                    if ($game->rollFireDie(clienttranslate('Day Event'), $currentCharacter) == 0) {
                        $game->character->adjustActiveStamina(-2);
                    } else {
                        $game->adjustResource('meat', 2);
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 2, 'resource_type' => 'meat']);
                    }
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Head back to camp'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveStamina(-1);
                    $game->adjustResource('berry', 2);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 2, 'resource_type' => 'berry']);
                    return ['notify' => false];
                },
            ],
        ],
    ],
    'hindrance_1_0' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Blind'),
        'onAcquireHindrance' => function (Game $game, $card, &$data) {
            if ($card['id'] == $data['id']) {
                $character = $game->character->getTurnCharacter();
                $weapons = array_filter($character['equipment'], function ($item) {
                    return $item['itemType'] == 'weapon' && $item['range'] > 1;
                });
                // Only range 1 weapon can be equipped
                if (sizeof($weapons) > 0) {
                    $game->character->unequipEquipment(
                        $game->character->getTurnCharacterId(),
                        array_map(function ($item) {
                            return $item['id'];
                        }, $weapons)
                    );
                    $game->activeCharacterEventLog('sent their weapon to camp');
                }
            }
        },
    ],
    'hindrance_1_1' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Broken Arm'),
        'onAcquireHindrance' => function (Game $game, $card, &$data) {
            if ($card['id'] == $data['id']) {
                // No weapon can be equipped
                $character = $game->character->getTurnCharacter();
                $weaponIds = array_map(
                    function ($item) {
                        return $item['id'];
                    },
                    array_filter($character['equipment'], function ($item) {
                        return $item['itemType'] == 'weapon';
                    })
                );
                if (sizeof($weaponIds) > 0) {
                    $game->character->unequipEquipment($game->character->getTurnCharacterId(), $weaponIds);
                    $game->activeCharacterEventLog('sent their weapon to camp');
                }
            }
        },
    ],
    'hindrance_1_10' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Berserk'),
        'onAdjustHealth' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                // TODO
            }
        },
        'skills' => [
            // 'skill1' => [
            //     'type' => 'skill',
            //     'name' => clienttranslate('Sorry about that!'),
            //     'state' => ['dayEvent'],
            //     'onUse' => function (Game $game, $skill) {
            //         $currentCharacter = $game->character->getTurnCharacterId();
            //         $characters = array_filter($game->character->getAllCharacterIds(), function ($character) use ($currentCharacter) {
            //             return $character != $currentCharacter;
            //         });
            //         $game->gameData->set('characterSelectionState', [
            //             'selectableCharacters' => array_values($characters),
            //             'cancellable' => false,
            //             'id' => $skill['id'],
            //         ]);
            //         $data['interrupt'] = true;
            //         $game->gamestate->nextState('characterSelection');
            //         return ['notify' => false, 'nextState' => false];
            //     },
            //     'onCharacterSelection' => function (Game $game, $skill, &$data) {
            //         $state = $game->gameData->get('characterSelectionState');
            //         $game->log('onCharacterSelection', $skill, $data, $state);
            //         if ($state && $state['id'] == $skill['id']) {
            //             $game->character->adjustHealth($data['characterId'], -1);
            //             $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
            //                 'count' => 1,
            //                 'character_resource' => 'health',
            //                 'character_name' => $data['characterId'],
            //             ]);
            //             $data['nextState'] = 'playerTurn';
            //         }
            //     },
            // ],
        ],
    ],
    'hindrance_1_11' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Cowardly'),
        'onResolveDraw' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $data['discard'] = true;
                $game->activeCharacterEventLog('ran from the encounter');
                $game->character->adjustActiveHealth(-1);
                $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                    'count' => 1,
                    'character_resource' => 'health',
                ]);
            }
        },
    ],
    'hindrance_1_2' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Broken Leg'),
        'onGetValidActions' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                unset($data['actDrawForage']);
                unset($data['actDrawGather']);
                unset($data['actDrawHunt']);
                unset($data['actDrawHarvest']);
                unset($data['actDrawExplore']);
            }
        },
    ],
    'hindrance_1_3' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Obsessive'),
        'onEndTurn' => function (Game $game, $card, &$data) {
            if ($card['id'] == $data['id']) {
                $character = $game->character->getTurnCharacter();
                if ($character['health'] % 2 == 1) {
                    $game->character->adjustActiveHealth(-1);
                    $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                        'count' => 1,
                        'character_resource' => 'health',
                    ]);
                }
            }
        },
    ],
    'hindrance_1_4' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Paranoid'),
        // TODO: Always eat
        'onHandleEat' => function (Game $game, $card, &$data, ?string $preferType = null) {
            $variables = $game->gameData->getResources();
            $array = $game->actions->getActionSelectable('actEat');
            $array = array_values(
                array_filter(
                    $array,
                    function ($v) use ($variables) {
                        if (array_key_exists($v['id'], $variables)) {
                            return $v['actEat']['count'] <= $variables[$v['id']];
                        }
                    },
                    ARRAY_FILTER_USE_BOTH
                )
            );
            if ($preferType) {
                $i = array_search(
                    $preferType,
                    array_map(function ($d) {
                        return $d['id'];
                    }, $array)
                );
                if ($i !== false && $i > 0) {
                    $temp = $array[0];
                    $array[0] = $array[$i];
                    $array[$i] = $temp;
                }
            }
            if (sizeof($array) > 0) {
                foreach ($array as $v) {
                    $hinderedCharacter = $game->character->getCharacterData($card['characterId'], true);
                    if ($hinderedCharacter['health'] != $hinderedCharacter['maxHealth']) {
                        $prevCharacterId = $game->character->getSubmittingCharacterId();
                        $game->character->setSubmittingCharacterById($card['characterId']);
                        $game->actEat($v['id']);
                        $game->character->setSubmittingCharacterById($prevCharacterId);
                    }
                }
            }
        },
        'onEatBefore' => function (Game $game, $card, &$data) {
            if ($game->character->getSubmittingCharacterId() != $card['characterId']) {
                $card['onHandleEat']($game, $card, $data, $data['type']);
            }
        },
        'onCookAfter' => function (Game $game, $card, &$data) {
            $card['onHandleEat']($game, $card, $data);
        },
        'onResolveDraw' => function (Game $game, $card, &$data) {
            $card['onHandleEat']($game, $card, $data);
        },
        'onPlayerTurn' => function (Game $game, $card, &$data) {
            $card['onHandleEat']($game, $card, $data);
        },
    ],
    'hindrance_1_5' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Bad Back'),
        'onAcquireHindrance' => function (Game $game, $card, &$data) {
            if ($card['id'] == $data['id']) {
                // No weapon can be equipped
                $character = $game->character->getTurnCharacter();
                $toolIds = array_map(
                    function ($item) {
                        return $item['id'];
                    },
                    array_filter($character['equipment'], function ($item) {
                        return $item['itemType'] == 'tool';
                    })
                );
                if (sizeof($toolIds) > 0) {
                    $game->character->unequipEquipment($game->character->getTurnCharacterId(), $toolIds);
                    $game->activeCharacterEventLog('sent their tool to camp');
                }
            }
        },
    ],
    'hindrance_1_6' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Depressed'),
        'onGetActionCost' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $data['stamina'] > 0) {
                $data['stamina'] += 1;
            }
        },
    ],
    'hindrance_1_7' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Dumb'),
        'onGetActionCost' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $data['action'] == 'actInvestigateFire') {
                $data['stamina'] += 1;
            }
        },
        'onInvestigateFire' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $data['roll'] >= 1) {
                $game->activeCharacterEventLog('is dumb');
                $data['roll'] -= 1;
            }
        },
    ],
    'hindrance_1_8' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Forgetful'),
        'onActDraw' => function (Game $game, $card, &$data) {
            if (
                $card['characterId'] == $game->character->getTurnCharacterId() &&
                in_array($data['deck'], ['gather', 'hunt', 'harvest', 'forage'])
            ) {
                if ($game->rollFireDie(clienttranslate('Day Event'), $game->character->getTurnCharacterId()) == 0) {
                    $game->activeCharacterEventLog('forgot what they were doing');
                    $data['spendActionCost'] = true;
                    $data['cancel'] = true;
                }
            }
        },
    ],
    'hindrance_1_9' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'expansion' => 'hindrance',
        'type' => 'deck',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Anti-Social'),
        // Can't trade
        // Can't be healed by skills, going to handle this on the individual skills
        'onItemTrade' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                if (
                    (isset($data['trade1']['character']['id']) && $data['trade1']['character']['id'] == $card['characterId']) ||
                    (isset($data['trade2']['character']['id']) && $data['trade2']['character']['id'] == $card['characterId'])
                ) {
                    throw new BgaUserException($this->game->translate('Cannot trade with') . $card['characterId']);
                }
            }
        },
    ],
    'physical-hindrance-back' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'back',
        'expansion' => 'hindrance',
    ],
    'mental-hindrance-back' => [
        'deck' => 'mental-hindrance',
        'deckType' => 'mental-hindrance',
        'type' => 'back',
        'expansion' => 'hindrance',
    ],
    'hindrance_2_0' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Sun Burnt'),
        'onGetValidActions' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                unset($data['actInvestigateFire']);
            }
        },
    ],
    'hindrance_2_1' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('has'),
        'dropSentence' => clienttranslate('no longer has'),
        'name' => clienttranslate('Swollen Eyes'),
        'onDraw' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $card['deckType'] == 'resource') {
                $card['count'] -= 1;
            }
        },
    ],
    'hindrance_2_10' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Deep Wound'),
        'onGetCharacterData' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $data['maxHealth'] = clamp($data['maxHealth'] - 1, 0, 10);
                $data['health'] = clamp($data['health'], 0, $data['maxHealth']);
            }
        },
    ],
    'hindrance_2_11' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Dehydrated'),
        'onEncounterPre' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $data['willTakeDamage'] += 1;
            }
        },
    ],
    'hindrance_2_2' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Twisted Ankle'),
        'onGetCharacterData' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $data['maxStamina'] = clamp($data['maxStamina'] - 1, 0, 10);
                $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
            }
        },
    ],
    'hindrance_2_3' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Nauseous'),
        'onGetValidActions' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && getUsePerDay($card['id'] . 'nauseous', $game) < 1) {
                unset($data['actEat']);
            }
        },
        'onEat' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && getUsePerDay($card['id'] . 'nauseous', $game) < 1) {
                usePerDay($card['id'] . 'nauseous', $game);
                $game->activeCharacterEventLog('feels nauseous');
            }
        },
    ],
    'hindrance_2_4' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('has'),
        'dropSentence' => clienttranslate('no longer has'),
        'name' => clienttranslate('Parasites'),
        'onAdjustHealth' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $data['change'] > 0) {
                $data['change'] -= 1;
            }
        },
    ],
    'hindrance_2_5' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Sick'),
        // Physical hindrances need medical herbs to remove
    ],
    'hindrance_2_6' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Diseased'),
        'onMorning' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $game->character->adjustActiveHealth(-1);
                $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                    'count' => 1,
                    'character_resource' => 'health',
                ]);
            }
        },
    ],
    'hindrance_2_7' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Exhausted'),
        'onMorningAfter' => function (Game $game, $card, &$data) {
            $game->character->adjustStamina($game->character->getTurnCharacterId(), -2);
            $game->activeCharacterEventLog('is exhausted');
        },
    ],
    'hindrance_2_8' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('is'),
        'dropSentence' => clienttranslate('is no longer'),
        'name' => clienttranslate('Malnourished'),
        'onEncounterPre' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId()) {
                $data['encounterHealth'] += 1;
            }
        },
    ],
    'hindrance_2_9' => [
        'deck' => 'physical-hindrance',
        'deckType' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'acquireSentence' => clienttranslate('has a'),
        'dropSentence' => clienttranslate('no longer has a'),
        'name' => clienttranslate('Concussion'),
        'onInvestigateFire' => function (Game $game, $card, &$data) {
            if ($card['characterId'] == $game->character->getTurnCharacterId() && $data['roll'] >= 1) {
                $game->activeCharacterEventLog('', [], clienttranslate('has a') . clienttranslate('Concussion'));
                $data['roll'] -= 1;
            }
        },
    ],
];
