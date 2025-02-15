<?php

use Bga\Games\DontLetItDie\Game;

$knowledgeTreeData = [
    'warmth-1' => [
        'name' => 'Warmth 1',
        'onUse' => function (Game $game, $char) {
            $game->character->updateAllCharacterData('max_stamina', 1);
        },
    ],
    'warmth-2' => [
        'name' => 'Warmth 2',
        'onUse' => function (Game $game, $char) {
            $game->character->updateAllCharacterData('max_stamina', 1);
        },
    ],
    'warmth-3' => [
        'name' => 'Warmth 3',
        'onUse' => function (Game $game, $char) {
            $game->character->updateAllCharacterData('max_stamina', 1);
        },
    ],
    'spices' => [
        'name' => 'Spices',
        'onEat' => function (Game $game, $char, &$data) {
            $data['health'] += 1;
        },
    ],
    'cooking-1' => [
        'name' => 'Cooking 1',
        'onGetActionSelectable' => function (Game $game, $obj, &$data) {
            if ($data['action'] == 'actEat') {
                array_push($data['selectable'], ['berry']);
            }
        },
    ],
    'cooking-2' => [
        'name' => 'Cooking 2',
        'onGetActionSelectable' => function (Game $game, $obj, &$data) {
            if ($data['action'] == 'actEat') {
                array_push($data['selectable'], ['meat', 'fish', 'dino-egg']);
            }
        },
    ],
    'crafting-1' => [
        'name' => 'Crafting 1',
    ],
    'crafting-2' => [
        'name' => 'Crafting 2',
    ],
    'crafting-3' => [
        'name' => 'Crafting 3',
    ],
    'fire-starter' => [
        'name' => 'Fire Starter',
    ],
    'resource-1' => [
        'name' => 'Resource 1',
        'onDraw' => function (Game $game, $obj, $card) {
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
        'name' => 'Resource 2',
        'onDraw' => function (Game $game, $obj, $card) {
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
        'name' => 'Hunt 1',
        'onDraw' => function (Game $game, $obj, $card) {
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
        'name' => 'Forage 1',
        'onDraw' => function (Game $game, $obj, $card) {
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
        'name' => 'Forage 2',
        'onDraw' => function (Game $game, $obj, $card) {
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
        'name' => 'Relaxation',
        'onUse' => function (Game $game, $char) {
            $game->character->updateAllCharacterData('max_health', 2);
        },
    ],
];
