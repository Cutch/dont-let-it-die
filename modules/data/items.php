<?php

use Bga\Games\DontLetItDie\Game;

if (!function_exists('getUsePerDay')) {
    function getUsePerDay(string $itemId, $game)
    {
        $dailyUseItems = $game->gameData->getGlobals('dailyUseItems');
        return array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] : 0;
    }
    function usePerDay(string $itemId, $game)
    {
        $dailyUseItems = $game->gameData->getGlobals('dailyUseItems');
        $dailyUseItems[$itemId] = array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] + 1 : 1;
        $game->gameData->set('dailyUseItems', $dailyUseItems);
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
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'wood' => 1,
            'hide' => 1,
        ],
    ],
    'medical-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Medical Hut',
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 3,
            'bone' => 2,
        ],
    ],
    'bone-club' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => 'Bone Club',
        'itemType' => 'weapon',
        'range' => 3,
        'damage' => 1,
        'cost' => [
            'fiber' => 1,
            'rock' => 1,
            'wood' => 1,
            'bone' => 2,
        ],
    ],
    'bone-scythe' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Bone Scythe',
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'bone' => 2,
        ],
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'fiber') {
                $game->gameData->setResource('fiber', $game->gameData->getResource('fiber') + 1);
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
        'cost' => [
            'fiber' => 2,
            'hide' => 1,
        ],
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'berry') {
                $game->gameData->setResource('berry', $game->gameData->getResource('berry') + 1);
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
        'cost' => [
            'fiber' => 1,
            'bone' => 3,
            'rock' => 1,
        ],
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
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'bone' => 2,
        ],
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
        'cost' => [
            'fiber' => 1,
            'hide' => 2,
        ],
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
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
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
        'cost' => [
            'fiber' => 2,
            'wood' => 1,
            'rock' => 2,
        ],
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'wood') {
                $game->gameData->setResource('wood', $game->gameData->getResource('wood') + 1);
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
        'cost' => [
            'wood' => 1,
        ],
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            if ($data['character_name'] == $item['character_name']) {
                $data['maxStamina'] -= 1;
                $data['stamina'] = min($data['maxStamina'], $data['stamina']);
            }
        },
    ],
    'cooking-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Cooking Hut',
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
    ],
    'carving-knife' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Carving Knife',
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'bone' => 1,
        ],
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'meat') {
                $game->gameData->setResource('meat', $game->gameData->getResource('meat') + 1);
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
        'cost' => [
            'fiber' => 1,
            'hide' => 1,
            'wood' => 1,
        ],
        'requires' => function (Game $game, $item) {
            return $game->gameData->getResource('rock') > 0;
        },
        'onUse' => function (Game $game, $item) {
            $game->gameData->setResource('rock', $game->gameData->getResource('rock') - 1);
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
        'cost' => [
            'fiber' => 2,
            'wood' => 1,
            'rock' => 1,
        ],
        'onDraw' => function (Game $game, $item, $card) {
            if ($card['resourceType'] == 'rock') {
                $game->gameData->setResource('rock', $game->gameData->getResource('rock') + 1);
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
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
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
        'cost' => [
            'fiber' => 1,
            'rock' => 2,
            'wood' => 1,
        ],
    ],
    'sharp-stick' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Sharp Stick',
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
        'cost' => [
            'wood' => 1,
            'rock' => 1,
        ],
    ],
    'shelter' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => 'Shelter',
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
    ],
    'rock-knife' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Rock Knife',
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
        'cost' => [
            'fiber' => 1,
            'rock' => 2,
        ],
    ],
    'stone-hammer' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Stone Hammer',
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'wood' => 1,
        ],
    ],
    'mortar-and-pestle' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Mortar And Pestle',
        'itemType' => 'tool',
        'cost' => [
            'rock' => 3,
        ],
    ],
    'bandage' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => 'Bandage',
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 1,
            'hide' => 1,
            'herb' => 1,
        ],
    ],
    'skull-shield' => [
        'type' => 'item',
        'craftingLevel' => 4,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Skull Shield',
        'itemType' => 'tool',
        'character' => 'Faye',
        'cost' => [],
    ],
    'cooking-pot' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => 'Cooking Pot',
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'bone' => 2,
        ],
    ],
    'bone-claws' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => 'Bone Claws',
        'itemType' => 'tool',
        'cost' => [
            'rock' => 2,
            'bone' => 2,
        ],
    ],
    'bone-flute' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Bone Flute',
        'itemType' => 'tool',
        'cost' => [
            'hide' => 1,
            'bone' => 2,
        ],
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
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
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
        'cost' => [
            'fiber' => 3,
            'hide' => 2,
        ],
    ],
    'fire-stick' => [
        'type' => 'item',
        'craftingLevel' => 4,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => 'Fire Stick',
        'itemType' => 'weapon',
        'range' => 0,
        'damage' => 1,
        'character' => 'Rex',
        'cost' => [],
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
        'cost' => [
            'rock' => 1,
        ],
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
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
        ],
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
        'cost' => [
            'rock' => 2,
            'hide' => 2,
            'wood' => 1,
        ],
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
    ],
];
