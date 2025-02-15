<?php

use Bga\Games\DontLetItDie\Game;

if (!function_exists('getUsePerDay')) {
    function getUsePerDay($item, $game)
    {
        $dailyUseItems = $game->globals->get('dailyUseItems');
        return $dailyUseItems[$item['id']] || 0;
    }
    function usePerDay($item, $game)
    {
        $dailyUseItems = $game->globals->get('dailyUseItems');
        $dailyUseItems[$item['id']] = ($dailyUseItems[$item['id']] || 0) + 1;
        $game->globals->set('dailyUseItems', $dailyUseItems);
    }
}
$itemsData = [
    'bow-and-arrow' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => 'Bow And Arrow',
        'itemType' => 'weapon',
        'damage' => 100,
        'range' => 2,
    ],
    'medical-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Medical Hut',
        'itemType' => 'building',
    ],
    'bone-club' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => 'Bone Club',
        'itemType' => 'weapon',
        'range' => 3,
        'damage' => 1,
    ],
    'bone-scythe' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Bone Scythe',
        'itemType' => 'tool',
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'fiber') {
                $game->globals->set('fiber', $game->globals->get('fiber') + 1);
                $this->notify->all(
                    'usedItem',
                    clienttranslate('${player_name} - ${character_name} used ${item_name} and received one ${resource_type}'),
                    [
                        'item_name' => $item['name'],
                        'resource_type' => $card['resourceType'],
                    ]
                );
            }
        },
    ],
    'bag' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Bag',
        'itemType' => 'tool',
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'berry') {
                $game->globals->set('berry', $game->globals->get('berry') + 1);
                $this->notify->all(
                    'usedItem',
                    clienttranslate('${player_name} - ${character_name} used ${item_name} and received one ${resource_type}'),
                    [
                        'item_name' => $item['name'],
                        'resource_type' => $card['resourceType'],
                    ]
                );
            }
        },
    ],
    'bone-armor' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Bone Armor',
        'itemType' => 'tool',
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 2;
        },
    ],
    'camp-walls' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Camp Walls',
        'itemType' => 'building',
    ],
    'fire' => [
        'type' => 'game-piece',
        'name' => 'Fire',
    ],
    'hide-armor' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Hide Armor',
        'itemType' => 'tool',
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
    ],
    'knowledge-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Knowledge Hut',
        'itemType' => 'building',
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
    ],
    'skull' => [
        'type' => 'game-piece',
        'name' => 'Skull',
    ],
    'hatchet' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => 'Hatchet',
        'itemType' => 'tool',
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'wood') {
                $game->globals->set('wood', $game->globals->get('wood') + 1);
                $this->notify->all(
                    'usedItem',
                    clienttranslate('${player_name} - ${character_name} used ${item_name} and received one ${resource_type}'),
                    [
                        'item_name' => $item['name'],
                        'resource_type' => $card['resourceType'],
                    ]
                );
            }
        },
    ],
    'club' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'name' => 'Club',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
    ],
    'cooking-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Cooking Hut',
        'itemType' => 'building',
    ],
    'carving-knife' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Carving Knife',
        'itemType' => 'tool',
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'meat') {
                $game->globals->set('meat', $game->globals->get('meat') + 1);
                $this->notify->all(
                    'usedItem',
                    clienttranslate('${player_name} - ${character_name} used ${item_name} and received one ${resource_type}'),
                    [
                        'item_name' => $item['name'],
                        'resource_type' => $card['resourceType'],
                    ]
                );
            }
        },
    ],
    'item-back' => [
        'type' => 'item',
        'type' => 'back',
        'name' => 'Item Back',
    ],
    'sling-shot' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Sling Shot',
        'itemType' => 'weapon',
        'range' => 3,
        'damage' => 2,
        'requires' => function (Game $game, $item) {
            return $game->globals->get('rock') > 0;
        },
        'onUse' => function (Game $game, $item) {
            $game->globals->set('rock', $game->globals->get('rock') - 1);
            $this->notify->all(
                'usedItem',
                clienttranslate('${player_name} - ${character_name} used ${item_name} and lost one ${resource_type}'),
                [
                    'item_name' => $item['name'],
                    'resource_type' => 'rock',
                ]
            );
        },
    ],
    'pick-axe' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Pick Axe',
        'itemType' => 'tool',
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'rock') {
                $game->globals->set('rock', $game->globals->get('rock') + 1);
                $this->notify->all(
                    'usedItem',
                    clienttranslate('${player_name} - ${character_name} used ${item_name} and received one ${resource_type}'),
                    [
                        'item_name' => $item['name'],
                        'resource_type' => $card['resourceType'],
                    ]
                );
            }
        },
    ],
    'planning-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Planning Hut',
        'itemType' => 'building',
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 2;
        },
    ],
    'spear' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Spear',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
    ],
    'sharp-stick' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Sharp Stick',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
    ],
    'shelter' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Shelter',
        'itemType' => 'building',
    ],
    'rock-knife' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Rock Knife',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
    ],
    'item-back-hindrance' => [
        'type' => 'item',
        'type' => 'back',
        'expansion' => 'hindrance',
        'name' => 'Item Back Hindrance',
    ],
    'mortar-and-pestle' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Mortar And Pestle',
        'itemType' => 'tool',
    ],
    'bandage' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Bandage',
        'itemType' => 'tool',
    ],
    'skull-shield' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Skull Shield',
        'itemType' => 'tool',
        'character' => 'Faye',
    ],
    'cooking-pot' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Cooking Pot',
        'itemType' => 'tool',
    ],
    'bone-claws' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Bone Claws',
        'itemType' => 'tool',
    ],
    'bone-flute' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Bone Flute',
        'itemType' => 'tool',
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
    ],
    'stock-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Stock Hut',
        'itemType' => 'building',
    ],
    'whip' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Whip',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
    ],
    'fire-stick' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Fire Stick',
        'itemType' => 'weapon',
        'range' => 0,
        'damage' => 1,
        'character' => 'Rex',
    ],
    'rock' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Rock',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 2,
        'onUse' => function (Game $game, $item) {
            $game->character->updateCharacterData($game->character->getActivateCharacter()['character_name'], function (&$data) use (
                $item
            ) {
                if ($item['id'] == $data['item_2']) {
                    $data['item_2'] = null;
                } else {
                    $data['item_2'] = null;
                }
            });
            $this->notify->all(
                'usedItem',
                clienttranslate('${player_name} - ${character_name} used ${item_name} and lost their ${item_name}'),
                [
                    'item_name' => $item['name'],
                ]
            );
        },
    ],
    'bola' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Bola',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
    ],
    'boomerang' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Boomerang',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
    ],
    'stone-hammer' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Stone Hammer',
        'itemType' => 'tool',
    ],
];
