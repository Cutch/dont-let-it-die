<?php

// class Skill
// {
//     public string $id;
//     public string $characterId;
//     public function __construct(
//         public string $name,
//         public ?string $damage = null,
//         public ?int $stamina = null,
//         public Closure $onUse,
//         public Closure $requires
//     ) {
//     }
//     public function setCharacter($characterId){
//         $this->characterId = $characterId;
//     }
// }
use Bga\Games\DontLetItDie\Game;
$charactersData = [
    'Gronk' => [
        // Done
        'type' => 'character',
        'health' => '7',
        'stamina' => '4',
        'name' => 'Gronk',
        'slots' => ['weapon', 'weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Gain 2 Stamina'),
                'health' => 2,
                'perDay' => 1,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char['id'], $game);
                    $game->character->adjustActiveStamina(2);
                    $game->character->adjustActiveHealth(-2);
                    $game->activeCharacterEventLog('gained ${count_1} ${character_resource_1}, lost ${count_2} ${character_resource_2}', [
                        'count_1' => 2,
                        'character_resource_1' => 'stamina',
                        'count_2' => 2,
                        'character_resource_2' => 'health',
                    ]);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char['id'], $game) < 1;
                    }
                },
            ],
        ],
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['encounterHealth'] <= $data['characterDamage']) {
                $data['stamina'] += 2;
                $game->activeCharacterEventLog('gained ${count} ${character_resource}', ['count' => 2, 'character_resource' => 'stamina']);
            }
        },
    ],
    'Grub' => [
        // Done
        'type' => 'character',
        'health' => '4',
        'stamina' => '7',
        'name' => 'Grub',
        'slots' => ['weapon', 'tool'],
        'onGetValidActions' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                unset($data['actDrawHunt']);
            }
        },
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['escape'] = true;
            }
        },
        'onDraw' => function (Game $game, $char, &$data) {
            $deck = $data['deck'];
            if ($char['isActive'] && $deck == 'gather') {
                if ($game->adjustResource('fiber', 1)['changed'] > 0) {
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => 1,
                        'resource_type' => 'fiber',
                    ]);
                }
            }
        },
    ],
    'Kara' => [
        'type' => 'character',
        'health' => '4',
        'stamina' => '5',
        'name' => 'Kara',
        'slots' => ['weapon', 'tool'],
        'onEat' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['health'] *= 2;
            }
        },
        'onGetEatData' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['health'] *= 2;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Re-Roll Fire Die'),
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'perDay' => 1,
                'onInvestigateFire' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($data['roll'] < 3 && getUsePerDay($char['id'] . $skill['id'], $game) < 1) {
                        // If kara is not the character, and the roll is not the max
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        $game->activeCharacterEventLog('is re-rolling ${active_character_name}\'s fire die', [
                            ...$char,
                            'active_character_name' => $game->character->getTurnCharacter()['character_name'],
                        ]);
                        $data['data']['roll'] = $game->rollFireDie($char['character_name']);
                        usePerDay($char['id'] . $skill['id'], $game);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Request 2 Stamina from Kara'),
                'state' => ['playerTurn'],
                'cancellable' => true,
                'perDay' => 1,
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if (!$char['isActive'] && $data['action'] == 'actUseSkill' && $data['subAction'] == $skill['id']) {
                        $data['perDay'] = 1 - getUsePerDay($char['id'] . 'stamina', $game);
                    }
                },
                'onUseSkill' => function (Game $game, $skill, &$data) {
                    if ($data['skillId'] == $skill['id']) {
                        $turnChar = $game->character->getTurnCharacter();
                        $char = $game->character->getCharacterData($skill['characterId']);
                        $game->actInterrupt->addSkillInterrupt($char['skills']['Karaskill3']);
                        // $data['args'] = ['Karaskill3'];
                        $data['skillId'] = 'Karaskill3';
                        $data['skill'] = $char['skills']['Karaskill3'];
                        $game->activeCharacterEventLog('requested Kara use their stamina skill', [
                            'character_name' => $game->getCharacterHTML($turnChar['character_name']),
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'] &&
                        getUsePerDay($char['id'] . 'stamina', $game) < 1 &&
                        !in_array('night-event-8_11', $game->getActiveNightCardIds());
                },
            ],
            'skill3' => [
                'type' => 'skill',
                'name' => clienttranslate('Give 2 Stamina'),
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'cancellable' => true,
                'perDay' => 1,
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $data['args'] = ['Karaskill3'];
                    }
                },
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $interruptState = $game->actInterrupt->getState('actUseSkill');
                    if (
                        !$char['isActive'] &&
                        $data['action'] == 'actUseSkill' &&
                        $data['subAction'] == $skill['id'] &&
                        $interruptState &&
                        array_key_exists('data', $interruptState) &&
                        $interruptState['data']['skillId'] == $skill['id']
                    ) {
                        $data['perDay'] = 1 - getUsePerDay($char['id'] . 'stamina', $game);
                        $data['name'] = str_replace(
                            '${character_name}',
                            $interruptState['data']['turnCharacter']['character_name'],
                            clienttranslate('Give 2 Stamina to ${character_name}')
                        );
                    }
                },
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $turn_char = $game->character->getTurnCharacter();
                    usePerDay($char['id'] . 'stamina', $game);
                    $game->character->adjustStamina($turn_char['character_name'], 2);
                    // $game->adjustResource($data['data']['card']['resourceType'], $data['data']['card']['count']);
                    $game->activeCharacterEventLog('gave ${turn_character_name} 2 stamina', [
                        'turn_character_name' => $turn_char['character_name'],
                    ]);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'] && getUsePerDay($char['id'] . 'stamina', $game) < 1;
                },
            ],
        ],
    ],
    'Cron' => [
        // Done
        'type' => 'character',
        'health' => '5',
        'stamina' => '6',
        'name' => 'Cron',
        'startsWith' => 'hide-armor',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Shuffle Discard Pile'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        // Shuffle it
                        $game->gameData->set('state', ['id' => $skill['id']]);
                        $game->deckSelection();
                        return ['spendActionCost' => false, 'notify' => false];
                    }
                },
                'onDeckSelection' => function (Game $game, $skill, $deckName) {
                    if ($game->gameData->get('state')['id'] == $skill['id']) {
                        $game->decks->shuffleInDiscard($deckName, false);
                        $game->actions->spendActionCost('actUseSkill', $skill['id']);
                        $game->activeCharacterEventLog('shuffled the ${deck} deck using their skill', [
                            'deck' => $deckName,
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
        'onEncounter' => function (Game $game, $char, $data) {
            if ($data['encounterHealth'] <= $data['characterDamage']) {
                $data['stamina'] += 1;

                $game->activeCharacterEventLog('gave 1 stamina to ${active_character_name}', [
                    'active_character_name' => $game->getCharacterHTML($char['character_name']),
                ]);
            }
        },
    ],
    'Dub' => [
        'type' => 'character',
        'health' => '5',
        'stamina' => '4',
        'name' => 'Dub',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Redraw Night Event'),
                'state' => ['interrupt'],
                'interruptState' => ['nightPhase', 'nightDrawCard'],
                'perDay' => 1,
                // 'onUse' => function (Game $game, $skill) {
                // },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return getUsePerDay($char['id'], $game) < 1 && $game->gameData->getResource('bone') > 0;
                },
                'onNightDrawCard' => function (Game $game, $skill, $data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'], $game);
                        $game->adjustResource('bone', -1);
                        $game->activeCharacterEventLog('re-drew the night event');
                        // TODO: Interrupt and Discard current night event
                        $card = $game->decks->pickCard('night-event');
                        $data['state']['card'] = $card;
                        $game->gameData->set('state', ['card' => $card, 'deck' => 'night-event']);
                        $game->cardDrawEvent($card, 'night-event');
                    }
                },
            ],
        ],
        'onGetActionSelectable' => function (Game $game, $char, &$data) {
            if ($data['action'] == 'actSpendFKP') {
                $data['selectable'] = ['fkp', 'bone'];
            }
        },
        'onRollDie' => function (Game $game, $char, &$data) {
            $data['sendNotification']();
            if ($char['isActive'] && $data['value'] == 1) {
                if ($game->adjustResource('berry', 1)['changed'] > 0) {
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => 1,
                        'resource_type' => 'berry',
                    ]);
                }
            }
        },
    ],
    'Faye' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '7',
        'stamina' => '4',
        'name' => 'Faye',
        'startsWith' => 'skull-shield',
        'slots' => ['weapon', 'tool'],
        // Spend 2 to gain take a physical hindrance from another
        // If using an herb to clear a physical hindrance gain a health
    ],
    'Ajax' => [
        // Done
        'type' => 'character',
        'health' => '8',
        'stamina' => '5',
        'name' => 'Ajax',
        'slots' => ['weapon', 'tool', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Gain 2 Health'),
                'stamina' => 2,
                'perDay' => 1,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char['id'], $game);
                    $game->character->adjustActiveStamina(-2);
                    $game->character->adjustActiveHealth(2);
                    $game->activeCharacterEventLog('gained ${count_1} ${character_resource_1}, lost ${count_2} ${character_resource_2}', [
                        'count_1' => 2,
                        'character_resource_1' => 'health',
                        'count_2' => 2,
                        'character_resource_2' => 'stamina',
                    ]);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char['id'], $game) < 1;
                    }
                },
            ],
        ],
        // 'onEncounter' => function (Game $game, $char, &$data) {
        //     if ($char['isActive'] && $data['name'] == 'Beast') {
        //         $data['encounterHealth'] = 0;
        //         $data['willTakeDamage'] = 0;
        //         $data['willReceiveMeat'] = 1;
        //     }
        // },
    ],
    'Atouk' => [
        'type' => 'character',
        'health' => '4',
        'stamina' => '6',
        'name' => 'Atouk',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Gain 1 Wood'),
                'stamina' => 2,
                'perDay' => 1,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char['id'], $game);
                    $game->character->adjustActiveStamina(-2);
                    $game->adjustResource('wood', 1);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => 1,
                        'resource_type' => 'wood',
                    ]);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char['id'], $game) < 1;
                    }
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Reduce Crafting Resources'),
                'onCraft' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $existingData = $game->actInterrupt->getState('actCraft');
                    if ($char['isActive'] && !$existingData) {
                        $game->gameData->set('state', ['id' => $skill['id'], ...$data, 'title' => clienttranslate('Item Costs')]);
                        $game->gamestate->nextState('resourceSelection');
                        $data['interrupt'] = true;
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && sizeof(array_filter($game->gameData->getResources())) > 0;
                },
                'onResourceSelection' => function (Game $game, $skill, &$data) {
                    if ($game->gameData->get('state')['id'] == $skill['id']) {
                        $state = $game->actInterrupt->getState('actCraft');
                        if (array_key_exists($data['resourceType'], $state['data']['item']['cost'])) {
                            $state['data']['item']['cost'][$data['resourceType']] = max(
                                $state['data']['item']['cost'][$data['resourceType']] - 2,
                                0
                            );
                        }
                        $game->actInterrupt->setState('actCraft', $state);
                    }
                },
                'onResourceSelectionOptions' => function (Game $game, $skill, &$resources) {
                    $state = $game->gameData->get('state');
                    if ($state['id'] == $skill['id']) {
                        $resources = $state['item']['cost'];
                    }
                },
            ],
        ],
        'onGetValidActions' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                unset($data['actDrawHunt']);
                unset($data['actDrawForage']);
            }
        },
    ],
    'Ayla' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Ayla',
        'slots' => ['weapon', 'tool'],
        'onGetActionCost' => function (Game $game, $char, &$data) {
            // Tested
            if ($char['isActive'] && $data['action'] == 'actDrawHunt') {
                $data['stamina'] = min($data['stamina'], 2);
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                // Tested
                'name' => clienttranslate('Convert 1 Berry to Fiber'),
                'stamina' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('berry', -1);
                    $game->adjustResource('fiber', 1);
                    $game->activeCharacterEventLog('converted 1 raw berry to 1 fiber');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && $game->gameData->getResource('berry') > 0;
                },
            ],
            'skill2' => [
                'type' => 'skill',
                // Tested
                'name' => clienttranslate('Heal 2'),
                'stamina' => 0,
                'perDay' => 1,
                'state' => ['postEncounter'],
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char['id'], $game);
                    $game->character->adjustActiveHealth(2);
                    $game->activeCharacterEventLog('healed by 2');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive'] && $char['health'] < $char['maxHealth']) {
                        $state = $game->gameData->get('encounterState');
                        if ($state['encounterHealth'] <= $state['characterDamage']) {
                            return getUsePerDay($char['id'], $game) < 1;
                        }
                    }
                },
            ],
        ],
    ],
    'River' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '4',
        'name' => 'River',
        'slots' => ['weapon', 'tool'],
        'onGetActionCost' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actInvestigateFire' && getUsePerDay($char['id'], $game) < 1) {
                $data['stamina'] = min($data['stamina'], 0);
            }
        },
        'onInvestigateFire' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && getUsePerDay($char['id'], $game) < 1) {
                usePerDay($char['id'], $game);
                $data['spendActionCost'] = false;
            }
        },
        'onDraw' => function (Game $game, $char, &$data) {
            $card = $data['card'];
            if ($char['isActive'] && $card['deckType'] == 'nothing') {
                $game->adjustResource('fkp', 2);
                $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                    'count' => 2,
                    'resource_type' => 'fkp',
                ]);
            }
        },
        'onGetActionSelectable' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actEat') {
                $data['selectable'] = array_filter(
                    $data['selectable'],
                    function ($v, $k) {
                        return in_array($v['id'], ['berry', 'berry-cooked']);
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }
        },
    ],
    'Sig' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Sig',
        'slots' => ['weapon', 'tool'],
        'expansion' => 'mini-expansion',
        // Skip trading ITEMS with others
        'onGetActionCost' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actInvestigateFire') {
                $data['stamina'] = 5;
            }
        },
        'onInvestigateFire' => function (Game $game, $skill, &$data) {
            $char = $game->character->getCharacterData($skill['characterId']);
            if (!$char['isActive']) {
                $roll2 = $game->rollFireDie();
                $data['roll'] += $roll2;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Go Fish'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $skill['sendNotification']();
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => $game->rollFireDie(),
                        'resource_type' => 'fish',
                    ]);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $hasItem =
                        sizeof(
                            array_filter(
                                $char['equipment'],
                                function ($item) {
                                    return in_array($item['id'], ['sharp-stick', 'spear']);
                                },
                                ARRAY_FILTER_USE_BOTH
                            )
                        ) > 0;
                    return $char['isActive'] && $hasItem;
                },
            ],
        ],
    ],
    'Tara' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Tara',
        'slots' => ['weapon', 'tool'],
        'onEat' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && getUsePerDay($char['id'] . 'skillonEat', $game) < 1) {
                $data['stamina'] = 2;
                usePerDay($char['id'] . 'skillonEat', $game);
            }
        },
        'onGetEatData' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && getUsePerDay($char['id'] . 'skillonEat', $game) < 1) {
                $data['stamina'] = 2;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Heal 1 HP'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $game->activeCharacterEventLog('healed for 1 hp');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'Nirv' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '3',
        'name' => 'Nirv',
        'slots' => ['weapon', 'tool'],
        'onNight' => function (Game $game, $char, &$data) {
            if (array_key_exists('eventType', $data['card']) && $data['card']['eventType'] == 'rival-tribe') {
                foreach ($game->character->getAllCharacterData() as $k => $character) {
                    if (
                        sizeof(
                            array_filter($char['mentalHindrance'], function ($hindrance) {
                                return $hindrance['id'] == 'hindrance_1_9';
                            })
                        ) == 0
                    ) {
                        $game->character->adjustHealth($character['character_name'], 1);
                    }
                }
                $game->activeCharacterEventLog('All tribe members gained 1 health after the rival tribe event');
            }
        },
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($data['encounterHealth'] <= $data['characterDamage']) {
                if (
                    sizeof(
                        array_filter($char['mentalHindrance'], function ($hindrance) {
                            return $hindrance['id'] == 'hindrance_1_9';
                        })
                    ) == 0
                ) {
                    $game->character->adjustHealth($char['character_name'], 1);
                    $game->activeCharacterEventLog('gained 1 health after the danger cards death', [
                        'character_name' => $game->getCharacterHTML($char['character_name']),
                    ]);
                }
            }
        },
        'onGetActionCost' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actDrawGather' && getUsePerDay($char['id'] . 'skillonDraw', $game) < 1) {
                $data['stamina'] = min($data['stamina'], 0);
            }
        },
        'onDraw' => function (Game $game, $char, &$data) {
            $deck = $data['deck'];
            if ($char['isActive'] && $deck == 'gather' && getUsePerDay($char['id'] . 'skillonDraw', $game) < 1) {
                usePerDay($char['id'] . 'skillonDraw', $game);
                $data['spendActionCost'] = false;
            }
        },
    ],
    'Oof' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '6',
        'stamina' => '6',
        'name' => 'Oof',
        'startsWith' => 'mortar-and-pestle',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Remove Physical Hindrance'),
                'state' => ['playerTurn'],
                'stamina' => 3,
                'onUse' => function (Game $game, $skill) {},
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'Rex' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '5',
        'stamina' => '7',
        'name' => 'Rex',
        'startsWith' => 'fire-stick',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('View Top Card'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        $game->gameData->set('state', ['id' => $skill['id']]);
                        $game->deckSelection(
                            array_intersect($game->decks->getAllDeckNames(), ['harvest', 'forage', 'hunt', 'gather', 'explore'])
                        );
                        return ['spendActionCost' => false, 'notify' => false];
                    }
                },
                'onDeckSelection' => function (Game $game, $skill, $deckName) {
                    if ($game->gameData->get('state')['id'] == $skill['id']) {
                        $game->actions->spendActionCost('actUseSkill', $skill['id']);
                        $topCard = $game->decks->getDeck($deckName)->getCardOnTop('deck');
                        $card = $game->decks->getCard($topCard['type_arg']);
                        $game->cardDrawEvent($card, $deckName, ['partial' => true]);
                        $game->activeCharacterEventLog('viewed the top card', [
                            'deck' => $deckName,
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Place Trap'),
                'state' => ['playerTurn'],
                'stamina' => 1,
                'onUse' => function (Game $game, $skill) {
                    // TODO: Need to be able to place or move trap
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        $game->gameData->set('state', ['id' => $skill['id']]);
                        $game->deckSelection(
                            array_intersect($game->decks->getAllDeckNames(), ['harvest', 'forage', 'hunt', 'gather', 'explore'])
                        );
                        return ['spendActionCost' => false, 'notify' => false];
                    }
                },
                'onDeckSelection' => function (Game $game, $skill, $deckName) {
                    if ($game->gameData->get('state')['id'] == $skill['id']) {
                        $game->actions->spendActionCost('actUseSkill', $skill['id']);
                        $game->activeCharacterEventLog('placed a trap on ${deck}', [
                            'deck' => $deckName,
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'Mabe' => [
        // Done
        'type' => 'character',
        'health' => '5',
        'stamina' => '5',
        'name' => 'Mabe',
        'slots' => ['weapon', 'tool'],
        'onGetValidActions' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                unset($data['actInvestigateFire']);
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Copy Resource'),
                'stamina' => 3,
                'onUse' => function (Game $game, $skill, $data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        $game->gameData->set('state', ['id' => $skill['id']]);
                        $game->gamestate->nextState('resourceSelection');
                        return ['spendActionCost' => false, 'notify' => false];
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && sizeof(array_filter($game->gameData->getResources())) > 0;
                },
                'onResourceSelection' => function (Game $game, $skill, &$data) {
                    if ($game->gameData->get('state')['id'] == $skill['id']) {
                        $game->actions->spendActionCost('actUseSkill', $skill['id']);
                        $game->adjustResource($data['resourceType'], 1);
                        $game->activeCharacterEventLog('copied 1 ${resource_type}', ['resource_type' => $data['resourceType']]);
                    }
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Roll Low Health Die'),
                'perDay' => 1,
                'onUse' => function (Game $game, $skill) {
                    $skill['sendNotification']();
                    $value = $game->rollFireDie($skill['characterId']);
                    usePerDay($skill['id'], $game);
                    if ($value == 0) {
                        $game->character->getActiveStamina(2);
                        $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                            'count' => 2,
                            'character_resource' => 'stamina',
                        ]);
                    } else {
                        $game->adjustResource('fkp', 1);
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                            'count' => 1,
                            'resource_type' => 'fkp',
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && $char['health'] < 3 && getUsePerDay($skill['id'], $game) < 1;
                },
            ],
        ],
    ],
    'Nanuk' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Nanuk',
        'slots' => ['weapon', 'tool'],
        'onEat' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['health'] *= 2;
            }
        },
        'onGetEatData' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['health'] *= 2;
            }
        },
        'onGetActionSelectable' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actEat') {
                $data['selectable'] = array_filter(
                    $data['selectable'],
                    function ($v, $k) {
                        return in_array($v['id'], ['meat', 'meat-cooked', 'fish', 'fish-cooked']);
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }
        },
    ],
    'Nibna' => [
        'type' => 'character',
        'health' => '7',
        'stamina' => '6',
        'name' => 'Nibna',
        'startsWith' => 'bag',
        'slots' => ['weapon', 'tool'],
        'onEat' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && getUsePerDay($char['id'], $game) < 1) {
                usePerDay($char['id'], $game);
                $data['health'] *= 2;
            }
        },
        'onGetEatData' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && getUsePerDay($char['id'], $game) < 1) {
                $data['health'] *= 2;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Heal everyone else for 1 hp'),
                'health' => 2,
                'onUse' => function (Game $game, $skill, $data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        foreach ($game->character->getAllCharacterData() as $k => $character) {
                            if (
                                $character['character_name'] != $char['character_name'] &&
                                sizeof(
                                    array_filter($char['mentalHindrance'], function ($hindrance) {
                                        return $hindrance['id'] == 'hindrance_1_9';
                                    })
                                ) == 0
                            ) {
                                $game->character->adjustHealth($character['character_name'], 1);
                            }
                        }
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'Zeebo' => [
        // Done
        'type' => 'character',
        'health' => '4',
        'stamina' => '6',
        'name' => 'Zeebo',
        'slots' => ['weapon'],
        'onDraw' => function (Game $game, $char, &$data) {
            $card = $data['card'];
            if ($char['isActive'] && $card['type'] == 'berry') {
                if ($game->character->adjustActiveHealth(1) == 1) {
                    $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                        'count' => 1,
                        'character_resource' => 'health',
                    ]);
                }
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Double Resources'),
                'state' => ['interrupt'],
                'interruptState' => ['drawCard'],
                'stamina' => 3,
                'onResolveDraw' => function (Game $game, $skill, $data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $card = $data['card'];
                    if ($char['isActive'] && $card['deckType'] == 'resource') {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onUse' => function (Game $game, $skill) {
                    return ['notify' => false];
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $game->adjustResource($data['data']['card']['resourceType'], $data['data']['card']['count']);
                        $game->activeCharacterEventLog('doubled the resources they found');
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'Thunk' => [
        // Done
        'type' => 'character',
        'health' => '6',
        'stamina' => '6',
        'name' => 'Thunk',
        'startsWith' => 'sharp-stick',
        'slots' => ['weapon', 'tool'],
        'onGetValidActions' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                unset($data['actDrawForage']);
                unset($data['actDrawGather']);
            }
        },
        'onDraw' => function (Game $game, $char, &$data) {
            $deck = $data['deck'];
            if ($char['isActive'] && $deck == 'hunt') {
                if ($game->adjustResource('meat', 1)['changed'] > 0) {
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => 1,
                        'resource_type' => 'meat',
                    ]);
                }
            }
        },
    ],
    'Tiku' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Tiku',
        'slots' => ['weapon', 'tool'],
        'onDraw' => function (Game $game, $char, &$data) {
            $card = $data['card'];
            $deck = $data['deck'];
            if ($char['isActive'] && $deck == 'explore' && $card['deckType'] != 'encounter') {
                if ($game->adjustResource('dino-egg', 1)['changed'] > 0) {
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                        'count' => 1,
                        'resource_type' => 'dino-egg',
                    ]);
                }
            }
        },
        // Not affected by mental hindrance, can hold 4 physical
        'onMaxHindrance' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['maxPhysicalHindrance'] = 4;
                $data['canDrawMentalHindrance'] = false;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => clienttranslate('Make Stew'),
                'stamina' => 1,
                'onUse' => function (Game $game, $skill, $data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        $game->adjustResource('berry', -1);
                        $game->adjustResource('meat', -1);
                        $game->adjustResource('herb', -1);
                        $game->adjustResource('stew', 1);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && sizeof(array_filter($game->gameData->getResources('berry', 'meat', 'herb'))) == 3;
                },
            ],
        ],
    ],
    'Vog' => [
        'type' => 'character',
        'health' => '10',
        'stamina' => '5',
        'name' => 'Vog',
        'slots' => ['weapon', 'tool'],
        'onGetActionCost' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['action'] == 'actEat') {
                $data['stamina'] += 1;
            }
        },
        'onMorning' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                array_push($data['skipMorningDamage'], 'Vog');
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Take Damage'),
                'state' => ['interrupt'],
                // 'interruptState' => ['playerTurn'],
                'interruptState' => ['drawCard'],
                'health' => 0,
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $interruptState = $game->actInterrupt->getState('actUseSkill');
                    if (
                        !$char['isActive'] &&
                        $data['action'] == 'actUseSkill' &&
                        $data['subAction'] == $skill['id'] &&
                        $interruptState &&
                        array_key_exists('data', $interruptState) &&
                        $interruptState['data']['skillId'] == $skill['id']
                    ) {
                        $damageTaken = $game->encounter->countDamageTaken($interruptState['data']);
                        $data['health'] = $damageTaken;
                    }
                },
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if (!$char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $interruptState = $game->actInterrupt->getState('stResolveEncounter');
                        $damageTaken = $game->encounter->countDamageTaken($interruptState['data']);
                        $game->character->adjustHealth($skill['characterId'], $damageTaken);
                        $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                            'character_name' => 'Vog',
                        ]);
                        $interruptState['willTakeDamage'] = 0;
                        $game->actInterrupt->setState('stResolveEncounter', $interruptState);
                    }
                },
                'onUse' => function (Game $game, $skill) {
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'];
                },
            ],
        ],
    ],
    'AlternateUpgradeTrack' => [
        'type' => 'instructions',
    ],
    'instructions-1' => [
        'type' => 'instructions',
    ],
    'instructions-2' => [
        'type' => 'instructions',
    ],
    'Blarg' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '6',
        'stamina' => '6',
        'name' => 'Blarg',
        'slots' => ['weapon', 'tool'],
        'onGetMaxBuildingCount' => function (Game $game, $char, &$data) {
            $data['count'] = 2;
        },
        'onGetUnlockCost' => function (Game $game, $unlock, &$data) {
            $data['unlockCost'] -= 2;
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Give Item'),
                'state' => ['playerTurn'],
                'stamina' => 1,
                'onUse' => function (Game $game, $skill) {
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'];
                },
            ],
        ],
    ],
    'Cali' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '2',
        'stamina' => '6',
        'name' => 'Cali',
        'slots' => ['weapon', 'tool'],
    ],
    'Loka' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '4',
        'stamina' => '7',
        'name' => 'Loka',
        'slots' => ['weapon', 'tool'],
        'onUnlock' => function (Game $game, $char, &$data) {
            $game->adjustResource('wood', 1);
            $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                'count' => 1,
                'resource_type' => 'wood',
            ]);
        },
        'onGetResourceMax' => function (Game $game, $char, &$data) {
            $data['maxCount'] -= getUsePerForever('hide-token', $game);
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Hold Hide'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    usePerForever('hide-token', $game);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Reduce Damage'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        subtractPerForever('hide-token', $game);
                    }
                },
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($game->character->getTurnCharacterId());
                    if ($char['isActive']) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerForever('hide-token', $game) > 0;
                },
            ],
            'skill3' => [
                'type' => 'skill',
                'name' => clienttranslate('+1 Resource'),
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        subtractPerForever('hide-token', $game);
                    }
                },
                'onResolveDraw' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($game->character->getTurnCharacterId());
                    $card = $data['card'];
                    if ($char['isActive'] && $card['deckType'] == 'resource') {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerForever('hide-token', $game) > 0;
                },
            ],
        ],
    ],
    'Tooth' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '8',
        'stamina' => '7',
        'name' => 'Tooth',
        'slots' => [],
        'onGetValidActions' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                unset($data['actCook']);
                unset($data['actCraft']);
                unset($data['actTrade']);
                unset($data['actInvestigateFire']);
            }
        },
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                $data['characterDamage'] = 2;
            }
        },
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'state' => ['playerTurn'],
                'name' => str_replace('${name}', 'Tooth', clienttranslate('Pet ${name}')),
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveHealth(1);
                    $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                        'count' => 1,
                        'character_resource' => 'health',
                    ]);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'];
                },
            ],
        ],
    ],
    'Sooha' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '8',
        'stamina' => '7',
        'name' => 'Sooha',
        'slots' => ['weapon', 'tool'],
        'onInvestigateFire' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['roll'] == 0) {
                $game->character->adjustActiveHealth(-1);
                $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                    'count' => 1,
                    'character_resource' => 'health',
                ]);
                $game->adjustResource('fkp', 2);
                $game->activeCharacterEventLog('received ${count} ${resource_type}', [
                    'count' => 2,
                    'resource_type' => 'fkp',
                ]);
            }
        },
        'onGetCharacterData' => function (Game $game, $char, &$data) {
            if ($char['id'] == $data['id'] && in_array('relaxation', $game->getUnlockedKnowledgeIds())) {
                $data['maxHealth'] = clamp($data['maxHealth'] + 2, 0, 10);
            }
        },
        'onUnlock' => function (Game $game, $char, &$data) {
            if ($data['id'] == 'relaxation') {
                $game->character->adjustHealth($char['id'], 10);
                $game->activeCharacterEventLog('gained ${count} ${character_resource}', [
                    'count' => clienttranslate('full'),
                    'character_resource' => 'health',
                ]);
            }
        },
        // TODO: Can spend health as if it was stamina
    ],
    'Samp' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '8',
        'stamina' => '7',
        'name' => 'Samp',
        'slots' => ['weapon', 'tool'],
    ],
    'Yurt' => [
        'type' => 'character',
        'expansion' => 'death-valley',
        'health' => '8',
        'stamina' => '7',
        'name' => 'Yurt',
        'slots' => ['weapon', 'tool'],
        'onCraftAfter' => function (Game $game, $unlock, &$data) {
            // Choose tribe member to gain 1hp or 1 stamina
        },
        'onGetActionCost' => function (Game $game, $char, &$data) {
            if ($data['action'] == 'actCraft') {
                $data['stamina'] = max($data['stamina'] - 2, 0);
            }
        },
        // Cannot be used with Atouk
    ],
];
