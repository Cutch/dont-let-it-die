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
        'onEncounter' => function (Game $game, $unlock, &$data) {
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
                'name' => clienttranslate('Use Flint'),
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'perDay' => 1,
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
                        $data['data']['roll'] = $data['data']['roll'] + $game->rollFireDie($char) - 1;
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
    ],
    '11-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Haggle'),
        'unlockCost' => 4,
        // 'onGetTradeRatio' => function (Game $game, $item, &$data) {
        //     $data['ratio'] = 2;
        // },
    ],
    '11-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Trade Routes'),
        'unlockCost' => 5,
        'onGetActionCost' => function (Game $game, $unlock, &$data) {
            if ($data['action'] == 'actTrade') {
                $data['stamina'] = 0;
            }
        },
    ],
    '12-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Planning'),
        'unlockCost' => 6,
    ],
    '12-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Focus'),
        'unlockCost' => 6,
    ],
    '13-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Bone Efficiency'),
        'unlockCost' => 4,
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'hide') {
                if ($game->adjustResource('hide', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
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
                if ($game->adjustResource('bone', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
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
                if ($game->adjustResource('herb', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
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
                if ($game->adjustResource('dino-egg', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
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
    ],
    '15-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Recycling'),
        'unlockCost' => 4,
    ],
    '16-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Tinder'),
        'unlockCost' => 6,
    ],
    '16-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Fire Watch'),
        'unlockCost' => 8,
    ],
    '2-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Smoke Cover'),
        'unlockCost' => 4,
    ],
    '2-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Revenge'),
        'unlockCost' => 8,
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
    ],
    '4-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('First Aid'),
        'unlockCost' => 6,
        'onGetReviveCost' => function (Game $game, $unlock, &$data) {
            $data['cost'] = 1;
        },
    ],
    '5-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Controlled Burn'),
        'unlockCost' => 6,
    ],
    '5-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Map Making'),
        'unlockCost' => 6,
    ],
    '6-A' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Berry Farming'),
        'unlockCost' => 5,
    ],
    '6-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Meditation'),
        'unlockCost' => 5,
        'onUse' => function (Game $game, $unlock) {
            $game->character->adjustAllHealth('10');
            $this->notify->all('morningPhase', clienttranslate('Everyone gained ${count} ${character_resource}'), [
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
    ],
    '7-B' => [
        'deck' => 'upgrade',
        'type' => 'deck',
        'name' => clienttranslate('Clarity'),
        'unlockCost' => 7,
        'onUse' => function (Game $game, $unlock) {
            $chars = $game->character->getAllCharacterData();
            foreach ($chars as $i => $char) {
                foreach ($char['mentalHindrance'] as $i => $card) {
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
        // On morning select the next character to go first
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
        'onEncounter' => function (Game $game, $unlock, &$data) {
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
        'onEncounter' => function (Game $game, $unlock, &$data) {
            if ($data['characterDamage'] > 0) {
                $data['characterDamage'] += 1;
            }
        },
    ],
];
