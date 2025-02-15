<?php

$tokensData = [
    '1-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '1-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    '2-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '2-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    '3-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '3-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    '4-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '4-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    '5-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '5-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    '6-token' => [
        'count' => 1,
        'type' => 'token',
    ],
    '6-unlocked' => [
        'count' => 1,
        'type' => 'token',
    ],
    'berry' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Berry',
        'cookable' => true,
        'actEat' => [
            'count' => 3,
            'health' => 1,
        ],
    ],
    'berry-cooked' => [
        'cooked' => 'berry',
        'type' => 'resource',
        'name' => 'Cooked Berry',
        'actEat' => [
            'count' => 2,
            'health' => 2,
        ],
    ],
    'bone' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Bone',
    ],
    'dino-egg' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Dino Egg',
        'cookable' => true,
        'actEat' => [
            'count' => 2,
            'health' => 1,
            'stamina' => 1,
            'expansion' => 'hindrance',
        ],
    ],
    'dino-egg-cooked' => [
        'cooked' => 'dino-egg',
        'type' => 'resource',
        'name' => 'Cooked Dino Egg',
        'actEat' => [
            'count' => 2,
            'health' => 3,
            'stamina' => 1,
            'expansion' => 'hindrance',
        ],
    ],
    'fish' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Fish',
        'cookable' => true,
        'actEat' => [
            'count' => 2,
            'health' => 1,
        ],
    ],
    'fish-cooked' => [
        'cooked' => 'fish',
        'type' => 'resource',
        'name' => 'Cooked Fish',
        'actEat' => [
            'count' => 1,
            'health' => 2,
        ],
    ],
    'meat' => [
        'count' => 8,
        'cookable' => true,
        'type' => 'resource',
        'name' => 'Meat',
        'actEat' => [
            'count' => 2,
            'health' => 1,
        ],
    ],
    'meat-cooked' => [
        'cooked' => 'meat',
        'type' => 'resource',
        'name' => 'Cooked Cooked',
        'actEat' => [
            'count' => 1,
            'health' => 2,
        ],
    ],
    'fkp' => [
        'count' => 40,
        'type' => 'resource',
        'name' => 'Fire Knowledge Point',
    ],
    'fkp-unlocked' => [
        'count' => 1,
        'type' => 'marker',
        'name' => 'Unlocked Fire Knowledge Point',
    ],
    'gem-1' => [
        'count' => 1,
        'type' => 'resource',
        'name' => 'Gem',
        'expansion' => 'hindrance',
    ],
    'gem-2' => [
        'count' => 1,
        'type' => 'resource',
        'name' => 'Gem',
        'expansion' => 'hindrance',
    ],
    'gem-3' => [
        'count' => 1,
        'type' => 'resource',
        'name' => 'Gem',
        'expansion' => 'hindrance',
    ],
    'fiber' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Fiber',
    ],
    'hide' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Hide',
    ],
    'herb' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Herb',
        'expansion' => 'hindrance',
    ],
    'rock' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Rock',
    ],
    'stew' => [
        'count' => 3,
        'type' => 'resource',
        'name' => 'Stew',
        'expansion' => 'hindrance',
    ],
    'trap' => [
        'count' => 2,
        'type' => 'resource',
        'name' => 'Trap',
        'expansion' => 'hindrance',
    ],
    'wood' => [
        'count' => 8,
        'type' => 'resource',
        'name' => 'Wood',
    ],
];
