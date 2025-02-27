<?php

use Bga\Games\DontLetItDie\Game;

$knowledgeTreeData = [
    'warmth-1' => [
        'name' => clienttranslate('Warmth 1'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] += 1;
            $data['stamina'] = min($data['maxStamina'], $data['stamina']);
        },
    ],
    'warmth-2' => [
        'name' => clienttranslate('Warmth 2'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] += 1;
            $data['stamina'] = min($data['maxStamina'], $data['stamina']);
        },
    ],
    'warmth-3' => [
        'name' => clienttranslate('Warmth 3'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] += 1;
            $data['stamina'] = min($data['maxStamina'], $data['stamina']);
        },
    ],
    'spices' => [
        'name' => clienttranslate('Spices'),
        'onEat' => function (Game $game, $char, &$data) {
            $data['health'] += 1;
        },
        'onGetEatData' => function (Game $game, $char, &$data) {
            $data['health'] += 1;
        },
    ],
    'cooking-1' => [
        'name' => clienttranslate('Cooking 1'),
        'onGetActionSelectable' => function (Game $game, $obj, &$data) {
            if ($data['action'] == 'actCook') {
                array_push($data['selectable'], ['berry']);
            }
        },
    ],
    'cooking-2' => [
        'name' => clienttranslate('Cooking 2'),
        'onGetActionSelectable' => function (Game $game, $obj, &$data) {
            if ($data['action'] == 'actCook') {
                array_push($data['selectable'], 'meat', 'fish', 'dino-egg');
            }
        },
    ],
    'crafting-1' => [
        'name' => clienttranslate('Crafting 1'),
    ],
    'crafting-2' => [
        'name' => clienttranslate('Crafting 2'),
    ],
    'crafting-3' => [
        'name' => clienttranslate('Crafting 3'),
    ],
    'fire-starter' => [
        'name' => clienttranslate('Fire Starter'),
        'onUse' => function (Game $game, $obj) {
            $this->notify->all('tree', clienttranslate('The tribe has discovered how to make fire!'));
            $game->win();
        },
    ],
    'resource-1' => [
        'name' => clienttranslate('Resource 1'),
        'onDraw' => function (Game $game, $obj, $deck, $card) {
            if ($card['resourceType'] == 'rock') {
                if ($game->adjustResource('rock', 1) == 0) {
                    $this->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'resource-2' => [
        'name' => clienttranslate('Resource 2'),
        'onDraw' => function (Game $game, $obj, $deck, $card) {
            if ($card['resourceType'] == 'wood') {
                if ($game->adjustResource('wood', 1) == 0) {
                    $this->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'hunt-1' => [
        'name' => clienttranslate('Hunt 1'),
        'onDraw' => function (Game $game, $obj, $deck, $card) {
            if ($card['resourceType'] == 'meat') {
                if ($game->adjustResource('meat', 1) == 0) {
                    $this->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'forage-1' => [
        'name' => clienttranslate('Forage 1'),
        'onDraw' => function (Game $game, $obj, $deck, $card) {
            if ($card['resourceType'] == 'berry') {
                if ($game->adjustResource('berry', 1) == 0) {
                    $this->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'forage-2' => [
        'name' => clienttranslate('Forage 2'),
        'onDraw' => function (Game $game, $obj, $deck, $card) {
            if ($card['resourceType'] == 'fiber') {
                if ($game->adjustResource('fiber', 1) == 0) {
                    $this->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'relaxation' => [
        'name' => clienttranslate('Relaxation'),
        'onUse' => function (Game $game, $char) {
            $game->character->updateAllCharacterData('maxHealth', 2);
        },
    ],
];
