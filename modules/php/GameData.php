<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DontLetItDie implementation : © Cutch <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;
use Exception;

class GameData
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function setup()
    {
        $this->game->globals->set('fireWood', '');
        $this->game->globals->set('state', []);
        $this->game->globals->set('lastNightCard', '');
        $this->game->globals->set('day', 1);
        $this->game->globals->set('wood', 0);
        $this->game->globals->set('bone', 0);
        $this->game->globals->set('meat', 0);
        $this->game->globals->set('meat-cooked', 0);
        $this->game->globals->set('fish', 0);
        $this->game->globals->set('fish-cooked', 0);
        $this->game->globals->set('dino-egg', 0);
        $this->game->globals->set('dino-egg-cooked', 0);
        $this->game->globals->set('berry', 0);
        $this->game->globals->set('berry-cooked', 0);
        $this->game->globals->set('rock', 0);
        $this->game->globals->set('stew', 0);
        $this->game->globals->set('fiber', 0);
        $this->game->globals->set('hide', 0);
        $this->game->globals->set('trap', 0);
        $this->game->globals->set('herb', 0);
        $this->game->globals->set('fkp', 0);
        $this->game->globals->set('gem', 0);
    }
}
