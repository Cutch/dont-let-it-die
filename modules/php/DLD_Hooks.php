<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class DLD_Hooks
{
    private DLD_Game $game;
    private bool $checkInterrupt = false;
    public function __construct(DLD_Game $game)
    {
        $this->game = $game;
    }
    private function getHook(): array
    {
        $unlocks = $this->game->getUnlockedKnowledge();
        $activeNightCards = $this->game->getActiveNightCards();
        // var_dump(json_encode($activeNightCards));
        $buildings = $this->game->getBuildings();
        $actions = $this->game->actions->getActions();
        $characters = $this->game->character->getAllCharacterData(true);
        $equipment = array_merge(
            ...array_map(function ($c) {
                return $c['equipment'];
            }, $characters),
            ...array_map(function ($c) {
                return $c['necklaces'];
            }, $characters),
            ...array_map(function ($c) {
                return $c['physicalHindrance'];
            }, $characters),
            ...array_map(function ($c) {
                return $c['mentalHindrance'];
            }, $characters)
        );
        $skills = array_merge(
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return array_map(function ($skill) use ($c) {
                        $skill['characterId'] = $this->game->character->getSubmittingCharacterId();
                        return $skill;
                    }, $c['skills']);
                }
                return [];
            }, $unlocks),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $buildings),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $equipment),
            ...array_map(function ($c) {
                $skills = [];
                if (array_key_exists('skills', $c)) {
                    $skills = $c['skills'];
                }
                array_walk($c['dayEvent'], function ($item) use (&$skills, $c) {
                    if (array_key_exists('skills', $item)) {
                        array_push(
                            $skills,
                            ...array_values(
                                array_map(
                                    function ($skill) use ($c) {
                                        $skill['characterId'] = $c['id'];
                                        return $skill;
                                    },
                                    array_filter($item['skills'], function ($item) {
                                        return $item['type'] == 'item-skill';
                                    })
                                )
                            )
                        );
                    }
                });
                return $skills;
            }, $characters),
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return array_filter($c['skills'], function ($item) {
                        return $item['type'] == 'skill';
                    });
                }
                return [];
            }, $this->game->actions->getActiveDayEvents())
        );
        return [...$actions, ...$unlocks, ...$buildings, ...$characters, ...$skills, ...$equipment, ...$activeNightCards];
    }
    private function callHooks($functionName, $args, &$data1, &$data2 = null, &$data3 = null, &$data4 = null)
    {
        $this->checkInterrupt = array_key_exists('checkInterrupt', $args) ? $args['checkInterrupt'] : false;
        $hooks = $this->getHook();
        if ($this->checkInterrupt) {
            $hooks = array_filter($hooks, function ($object) use ($data1, $data2, $data3, $data4, $args) {
                // $interruptData = array_filter([$data1, $data2, $data3, $data4]);
                // $interruptData = $interruptData[sizeof($interruptData) - 1];
                return (!array_key_exists('state', $object) || in_array('interrupt', $object['state'])) &&
                    (!array_key_exists('interruptState', $object) || in_array($data1['currentState'], $object['interruptState'])) &&
                    (!array_key_exists('requires', $object) ||
                        $object['requires']($this->game, [...$object, ...$args], $data1, $data2, $data3, $data4));
            });
        }
        // Pre
        if (!array_key_exists('suffix', $args) || $args['suffix'] == 'Pre') {
            foreach ($hooks as $object) {
                if (array_key_exists($functionName . 'Pre', $object)) {
                    $object[$functionName . 'Pre']($this->game, [...$object, ...$args], $data1, $data2, $data3, $data4);
                }
            }
        }
        // Normal
        foreach ($hooks as $object) {
            if ($functionName == 'onUseHerb') {
                $this->game->log('hooks', array_key_exists($functionName, $object), $object);
            }
            if (array_key_exists($functionName, $object)) {
                $object[$functionName]($this->game, [...$object, ...$args], $data1, $data2, $data3, $data4);
            }
        }
        // Post
        if (!array_key_exists('suffix', $args) || $args['suffix'] == 'Post') {
            foreach ($hooks as $object) {
                if (array_key_exists($functionName . 'Post', $object)) {
                    $object[$functionName . 'Post']($this->game, [...$object, ...$args], $data1, $data2, $data3, $data4);
                }
            }
        }
        $this->checkInterrupt = false;
    }
    function onInterrupt(&$data, $activatedSkill, array $args = [])
    {
        // Default checkInterrupt to true
        if (!array_key_exists('checkInterrupt', $args)) {
            $args['checkInterrupt'] = true;
        }
        $this->callHooks(__FUNCTION__, $args, $data, $activatedSkill);
        return $data;
    }
    public function reconnectHooks(&$jsonData, $underlyingData)
    {
        array_walk($underlyingData, function ($v, $k) use (&$jsonData) {
            if (str_starts_with($k, 'on') || str_starts_with($k, 'get')) {
                $jsonData[$k] = $v;
            }
        });
    }
    function onGetCharacterData(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetValidActions(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetActionCost(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetActionSelectable(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onMorning(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onMorningPost(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onMorningAfter(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onNight(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    // function onNightPost(&$data, array $args = [])
    // {
    //     $this->callHooks(__FUNCTION__, $args, $data);
    //     return $data;
    // }
    function onNightDrawCard(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onDraw(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
    }
    function onActDraw(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
    }
    function onResolveDraw(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
    }
    function onEncounter(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onUseSkill(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
    }
    function onUseHerb(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onEat(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onEatBefore(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetEatData(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCraft(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCraftAfter(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onRollDie(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onInvestigateFire(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onItemTrade(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetTradeRatio(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onHindranceSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onHindranceSelectionAfter(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onDeckSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCharacterSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCardSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onResourceSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onResourceSelectionOptions(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onAdjustStamina(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onAdjustHealth(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCheckSkillRequirements(&$data, array $args = [])
    {
        $requires = ['requires' => true];
        $this->callHooks(__FUNCTION__, $args, $data, $requires);
        return $requires['requires'];
    }
    function onCook(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCookAfter(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onIncapacitation(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onDayEvent(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onEndTurn(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onAcquireHindrance(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onMaxHindrance(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetUnlockCost(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onAddFireWood(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetMaxBuildingCount(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onUnlock(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onGetResourceMax(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onCharacterChoose(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onPlayerTurn(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onDinnerPhasePost(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onSpendActionCost(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
    function onItemSelection(&$data, array $args = [])
    {
        $this->callHooks(__FUNCTION__, $args, $data);
        return $data;
    }
}
