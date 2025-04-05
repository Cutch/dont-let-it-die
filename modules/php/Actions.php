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
        $this->actions = addId([
            'actRevive' => [
                'state' => ['playerTurn'],
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $variables = $game->gameData->getResources('fish-cooked', 'meat-cooked');
                    $total = array_sum($variables);
                    return $total >= $game->getReviveCost() &&
                        sizeof(
                            array_filter($game->character->getAllCharacterData(), function ($char) {
                                return $char['incapacitated'] && ($char['health'] ?? 0) == 0;
                            })
                        ) > 0;
                },
                'selectable' => function (Game $game) {
                    return ['fish-cooked', 'meat-cooked'];
                },
            ],
            'actSpendFKP' => [
                'state' => ['playerTurn'],
                'stamina' => 0,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $array = $this->getActionSelectable($action['id']);
                    $variables = $game->gameData->getResources(...$array);
                    $resourceCount = array_sum(
                        array_map(function ($type) use ($variables) {
                            return $variables[$type];
                        }, $array)
                    );
                    $availableUnlocks = $game->data->getValidKnowledgeTree();
                    return sizeof(
                        array_filter($availableUnlocks, function ($v) use ($resourceCount) {
                            return $v['unlockCost'] <= $resourceCount;
                        })
                    ) > 0;
                },
                'selectable' => function (Game $game) {
                    return ['fkp'];
                },
            ],
            'actEat' => [
                'state' => ['playerTurn', 'dinnerPhase'],
                'stamina' => 0,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $variables = $game->gameData->getResources();
                    $array = $this->getActionSelectable($action['id']);
                    $array = array_filter(
                        $array,
                        function ($v) use ($variables) {
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
                            $game->getValidTokens(),
                            function ($v, $k) {
                                return array_key_exists('actEat', $v);
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
            ],
            'actAddWood' => [
                'state' => ['playerTurn'],
                'stamina' => 0,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $wood = $game->gameData->getResource('wood');
                    return $wood > 0;
                },
            ],
            'actUseHerb' => [
                'state' => ['playerTurn'],
                'stamina' => 1,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    return sizeof($this->game->character->getTurnCharacter()['physicalHindrance']) > 0 &&
                        $game->gameData->getResource('herb') > 0;
                },
                'selectable' => function (Game $game) {
                    return [];
                },
                'onUseHerbPre' => function (Game $game, $action, &$data) {
                    $game->hindranceSelection($action['id']);
                    return ['notify' => false, 'nextState' => false, 'interrupt' => true];
                },
                'onHindranceSelection' => function (Game $game, $action, &$data) {
                    $state = $game->gameData->get('hindranceSelectionState');
                    if ($state && $state['id'] == $action['id']) {
                        foreach ($state['characters'] as $i => $char) {
                            foreach ($char['physicalHindrance'] as $i => $card) {
                                $this->game->character->removeHindrance($char['characterId'], $card);
                            }
                        }
                        $data['nextState'] = 'playerTurn';
                    }
                },
            ],
            'actTrade' => [
                'state' => ['playerTurn'],
                'stamina' => 1,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $array = $this->getActionSelectable($action['id']);
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
                    $tokens = $game->getValidTokens();
                    unset($tokens['trap']);
                    return $tokens;
                },
            ],
            'actCook' => [
                'state' => ['playerTurn'],
                'stamina' => 1,
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    $array = $this->getActionSelectable($action['id']);
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
                'type' => 'action',
                'requires' => function (Game $game, $action) {
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
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    return sizeof(
                        array_filter($game->character->getActiveEquipment(), function ($data) {
                            return $data['itemType'] == 'weapon';
                        })
                    ) > 0;
                },
            ],
            'actDrawExplore' => [
                'state' => ['playerTurn'],
                'stamina' => 4,
                'type' => 'action',
                'expansion' => 'hindrance',
            ],
            'actCraft' => [
                'state' => ['playerTurn'],
                'stamina' => 3,
                'type' => 'action',
                'requires' => function (Game $game) {
                    $result = [];
                    $game->getItemData($result);
                    return sizeof(array_filter(array_values($result['availableEquipment']))) > 0;
                },
                'selectable' => function (Game $game) {
                    $craftedItems = $game->getCraftedItems();
                    $craftingLevel = $game->gameData->get('craftingLevel');
                    $buildings = $game->gameData->get('buildings');
                    return array_values(
                        array_filter(
                            $game->data->items,
                            function ($v, $k) use ($craftingLevel, $craftedItems, $buildings, $game) {
                                return $v['type'] == 'item' &&
                                    $v['craftingLevel'] <= $craftingLevel &&
                                    ($v['itemType'] != 'building' || sizeof($buildings) < $game->getMaxBuildingCount()) &&
                                    (!array_key_exists($k, $craftedItems) || $craftedItems[$k] < $v['count']);
                            },
                            ARRAY_FILTER_USE_BOTH
                        )
                    );
                },
                'onGetActionCost' => function (Game $game, $action, &$data) {
                    if ($data['action'] == 'actCraft' && $data['subAction'] == 'rock-weapon') {
                        $data['stamina'] = min($data['stamina'], 1);
                    }
                },
            ],
            'actInvestigateFire' => [
                'state' => ['playerTurn'],
                'stamina' => 3,
            ],
            'actUseSkill' => [
                'getState' => function () {
                    $states = [];
                    foreach ($this->getAvailableSkills() as $skill) {
                        if (array_key_exists('state', $skill)) {
                            array_push($states, ...$skill['state']);
                        }
                    }
                    return $states;
                },
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    return sizeof($this->getAvailableSkills()) > 0;
                },
            ],
            'actUseItem' => [
                'getState' => function () {
                    $states = [];
                    foreach ($this->getAvailableItemSkills() as $skill) {
                        if (array_key_exists('state', $skill)) {
                            array_push($states, ...$skill['state']);
                        }
                    }
                    return $states;
                },
                'type' => 'action',
                'requires' => function (Game $game, $action) {
                    return sizeof($this->getAvailableItemSkills()) > 0;
                },
            ],
            'actTradeItem' => [
                'state' => ['tradePhase'],
                'type' => 'action',
                // 'requires' => function (Game $game, $action) {
                //     return sizeof($game->character->getSubmittingCharacter()['equipment']) > 0;
                // },
            ],
            'actConfirmTradeItem' => [
                'state' => ['confirmTradePhase'],
                'type' => 'action',
            ],
        ]);
        $this->game = $game;
    }
    private function expansionFilter(array $data)
    {
        if (!array_key_exists('expansion', $data)) {
            return true;
        }
        return $this->game->isValidExpansion($data['expansion']);
    }
    public function getActions()
    {
        return array_filter($this->actions, [$this, 'expansionFilter']);
    }
    public function getSkills(): array
    {
        $characters = $this->game->character->getAllCharacterData();
        $unlocks = $this->game->getUnlockedKnowledge();
        // $character = $this->game->character->getSubmittingCharacter();
        return array_merge(
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $characters),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return array_filter($c['skills'], function ($item) {
                        return $item['type'] == 'skill';
                    });
                }
                return [];
            }, $this->getActiveDayEvents()),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $unlocks),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return array_filter($c['skills'], function ($item) {
                        return $item['type'] == 'skill';
                    });
                }
                return [];
            }, $this->getActiveDayEvents())
            // ...array_map(function ($item) {
            //     if (!array_key_exists('skills', $item)) {
            //         return [];
            //     }
            //     return array_filter($item['skills'], function ($item) {
            //         return $item['type'] == 'skill';
            //     });
            // }, $character['dayEvent'])
        );
    }
    public function getActiveEquipmentSkills()
    {
        $character = $this->game->character->getSubmittingCharacter();
        $buildings = $this->game->getBuildings();
        $skills = array_merge(
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $buildings),
            ...array_map(function ($item) {
                if (!array_key_exists('skills', $item)) {
                    return [];
                }
                return $item['skills'];
            }, $character['equipment']),
            ...array_map(function ($item) {
                if (!array_key_exists('skills', $item)) {
                    return [];
                }
                return $item['skills'];
            }, $character['necklaces']),
            ...array_map(function ($item) use ($character) {
                if (!array_key_exists('skills', $item)) {
                    return [];
                }
                return array_map(
                    function ($item) use ($character) {
                        $item['$character'] = $character['id'];
                        return $item;
                    },
                    array_filter($item['skills'], function ($skill) {
                        return $skill['type'] == 'item-skill';
                    })
                );
            }, $character['dayEvent'])
        );
        return $skills;
    }
    public function getAction(string $actionId, ?string $subActionId = null): array
    {
        if ($actionId == 'actUseSkill') {
            $skills = $this->getSkills();
            if (isset($skills[$subActionId])) {
                return $skills[$subActionId];
            }
            return [];
        } elseif ($actionId == 'actUseItem') {
            $skills = $this->getActiveEquipmentSkills();
            if (isset($skills[$subActionId])) {
                return $skills[$subActionId];
            }
            return [];
        } else {
            $actionObj = $this->getActions()[$actionId];
            $actionObj['action'] = $actionId;
            return $actionObj;
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
    public function getAvailableSkills(): array
    {
        $skills = $this->getSkills();
        return array_values(
            array_filter($skills, function ($skill) {
                $character = $this->game->character->getCharacterData(
                    array_key_exists('characterId', $skill) ? $skill['characterId'] : $this->game->character->getTurnCharacterId()
                );
                $stamina = $character['stamina'];
                $health = $character['health'];

                $this->skillActionCost('actUseSkill', null, $skill);
                return $this->game->hooks->onCheckSkillRequirements($skill) &&
                    $this->checkRequirements($skill, $character) &&
                    (!array_key_exists('stamina', $skill) || $stamina >= $skill['stamina']) &&
                    (!array_key_exists('health', $skill) || $health >= $skill['health']);
            })
        );
    }

    public function getAvailableItemSkills(): array
    {
        $character = $this->game->character->getSubmittingCharacter();
        $skills = $this->getActiveEquipmentSkills();
        return array_values(
            array_filter($skills, function ($skill) use ($character) {
                $stamina = $character['stamina'];
                $health = $character['health'];
                $this->skillActionCost('actUseItem', null, $skill);
                return $this->checkRequirements($skill, $character) &&
                    (!array_key_exists('stamina', $skill) || $stamina >= $skill['stamina']) &&
                    (!array_key_exists('health', $skill) || $health >= $skill['health']);
            })
        );
    }
    public function getActionSelectable(string $actionId, ?string $subActionId = null)
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
    public function getActionCost(string $action, ?string $subAction = null): array
    {
        $actionObj = $this->getAction($action, $subAction);
        $this->skillActionCost($action, $subAction, $actionObj);
        return $actionObj;
    }
    private function skillActionCost(string $action, ?string $subAction = null, array &$skill)
    {
        $actionCost = [
            'action' => $action,
            'subAction' => $subAction ?? (array_key_exists('id', $skill) ? $skill['id'] : null),
            'stamina' => array_key_exists('stamina', $skill) ? $skill['stamina'] : null,
            'health' => array_key_exists('health', $skill) ? $skill['health'] : null,
            'perDay' => array_key_exists('perDay', $skill) ? $skill['perDay'] : null,
            'perForever' => array_key_exists('perForever', $skill) ? $skill['perForever'] : null,
            'name' => array_key_exists('name', $skill) ? $skill['name'] : null,
        ];
        $this->game->hooks->onGetActionCost($actionCost);

        $skill['action'] = $actionCost['action'];
        if (array_key_exists('stamina', $actionCost)) {
            $skill['stamina'] = $actionCost['stamina'];
        }
        if (array_key_exists('health', $actionCost)) {
            $skill['health'] = $actionCost['health'];
        }
        if (array_key_exists('perDay', $actionCost)) {
            $skill['perDay'] = $actionCost['perDay'];
        }
        if (array_key_exists('perForever', $actionCost)) {
            $skill['perForever'] = $actionCost['perForever'];
        }
        if (array_key_exists('name', $actionCost)) {
            $skill['name'] = $actionCost['name'];
        }
    }
    public function wrapSkills(array $skills, string $action): array
    {
        return array_values(
            array_map(function ($skill) use ($action) {
                $this->skillActionCost($action, null, $skill);
                return $skill;
            }, $skills)
        );
    }
    public function resetTurnActions()
    {
        $this->game->gameData->set('turnActions', []);
    }
    public function getTurnActions()
    {
        return $this->game->gameData->get('turnActions');
    }
    public function checkRequirements(array $actionObj, ...$args): bool
    {
        return (!array_key_exists('getState', $actionObj) || in_array($this->game->gamestate->state()['name'], $actionObj['getState']())) &&
            (!array_key_exists('state', $actionObj) || in_array($this->game->gamestate->state()['name'], $actionObj['state'])) &&
            (!array_key_exists('interruptState', $actionObj) ||
                ($this->game->actInterrupt->getLatestInterruptState() &&
                    in_array(
                        $this->game->actInterrupt->getLatestInterruptState()['data']['currentState'],
                        $actionObj['interruptState']
                    ))) &&
            (!array_key_exists('requires', $actionObj) || $actionObj['requires']($this->game, $actionObj, ...$args));
    }
    public function spendActionCost(string $action, ?string $subAction = null)
    {
        $cost = $this->getActionCost($action, $subAction);
        $this->game->log('$cost', $cost);
        if (array_key_exists('health', $cost)) {
            $this->game->character->adjustActiveHealth(-$cost['health']);
        }
        if (array_key_exists('stamina', $cost)) {
            $this->game->character->adjustActiveStamina(-$cost['stamina']);
        }
    }
    public function validateCanRunAction(string $action, ?string $subAction = null, ...$args)
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
        $turnActions = $this->game->gameData->get('turnActions');
        $turnActions[$action] = ($turnActions[$action] ?? 0) + 1;
        $this->game->gameData->set('turnActions', $turnActions);
    }
    public function getValidActions()
    {
        // Get some values from the current game situation from the database.
        $validActionsFiltered = array_filter($this->getActions(), function ($v) {
            // var_dump(json_encode($v));
            $actionCost = $this->getActionCost($v['id']);
            $stamina = $this->game->character->getActiveStamina();
            $health = $this->game->character->getActiveHealth();
            // Rock only needs 1 stamina, this is in the hindrance expansion
            $alwaysShowCraft = $this->game->isValidExpansion('hindrance') && $v['id'] == 'actCraft';
            // var_dump(json_encode([$v['id']]));
            return $this->checkRequirements($v) &&
                (!array_key_exists('stamina', $actionCost) ||
                    $stamina >= ($alwaysShowCraft ? min($actionCost['stamina'], 1) : $actionCost['stamina'])) &&
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
    public function getActiveDayEvents()
    {
        $activeDayCards = $this->game->gameData->get('activeDayCards');
        return array_map(function ($eventId) {
            return $this->game->data->expansion[$eventId];
        }, $activeDayCards);
    }
    public function addDayEvent(string $eventId)
    {
        $activeDayCards = $this->game->gameData->get('activeDayCards');
        array_push($activeDayCards, $eventId);
        $this->game->gameData->set('activeDayCards', $activeDayCards);
    }
    public function clearDayEvent()
    {
        // $eventId
        // $activeDayCards = $this->game->gameData->get('activeDayCards');
        // $activeDayCards = array_filter($activeDayCards, function ($id) use ($eventId) {
        //     return $eventId != $id;
        // });
        $this->game->gameData->set('activeDayCards', []);
    }
}
