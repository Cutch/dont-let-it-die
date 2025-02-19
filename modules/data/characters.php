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
        'type' => 'character',
        'health' => '7',
        'stamina' => '4',
        'name' => 'Gronk',
        'slots' => ['weapon', 'weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'name' => 'Gain 2 Stamina',
                'damage' => 2,
                'stamina' => 0,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char, $game);
                    $game->character->adjustActiveStamina(2);
                    $game->character->adjustActiveHealth(-2);
                    $game->activeCharacterEventLog('gained 2 stamina, lost 2 health');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char, $game) < 1;
                    }
                },
            ],
        ],
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['encounterHealth'] <= $data['characterDamage']) {
                $data['stamina'] += 2;
                $game->activeCharacterEventLog('gained 2 stamina');
            }
        },
    ],
    'Grub' => [
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
        'onDraw' => function (Game $game, $char, $deck) {
            if ($char['isActive'] && $deck == 'gather') {
                if ($game->adjustResource('fiber', 1) != 0) {
                    $game->activeCharacterEventLog('gained 1 fiber');
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
        // 'onInvestigateFire' => function (Game $game, &$data) {
        //     $data['escape'] = true;
        // },
        'skills' => [
            'skill1' => [
                'name' => 'Re-Roll Fire Die',
                'state' => ['reRoll'],
                'stamina' => 0,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char . 'investigateFire', $game);
                    $game->activeCharacterEventLog('is re-rolling ${active_character_name}\'s fire die', [
                        ...$char,
                        'active_character_name' => $game->character->getActivateCharacter(['character_name']),
                    ]);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return getUsePerDay($char . 'investigateFire', $game) < 1;
                },
            ],
            'skill2' => [
                'name' => 'Give 2 Stamina',
                'state' => ['playerTurn'],
                'stamina' => 0,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char . 'stamina', $game);
                    $game->activeCharacterEventLog('gave 2 stamina to ${active_character_name}', [
                        ...$char,
                        'active_character_name' => $game->character->getActivateCharacter(['character_name']),
                    ]);
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return !$char['isActive'] && getUsePerDay($char . 'stamina', $game) < 1;
                },
            ],
        ],
    ],
    'Cron' => [
        'type' => 'character',
        'health' => '5',
        'stamina' => '6',
        'name' => 'Cron',
        'startsWith' => 'hide-armor',
        'slots' => ['weapon', 'tool'],
        'skills' => [
            'skill1' => [
                'name' => 'Shuffle Discard Pile',
                'state' => ['playerTurn'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        // TODO: Choose a deck
                        // Shuffle it
                    }
                },
            ],
        ],
        'onEncounter' => function (Game $game, $char, $data) {
            if ($data['encounterHealth'] <= $data['characterDamage']) {
                $data['stamina'] += 1;
                $game->activeCharacterEventLog('game 1 stamina to ${active_character_name}', [
                    'active_character_name' => $game->character->getActivateCharacter(['character_name']),
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
                'name' => 'Discard Night Event',
                'state' => ['nightPhase'],
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char, $game);
                    $game->adjustResource('bone', -1);
                    // TODO: Interrupt and Discard current night event
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return getUsePerDay($char, $game) < 1 && $game->globals->get('bone') > 0;
                },
            ],
        ],
        'onGetActionSelectable' => function (Game $game, $char, &$data) {
            if ($data['action'] == 'actSpendFKP') {
                $data['selectable'] = ['fkp', 'bones'];
            }
        },
        'onRollDie' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data == 1) {
                if ($game->adjustResource('berry', 1) != 0) {
                    $game->activeCharacterEventLog('gained 1 berry');
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
        'type' => 'character',
        'health' => '8',
        'stamina' => '5',
        'name' => 'Ajax',
        'slots' => ['weapon', 'tool', 'tool'],
        'skills' => [
            'skill1' => [
                'name' => 'Gain 2 Health',
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char, $game);
                    $game->character->adjustActiveStamina(-2);
                    $game->character->adjustActiveHealth(2);
                    $game->activeCharacterEventLog('gained 2 health, lost 2 stamina');
                },
                'requires' => function (Game $game, $skill) {
                    // var_dump(json_encode(['here', $skill]));
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char, $game) < 1;
                    }
                },
            ],
        ],
        'onEncounter' => function (Game $game, $char, &$data) {
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
                'name' => 'Gain 1 Wood',
                'stamina' => 2,
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char, $game);
                    $game->character->adjustActiveStamina(-2);
                    $game->character->adjustActiveHealth(2);
                    $game->activeCharacterEventLog('gained 1 wood');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char, $game) < 1;
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
            if ($char['isActive'] && $data['action'] == 'actDrawHunt') {
                $data['stamina'] = 2;
            }
        },
        'skills' => [
            'skill1' => [
                'name' => 'Gain 1 Wood',
                'stamina' => 1,
                'onUse' => function (Game $game, $skill) {
                    $game->adjustResource('berry', -1);
                    $game->adjustResource('fiber', 1);
                    $game->activeCharacterEventLog('converted 1 raw berry to 1 fiber');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && $game->globals->get('berry') > 0;
                },
            ],
            'skill2' => [
                'name' => 'Heal 2',
                'stamina' => 0,
                'state' => ['postEncounter'],
                'onUse' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    usePerDay($char, $game);
                    $game->character->adjustActiveHealth(2);
                    $game->activeCharacterEventLog('healed by 2');
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    if ($char['isActive']) {
                        return getUsePerDay($char, $game) < 1;
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
            if ($char['isActive'] && $data['action'] == 'actInvestigateFIre' && getUsePerDay($char, $game) < 1) {
                $data['stamina'] = 0;
            }
        },
        'onInvestigateFire' => function (Game $game, $char, &$data) {
            if ($char['isActive']) {
                usePerDay($char, $game);
            }
        },
        'onEncounter' => function (Game $game, $char, &$data) {
            if ($char['isActive'] && $data['name'] == 'Nothing') {
                $game->adjustResource('fkp', 2);
                $game->activeCharacterEventLog('received 2 fkp');
            }
        },
        'onGetActionSelectable' => function (Game $game, $char, &$data) {
            if ($data['action'] == 'actEat') {
                $data['selectable'] = ['berry', 'berry-cooked'];
            }
        },
    ],
    'Sig' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Sig',
        'slots' => ['weapon', 'tool'],
    ],
    'Tara' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Tara',
        'slots' => ['weapon', 'tool'],
    ],
    'Nirv' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '3',
        'name' => 'Nirv',
        'slots' => ['weapon', 'tool'],
    ],
    'Oof' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '6',
        'stamina' => '6',
        'name' => 'Oof',
        'startsWith' => 'mortar-and-pestle',
        'slots' => ['weapon', 'tool'],
    ],
    'Rex' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '5',
        'stamina' => '7',
        'name' => 'Rex',
        'startsWith' => 'fire-stick',
        'slots' => ['weapon', 'tool'],
    ],
    'Mabe' => [
        'type' => 'character',
        'health' => '5',
        'stamina' => '5',
        'name' => 'Mabe',
        'slots' => ['weapon', 'tool'],
    ],
    'Nanuk' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Nanuk',
        'slots' => ['weapon', 'tool'],
    ],
    'Nibna' => [
        'type' => 'character',
        'health' => '7',
        'stamina' => '6',
        'name' => 'Nibna',
        'startsWith' => 'bag',
        'slots' => ['weapon', 'tool'],
    ],
    'Zeebo' => [
        'type' => 'character',
        'health' => '4',
        'stamina' => '6',
        'name' => 'Zeebo',
        'slots' => ['weapon', 'tool'],
    ],
    'Thunk' => [
        'type' => 'character',
        'health' => '6',
        'stamina' => '6',
        'name' => 'Thunk',
        'startsWith' => 'sharp-stick',
        'slots' => ['weapon', 'tool'],
    ],
    'Tiku' => [
        'expansion' => 'hindrance',
        'type' => 'character',
        'health' => '6',
        'stamina' => '5',
        'name' => 'Tiku',
        'slots' => ['weapon', 'tool'],
    ],
    'Vog' => [
        'type' => 'character',
        'health' => '10',
        'stamina' => '5',
        'name' => 'Vog',
        'slots' => ['weapon', 'tool'],
    ],
    'AlternateUpgradeTrack' => [
        'type' => 'instructions',
    ],
    'back-character' => [
        'type' => 'back',
    ],
    'back-character-hindrance' => [
        'type' => 'back',
        'expansion' => 'hindrance',
    ],
    'instructions-1' => [
        'type' => 'instructions',
    ],
    'instructions-2' => [
        'type' => 'instructions',
    ],
];
