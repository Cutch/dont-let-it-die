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
                'state' => ['playerTurn'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
                    $variables = $game->gameData->getResources();
                    $array = $_this->getActionSelectable($action['id']);
                    $array = array_filter(
                        $array,
                        function ($v) use ($variables, $_this) {
                            if (array_key_exists($v['id'], $variables)) {
                                return $v['actEat']['count'] <= $variables[$v['id']];
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
                'state' => ['playerTurn'],
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
                'state' => ['playerTurn'],
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
                'state' => ['playerTurn'],
                'stamina' => 2,
            ],
            'actDrawForage' => [
                'state' => ['playerTurn'],
                'stamina' => 2,
            ],
            'actDrawHarvest' => [
                'state' => ['playerTurn'],
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
                'state' => ['playerTurn'],
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
                'state' => ['playerTurn'],
                'stamina' => 3,
                'requires' => function (Game $game) {
                    $result = [];
                    $game->getItemData($result);
                    return sizeof(array_filter(array_values($result['availableEquipment']))) > 0;
                },
                'selectable' => function (Game $game) {
                    $craftedItems = $game->getCraftedItems();
                    $craftingLevel = $game->gameData->getGlobals('craftingLevel');
                    $buildings = $game->gameData->getGlobals('buildings');
                    return array_values(
                        array_filter(
                            $game->data->items,
                            function ($v, $k) use ($craftingLevel, $craftedItems, $buildings) {
                                return $v['type'] == 'item' &&
                                    $v['craftingLevel'] <= $craftingLevel &&
                                    ($v['itemType'] != 'building' || sizeof($buildings) == 0) &&
                                    (!array_key_exists($k, $craftedItems) || $craftedItems[$k] < $v['count']);
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actInvestigateFire' => [
                'state' => ['playerTurn'],
                'stamina' => 3,
            ],
            'actSpendFKP' => [
                'state' => ['playerTurn'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
                    $array = $_this->getActionSelectable($action['id']);
                    $variables = $game->gameData->getResources(...$array);
                    $resourceCount = array_sum(
                        array_map(function ($type) use ($variables) {
                            return $variables[$type];
                        }, $array)
                    );
                    $availableUnlocks = $game->data->getValidKnowledgeTree();
                    return sizeof(
                        array_filter($availableUnlocks, function ($v) use ($resourceCount) {
                            return $v['cost'] <= $resourceCount;
                        })
                    ) > 0;
                },
                'selectable' => function (Game $game) {
                    return ['fkp'];
                },
            ],
            'actAddWood' => [
                'state' => ['playerTurn'],
                'stamina' => 0,
                'requires' => function (Game $game, $action) use ($_this) {
                    $wood = $game->gameData->getResource('wood');
                    return $wood > 0;
                },
            ],
            'actUseSkill' => [
                'state' => ['playerTurn', 'resolveEncounter', 'postEncounter', 'interrupt'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($_this->getAvailableCharacterSkills()) > 0;
                },
            ],
            'actUseItem' => [
                'state' => ['playerTurn', 'resolveEncounter', 'postEncounter', 'interrupt'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($_this->getAvailableItemSkills()) > 0;
                },
            ],
            'actEquipItem' => [
                'state' => ['tradePhase'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->gameData->getGlobals('campEquipment')) > 0;
                },
            ],
            'actUnEquipItem' => [
                'state' => ['tradePhase'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getSubmittingCharacter()['equipment']) > 0;
                },
            ],
            'actTradeItem' => [
                'state' => ['tradePhase'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getSubmittingCharacter()['equipment']) > 0;
                },
            ],
            'actConfirmTradeItem' => [
                'state' => ['tradePhase'],
                'requires' => function (Game $game, $action) use ($_this) {
                    return sizeof($game->character->getSubmittingCharacter()['equipment']) > 0;
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
        } elseif ($actionId == 'actUseItem') {
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
        $character = $this->game->character->getSubmittingCharacter();
        if (!array_key_exists('skills', $character)) {
            return [];
        }
        return array_values(
            array_filter($character['skills'], function ($skill) use ($character) {
                $stamina = $character['stamina'];
                $health = $character['health'];
                $actionCost = [
                    'action' => 'actUseSkill',
                    'subAction' => $skill['id'],
                    'stamina' => array_key_exists('stamina', $skill) ? $skill['stamina'] : null,
                    'health' => array_key_exists('health', $skill) ? $skill['health'] : null,
                ];
                // var_dump(json_encode(['actUseSkill', $skill['id']]));
                $this->game->hooks->onGetActionCost($actionCost);
                return $this->checkRequirements($skill, $character) &&
                    (!array_key_exists('stamina', $actionCost) || $stamina >= $actionCost['stamina']) &&
                    (!array_key_exists('health', $actionCost) || $health >= $actionCost['health']);
            })
        );
    }
    public function getAvailableItemSkills(): array
    {
        $character = $this->game->character->getSubmittingCharacter();
        $skills = $this->game->character->getActiveEquipmentSkills();
        return array_values(
            array_filter($skills, function ($skill) use ($character) {
                $stamina = $character['stamina'];
                $health = $character['health'];
                $actionCost = [
                    'action' => 'actUseItem',
                    'subAction' => $skill['id'],
                    'stamina' => array_key_exists('stamina', $skill) ? $skill['stamina'] : null,
                    'health' => array_key_exists('health', $skill) ? $skill['health'] : null,
                ];
                $this->game->hooks->onGetActionCost($actionCost);
                return $this->checkRequirements($skill, $character) &&
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
     * @return array
     * @see ./states.inc.php
     */
    public function getActionCost($action, $subAction = null): array
    {
        $data = [
            'action' => $action,
            'subAction' => $subAction,
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
        return (!array_key_exists('state', $actionObj) || in_array($this->game->gamestate->state()['name'], $actionObj['state'])) &&
            (!array_key_exists('requires', $actionObj) || $actionObj['requires']($this->game, $actionObj, ...$args));
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
        $character = $this->game->character->getSubmittingCharacter();

        $cost = $this->getActionCost($action, $subAction);
        $stamina = $character['stamina'];
        $health = $character['health'];
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
    public function getValidActions()
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter($this->actions, function ($v) {
            // var_dump(json_encode($v));
            $actionCost = $this->getActionCost($v['id']);
            $stamina = $this->game->character->getActiveStamina();
            $health = $this->game->character->getActiveHealth();
            // var_dump(json_encode([$v['id']]));
            return $this->checkRequirements($v) &&
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
