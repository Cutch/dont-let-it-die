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
                    $variables = $game->gameData->getResources();
                    $array = $_this->getActionSelectable($action['id']);
                    $array = array_filter(
                        $array,
                        function ($v, $k) use ($variables, $_this) {
                            if (array_key_exists($k, $variables)) {
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
                    $variables = $game->gameData->getResources();
                    $count = 0;
                    foreach ($array as $key => $value) {
                        if (array_key_exists($value, $variables)) {
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
                    $variables = $game->gameData->getResources();
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
                'requires' => function (Game $game) {
                    $result = [];
                    $game->getItemData($result);
                    return sizeof(array_filter(array_values($result['availableEquipment']))) > 0;
                },
                'selectable' => function (Game $game) {
                    $craftedItems = $game->getCraftedItems();
                    $craftingLevel = $game->gameData->getGlobals('craftingLevel');
                    return array_values(
                        array_filter(
                            $game->data->items,
                            function ($v, $k) use ($craftingLevel, $craftedItems) {
                                // var_dump(json_encode($v['count']), json_encode($craftedItems));
                                return $v['type'] == 'item' &&
                                    $v['craftingLevel'] <= $craftingLevel &&
                                    (!array_key_exists($k, $craftedItems) || $craftedItems[$k] < $v['count']);
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
                    $variables = $game->gameData->getResources(...$array);
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
                    $wood = $game->gameData->getResource('wood');
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
                    return sizeof($game->gameData->getGlobals('campEquipment')) > 0;
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
                if (array_key_exists('skills', $char) && isset($char['skills'][$subActionId])) {
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
    //         'stamina' => array_key_exists('stamina', $this->getAction($action, $subAction)) ? $this->getAction($action, $subAction)['stamina'] : null,
    //         'health' => array_key_exists('health', $this->getAction($action, $subAction)) ? $this->getAction($action, $subAction)['health'] : null,
    //     ];
    //     $this->game->hooks->onGetActionCost($data);
    //     unset($data['action']);
    //     return $data;
    // }
    public function getAvailableCharacterSkills(): array
    {
        $character = $this->game->character->getActivateCharacter();
        if (!array_key_exists('skills', $character)) {
            return [];
        }
        return array_values(
            array_filter($character['skills'], function ($skill) use ($character) {
                $stamina = $character['stamina'];
                $health = $character['health'];
                $actionCost = [
                    'action' => 'actUseSkill',
                    'stamina' => array_key_exists('stamina', $skill) ? $skill['stamina'] : null,
                    'health' => array_key_exists('health', $skill) ? $skill['health'] : null,
                ];
                $this->game->hooks->onGetActionCost($actionCost);
                return $this->checkRequirements($skill) &&
                    (!array_key_exists('stamina', $actionCost) || $stamina >= $actionCost['stamina']) &&
                    (!array_key_exists('health', $actionCost) || $health >= $actionCost['health']);
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
    public function getActionCost($action, $subAction = null): array
    {
        $data = [
            'action' => $action,
            'stamina' => array_key_exists('stamina', $this->getAction($action, $subAction))
                ? $this->getAction($action, $subAction)['stamina']
                : null,
            'health' => array_key_exists('health', $this->getAction($action, $subAction))
                ? $this->getAction($action, $subAction)['health']
                : null,
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
        $this->game->gameData->set('turnActions', []);
    }
    public function getTurnActions()
    {
        return $this->game->gameData->getGlobals('turnActions');
    }
    public function checkRequirements($actionObj, ...$args): bool
    {
        return !array_key_exists('requires', $actionObj) ||
            ($actionObj['requires']($this->game, $actionObj, ...$args) &&
                (!array_key_exists('state', $actionObj) || in_array($this->game->gamestate->state()['name'], $actionObj['state'])));
    }
    public function spendActionCost($action, $subAction = null)
    {
        $cost = $this->getActionCost($action, $subAction);
        if (array_key_exists('health', $cost)) {
            $this->game->character->adjustActiveHealth(-$cost['health']);
        }
        if (array_key_exists('stamina', $cost)) {
            $this->game->character->adjustActiveStamina(-$cost['stamina']);
        }
    }
    public function validateCanRunAction($action, $subAction = null, ...$args)
    {
        $cost = $this->getActionCost($action, $subAction);
        $stamina = $this->game->character->getActiveStamina();
        $health = $this->game->character->getActiveHealth();
        if (array_key_exists('stamina', $cost) && $stamina < $cost['stamina']) {
            throw new BgaUserException($this->game->translate('Not enough stamina'));
        }
        if (array_key_exists('health', $cost) && $health < $cost['health']) {
            throw new BgaUserException($this->game->translate('Not enough health'));
        }
        if (!$this->checkRequirements($this->getAction($action, $subAction, ...$args))) {
            throw new BgaUserException($this->game->translate('Can\'t use this action'));
        }
        $validActions = $this->getValidActions();
        if (!array_key_exists($action, $validActions)) {
            throw new BgaUserException($this->game->translate('This action can not be used this turn'));
        }
        $turnActions = $this->game->gameData->getGlobals('turnActions');
        $turnActions[$action] = ($turnActions[$action] ?? 0) + 1;
        $this->game->gameData->set('turnActions', $turnActions);
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
                (!array_key_exists('stamina', $actionCost) || $stamina >= $actionCost['stamina']) &&
                (!array_key_exists('health', $actionCost) || $health >= $actionCost['health']);
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
