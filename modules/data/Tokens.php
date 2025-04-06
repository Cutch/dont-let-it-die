<?php

use Bga\Games\DontLetItDie\Game;

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
        'name' => clientTranslate('Berry'),
        'cookable' => true,
        'actEat' => [
            'count' => 3,
            'health' => 1,
        ],
    ],
    'berry-cooked' => [
        'cooked' => 'berry',
        'type' => 'resource',
        'name' => clientTranslate('Cooked Berry'),
        'actEat' => [
            'count' => 2,
            'health' => 2,
        ],
        // 'actRevive' => [
        //     'count' => 6,
        // ],
    ],
    'bone' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Bone'),
    ],
    'dino-egg' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Dino Egg'),
        'cookable' => true,
        'expansion' => 'hindrance',
        'actEat' => [
            'count' => 2,
            'health' => 1,
            'stamina' => 1,
        ],
    ],
    'dino-egg-cooked' => [
        'cooked' => 'dino-egg',
        'type' => 'resource',
        'name' => clientTranslate('Cooked Dino Egg'),
        'expansion' => 'hindrance',
        'actEat' => [
            'count' => 2,
            'health' => 3,
            'stamina' => 1,
        ],
    ],
    'fish' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Fish'),
        'cookable' => true,
        'expansion' => 'mini-expansion',
        'actEat' => [
            'count' => 2,
            'health' => 1,
        ],
        'requires' => function (Game $game) {
            return in_array('Sig', $game->character->getAllCharacterIds());
        },
    ],
    'fish-cooked' => [
        'cooked' => 'fish',
        'type' => 'resource',
        'name' => clientTranslate('Cooked Fish'),
        'expansion' => 'mini-expansion',
        'actEat' => [
            'count' => 1,
            'health' => 2,
        ],
        'requires' => function (Game $game) {
            return in_array('Sig', $game->character->getAllCharacterIds());
        },
    ],
    'meat' => [
        'count' => 8,
        'cookable' => true,
        'type' => 'resource',
        'name' => clientTranslate('Meat'),
        'actEat' => [
            'count' => 2,
            'health' => 1,
        ],
    ],
    'meat-cooked' => [
        'cooked' => 'meat',
        'type' => 'resource',
        'name' => clientTranslate('Cooked Meat'),
        'actEat' => [
            'count' => 1,
            'health' => 2,
        ],
        'actRevive' => [
            'count' => 3,
        ],
    ],
    'fkp' => [
        'count' => 40,
        'type' => 'resource',
        'name' => clientTranslate('Fire Knowledge Point'),
    ],
    'fkp-unlocked' => [
        'count' => 1,
        'type' => 'marker',
        'name' => clientTranslate('Unlocked Fire Knowledge Point'),
    ],
    'gem-b' => [
        'count' => 1,
        'type' => 'resource',
        'name' => clientTranslate('Gem'),
        'expansion' => 'hindrance',
        'worth' => 2,
    ],
    'gem-p' => [
        'count' => 1,
        'type' => 'resource',
        'name' => clientTranslate('Gem'),
        'expansion' => 'hindrance',
        'worth' => 2,
    ],
    'gem-y' => [
        'count' => 1,
        'type' => 'resource',
        'name' => clientTranslate('Gem'),
        'expansion' => 'hindrance',
        'worth' => 2,
    ],
    'fiber' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Fiber'),
    ],
    'hide' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Hide'),
    ],
    'herb' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Herb'),
        'expansion' => 'hindrance',
    ],
    'rock' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Rock'),
    ],
    'stew' => [
        'count' => 3,
        'type' => 'resource',
        'name' => clientTranslate('Stew'),
        'expansion' => 'hindrance',
        'requires' => function (Game $game) {
            return in_array('Tiku', $game->character->getAllCharacterIds());
        },
    ],
    'trap' => [
        'count' => 2,
        'type' => 'resource',
        'name' => clientTranslate('Trap'),
        'expansion' => 'hindrance',
        'requires' => function (Game $game) {
            return in_array('Rex', $game->character->getAllCharacterIds());
        },
    ],
    'wood' => [
        'count' => 8,
        'type' => 'resource',
        'name' => clientTranslate('Wood'),
    ],
];
