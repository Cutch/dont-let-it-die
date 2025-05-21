<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
class DLD_BoardsData
{
    public function getData(): array
    {
        return [
            'character-board' => [],
            'track-normal' => [],
            'track-hard' => [],
            'instructions' => [],
            'board' => [],
            'knowledge-tree-easy' => [
                'track' => [
                    'warmth-1' => [
                        'upgradeType' => 'start',
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            return true;
                        },
                    ],
                    'warmth-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'warmth-3' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-2', $unlocks);
                        },
                    ],
                    'spices' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-3', $unlocks) && in_array('crafting-1', $unlocks);
                        },
                    ],
                    'cooking-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'cooking-2' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-1', $unlocks);
                        },
                    ],
                    'crafting-1' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-1', $unlocks);
                        },
                    ],
                    'crafting-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'fire-starter' => [
                        'upgradeType' => 'end',
                        'unlockCost' => 15,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks) && in_array('spices', $unlocks);
                        },
                    ],
                ],
            ],
            'knowledge-tree-normal' => [
                'track' => [
                    'warmth-1' => [
                        'upgradeType' => 'start',
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            return true;
                        },
                    ],
                    'warmth-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'warmth-3' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-2', $unlocks);
                        },
                    ],
                    'cooking-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'cooking-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-1', $unlocks);
                        },
                    ],
                    'relaxation' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-2', $unlocks);
                        },
                    ],
                    'crafting-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-1', $unlocks);
                        },
                    ],
                    'forage-1' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'forage-2' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-1', $unlocks);
                        },
                    ],
                    'hunt-1' => [
                        'unlockCost' => 6,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-2', $unlocks);
                        },
                    ],
                    'crafting-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'resource-1' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks);
                        },
                    ],
                    'crafting-3' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks);
                        },
                    ],
                    'resource-2' => [
                        'unlockCost' => 6,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-3', $unlocks);
                        },
                    ],
                    'fire-starter' => [
                        'upgradeType' => 'end',
                        'unlockCost' => 15,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('resource-2', $unlocks) && in_array('hunt-1', $unlocks);
                        },
                    ],
                ],
            ],
            'knowledge-tree-normal+' => [
                'track' => [
                    'crafting-1' => [
                        'upgradeType' => 'start',
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            return true;
                        },
                    ],
                    'resource-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'crafting-2' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('resource-1', $unlocks);
                        },
                    ],
                    'crafting-3' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks);
                        },
                    ],
                    'relaxation' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-3', $unlocks);
                        },
                    ],
                    'cooking-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'warmth-1' => [
                        'unlockCost' => 3,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('resource-1', $unlocks) && in_array('cooking-1', $unlocks);
                        },
                    ],
                    'forage-1' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'warmth-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-1', $unlocks);
                        },
                    ],
                    'forage-2' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-2', $unlocks);
                        },
                    ],
                    'warmth-3' => [
                        'unlockCost' => 4,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-2', $unlocks);
                        },
                    ],
                    'cooking-2' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-2', $unlocks);
                        },
                    ],
                    'hunt-1' => [
                        'unlockCost' => 6,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-2', $unlocks);
                        },
                    ],
                    'resource-2' => [
                        'unlockCost' => 6,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-1', $unlocks);
                        },
                    ],
                    'fire-starter' => [
                        'upgradeType' => 'end',
                        'unlockCost' => 15,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('resource-2', $unlocks) && in_array('hunt-1', $unlocks);
                        },
                    ],
                ],
            ],
            'knowledge-tree-hard' => [
                'track' => [
                    'warmth-1' => [
                        'upgradeType' => 'start',
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            return true;
                        },
                    ],
                    'crafting-1' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'resource-1' => [
                        'unlockCost' => 5,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-1', $unlocks);
                        },
                    ],
                    'crafting-2' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('resource-1', $unlocks);
                        },
                    ],
                    'relaxation' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks);
                        },
                    ],
                    'crafting-3' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('relaxation', $unlocks);
                        },
                    ],
                    'resource-2' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-3', $unlocks);
                        },
                    ],
                    'hunt-1' => [
                        'unlockCost' => 9,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('relaxation', $unlocks);
                        },
                    ],

                    'cooking-1' => [
                        'unlockCost' => 6,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-1', $unlocks);
                        },
                    ],
                    'warmth-2' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-1', $unlocks);
                        },
                    ],
                    'cooking-2' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('warmth-2', $unlocks);
                        },
                    ],
                    'warmth-3' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('cooking-2', $unlocks);
                        },
                    ],
                    'forage-1' => [
                        'unlockCost' => 7,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('crafting-2', $unlocks) && in_array('cooking-2', $unlocks);
                        },
                    ],
                    'forage-2' => [
                        'unlockCost' => 9,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-1', $unlocks);
                        },
                    ],
                    'fire-starter' => [
                        'upgradeType' => 'end',
                        'unlockCost' => 20,
                        'type' => 'unlock',
                        'requires' => function (Game $game, $obj) {
                            $unlocks = $game->getUnlockedKnowledgeIds(false);
                            return in_array('forage-2', $unlocks) && in_array('hunt-1', $unlocks);
                        },
                    ],
                ],
            ],
            'dice' => [],
        ];
    }
}
