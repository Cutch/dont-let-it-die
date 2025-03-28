<?php

use Bga\Games\DontLetItDie\Game;

$knowledgeTreeData = [
    'warmth-1' => [
        'name' => clienttranslate('Warmth 1'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
            $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
        },
    ],
    'warmth-2' => [
        'name' => clienttranslate('Warmth 2'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
            $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
        },
    ],
    'warmth-3' => [
        'name' => clienttranslate('Warmth 3'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
            $data['stamina'] = clamp($data['stamina'], 0, $data['maxStamina']);
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
                array_push($data['selectable'], 'berry');
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
        'onUse' => function (Game $game, $obj) {
            $craftingLevel = $game->gameData->get('craftingLevel');
            $game->gameData->set('craftingLevel', max($craftingLevel, 1));
        },
    ],
    'crafting-2' => [
        'name' => clienttranslate('Crafting 2'),
        'onUse' => function (Game $game, $obj) {
            $craftingLevel = $game->gameData->get('craftingLevel');
            $game->gameData->set('craftingLevel', max($craftingLevel, 2));
        },
    ],
    'crafting-3' => [
        'name' => clienttranslate('Crafting 3'),
        'onUse' => function (Game $game, $obj) {
            $craftingLevel = $game->gameData->get('craftingLevel');
            $game->gameData->set('craftingLevel', max($craftingLevel, 3));
        },
    ],
    'fire-starter' => [
        'name' => clienttranslate('Fire Starter'),
        'onUse' => function (Game $game, $obj) {
            $game->notify->all('tree', clienttranslate('The tribe has discovered how to make fire!'));
            $game->win();
        },
    ],
    'resource-1' => [
        'name' => clienttranslate('Resource 1'),
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'rock') {
                if ($game->adjustResource('rock', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'resource-2' => [
        'name' => clienttranslate('Resource 2'),
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'wood') {
                if ($game->adjustResource('wood', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'hunt-1' => [
        'name' => clienttranslate('Hunt 1'),
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'meat') {
                if ($game->adjustResource('meat', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'forage-1' => [
        'name' => clienttranslate('Forage 1'),
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'berry') {
                if ($game->adjustResource('berry', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'forage-2' => [
        'name' => clienttranslate('Forage 2'),
        'onResolveDraw' => function (Game $game, $obj, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'fiber') {
                if ($game->adjustResource('fiber', 1) == 0) {
                    $game->notify->all('tree', clienttranslate('Received an additional ${resource_type} from ${action_name}'), [
                        'action_name' => $obj['name'],
                        'resource_type' => $card['resourceType'],
                    ]);
                }
            }
        },
    ],
    'relaxation' => [
        'name' => clienttranslate('Relaxation'),
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            $data['maxHealth'] += 2;
            $data['health'] = clamp($data['health'], 0, $data['maxHealth']);
        },
    ],
];
