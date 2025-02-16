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
        $characters = $this->game->character->getAllCharacterData();
        $equipment = array_values(
            array_filter($characters, function ($c) {
                return $c['isActive'];
            })
        )[0]['equipment'];
        $array = [...$unlocks, ...$activeNightCards, ...$buildings, ...$characters, ...$equipment];
        foreach ($array as $i => $object) {
            if (isset($data[$functionName])) {
                $object[$functionName]($this->game, $object, $data1, $data2 = null, $data3 = null, $data4);
            }
        }
    }
    function onGetValidActions(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
        return $data;
    }
    function onGetActionStaminaCost(&$data)
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
}
