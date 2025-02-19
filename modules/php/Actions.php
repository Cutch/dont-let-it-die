<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

include dirname(__DIR__) . '/data/Utils.php';
class Actions
{
    private $actions;
    private Game $game;
    public function __construct(Game $game)
    {
        $_this = $this;
        $this->actions = addId([
            'actEat' => [
                'type' => ['player'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
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
                'selectable' => function (Game $game) {
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
                'type' => ['player'],
                'stamina' => 1,
                'requires' => function (Game $game, $action) use ($_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$value])) {
                            $count += $variables[$value];
                        }
                    }
                    return $count > 0;
                },
                'selectable' => function (Game $game) {
                    return [];
                },
            ],
            'actTrade' => [
                'type' => ['player'],
                'stamina' => 1,
                'requires' => function (Game $game, $action) use ($_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (isset($variables[$value['id']])) {
                            $count += $variables[$value['id']];
                        }
                    }
                    return $count >= $game->getTradeRatio();
                },
                'selectable' => function (Game $game) {
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
                'type' => ['player'],
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'type' => ['player'],
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'type' => ['player'],
                'stamina' => 3,
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'tool' && !in_array($data['id'], ['mortar-and-pestle', 'bandage']);
                        })
                    ) > 0;
                },
            ],
            'actDrawHunt' => [
                'type' => ['player'],
                'stamina' => 3,
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'weapon';
                        })
                    ) > 0;
                },
            ],
            'actCraft' => [
                'type' => ['player'],
                'stamina' => 3,
                'selectable' => function (Game $game) {
                    $craftedItems = $game->getCraftedItems();
                    $craftingLevel = $game->globals->get('craftingLevel');
                    return array_values(
                        array_filter(
                            $game->data->items,
                            function ($v, $k) use ($craftingLevel, $craftedItems) {
                                return $v['type'] == 'item' &&
                                    $v['craftingLevel'] <= $craftingLevel &&
                                    (!isset($craftedItems[$k]) || $v['count'] < $craftedItems[$k]);
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actInvestigateFire' => [
                'type' => ['player'],
                'stamina' => 3,
            ],
            'actSpendFKP' => [
                'type' => ['player'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->globals->getAll(...$array);
                    return array_sum(
                        array_map(function ($type) use ($variables) {
                            return $variables[$type];
                        }, $array)
                    ) > 0;
                },
                'selectable' => function (Game $game) {
                    return ['fkp'];
                },
            ],
            'actAddWood' => [
                'type' => ['player'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
                    $wood = $game->globals->get('wood');
                    return $wood > 0;
                },
            ],
            'actUseSkill' => [
                'type' => ['player', 'encounter'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($_this->getAvailableCharacterSkills()) > 0;
                },
            ],
            'actUseItem' => [
                'type' => ['encounter'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'tool';
                        })
                    ) > 0;
                },
            ],
            'actEquipItem' => [
                'type' => ['trade'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->globals->get('campEquipment')) > 0;
                },
            ],
            'actUnEquipItem' => [
                'type' => ['trade'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
            'actTradeItem' => [
                'type' => ['trade'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
            'actConfirmTradeItem' => [
                'type' => ['trade'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getActivateCharacter()['equipment']) > 0;
                },
            ],
        ]);
        $this->game = $game;
    }
    public function getAction($actionId, $subActionId = null): array
    {
        if ($actionId == 'actUseSkill') {
            foreach ($this->game->character->getAllCharacterData() as $k => $char) {
                if (isset($char['skills']) && isset($char['skills'][$subActionId])) {
                    return $char['skills'][$subActionId];
                }
            }
            return [];
        } else {
            return $this->actions[$actionId];
        }
    }
    // public function getCharacterSkillsCost($action, $subAction = null): ?array
    // {
    //     $data = [
    //         'action' => $action,
    //         'stamina' => isset($this->getAction($action, $subAction)['stamina']) ? $this->getAction($action, $subAction)['stamina'] : null,
    //         'health' => isset($this->getAction($action, $subAction)['health']) ? $this->getAction($action, $subAction)['health'] : null,
    //     ];
    //     $this->game->hooks->onGetActionCost($data);
    //     unset($data['action']);
    //     return $data;
    // }
    public function getAvailableCharacterSkills(): array
    {
        $character = $this->game->character->getActivateCharacter();
        if (!isset($character['skills'])) {
            return [];
        }
        return array_values(
            array_filter($character['skills'], function ($skill) use ($character) {
                $stamina = $character['stamina'];
                $health = $character['health'];
                $actionCost = [
                    'action' => 'actUseSkill',
                    'stamina' => isset($skill['stamina']) ? $skill['stamina'] : null,
                    'health' => isset($skill['health']) ? $skill['health'] : null,
                ];
                $this->game->hooks->onGetActionCost($actionCost);
                return $this->checkRequirements($skill) &&
                    (!isset($actionCost['stamina']) || $stamina >= $actionCost['stamina']) &&
                    (!isset($actionCost['health']) || $health >= $actionCost['health']);
            })
        );
    }

    public function getActionSelectable($actionId, $subActionId = null)
    {
        $data = [
            'action' => $actionId,
            'selectable' => $this->getAction($actionId, $subActionId)['selectable']($this->game),
        ];
        return $this->game->hooks->onGetActionSelectable($data)['selectable'];
    }
    /**
     * Get character stamina cost
     * @return int
     * @see ./states.inc.php
     */
    public function getActionCost($action, $subAction = null): ?array
    {
        $data = [
            'action' => $action,
            'stamina' => isset($this->getAction($action, $subAction)['stamina']) ? $this->getAction($action, $subAction)['stamina'] : null,
            'health' => isset($this->getAction($action, $subAction)['health']) ? $this->getAction($action, $subAction)['health'] : null,
        ];
        $this->game->hooks->onGetActionCost($data);
        unset($data['action']);
        return $data;
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
        return !array_key_exists('requires', $actionObj) ||
            ($actionObj['requires']($this->game, $actionObj, ...$args) &&
                (!isset($actionObj['state']) || in_array($this->game->gamestate->state()['name'], $actionObj['state'])));
    }
    public function spendActionCost($action, $subAction = null)
    {
        $cost = $this->getActionCost($action, $subAction);
        if (isset($cost['health'])) {
            $this->game->character->adjustActiveHealth(-$cost['health']);
        }
        if (isset($cost['stamina'])) {
            $this->game->character->adjustActiveStamina(-$cost['stamina']);
        }
    }
    public function validateCanRunAction($action, $subAction = null, ...$args)
    {
        $cost = $this->getActionCost($action, $subAction);
        $stamina = $this->game->character->getActiveStamina();
        $health = $this->game->character->getActiveHealth();
        if (isset($cost['stamina']) && $stamina < $cost['stamina']) {
            throw new BgaUserException($this->game->translate('Not enough stamina'));
        }
        if (isset($cost['health']) && $health < $cost['health']) {
            throw new BgaUserException($this->game->translate('Not enough health'));
        }
        if (!$this->checkRequirements($this->getAction($action, $subAction, ...$args))) {
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
            $actionCost = $this->getActionCost($v['id']);
            $stamina = $this->game->character->getActiveStamina();
            $health = $this->game->character->getActiveHealth();
            return in_array($type, $v['type']) &&
                $this->checkRequirements($v) &&
                (!isset($actionCost['stamina']) || $stamina >= $actionCost['stamina']) &&
                (!isset($actionCost['health']) || $health >= $actionCost['health']);
        });
        $data = array_column(
            array_map(
                function ($k, $v) {
                    return [$k, $this->getActionCost($k)];
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
