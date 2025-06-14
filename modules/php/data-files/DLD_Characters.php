<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
use BgaUserException;
class DLD_CharactersData
{
    public function getData(): array
    {
        return [
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
                        'healthAsStamina' => true,
                        'perDay' => 1,
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'];
                        },
                        'onUse' => function (Game $game, $skill) {
                            usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            $game->character->adjustActiveStamina(2);
                            $game->eventLog(
                                clienttranslate(
                                    '${character_name} gained ${count_1} ${character_resource_1}, lost ${count_2} ${character_resource_2}'
                                ),
                                [
                                    'count_1' => 2,
                                    'character_resource_1' => clienttranslate('Stamina'),
                                    'count_2' => 2,
                                    'character_resource_2' => clienttranslate('Health'),
                                ]
                            );
                            return ['notify' => false];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive']) {
                                return getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
                            }
                        },
                    ],
                ],
                'onEncounterPost' => function (Game $game, $char, &$data) {
                    if ($char['isActive'] && $game->encounter->$game->encounter->killCheck($data)) {
                        $data['stamina'] += 2;
                        $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                            'count' => 2,
                            'character_resource' => clienttranslate('Stamina'),
                        ]);
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
                'onEncounterPre' => function (Game $game, $char, &$data) {
                    if ($char['isActive'] && !$data['noEscape']) {
                        $data['escape'] = true;
                    }
                },
                'onDraw' => function (Game $game, $char, &$data) {
                    $deck = $data['deck'];
                    if ($char['isActive'] && $deck == 'gather') {
                        if ($game->adjustResource('fiber', 1)['changed'] > 0) {
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
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
                    if ($data['characterId'] == $char['id']) {
                        $data['health'] *= 2;
                    }
                },
                'onGetEatData' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id']) {
                        $data['health'] *= 2;
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Re-Roll'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'perDay' => 1,
                        'random' => true,
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . $skill['id'];
                        },
                        'onInvestigateFire' => function (Game $game, $skill, &$data) {
                            if ($data['originalRoll'] < 3 && getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1) {
                                // If kara is not the character, and the roll is not the max
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $char = $game->character->getCharacterData($skill['characterId']);
                                $game->eventLog(clienttranslate('${character_name} is re-rolling ${active_character_name}\'s fire die'), [
                                    'character_name' => $game->getCharacterHTML($char['character_name']),
                                    'active_character_name' => $game->getCharacterHTML(
                                        $game->character->getTurnCharacter()['character_name']
                                    ),
                                ]);
                                $data['data']['roll'] = $game->rollFireDie($skill['name'], $char['character_name']);
                                usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] && getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Request 2 Stamina'),
                        'state' => ['playerTurn'],
                        'cancellable' => true,
                        'perDay' => 1,
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . 'stamina';
                        },
                        'onGetActionCost' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            if (
                                !$char['incapacitated'] &&
                                !$char['isActive'] &&
                                $data['action'] == 'actUseSkill' &&
                                $data['subAction'] == $skill['id']
                            ) {
                                $data['perDay'] = 1 - getUsePerDay($skill['getPerDayKey']($game, $skill), $game);
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
                                $game->eventLog(clienttranslate('${character_name} requested Kara use their stamina skill'), [
                                    'character_name' => $game->getCharacterHTML($turnChar['character_name']),
                                ]);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] &&
                                !$char['isActive'] &&
                                getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1 &&
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
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . 'stamina'; // This should match skill 2
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['args'] = ['Karaskill3'];
                            }
                        },
                        'onGetActionCost' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            $interruptState = $game->actInterrupt->getState('actUseSkill');
                            if (
                                !$char['incapacitated'] &&
                                !$char['isActive'] &&
                                $data['action'] == 'actUseSkill' &&
                                $data['subAction'] == $skill['id'] &&
                                $interruptState &&
                                array_key_exists('data', $interruptState) &&
                                $interruptState['data']['skillId'] == $skill['id']
                            ) {
                                $data['perDay'] = 1 - getUsePerDay($skill['getPerDayKey']($game, $skill), $game);
                                $data['name'] = str_replace(
                                    '${character_name}',
                                    $interruptState['data']['turnCharacter']['character_name'],
                                    clienttranslate('Give 2 Stamina to ${character_name}')
                                );
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            $turn_char = $game->character->getTurnCharacter();
                            usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            $game->character->adjustStamina($turn_char['character_name'], 2);
                            // $game->adjustResource($data['data']['card']['resourceType'], $data['data']['card']['count']);
                            $game->eventLog(clienttranslate('${character_name} gave ${turn_character_name} 2 stamina'), [
                                'character_name' => $game->getCharacterHTML($skill['characterId']),
                                'turn_character_name' => $turn_char['character_name'],
                            ]);
                            return ['notify' => false];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] &&
                                !$char['isActive'] &&
                                getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
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
                                $game->selectionStates->initiateDeckSelection($skill['id']);
                                return ['spendActionCost' => false, 'notify' => false];
                            }
                        },
                        'onDeckSelection' => function (Game $game, $skill, $data) {
                            $state = $game->selectionStates->getState('deckSelection');
                            if ($state['id'] == $skill['id']) {
                                $game->decks->shuffleInDiscard($data['deck'], false);
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                                $game->eventLog(clienttranslate('${character_name} shuffled the ${deck} deck using their skill'), [
                                    'deck' => $game->decks->getDeckName($data['deck']),
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                ],
                'onEncounterPost' => function (Game $game, $char, &$data) {
                    if ($game->encounter->killCheck($data)) {
                        $data['stamina'] += 1;

                        $game->eventLog(clienttranslate('${character_name} gave 1 stamina to ${active_character_name}'), [
                            'active_character_name' => $game->getCharacterHTML($game->character->getSubmittingCharacterId()),
                            'character_name' => $game->getCharacterHTML($char['character_name']),
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
                        'random' => true,
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . $skill['id'];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] &&
                                getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1 &&
                                $game->gameData->getResource('bone') > 0;
                        },
                        'onNightDrawCardPre' => function (Game $game, $skill, &$data) {
                            $card = $data['state']['card'];
                            $game->eventLog('${buttons}', [
                                'buttons' => notifyButtons([
                                    [
                                        'name' => $game->decks->getDeckName($card['deck']),
                                        'dataId' => $card['id'],
                                        'dataType' => 'night-event',
                                    ],
                                ]),
                            ]);
                            $game->actInterrupt->addSkillInterrupt($skill);
                            $data['notify'] = false;
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                usePerDay($skill['getPerDayKey']($game, $skill), $game);
                                $game->adjustResource('bone', -1);
                                $game->eventLog(clienttranslate('${character_name} re-draws the night event'));
                                $card = $game->decks->pickCard('night-event');
                                $game->setActiveNightCard($card['id']);
                                $data['data']['state']['card'] = $card;
                                $data['data']['notify'] = true;
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
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
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
                // If using an herb to clear a physical hindrance gain a health
                'onUseHerb' => function (Game $game, $char, &$data) {
                    if ($char['isActive']) {
                        $change = $game->character->adjustHealth($char['id'], 1);
                        $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                            'count' => $change,
                            'character_resource' => clienttranslate('Health'),
                        ]);
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'state' => ['playerTurn'],
                        'name' => clienttranslate('Trade/Take 1 Physical Hindrance'),
                        'stamina' => 2,
                        'onUse' => function (Game $game, $skill, &$data) {
                            $myCharId = $skill['characterId'];
                            $characters = array_values(
                                array_map(
                                    function ($d) {
                                        return $d['id'];
                                    },
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0;
                                    })
                                )
                            );
                            // Swap Faye first
                            $i = array_search($myCharId, $characters);
                            if ($i !== false && $i > 0) {
                                $temp = $characters[0];
                                $characters[0] = $characters[$i];
                                $characters[$i] = $temp;
                            }
                            $game->selectionStates->initiateHindranceSelection($skill['id'], $characters, $skill['name']);
                            $data['interrupt'] = true;
                            return ['notify' => false, 'nextState' => false, 'interrupt' => true, 'spendActionCost' => false];
                        },
                        'onHindranceSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('hindranceSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $myCount = 0;
                                $count = 0;
                                $myCharId = $skill['characterId'];
                                $otherCharId = null;
                                $card1 = null;
                                $card2 = null;
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
                                            if ($char['characterId'] == $myCharId) {
                                                $card1 = $card;
                                                $myCount++;
                                            } else {
                                                $card2 = $card;
                                                $otherCharId = $char['characterId'];
                                                $count++;
                                            }
                                        }
                                    }
                                }
                                if ($count < 1) {
                                    throw new BgaUserException(clienttranslate('1 hindrance must be taken/traded for'));
                                }
                                if ($count > 1) {
                                    throw new BgaUserException(clienttranslate('Only 1 hindrance can be taken'));
                                }
                                if ($myCount > 1) {
                                    throw new BgaUserException(clienttranslate('Only 1 hindrance can be traded'));
                                }
                                $game->character->updateCharacterData($myCharId, function (&$char) use ($card1, $card2, $game) {
                                    if ($card1) {
                                        $char['physicalHindrance'] = array_filter($char['physicalHindrance'], function ($d) use ($card1) {
                                            return $card1['id'] != $d['id'];
                                        });
                                    }
                                    if ($card2) {
                                        array_push($char['physicalHindrance'], $card2);
                                    }
                                });

                                $game->character->updateCharacterData($otherCharId, function (&$char) use ($card1, $card2, $game) {
                                    if ($card2) {
                                        $char['physicalHindrance'] = array_filter($char['physicalHindrance'], function ($d) use ($card2) {
                                            return $card2['id'] != $d['id'];
                                        });
                                    }
                                    if ($card1) {
                                        array_push($char['physicalHindrance'], $card1);
                                    }
                                });

                                // $game->character->updateAllCharacterData(function ($charData) use ($state) {
                                //     foreach ($state['characters'] as $char) {
                                //         if ($charData['characterId'] == $char) {
                                //             foreach ($char['physicalHindrance'] as $card) {
                                //                 if ($char['characterId'] == $myCharId) {
                                //                     $myCount++;
                                //                 } else {
                                //                     $count++;
                                //                 }
                                //             };
                                //         }
                                //     }
                                // });
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);

                                $data['nextState'] = 'playerTurn';
                            }
                        },
                        'onHindranceSelectionAfter' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('hindranceSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                if ($data['nextState'] != false) {
                                    // Check if have max physical hindrance
                                    $game->checkHindrance(false);
                                }
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] &&
                                sizeof(
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0;
                                    })
                                ) > 0;
                        },
                    ],
                ],
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
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . $skill['id'];
                        },
                        'onUse' => function (Game $game, $skill) {
                            usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            $game->character->adjustActiveHealth(2);
                            $game->eventLog(
                                clienttranslate(
                                    '${character_name} gained ${count_1} ${character_resource_1}, lost ${count_2} ${character_resource_2}'
                                ),
                                [
                                    'count_1' => 2,
                                    'character_resource_1' => clienttranslate('Health'),
                                    'count_2' => 2,
                                    'character_resource_2' => clienttranslate('Stamina'),
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]
                            );
                            return ['notify' => false];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive']) {
                                return getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
                            }
                        },
                    ],
                ],
                'onEncounterPre' => function (Game $game, $char, &$data) {
                    if ($char['isActive'] && $data['name'] == 'Beast') {
                        $data['encounterHealth'] = 0;
                        $data['willTakeDamage'] = 0;
                        $data['willReceiveMeat'] = 1;
                    }
                },
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
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . $skill['id'];
                        },
                        'onUse' => function (Game $game, $skill) {
                            usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            $game->adjustResource('wood', 1);
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                'count' => 1,
                                'resource_type' => 'wood',
                            ]);
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive']) {
                                return getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
                            }
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'name' => clienttranslate('Reduce Crafting Resources'),
                        'onCraft' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            $existingData = $game->actInterrupt->getState('actCraft');
                            if ($char['isActive'] && !$existingData) {
                                $game->selectionStates->initiateState(
                                    'resourceSelection',
                                    ['id' => $skill['id'], ...$data, 'title' => clienttranslate('Item Costs')],
                                    $char['id'],
                                    true,
                                    'playerTurn',
                                    null,
                                    true
                                );

                                $data['interrupt'] = true;
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && sizeof(array_filter($game->gameData->getResources())) > 0;
                        },
                        'onResourceSelection' => function (Game $game, $skill, &$data) {
                            $selectionState = $game->selectionStates->getState('resourceSelection');
                            if ($selectionState['id'] == $skill['id']) {
                                $state = $game->actInterrupt->getState('actCraft');
                                if ($state) {
                                    $maxChange = clamp(array_sum($state['data']['item']['cost']) - 2, 0, 2);
                                    if (array_key_exists($data['resourceType'], $state['data']['item']['cost'])) {
                                        $state['data']['item']['cost'][$data['resourceType']] = max(
                                            $state['data']['item']['cost'][$data['resourceType']] - $maxChange,
                                            0
                                        );
                                    }
                                    $game->actInterrupt->setState('actCraft', $state);
                                }
                            }
                        },
                        'onResourceSelectionOptions' => function (Game $game, $skill, &$resources) {
                            $state = $game->selectionStates->getState('resourceSelection');
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
                            $game->eventLog(clienttranslate('${character_name} converted 1 raw berry to 1 fiber'));
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] &&
                                $game->gameData->getResource('berry') > 0 &&
                                $game->gameData->getResourceLeft('fiber') != 0;
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        // Tested
                        'name' => clienttranslate('Heal 2'),
                        'stamina' => 0,
                        'perDay' => 1,
                        'state' => ['postEncounter'],
                        'getPerDayKey' => function (Game $game, $skill): string {
                            return $skill['characterId'] . $skill['id'];
                        },
                        'onUse' => function (Game $game, $skill) {
                            usePerDay($skill['getPerDayKey']($game, $skill), $game);
                            $game->character->adjustActiveHealth(2);
                            $game->eventLog(clienttranslate('${character_name} healed by 2'));
                            if (sizeof($game->actions->getValidActions()) == 0) {
                                $game->nextState('playerTurn');
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive'] && $char['health'] < $char['maxHealth']) {
                                $state = $game->gameData->get('encounterState');
                                if ($game->encounter->killCheck($state)) {
                                    return getUsePerDay($skill['getPerDayKey']($game, $skill), $game) < 1;
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
                'characterSkillName' => clienttranslate('Free Investigate Fire Action'),
                'getPerDayKey' => function (Game $game, $char): string {
                    return $char['id'];
                },
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
                        $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                            'count' => 2,
                            'resource_type' => 'fkp',
                        ]);
                    }
                },
                'onGetActionSelectable' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id'] && $data['action'] == 'actEat') {
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
                'onInvestigateFire' => function (Game $game, $char, &$data) {
                    $char = $game->character->getCharacterData($char['id']);
                    if ($char['isActive']) {
                        $roll2 = $game->rollFireDie(clienttranslate('Investigate Fire'), $char['character_name']);
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
                            $roll = $game->rollFireDie($skill['name']);
                            if ($roll > 0) {
                                if ($game->adjustResource('fish', $roll)['changed'] != 0) {
                                    $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                        'count' => $roll,
                                        'resource_type' => 'fish',
                                    ]);
                                }
                            }
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
                'characterSkillName' => clienttranslate('Gain 2 Stamina'),
                'getPerDayKey' => function (Game $game, $char): string {
                    return $char['id'] . 'skillonEat';
                },
                'onEat' => function (Game $game, $char, &$data) {
                    if (
                        $data['characterId'] == $char['id'] &&
                        str_contains($data['type'], 'berry') &&
                        getUsePerDay($char['getPerDayKey']($game, $char), $game) < 1
                    ) {
                        $data['stamina'] = 2;
                        usePerDay($char['getPerDayKey']($game, $char), $game);
                    }
                },
                'onGetEatData' => function (Game $game, $char, &$data) {
                    if (
                        $data['characterId'] == $char['id'] &&
                        str_contains($data['id'], 'berry') &&
                        getUsePerDay($char['getPerDayKey']($game, $char), $game) < 1
                    ) {
                        $data['stamina'] = 2;
                    }
                },
                'onDraw' => function (Game $game, $char, &$data) {
                    $card = $data['card'];
                    if ($char['isActive'] && $card['deck'] == 'forage') {
                        if ($game->adjustResource('berry', 1)['changed'] > 0) {
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                'count' => 1,
                                'resource_type' => 'berry',
                            ]);
                        }
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Give 1 Health'),
                        'state' => ['playerTurn'],
                        'stamina' => 2,
                        'onCharacterSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('characterSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $change = $game->character->adjustHealth($data['characterId'], 1);
                                $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                    'count' => $change,
                                    'character_resource' => clienttranslate('Health'),
                                    'character_name' => $game->getCharacterHTML($data['characterId']),
                                ]);
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            $characterIds = array_map(
                                function ($d) {
                                    return $d['id'];
                                },
                                array_filter($game->character->getAllCharacterData(), function ($character) {
                                    return !$character['incapacitated'];
                                })
                            );

                            $game->selectionStates->initiateState(
                                'characterSelection',
                                [
                                    'selectableCharacters' => array_values($characterIds),
                                    'id' => $skill['id'],
                                ],
                                $skill['characterId'],
                                true,
                                'playerTurn'
                            );
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
                'characterSkillName' => clienttranslate('Free Gather Action'),
                'getPerDayKey' => function (Game $game, $char): string {
                    return $char['id'] . 'skillonDraw';
                },
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
                        $game->eventLog(clienttranslate('All tribe members gained 1 health after the rival tribe event'));
                    }
                },
                'onEncounterPost' => function (Game $game, $char, &$data) {
                    if ($game->encounter->killCheck($data)) {
                        if (
                            sizeof(
                                array_filter($char['mentalHindrance'], function ($hindrance) {
                                    return $hindrance['id'] == 'hindrance_1_9';
                                })
                            ) == 0
                        ) {
                            $change = $game->character->adjustHealth($char['character_name'], 1);
                            $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                'count' => $change,
                                'character_resource' => clienttranslate('Health'),
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
                // Revive with 6 cooked berries
                'onGetActionSelectable' => function (Game $game, $char, &$data) {
                    if ($data['action'] == 'actRevive') {
                        array_push($data['selectable'], [...$game->data->getTokens()['berry-cooked'], 'actRevive' => ['count' => 6]]);
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Remove Physical Hindrance'),
                        'state' => ['playerTurn'],
                        'stamina' => 3,
                        'onUse' => function (Game $game, $skill, &$data) {
                            $game->selectionStates->initiateHindranceSelection(
                                $skill['id'],
                                array_map(
                                    function ($d) {
                                        return $d['id'];
                                    },
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0;
                                    })
                                )
                            );
                            $data['interrupt'] = true;
                            return ['notify' => false, 'nextState' => false, 'interrupt' => true, 'spendActionCost' => false];
                        },
                        'onHindranceSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('hindranceSelection');
                            if ($state && $state['id'] == $skill['id']) {
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
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                                $data['nextState'] = 'playerTurn';
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] &&
                                sizeof(
                                    array_filter($game->character->getAllCharacterData(false), function ($d) {
                                        return sizeof($d['physicalHindrance']) > 0;
                                    })
                                ) > 0;
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
                        'random' => true,
                        'onUse' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive']) {
                                $game->selectionStates->initiateDeckSelection(
                                    $skill['id'],
                                    array_intersect(['explore', 'gather', 'forage', 'harvest', 'hunt'], $game->decks->getAllDeckNames())
                                );
                                return ['spendActionCost' => false, 'notify' => false];
                            }
                        },
                        'onDeckSelection' => function (Game $game, $skill, $data) {
                            $state = $game->selectionStates->getState('deckSelection');
                            if ($state['id'] == $skill['id']) {
                                $game->markRandomness();
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                                $topCard = $game->decks->getDeck($data['deck'])->getCardOnTop('deck');
                                $card = $game->decks->getCard($topCard['type_arg']);
                                $game->cardDrawEvent($card, $data['deck'], ['partial' => true]);
                                $game->eventLog(clienttranslate('${character_name} viewed the top card ${buttons}'), [
                                    'deck' => $game->decks->getDeckName($data['deck']),
                                    'buttons' => notifyButtons([
                                        ['name' => $game->decks->getDeckName($card['deck']), 'dataId' => $card['id'], 'dataType' => 'card'],
                                    ]),
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
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
                            // Place or move trap
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive']) {
                                $tokens = $game->gameData->get('tokens') ?? [];
                                $count = sizeof(
                                    array_filter(array_keys($tokens ?? []), function ($deckName) use ($tokens) {
                                        return in_array('trap', $tokens[$deckName]);
                                    })
                                );
                                if ($count >= 2) {
                                    $decks = array_filter(
                                        array_intersect(
                                            ['explore', 'gather', 'forage', 'harvest', 'hunt'],
                                            $game->decks->getAllDeckNames()
                                        ),
                                        function ($deckName) use ($tokens) {
                                            return array_key_exists($deckName, $tokens) && in_array('trap', $tokens[$deckName]);
                                        }
                                    );
                                    $game->selectionStates->initiateDeckSelection(
                                        $skill['id'],
                                        $decks,
                                        clienttranslate('Remove Trap'),
                                        true,
                                        ['type' => 'move']
                                    );
                                } else {
                                    $decks = array_filter(
                                        array_intersect(
                                            ['explore', 'gather', 'forage', 'harvest', 'hunt'],
                                            $game->decks->getAllDeckNames()
                                        ),
                                        function ($deckName) use ($tokens) {
                                            return !array_key_exists($deckName, $tokens) || !in_array('trap', $tokens[$deckName]);
                                        }
                                    );
                                    $game->selectionStates->initiateDeckSelection(
                                        $skill['id'],
                                        $decks,
                                        clienttranslate('Place Trap'),
                                        true,
                                        ['type' => 'place']
                                    );
                                }
                                return ['spendActionCost' => false, 'notify' => false];
                            }
                        },
                        'onDeckSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('deckSelection');
                            if ($state['id'] == $skill['id']) {
                                $tokens = $game->gameData->get('tokens') ?? [];
                                if ($state['type'] == 'move') {
                                    $game->actions->spendActionCost('actUseSkill', $skill['id']);
                                    $tokens[$data['deck']] = array_values(
                                        array_filter($tokens[$data['deck']], function ($token) {
                                            return $token != 'trap';
                                        })
                                    );
                                    $game->gameData->set('tokens', $tokens);
                                    $game->eventLog(clienttranslate('${character_name} removed a trap from ${deck}'), [
                                        'deck' => $game->decks->getDeckName($data['deck']),
                                    ]);
                                    $decks = array_filter(
                                        array_intersect(
                                            ['explore', 'gather', 'forage', 'harvest', 'hunt'],
                                            $game->decks->getAllDeckNames()
                                        ),
                                        function ($deckName) use ($tokens) {
                                            return !array_key_exists($deckName, $tokens) || !in_array('trap', $tokens[$deckName]);
                                        }
                                    );
                                    $game->selectionStates->initiateDeckSelection(
                                        $skill['id'],
                                        $decks,
                                        clienttranslate('Place Trap'),
                                        false,
                                        ['type' => 'place']
                                    );
                                    // $data['nextState'] = false;
                                } else {
                                    if (!array_key_exists($data['deck'], $tokens)) {
                                        $tokens[$data['deck']] = [];
                                    }
                                    array_push($tokens[$data['deck']], 'trap');
                                    $game->gameData->set('tokens', $tokens);

                                    $game->eventLog(clienttranslate('${character_name} placed a trap on ${deck}'), [
                                        'deck' => $game->decks->getDeckName($data['deck']),
                                        'usedActionId' => 'actUseSkill',
                                        'usedActionName' => $skill['name'],
                                    ]);
                                }
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            $maxCount = $game->gameData->getResourceMax('trap');
                            return $char['isActive'] && $maxCount > 0;
                        },
                    ],
                    'skill3' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Trap It'),
                        'state' => ['interrupt'],
                        'interruptState' => ['drawCard'],
                        'onResolveDrawPre' => function (Game $game, $skill, &$data) {
                            $card = $data['card'];
                            $tokens = $game->gameData->get('tokens') ?? [];
                            $count = sizeof(
                                array_filter(array_keys($tokens ?? []), function ($deck) use ($tokens) {
                                    return in_array('trap', $tokens[$deck]);
                                })
                            );
                            if ($card['deckType'] == 'encounter' && $count > 0) {
                                $value = $game->rollFireDie(clienttranslate('Trap'), $game->character->getSubmittingCharacterId());
                                if ($value >= $card['health']) {
                                    $game->actInterrupt->addSkillInterrupt($skill);
                                }
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            return ['notify' => false];
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->decks->removeFromDeck($data['data']['deck'], $data['data']['card']['id']);
                                $data['data']['discard'] = true;
                                $tokens = $game->gameData->get('tokens') ?? [];
                                $tokens[$data['data']['deck']] = array_values(
                                    array_filter($tokens[$data['data']['deck']], function ($token) {
                                        return $token != 'trap';
                                    })
                                );
                                $game->gameData->set('tokens', $tokens);
                                $game->gameData->destroyResource('trap');
                                $game->eventLog(clienttranslate('${character_name} removed a ${name} from the game'), [
                                    ...$data['data']['card'],
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            $tokens = $game->gameData->get('tokens') ?? [];
                            $count = sizeof(
                                array_filter(array_keys($tokens ?? []), function ($deck) use ($tokens) {
                                    return in_array('trap', $tokens[$deck]);
                                })
                            );
                            return $char['isActive'] && $count > 0;
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
                                $game->selectionStates->initiateState('resourceSelection', ['id' => $skill['id']], $char['id'], true);
                                return ['spendActionCost' => false, 'notify' => false];
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && sizeof(array_filter($game->gameData->getResources())) > 0;
                        },
                        'onResourceSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('resourceSelection');
                            if ($state['id'] == $skill['id']) {
                                $game->actions->spendActionCost('actUseSkill', $skill['id']);
                                $game->adjustResource($data['resourceType'], 1);
                                $game->eventLog(clienttranslate('${character_name} copied 1 ${resource_type}'), [
                                    'resource_type' => $data['resourceType'],
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                            }
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'state' => ['playerTurn'],
                        'name' => clienttranslate('Roll Low Health Die'),
                        'perDay' => 1,
                        'random' => true,
                        'onUse' => function (Game $game, $skill) {
                            $skill['sendNotification']();
                            $value = $game->rollFireDie($skill['name'], $skill['characterId']);
                            usePerDay($skill['id'], $game);
                            if ($value == 0) {
                                $change = $game->character->getActiveStamina(2);
                                $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                    'count' => $change,
                                    'character_resource' => clienttranslate('Stamina'),
                                ]);
                            } else {
                                $game->adjustResource('fkp', 1);
                                $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
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
                // Double Health from meat
                'onEat' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id']) {
                        $data['health'] *= 2;
                    }
                },
                'onGetEatData' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id']) {
                        $data['health'] *= 2;
                    }
                },
                'onGetActionSelectable' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id'] && $data['action'] == 'actEat') {
                        $data['selectable'] = array_filter(
                            $data['selectable'],
                            function ($v, $k) {
                                return in_array($v['id'], ['meat', 'meat-cooked', 'fish', 'fish-cooked']);
                            },
                            ARRAY_FILTER_USE_BOTH
                        );
                    }
                },
                // 1 FKP for Danger Cards Killed
                'onEncounterPost' => function (Game $game, $char, &$data) {
                    if ($game->encounter->killCheck($data)) {
                        if ($game->adjustResource('fkp', 1)['changed'] > 0) {
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                'count' => 1,
                                'resource_type' => 'fkp',
                            ]);
                        }
                    }
                },
                // Choose Meat, Hide Bone
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Take Meat'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                            }
                        },
                        'onEncounter' => function (Game $game, $skill, &$data) {
                            if ($game->encounter->killCheck($data)) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Take Hide'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['data']['loot']['hide'] = $data['data']['willReceiveMeat'];
                                $data['data']['willReceiveMeat'] = 0;
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                            }
                        },
                        'onEncounter' => function (Game $game, $skill, &$data) {
                            if ($game->encounter->killCheck($data)) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                    'skill3' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Take Bone'),
                        'state' => ['interrupt'],
                        'interruptState' => ['resolveEncounter'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['data']['loot']['bone'] = $data['data']['willReceiveMeat'];
                                $data['data']['willReceiveMeat'] = 0;
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                            }
                        },
                        'onEncounter' => function (Game $game, $skill, &$data) {
                            if ($game->encounter->killCheck($data)) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                ],
            ],
            'Nibna' => [
                'type' => 'character',
                'health' => '7',
                'stamina' => '6',
                'name' => 'Nibna',
                'startsWith' => 'bag',
                'slots' => ['weapon', 'tool'],
                'characterSkillName' => clienttranslate('Double Healing'),
                'getPerDayKey' => function (Game $game, $char): string {
                    return $char['id'];
                },
                'onEat' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id'] && getUsePerDay($char['id'], $game) < 1) {
                        usePerDay($char['id'], $game);
                        $data['health'] *= 2;
                    }
                },
                'onGetEatData' => function (Game $game, $char, &$data) {
                    if ($data['characterId'] == $char['id'] && getUsePerDay($char['id'], $game) < 1) {
                        $data['health'] *= 2;
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'state' => ['playerTurn'],
                        'name' => clienttranslate('Heal everyone else for 1 hp'),
                        'health' => 2,
                        'healthAsStamina' => true,
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
                    $game->log('onDraw check', $char['isActive'], $card);
                    if ($char['isActive'] && $card['type'] == 'berry') {
                        if ($game->character->adjustActiveHealth(1) == 1) {
                            $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                'count' => 1,
                                'character_resource' => clienttranslate('Health'),
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
                                $game->eventLog(clienttranslate('${character_name} doubled the resources they found'), [
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                                return ['notify' => false];
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && $char['stamina'] >= $skill['stamina'];
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
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
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
                            $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
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
                            return $char['isActive'] &&
                                $game->gameData->getResourceLeft('stew') != 0 &&
                                sizeof(array_filter($game->gameData->getResources('berry', 'meat', 'herb'))) == 3;
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'state' => ['playerTurn'],
                        'name' => clienttranslate('Eat Stew'),
                        'stamina' => 0,
                        'global' => true,
                        'skillOptions' => [],
                        'onButtonSelection' => function (Game $game, $skill, $data) {
                            $state = $game->selectionStates->getState('buttonSelection');
                            if ($state && $state['id'] == $skill['id']) {
                                $char = $game->character->getSubmittingCharacter(true);
                                if ($data['buttonValue'] == 'char') {
                                    subtractPerDay($char['getPerDayKey']($game, $char), $game);
                                } else {
                                    $res = $game->character->getSkill($data['buttonValue'])['skill'];
                                    subtractPerDay($res['getPerDayKey']($game, $res), $game);
                                }
                                $game->adjustResource('stew', -1);
                                usePerDay($char['id'] . 'eatstew', $game);
                            }
                        },
                        'onUseSkill' => function (Game $game, $skill, &$data) {
                            if ($data['skillId'] == $skill['id']) {
                                $char = $game->character->getSubmittingCharacter();
                                // $skill['characterId'] = $game->character->getSubmittingCharacterId();
                                // $char = $game->character->getCharacterData($skill['characterId']);
                                // $game->actInterrupt->addSkillInterrupt($skill);
                                $skillOptions = [];
                                if (array_key_exists('getPerDayKey', $char)) {
                                    array_push($skillOptions, ['name' => $char['characterSkillName'], 'value' => 'char']);
                                }
                                foreach (array_values($char['skills']) as $s) {
                                    if (array_key_exists('getPerDayKey', $s)) {
                                        array_push($skillOptions, ['name' => $s['name'], 'value' => $s['id']]);
                                    }
                                }

                                $game->selectionStates->initiateState(
                                    'buttonSelection',
                                    [
                                        'items' => $skillOptions,
                                        'id' => $skill['id'],
                                    ],
                                    $char['id'],
                                    false
                                );
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getSubmittingCharacter(true);
                            // $game->log(
                            //     'stew requires',
                            //     array_key_exists('getPerDayKey', $char) ? $char['getPerDayKey']($game, $char) : null,
                            //     array_key_exists('getPerDayKey', $char),
                            //     array_key_exists('getPerDayKey', $char) && getUsePerDay($char['getPerDayKey']($game, $char), $game) > 0
                            // );
                            if (getUsePerDay($char['id'] . 'eatstew', $game) >= 1) {
                                return false;
                            }
                            if ($game->gameData->getResource('stew') == 0) {
                                return false;
                            }
                            if (array_key_exists('getPerDayKey', $char)) {
                                if (getUsePerDay($char['getPerDayKey']($game, $char), $game) > 0) {
                                    return true;
                                }
                            }
                            if (array_key_exists('skills', $char)) {
                                foreach (array_values($char['skills']) as $skill) {
                                    if (array_key_exists('getPerDayKey', $skill)) {
                                        if (getUsePerDay($skill['getPerDayKey']($game, $skill), $game) > 0) {
                                            return true;
                                        }
                                    }
                                }
                            }
                            return false;
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
                        'interruptState' => ['resolveEncounter'],
                        'health' => 0,
                        'onGetActionCost' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            $interruptState = $game->actInterrupt->getState('stResolveEncounter');
                            // $game->log('$interruptState', $interruptState);
                            if (
                                !$char['incapacitated'] &&
                                !$char['isActive'] &&
                                $data['action'] == 'actUseSkill' &&
                                $data['subAction'] == $skill['id'] &&
                                $interruptState &&
                                array_key_exists('skills', $interruptState) &&
                                sizeof(
                                    array_filter($interruptState['skills'], function ($d) {
                                        return $d['id'];
                                    })
                                ) > 0
                            ) {
                                $damageTaken = $game->encounter->countDamageTaken($interruptState['data']);
                                $data['health'] = $damageTaken;
                                // $interruptState['data']['willTakeDamage'] = 0;
                                // $game->actInterrupt->setState('stResolveEncounter', $interruptState);
                            }
                        },
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
                            $damageTaken = $game->encounter->countDamageTaken($data);
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            if (!$char['incapacitated'] && !$char['isActive'] && $damageTaken > 0 && !$data['damageStamina']) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            // $game->log('stResolveEncounter onInterrupt', $skill, $activatedSkill, $data);
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['data']['damagedCharacter'] = $skill['characterId'];
                                $damageTaken = $game->encounter->countDamageTaken($data['data']);
                                $change = $game->character->adjustHealth($skill['characterId'], -$damageTaken);
                                $game->eventLog(clienttranslate('${character_name} lost ${count} ${character_resource}'), [
                                    'count' => -$change,
                                    'character_resource' => clienttranslate('Health'),
                                    'character_name' => $game->getCharacterHTML($skill['characterId']),
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                                // $data['data']['willTakeDamage'] = 0;
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            return ['notify' => false];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] && !$char['isActive'];
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
                    if (str_contains($data['id'], 'crafting')) {
                        $data['unlockCost'] -= 2;
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Give Item'),
                        'state' => ['playerTurn'],
                        'stamina' => 1,
                        'onUse' => function (Game $game, $skill) {
                            $currentCharacter = $game->character->getTurnCharacter();
                            $characters = array_filter($game->character->getAllCharacterIds(), function ($character) use (
                                $currentCharacter
                            ) {
                                return $character != $currentCharacter['id'];
                            });

                            $items = array_map(function ($d) {
                                return ['name' => $d['id'], 'itemId' => $d['itemId']];
                            }, $currentCharacter['equipment']);

                            $game->selectionStates->initiateState(
                                'itemSelection',
                                [
                                    'items' => $items,
                                    'id' => $skill['id'],
                                ],
                                $currentCharacter['id'],
                                true
                            );
                            $game->selectionStates->initiateState(
                                'characterSelection',
                                [
                                    'selectableCharacters' => array_values($characters),
                                    'id' => $skill['id'],
                                ],
                                $currentCharacter['id'],
                                true
                            );
                            return ['notify' => false, 'nextState' => false];
                        },
                        'onCharacterSelection' => function (Game $game, $skill, &$data) {
                            $characterSelectionState = $game->selectionStates->getState('characterSelection');
                            if ($characterSelectionState && $characterSelectionState['id'] == $skill['id']) {
                                $itemSelectionState = $game->selectionStates->getState('itemSelection');
                                $characterId = $characterSelectionState['selectedCharacterId'];
                                $itemId = $itemSelectionState['selectedItemId'];
                                $itemsLookup = $game->gameData->getCreatedItems();
                                $itemName = $itemsLookup[$itemId];
                                $game->character->unequipEquipment($skill['characterId'], [$itemId]);
                                $itemObj = $game->data->getItems()[$itemName];

                                $game->eventLog(clienttranslate('${character_name_1} gave ${item_name} to ${character_name_2}'), [
                                    'character_name_1' => $game->getCharacterHTML($skill['characterId']),
                                    'character_name_2' => $game->getCharacterHTML($characterId),
                                    'item_name' => notifyTextButton([
                                        'name' => $itemObj['name'],
                                        'dataId' => $itemObj['id'],
                                        'dataType' => 'item',
                                    ]),
                                    'usedActionId' => 'actUseSkill',
                                    'usedActionName' => $skill['name'],
                                ]);
                                $game->character->equipAndValidateEquipment($characterId, $itemId);
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && sizeof($char['equipment']) > 0;
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
                // Assign Paranoid
                'onCharacterChoose' => function (Game $game, $char, &$data) {
                    if ($data['id'] == $char['id']) {
                        $game->character->addHindrance($char['id'], $game->decks->getCard('hindrance_1_4'));
                    }
                },
                // Investigate Fire Cost 2
                'onGetActionCostPre' => function (Game $game, $char, &$data) {
                    if ($char['isActive'] && $data['action'] == 'actInvestigateFire') {
                        $data['stamina'] = min($data['stamina'], 2);
                    }
                },
                // Guess is handled on the FE, if correct take double else 0
                'onInvestigateFire' => function (Game $game, $char, &$data) {
                    if ($char['isActive']) {
                        if ($data['originalRoll'] == $data['guess']) {
                            $data['roll'] *= 2;
                        } else {
                            $data['roll'] = 0;
                        }
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Double Healing'), // If Cooked
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn', 'eatSelection'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                                $data['data']['health'] *= 2;
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            $skill['sendNotification']();
                        },
                        'onEat' => function (Game $game, $skill, &$data) {
                            if ($game->character->getSubmittingCharacterId() == $skill['characterId']) {
                                if (str_contains($data['type'], '-cooked')) {
                                    $game->actInterrupt->addSkillInterrupt($skill);
                                }
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            return $game->character->getSubmittingCharacterId() == $skill['characterId'];
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'name' => clienttranslate('+1 Max Health'), // If Cooked
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn', 'eatSelection'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                                $game->character->updateCharacterData($skill['characterId'], function (&$data) {
                                    $data['modifiedMaxHealth'] += 1;
                                });
                            }
                        },
                        'onUse' => function (Game $game, $skill) {
                            $skill['sendNotification']();
                        },
                        'onEat' => function (Game $game, $skill, &$data) {
                            if ($game->character->getSubmittingCharacterId() == $skill['characterId']) {
                                if (str_contains($data['type'], '-cooked')) {
                                    $game->actInterrupt->addSkillInterrupt($skill);
                                }
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            return $game->character->getSubmittingCharacterId() == $skill['characterId'];
                        },
                    ],
                ],
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
                    $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                        'count' => 1,
                        'resource_type' => 'wood',
                    ]);
                },
                'onGetResourceMax' => function (Game $game, $char, &$data) {
                    if ($data['resourceType'] == 'hide') {
                        $data['maxCount'] -= getUsePerForever('hide-token', $game);
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Hold Hide'),
                        'state' => ['playerTurn'],
                        'stamina' => 2,
                        'onUse' => function (Game $game, $skill) {
                            usePerForever('hide-token', $game);
                            $game->adjustResource('hide', -1);
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'] && $game->gameData->getResourceLeft('hide') > 0;
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

                                if ($data['data']['willTakeDamage'] > 1) {
                                    $data['data']['willTakeDamage'] -= 1;
                                }
                            }
                        },
                        'onEncounterPre' => function (Game $game, $skill, &$data) {
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
                        'interruptState' => ['drawCard'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            // $game->log('$interruptState', $skill['id'], $activatedSkill['id']);
                            if ($skill['id'] == $activatedSkill['id']) {
                                subtractPerForever('hide-token', $game);

                                $card = $data['data']['card'];
                                if ($card['deckType'] == 'resource') {
                                    $game->adjustResource($card['resourceType'], 1);
                                }
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
                'onEncounterPre' => function (Game $game, $char, &$data) {
                    if ($char['isActive']) {
                        $data['characterDamage'] = 2;
                    }
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'state' => ['playerTurn'],
                        'name' => clienttranslate('Pet Tooth'),
                        'stamina' => 2,
                        'global' => true,
                        'onUse' => function (Game $game, $skill) {
                            $skill['sendNotification']();
                            // $game->character->adjustStamina($game->character->getTurnCharacterId(), -2);

                            $change = $game->character->adjustHealth($game->character->getTurnCharacterId(), 1);
                            $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                'count' => $change,
                                'character_resource' => clienttranslate('Health'),
                            ]);
                            // return ['spendActionCost' => false];
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId'], true);
                            return !$char['incapacitated'] && !$char['isActive'];
                        },
                    ],
                ],
            ],
            'Sooha' => [
                'type' => 'character',
                'expansion' => 'death-valley',
                'health' => '4',
                'stamina' => '5',
                'name' => 'Sooha',
                'slots' => ['weapon', 'tool'],
                'onInvestigateFire' => function (Game $game, $char, &$data) {
                    if ($char['isActive'] && $data['roll'] == 0) {
                        $game->character->adjustActiveHealth(-1);
                        $game->eventLog(clienttranslate('${character_name} lost ${count} ${character_resource}'), [
                            'count' => 1,
                            'character_resource' => clienttranslate('Health'),
                        ]);
                        $game->adjustResource('fkp', 2);
                        $game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                            'count' => 2,
                            'resource_type' => 'fkp',
                        ]);
                    }
                },
                'onGetCharacterData' => function (Game $game, $char, &$data) {
                    if ($char['id'] == $data['id'] && in_array('relaxation', $game->getUnlockedKnowledgeIds())) {
                        // $game->log('onGetCharacterData', $data['maxHealth'], clamp($data['maxHealth'] + 2, 0, 10));
                        $data['maxHealth'] = clamp($data['maxHealth'] + 2, 0, 10);
                    }
                },
                'onUnlock' => function (Game $game, $char, &$data) {
                    if ($data['id'] == 'relaxation') {
                        // $game->log('relaxation', $data, $char);
                        $game->character->adjustHealth($char['id'], 20);
                        $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                            'count' => clienttranslate('full'),
                            'character_resource' => clienttranslate('Health'),
                            'character_name' => $game->getCharacterHTML($char['character_name']),
                        ]);
                    }
                },
                'onGetActionCost' => function (Game $game, $char, &$data) {
                    if ($char['id'] == $game->character->getSubmittingCharacterId()) {
                        if (array_key_exists('stamina', $data) && $data['stamina'] > 0) {
                            $diff = $game->character->getActiveStamina() - $data['stamina'];
                            if ($diff < 0) {
                                $data['stamina'] += $diff;
                                $data['health'] = (array_key_exists('health', $data) ? $data['health'] : 0) - $diff;
                                $data['healthAsStamina'] = true;
                            }
                        }
                    }
                },
                // TODO: Can spend health as if it was stamina (Needs testing)
                'onSpendActionCost' => function (Game $game, $char, &$data) {
                    if ($char['id'] == $game->character->getSubmittingCharacterId()) {
                        if (array_key_exists('stamina', $data) && $data['stamina'] > 0) {
                            $diff = $game->character->getActiveStamina() - $data['stamina'];
                            if ($diff < 0) {
                                $data['stamina'] += $diff;
                                $data['health'] = (array_key_exists('health', $data) ? $data['health'] : 0) - $diff;
                                $data['healthAsStamina'] = true;
                            }
                        }
                    }
                },
            ],
            'Samp' => [
                'type' => 'character',
                'expansion' => 'death-valley',
                'health' => '7',
                'stamina' => '3',
                'name' => 'Samp',
                'slots' => ['weapon', 'tool'],
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'name' => '+1 ' . clientTranslate('Roll'),
                        'state' => ['interrupt'],
                        'interruptState' => ['playerTurn'],
                        'health' => 1,
                        'random' => true,
                        'onInvestigateFire' => function (Game $game, $skill, &$data) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            if ($char['isActive'] && $data['roll'] < 3) {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $data['data']['roll'] += 1;
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            $char = $game->character->getCharacterData($skill['characterId']);
                            return $char['isActive'];
                        },
                    ],
                ],
                'onEncounterPost' => function (Game $game, $char, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    if ($char['isActive'] && $damageTaken > 0) {
                        $data['stamina'] += 1;
                        $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                            'count' => 1,
                            'character_resource' => clienttranslate('Stamina'),
                        ]);
                    }
                },
                'onInvestigateFirePost' => function (Game $game, $char, &$data) {
                    if ($data['roll'] == 3) {
                        $game->character->updateCharacterData($char['id'], function (&$data) {
                            $data['modifiedMaxStamina'] += 1;
                        });
                    }
                },
            ],
            'Yurt' => [
                'type' => 'character',
                'expansion' => 'death-valley',
                'health' => '5',
                'stamina' => '5',
                'name' => 'Yurt',
                'slots' => ['weapon', 'tool'],
                'onCraftPost' => function (Game $game, $char, &$data) {
                    // Choose tribe member to gain 1 hp or 1 stamina
                    $characterIds = array_map(
                        function ($d) {
                            return $d['id'];
                        },
                        array_filter($game->character->getAllCharacterData(), function ($character) {
                            return !$character['incapacitated'];
                        })
                    );
                    $game->selectionStates->initiateState(
                        'characterSelection',
                        [
                            'selectableCharacters' => array_values($characterIds),
                            'id' => $char['id'] . 'craft',
                        ],
                        $char['id'],
                        false,
                        'playerTurn',
                        clienttranslate('Give Character 1 Health or Stamina')
                    );
                },
                'skills' => [
                    'skill1' => [
                        'type' => 'skill',
                        'state' => ['interrupt'],
                        'interruptState' => ['characterSelection'],
                        'name' => clienttranslate('Give 1 Health'),
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->character->setSubmittingCharacter('actUseSkill', $activatedSkill['id']);
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                                $state = $game->selectionStates->getState('characterSelection');
                                $change = $game->character->adjustHealth($state['selectedCharacterId'], 1);
                                $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                    'count' => $change,
                                    'character_resource' => clienttranslate('Health'),
                                    'character_name' => $game->getCharacterHTML($state['selectedCharacterId']),
                                ]);
                                $data['nextState'] = 'playerTurn';
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            return true;
                        },
                        'onCharacterSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('characterSelection');
                            if ($state && $state['id'] == $skill['characterId'] . 'craft') {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                    ],
                    'skill2' => [
                        'type' => 'skill',
                        'name' => clienttranslate('Give 1 Stamina'),
                        'state' => ['interrupt'],
                        'interruptState' => ['characterSelection'],
                        'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                            if ($skill['id'] == $activatedSkill['id']) {
                                $game->character->setSubmittingCharacter('actUseSkill', $activatedSkill['id']);
                                $this->clearCharacterSkills($data['skills'], $skill['characterId']);
                                $state = $game->selectionStates->getState('characterSelection');
                                $change = $game->character->adjustStamina($state['selectedCharacterId'], 1);
                                $game->eventLog(clienttranslate('${character_name} gained ${count} ${character_resource}'), [
                                    'count' => $change,
                                    'character_resource' => clienttranslate('Stamina'),
                                    'character_name' => $game->getCharacterHTML($state['selectedCharacterId']),
                                ]);
                                $data['nextState'] = 'playerTurn';
                            }
                        },
                        'requires' => function (Game $game, $skill) {
                            return true;
                        },
                        'onCharacterSelection' => function (Game $game, $skill, &$data) {
                            $state = $game->selectionStates->getState('characterSelection');
                            if ($state && $state['id'] == $skill['characterId'] . 'craft') {
                                $game->actInterrupt->addSkillInterrupt($skill);
                            }
                        },
                    ],
                ],
                'onGetActionCost' => function (Game $game, $char, &$data) {
                    if ($data['action'] == 'actCraft') {
                        $data['stamina'] = max($data['stamina'] - 2, 0);
                    }
                },
                // Cannot be used with Atouk, handled in choose
            ],
        ];
    }
    private function clearCharacterSkills(&$skills, $itemId)
    {
        array_walk($skills, function ($v, $k) use (&$skills, $itemId) {
            if ($v['characterId'] == $itemId) {
                unset($skills[$k]);
            }
        });
    }
}
