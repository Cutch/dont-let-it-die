<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class Hooks
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    private function callHooks($functionName, &$data1, &$data2 = null, &$data3 = null, &$data4 = null)
    {
        $unlocks = $this->game->getUnlockedKnowledge();
        $activeNightCards = $this->game->getActiveNightCards();
        $buildings = $this->game->getBuildings();
        $characters = $this->game->character->getAllCharacterData(true);
        $equipment = array_merge(
            ...array_map(function ($c) {
                return $c['equipment'];
            }, $characters)
        );
        $skills = array_merge(
            ...array_map(function ($c) {
                if (array_key_exists('skills', $c)) {
                    return $c['skills'];
                }
                return [];
            }, $characters)
        );
        $array = [...$unlocks, ...$activeNightCards, ...$buildings, ...$characters, ...$skills, ...$equipment];
        foreach ($array as $i => $object) {
            if (array_key_exists($functionName, $object)) {
                $object[$functionName]($this->game, $object, $data1, $data2 = null, $data3 = null, $data4);
            }
        }
    }
    function onGetCharacterData(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetValidActions(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetActionCost(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetActionSelectable(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onMorning(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onNight(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onDraw($deck, $card)
    {
        $this->callHooks(__FUNCTION__, $deck, $card);
    }
    function onEncounter(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onEat(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetEatData(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onCraft($card)
    {
        $this->callHooks(__FUNCTION__, $card);
    }
    function onRollDie(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onInvestigateFire(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetTradeRatio(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onDeckSelection(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onSkillConfirmation(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
}
