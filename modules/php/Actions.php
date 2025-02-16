<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class Actions
{
    private $actions;
    private Game $game;
    public function __construct(Game $game)
    {
        $_this = $this;
        $this->actions = $this->addId([
            'actEat' => [
                'type' => 'player',
                'stamina' => 0,
                'requires' => function ($action) use ($game, $_this) {
                    $variables = $game->globals->getAll();
                    $array = $_this->getActionSelectable($action['id']);
                    $array = array_filter(
                        $array,
                        function ($v, $k) use ($variables, $_this) {
                            if (isset($variables[$k])) {
                                return $v['actEat']['count'] <= $variables[$k];
                            }
                        },
                        ARRAY_FILTER_USE_BOTH
                    );
                    return sizeof($array) > 0;
                },
                'selectable' => function () use ($game) {
                    return array_values(
                        array_filter(
                            $game->data->tokens,
                            function ($v, $k) {
                                return array_key_exists('actEat', $v);
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actCook' => [
                'type' => 'player',
                'stamina' => 1,
                'requires' => function ($action) use ($game, $_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count > 0;
                },
                'selectable' => function () use ($game) {
                    return [];
                },
            ],
            'actTrade' => [
                'type' => 'player',
                'stamina' => 1,
                'requires' => function ($action) use ($game, $_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$key])) {
                            $count += $variables[$key];
                        }
                    }
                    return $count >= 3;
                },
                'selectable' => function () use ($game) {
                    return array_values(
                        array_filter(
                            $game->data->tokens,
                            function ($v, $k) {
                                return $v['type'] === 'resource';
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actDrawGather' => [
                'type' => 'player',
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'type' => 'player',
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'type' => 'player',
                'stamina' => 3,
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'tool' && !in_array($data['id'], ['mortar-and-pestle', 'bandage']);
                        })
                    ) > 0;
                },
            ],
            'actDrawHunt' => [
                'type' => 'player',
                'stamina' => 3,
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'weapon';
                        })
                    ) > 0;
                },
            ],
            'actCraft' => [
                'type' => 'player',
                'stamina' => 3,
                'selectable' => function () use ($game) {
                    $craftedItems = $game->getCraftedItems();
                    $craftingLevel = $game->globals->get('craftingLevel');
                    return array_values(
                        array_filter(
                            $game->data->items,
                            function ($v, $k) use ($craftingLevel, $craftedItems) {
                                return $v['type'] == 'item' && $v['craftingLevel'] <= $craftingLevel && $v['count'] < $craftedItems[$k];
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actInvestigateFire' => [
                'type' => 'player',
                'stamina' => 3,
            ],
            'actSpendFKP' => [
                'type' => 'player',
                'stamina' => 0,
                'requires' => function ($action) use ($game, $_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll(...$array);
                    return array_sum(
                        array_map(function ($type) use ($variables) {
                            return $variables[$type];
                        }, $array)
                    ) > 0;
                },
                'selectable' => function () use ($game) {
                    return ['fkp'];
                },
            ],
            'actAddWood' => [
                'type' => 'player',
                'stamina' => 0,
                'requires' => function ($action) use ($game, $_this) {
                    $wood = $game->globals->get('wood');
                    return $wood > 0;
                },
            ],
            'actUseSkill' => [
                'type' => 'player',
                'requires' => function ($action) use ($game, $_this) {
                    return isset($game->character->getActivateCharacter()['skills']);
                },
            ],
            'actUseItem' => [
                'type' => 'encounter',
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'tool';
                        })
                    ) > 0;
                },
            ],
            'actUseSkill' => [
                'type' => 'encounter',
                'requires' => function ($action) use ($game, $_this) {
                    return isset($game->character->getActivateCharacter()['skills']);
                },
            ],
            'actEquipItem' => [
                'type' => 'trade',
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof($game->globals->get('campEquipment')) > 0;
                },
            ],
            'actUnEquipItem' => [
                'type' => 'trade',
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
            'actTradeItem' => [
                'type' => 'trade',
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
            'actConfirmTradeItem' => [
                'type' => 'trade',
                'requires' => function ($action) use ($game, $_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
        ]);
        $this->game = $game;
    }
    function addId($data)
    {
        array_walk($data, function (&$v, $k) {
            $v['id'] = $k;
        });
        return $data;
    }

    public function getActionSelectable($actionId)
    {
        $data = [
            'action' => $actionId,
            'selectable' => $this->actions[$actionId]['selectable'](),
        ];
        return $this->game->hooks->onGetActionSelectable($data)['selectable'];
    }
    /**
     * Get character stamina cost
     * @return int
     * @see ./states.inc.php
     */
    public function getActionStaminaCost($action): ?int
    {
        $data = [
            'action' => $action,
            'stamina' => isset($this->actions[$action]['stamina']) ? $this->actions[$action]['stamina'] : null,
        ];
        return $this->game->hooks->onGetActionStaminaCost($data)['stamina'];
    }
    public function setup()
    {
        $this->resetTurnActions();
    }

    public function resetTurnActions()
    {
        $this->game->globals->set('turnActions', []);
    }
    public function getTurnActions()
    {
        return $this->game->globals->get('turnActions');
    }
    public function checkRequirements($actionObj, ...$args): bool
    {
        return !array_key_exists('requires', $actionObj) || $actionObj['requires']($actionObj, ...$args);
    }
    public function validateCanRunAction($action, ...$args)
    {
        $cost = $this->getActionStaminaCost($action);
        $stamina = $this->game->character->getActiveStamina();
        if ($stamina < $cost) {
            throw new BgaUserException($this->game->translate('Not enough stamina'));
        }
        if (!$this->checkRequirements($this->actions[$action])) {
            throw new BgaUserException($this->game->translate('Can\'t use this action'));
        }
        $validActions = $this->getValidActions();
        if (!isset($validActions[$action])) {
            throw new BgaUserException($this->game->translate('This action can not be used this turn'));
        }
        $turnActions = $this->game->globals->get('turnActions');
        $turnActions[$action] = ($turnActions[$action] ?? 0) + 1;
        $this->game->globals->set('turnActions', $turnActions);
    }
    public function getValidActions($type = 'player')
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter($this->actions, function ($v) use ($type) {
            return $v['type'] == $type &&
                $this->checkRequirements($v) &&
                $this->getActionStaminaCost($v['id']) <= $this->game->character->getActiveStamina();
        });
        $data = array_column(
            array_map(
                function ($k, $v) {
                    return [$k, $this->getActionStaminaCost($k)];
                },
                array_keys($validActionsFiltered),
                $validActionsFiltered
            ),
            1,
            0
        );
        return $this->game->hooks->onGetValidActions($data);
    }
}
