<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

class Actions
{
    public $actions;
    public function __construct(Game $game)
    {
        $this->actions = [
            'actInvestigateFire' => [
                'stamina' => 3,
            ],
            'actCraft' => [
                'stamina' => 3,
            ],
            'actDrawGather' => [
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'stamina' => 3,
            ],
            'actDrawHunt' => [
                'stamina' => 3,
            ],
            'actSpendFKP' => [
                'stamina' => 0,
                'requires' => function () use ($game) {
                    $fkp = $game->globals->get('fkp');
                    return $fkp > 0;
                },
            ],
            'actAddWood' => [
                'stamina' => 0,
                'requires' => function () use ($game) {
                    $wood = $game->globals->get('wood');
                    return $wood > 0;
                },
            ],
            'actEat' => [
                'stamina' => 0,
                'requires' => function () use ($game) {
                    $variables = $game->globals->getAll();
                    $array = array_filter(
                        $game->data->tokens,
                        function ($v, $k) use ($variables) {
                            if (isset($variables[$k])) {
                                return array_key_exists('actEat', $v) && $v['actEat']['count'] <= $variables[$k];
                            }
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    return sizeof($array) > 0;
                },
            ],
            'actCook' => [
                'stamina' => 1,
                'requires' => function () use ($game) {
                    $array = array_filter(
                        $game->data->tokens,
                        function ($v, $k) {
                            return array_key_exists('cookable', $v);
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count >= 3;
                },
            ],
            'actTrade' => [
                'stamina' => 1,
                'requires' => function () use ($game) {
                    $array = array_filter(
                        $game->data->tokens,
                        function ($v, $k) {
                            return $v['type'] === 'resource';
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count >= 3;
                },
            ],
        ];
    }
}
