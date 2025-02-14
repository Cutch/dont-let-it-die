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
        $lastNightCard = $this->game->globals->get('lastNightCard');
        $building = $this->game->globals->get('building');
        $characters = $this->game->character->getAllCharacterData();
        $equipment = array_filter($characters['equipment'], function ($c) {
            return $c['isActive'];
        })[0]['equipment'];
        $array = [$this->game->data->decks[$lastNightCard], $this->game->data->decks[$building], ...$characters, ...$equipment];
        foreach ($array as $i => $object) {
            if (isset($data[$functionName])) {
                $object[$functionName]($this->game, $object, $data1, $data2 = null, $data3 = null, $data4);
            }
        }
    }
    function onGetValidPlayerActions(&$data)
    {
        $this->callHooks(__FUNCTION__, $data);
    }
    function onGetStaminaCost(&$data)
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
