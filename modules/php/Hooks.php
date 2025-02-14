<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class Hooks
{
    private Game $game;
    public function __construct(Game $game)
    {
        // $hooks = ['onGetValidPlayerActions', 'onMorning', 'onNight', 'onDraw', 'onEncounter', 'onEat', 'onCraft'];
        $this->game = $game;
    }
    private function callHooks($functionName, &$data1, &$data2 = null, &$data3 = null, &$data4 = null)
    {
        $lastNightCard = $this->game->globals->get('lastNightCard');
        $array = [$this->game->data->decks[$lastNightCard]];
        $this->game->character->getAllCharacterData();
        foreach ($array as $i => $data) {
            if (isset($data[$functionName])) {
                $data[$functionName]($this->game, $data1, $data2 = null, $data3 = null, $data4);
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
}
