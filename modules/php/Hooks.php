<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class Hooks
{
    private Game $game;
    private bool $checkInterrupt = false;
    public function __construct(Game $game)
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
    private function callHooks($functionName, &$data1, &$data2 = null, &$data3 = null, &$data4 = null)
    {
        $hooks = $this->getHook();
        if ($functionName == 'onEncounter') {
            $this->game->log('getHook start', $hooks);
        }
        if ($this->checkInterrupt) {
            $hooks = array_filter($hooks, function ($object) use ($data1, $data2, $data3, $data4) {
                // $interruptData = array_filter([$data1, $data2, $data3, $data4]);
                // $interruptData = $interruptData[sizeof($interruptData) - 1];
                return (!array_key_exists('state', $object) || in_array('interrupt', $object['state'])) &&
                    (!array_key_exists('interruptState', $object) || in_array($data1['currentState'], $object['interruptState'])) &&
                    (!array_key_exists('requires', $object) || $object['requires']($this->game, $object, $data1, $data2, $data3, $data4));
            });
        }
        foreach ($hooks as $i => $object) {
            if (array_key_exists($functionName, $object)) {
                $object[$functionName]($this->game, $object, $data1, $data2, $data3, $data4);
            }
        }
        $this->checkInterrupt = false;
    }
    function onGetCharacterData(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetValidActions(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetActionCost(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetActionSelectable(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onMorning(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onMorningAfter(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onNight(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onNightDrawCard(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onDraw(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
    }
    function onActDraw(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
    }
    function onResolveDraw(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
    }
    function onEncounter(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onUseSkill(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
    }
    function onEat(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetEatData(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCraft(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCraftAfter(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onRollDie(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onInvestigateFire(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onItemTrade(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetTradeRatio(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onDeckSelection(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCharacterSelection(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCardSelection(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onResourceSelection(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onResourceSelectionOptions(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onInterrupt(&$data, $activatedSkill, $checkInterrupt = true)
    {
        $this->checkInterrupt = $checkInterrupt;
        // var_dump(json_encode([$data, $activatedSkill]));
        $this->callHooks(__FUNCTION__, $data, $activatedSkill);
        return $data;
    }
    public function reconnectHooks(&$jsonData, $underlyingData)
    {
        array_walk($underlyingData, function ($v, $k) use (&$jsonData) {
            if (str_starts_with($k, 'on')) {
                $jsonData[$k] = $v;
            }
        });
    }
    function onAdjustStamina(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onAdjustHealth(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCheckSkillRequirements(&$data, $checkInterrupt = false)
    {
        $requires = ['requires' => true];
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data, $requires);
        return $requires['requires'];
    }
    function onCook(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onIncapacitation(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onDayEvent(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onEndTurn(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onAcquireHindrance(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onMaxHindrance(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetUnlockCost(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetReviveCost(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onAddFireWood(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetMaxBuildingCount(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onUnlock(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetResourceMax(&$data, $checkInterrupt = false)
    {
        $this->checkInterrupt = $checkInterrupt;
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
}
