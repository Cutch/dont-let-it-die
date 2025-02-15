<?php

use Bga\Games\DontLetItDie\Game;

$boardsData = [
    'character-board' => [],
    'track-normal' => [],
    'track-hard' => [],
    'instructions' => [],
    'board' => [],
    'knowledge-tree-easy' => [
        'track' => [
            'warmth-1' => [
                'requires' => function (Game $game, $obj) {
                    return true;
                },
            ],
            'warmth-2' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('warmth-1', $unlocks);
                },
            ],
            'warmth-3' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('warmth-2', $unlocks);
                },
            ],
            'spices' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('warmth-3', $unlocks) && in_array('crafting-1', $unlocks);
                },
            ],
            'cooking-1' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('warmth-1', $unlocks);
                },
            ],
            'cooking-2' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('cooking-1', $unlocks);
                },
            ],
            'crafting-1' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('cooking-1', $unlocks);
                },
            ],
            'crafting-2' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('crafting-1', $unlocks);
                },
            ],
            'fire-starter' => [
                'requires' => function (Game $game, $obj) {
                    $unlocks = $game->getUnlockedKnowledge();
                    return in_array('crafting-2', $unlocks) && in_array('spices', $unlocks);
                },
            ],
        ],
    ],
    'knowledge-tree-normal' => [
        'warmth-1' => [
            'requires' => function (Game $game, $obj) {
                return true;
            },
        ],
        'warmth-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-1', $unlocks);
            },
        ],
        'warmth-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-2', $unlocks);
            },
        ],
        'cooking-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-1', $unlocks);
            },
        ],
        'cooking-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-1', $unlocks);
            },
        ],
        'relaxation' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-2', $unlocks);
            },
        ],
        'crafting-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-1', $unlocks);
            },
        ],
        'forage-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-1', $unlocks);
            },
        ],
        'forage-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-1', $unlocks);
            },
        ],
        'hunt-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-2', $unlocks);
            },
        ],
        'crafting-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-1', $unlocks);
            },
        ],
        'resource-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks);
            },
        ],
        'crafting-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks);
            },
        ],
        'resource-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-3', $unlocks);
            },
        ],
        'fire-starter' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks) && in_array('hunt-1', $unlocks);
            },
        ],
    ],
    'knowledge-tree-normal+' => [
        'crafting-1' => [
            'requires' => function (Game $game, $obj) {
                return true;
            },
        ],
        'resource-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-1', $unlocks);
            },
        ],
        'crafting-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('resource-1', $unlocks);
            },
        ],
        'crafting-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks);
            },
        ],
        'relaxation' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-3', $unlocks);
            },
        ],
        'cooking-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-1', $unlocks);
            },
        ],
        'warmth-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('resource-1', $unlocks) && in_array('cooking-1', $unlocks);
            },
        ],
        'forage-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-1', $unlocks);
            },
        ],
        'warmth-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-1', $unlocks);
            },
        ],
        'forage-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-2', $unlocks);
            },
        ],
        'warmth-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-2', $unlocks);
            },
        ],
        'cooking-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-2', $unlocks);
            },
        ],
        'hunt-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-2', $unlocks);
            },
        ],
        'resource-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-1', $unlocks);
            },
        ],
        'fire-starter' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('resource-2', $unlocks) && in_array('hunt-1', $unlocks);
            },
        ],
    ],
    'knowledge-tree-hard' => [
        'warmth-1' => [
            'requires' => function (Game $game, $obj) {
                return true;
            },
        ],
        'crafting-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-1', $unlocks);
            },
        ],
        'resource-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-1', $unlocks);
            },
        ],
        'crafting-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('resource-1', $unlocks);
            },
        ],
        'relaxation' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks);
            },
        ],
        'crafting-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('relaxation', $unlocks);
            },
        ],
        'resource-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-3', $unlocks);
            },
        ],
        'hunt-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('relaxation', $unlocks);
            },
        ],

        'cooking-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-1', $unlocks);
            },
        ],
        'warmth-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-1', $unlocks);
            },
        ],
        'cooking-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('warmth-2', $unlocks);
            },
        ],
        'warmth-3' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('cooking-2', $unlocks);
            },
        ],
        'forage-1' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('crafting-2', $unlocks) && in_array('cooking-2', $unlocks);
            },
        ],
        'forage-2' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-1', $unlocks);
            },
        ],
        'fire-starter' => [
            'requires' => function (Game $game, $obj) {
                $unlocks = $game->getUnlockedKnowledge();
                return in_array('forage-2', $unlocks) && in_array('hunt-1', $unlocks);
            },
        ],
    ],
    'dice' => [],
];
