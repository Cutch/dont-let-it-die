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
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Tame the beast'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->activeCharacterEventLog('obtained a wolf pup');
                    // TODO: Add to character, maybe a little icon
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('Ask Wolfy to Fetch'),
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    if ($game->rollFireDie($skill['characterId']) == 0) {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 1, 'resource_type' => 'wood']);
                    } else {
                        $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 1, 'resource_type' => 'rock']);
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
        'health' => 1,
        'health' => 3,
        'name' => clienttranslate('Snapping Turtle'),
        'skills' => [
            'skill1' => [
                'type' => 'skill',
                'name' => clienttranslate('Make a snappy comeback'),
                'state' => ['dayEvent'],
                'health' => 1,
                'onUse' => function (Game $game, $skill) {
                    // TODO: Add 1 / 3 fight
                    $game->gameData->set('state', ['card' => $skill['card'], 'deck' => 'day-event']);
                    $game->gamestate->nextState('resolveEncounter');
                    return ['notify' => false];
                },
            ],
            'skill2' => [
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
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($skill['characterId']);

                    if ($char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'] . $skill['id'], $game);
                        $game->log('onInterrupt', $data);
                        $data['data']['willTakeDamage'] = 0;

                        $game->activeCharacterEventLog('used ${item_name} to block the damage', [
                            'item_name' => clienttranslate('Shell Shield'),
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
                    $game->character->adjustActiveHealth(-2);
                    $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 3, 'resource_type' => 'wood']);
                    return ['notify' => false];
                },
            ],
            'skill2' => [
                'type' => 'skill',
                'name' => clienttranslate('When in doubt, throw a rock'),
                'state' => ['dayEvent'],
                'cost' => ['rock' => 1],
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('rock', -1);
                    if ($game->rollFireDie($skill['characterId']) == 0) {
                        usePerDay($skill['id'], $game);
                        if (getUsePerDay($skill['id'], $game) == 2) {
                            $game->activeCharacterEventLog('received ${count} ${resource_type}', ['count' => 3, 'resource_type' => 'meat']);
                        } else {
                            $game->activeCharacterEventLog('hit the beast');
                        }
                    } else {
                        $game->activeCharacterEventLog('missed the beast');
                    }
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return $game->gameData->getResource('rock') >= 1 && getUsePerDay($skill['id'], $game) < 2;
                },
            ],
            'skill3' => [
                'type' => 'skill',
                'name' => clienttranslate('Give Up'),
                'state' => ['dayEvent'],
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveHealth(-2);
                    return ['notify' => false];
                },
                'requires' => function (Game $game, $skill) {
                    return getUsePerDay($skill['id'], $game) < 2;
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
                    $game->character->updateCharacterData($game->getActivePlayerId(), function (&$data) {
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
                        'characters' => $characters,
                        'cancellable' => false,
                        'id' => $skill['id'],
                    ]);
                    $data['interrupt'] = true;
                    $game->gamestate->nextState('characterSelection');
                    return ['notify' => false];
                },
                'onCharacterSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('characterSelectionState');
                    if ($state && $state['id'] == $skill['id']) {
                        $data['nextState'] = 'morningPhase';
                        $game->character->adjustHealth($data['characterId'], -1);
                        $game->activeCharacterEventLog('lost ${count} ${character_resource}', [
                            'count' => 1,
                            'character_resource' => 'health',
                            'character_name' => $data['characterId'],
                        ]);
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
                        'characters' => $characters,
                        'cancellable' => false,
                        'id' => $skill['id'],
                    ]);
                    $data['interrupt'] = true;
                    $game->gamestate->nextState('characterSelection');
                    return ['notify' => false];
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
                        $data['nextState'] = 'morningPhase';
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
                    if ($game->rollFireDie($currentCharacter) != 0) {
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
                        $game->rollFireDie($currentCharacter) +
                            $game->rollFireDie($currentCharacter) +
                            $game->rollFireDie($currentCharacter) >=
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
                'onUse' => function (Game $game, $skill) {
                    $game->character->adjustActiveHealth(-1);
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
                    if ($game->rollFireDie($currentCharacter) == 0) {
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
                'stamina' => 1,
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
        'type' => 'deck',
    ],
    'hindrance_1_1' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_10' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_11' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_2' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_3' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_4' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_5' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_6' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_7' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_8' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'hindrance_1_9' => [
        'deck' => 'mental-hindrance',
        'type' => 'deck',
    ],
    'physical-hindrance-back' => [
        'deck' => 'physical-hindrance',
        'type' => 'back',
        'expansion' => 'hindrance',
    ],
    'mental-hindrance-back' => [
        'deck' => 'mental-hindrance',
        'type' => 'back',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_0' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_1' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_10' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_11' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_2' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_3' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_4' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_5' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_6' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_7' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_8' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
    'hindrance_1_9' => [
        'deck' => 'physical-hindrance',
        'type' => 'deck',
        'expansion' => 'hindrance',
    ],
];
