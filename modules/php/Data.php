<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

class Data
{
    public array $decks;
    public array $characters;
    public array $tokens;
    public array $boards;
    public array $items;
    public array $upgrades;
    public array $expansion;

    public function __construct(Game $game)
    {
        include dirname(__DIR__) . '/data/boards.php';
        include dirname(__DIR__) . '/data/characters.php';
        include dirname(__DIR__) . '/data/decks.php';
        include dirname(__DIR__) . '/data/expansion.php';
        include dirname(__DIR__) . '/data/items.php';
        include dirname(__DIR__) . '/data/tokens.php';
        include dirname(__DIR__) . '/data/upgrades.php';
        $expansion = $game->getExpansion();
        $expansionI = array_search($expansion, $game::$expansionList);
        $expansionList = $game::$expansionList;
        $expansionFilter = function ($data) use ($expansionI, $expansionList) {
            if (!isset($data['expansion'])) {
                return true;
            }
            return array_search($data['expansion'], $expansionList) <= $expansionI;
        };
        $this->decks = array_filter($decksData, $expansionFilter);
        $this->characters = array_filter($charactersData, $expansionFilter);
        $this->tokens = array_filter($tokensData, $expansionFilter);
        $this->boards = array_filter($boardsData, $expansionFilter);
        $this->items = array_filter($itemsData, $expansionFilter);
        $this->upgrades = array_filter($upgradesData, $expansionFilter);
        $this->expansion = array_filter($expansionData, $expansionFilter);
    }
}
