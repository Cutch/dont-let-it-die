<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
class DLD_TokensData
{
    public function getData(): array
    {
        return [
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
                    'stamina' => 1,
                ],
                'requires' => function (Game $game) {
                    return in_array('Sig', $game->character->getAllCharacterIds());
                },
                'onGetActionSelectable' => function (Game $game, $token, &$data) {
                    if ($data['action'] == 'actEat' && getUsePerDay($data['characterId'] . 'fish', $game) >= 1) {
                        $data['selectable'] = array_values(
                            array_filter(
                                $data['selectable'],
                                function ($v, $k) {
                                    return $v['id'] != 'fish';
                                },
                                ARRAY_FILTER_USE_BOTH
                            )
                        );
                    }
                },
                'onEat' => function (Game $game, $token, &$data) {
                    if ($data['type'] == $token['id']) {
                        usePerDay($data['characterId'] . 'fish', $game);
                    }
                },
            ],
            'fish-cooked' => [
                'cooked' => 'fish',
                'type' => 'resource',
                'name' => clientTranslate('Cooked Fish'),
                'expansion' => 'mini-expansion',
                'actEat' => [
                    'count' => 1,
                    'stamina' => 2,
                ],
                'requires' => function (Game $game) {
                    return in_array('Sig', $game->character->getAllCharacterIds());
                },
                'onGetActionSelectable' => function (Game $game, $token, &$data) {
                    if ($data['action'] == 'actEat' && getUsePerDay($data['characterId'] . 'fish', $game) >= 1) {
                        $data['selectable'] = array_values(
                            array_filter(
                                $data['selectable'],
                                function ($v, $k) {
                                    return $v['id'] != 'fish-cooked';
                                },
                                ARRAY_FILTER_USE_BOTH
                            )
                        );
                    }
                },
                'onEat' => function (Game $game, $token, &$data) {
                    if ($data['type'] == $token['id']) {
                        usePerDay($data['characterId'] . 'fish', $game);
                    }
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
    }
}
