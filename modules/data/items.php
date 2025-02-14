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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Bow And Arrow',
        'itemType' => 'weapon',
        'damage' => 100,
        'range' => 2,
    ],
    'medical-hut' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
        'name' => 'Medical Hut',
        'itemType' => 'building',
    ],
    'bone-club' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Bone Club',
        'itemType' => 'weapon',
        'range' => 3,
        'damage' => 1,
    ],
    'bone-scythe' => [
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
        'name' => 'Camp Walls',
        'itemType' => 'building',
    ],
    'fire' => [
        'type' => 'game-piece',
        'name' => 'Fire',
    ],
    'hide-armor' => [
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Club',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
    ],
    'cooking-hut' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
        'name' => 'Cooking Hut',
        'itemType' => 'building',
    ],
    'carving-knife' => [
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'type' => 'back',
        'name' => 'Item Back',
    ],
    'sling-shot' => [
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Spear',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
    ],
    'sharp-stick' => [
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Sharp Stick',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
    ],
    'shelter' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
        'name' => 'Shelter',
        'itemType' => 'building',
    ],
    'rock-knife' => [
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Rock Knife',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
    ],
    'item-back-hindrance' => [
        'deck' => 'item',
        'type' => 'back',
        'expansion' => 'hindrance',
        'name' => 'Item Back Hindrance',
    ],
    'mortar-and-pestle' => [
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Mortar And Pestle',
        'itemType' => 'tool',
    ],
    'bandage' => [
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Bandage',
        'itemType' => 'tool',
    ],
    'skull-shield' => [
        'deck' => 'item',
        'craftingLevel' => 0,
        'count' => 1,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Skull Shield',
        'itemType' => 'tool',
        'character' => 'Faye',
    ],
    'cooking-pot' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Cooking Pot',
        'itemType' => 'tool',
    ],
    'bone-claws' => [
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
        'name' => 'Bone Claws',
        'itemType' => 'tool',
    ],
    'bone-flute' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Stock Hut',
        'itemType' => 'building',
    ],
    'whip' => [
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Whip',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
    ],
    'fire-stick' => [
        'deck' => 'item',
        'craftingLevel' => 0,
        'count' => 1,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Fire Stick',
        'itemType' => 'weapon',
        'range' => 0,
        'damage' => 1,
        'character' => 'Rex',
    ],
    'rock' => [
        'deck' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Rock',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 2,
        'onUse' => function (Game $game, $item) {
            $game->character->updateCharacterData($game->character->getActivateCharacter()['character_name'], function (&$data) use (
                $item
            ) {
                if ($item['id'] == $data['item_2_name']) {
                    $data['item_2_name'] = null;
                } else {
                    $data['item_2_name'] = null;
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
        'deck' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Bola',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
    ],
    'boomerang' => [
        'deck' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'type' => 'deck',
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
        'deck' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'type' => 'deck',
        'expansion' => 'hindrance',
        'name' => 'Stone Hammer',
        'itemType' => 'tool',
    ],
];
