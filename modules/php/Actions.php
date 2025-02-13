<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class Actions
{
    public $playerActions;
    public $encounterActions;
    private Game $game;
    public function __construct(Game $game)
    {
        $this->playerActions = [
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
            'actDrawGather' => [
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'stamina' => 3,
                'requires' => function () use ($game) {
                    return in_array('tool', $game->character->listActiveEquipmentTypes());
                },
            ],
            'actDrawHunt' => [
                'stamina' => 3,
                'requires' => function () use ($game) {
                    return in_array('weapon', $game->character->listActiveEquipmentTypes());
                },
            ],
            'actCraft' => [
                'stamina' => 3,
            ],
            'actInvestigateFire' => [
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
        ];
        $this->encounterActions = [
            'actUseItem' => [
                'requires' => function () use ($game) {
                    return in_array('tool', $game->character->listActiveEquipmentTypes());
                },
            ],
            'actUseSpecial' => [
                'requires' => function () use ($game) {
                    return in_array('tool', $game->character->listActiveEquipmentTypes());
                },
            ],
        ];
        $this->game = $game;
    }
    /**
     * Get character stamina cost
     * @return int
     * @see ./states.inc.php
     */
    public function getStaminaCost($action): int
    {
        return $this->playerActions[$action]['stamina'];
    }
    public function validateCanRunAction($action)
    {
        $cost = $this->getStaminaCost($action);
        $stamina = $this->game->character->getActiveStamina();
        if ($stamina < $cost) {
            throw new BgaUserException($this->game->translate('Not enough stamina'));
        }
        if (!(!array_key_exists('requires', $this->playerActions[$action]) || $this->playerActions[$action]['requires']())) {
            throw new BgaUserException($this->game->translate('Can\'t use this action'));
        }
    }
    public function getValidPlayerActions()
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter(
            $this->playerActions,
            function ($v, $k) {
                return (!array_key_exists('requires', $v) || $v['requires']()) &&
                    $this->getStaminaCost($k) <= $this->game->character->getActiveStamina();
            },
            ARRAY_FILTER_USE_BOTH
        );
        return array_column(
            array_map(
                function ($k, $v) {
                    return [$k, $this->getStaminaCost($k)];
                },
                array_keys($validActionsFiltered),
                $validActionsFiltered
            ),
            1,
            0
        );
    }
    public function getValidEncounterActions()
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter(
            $this->playerActions,
            function ($v, $k) {
                return (!array_key_exists('requires', $v) || $v['requires']()) &&
                    $this->getStaminaCost($k) <= $this->game->character->getActiveStamina();
            },
            ARRAY_FILTER_USE_BOTH
        );
        return array_column(
            array_map(
                function ($k, $v) {
                    return [$k, $this->getStaminaCost($k)];
                },
                array_keys($validActionsFiltered),
                $validActionsFiltered
            ),
            1,
            0
        );
    }
}
