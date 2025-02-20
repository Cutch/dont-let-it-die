<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
include dirname(__DIR__) . '/data/Utils.php';
class Data
{
    public array $decks;
    public array $characters;
    public array $tokens;
    public array $boards;
    public array $knowledgeTree;
    public array $items;
    public array $upgrades;
    public array $expansion;

    public function __construct(Game $game)
    {
        include dirname(__DIR__) . '/data/Boards.php';
        include dirname(__DIR__) . '/data/Characters.php';
        include dirname(__DIR__) . '/data/Decks.php';
        include dirname(__DIR__) . '/data/Expansion.php';
        include dirname(__DIR__) . '/data/KnowledgeTree.php';
        include dirname(__DIR__) . '/data/Items.php';
        include dirname(__DIR__) . '/data/Tokens.php';
        include dirname(__DIR__) . '/data/Upgrades.php';
        $expansion = $game->getExpansion();
        $expansionI = array_search($expansion, $game::$expansionList);
        $expansionList = $game::$expansionList;
        $expansionFilter = function ($data) use ($expansionI, $expansionList) {
            if (!array_key_exists('expansion', $data)) {
                return true;
            }
            return array_search($data['expansion'], $expansionList) <= $expansionI;
        };
        $this->decks = addId(array_filter($decksData, $expansionFilter));
        $this->characters = addId(array_filter($charactersData, $expansionFilter));
        $this->tokens = addId(array_filter($tokensData, $expansionFilter));
        $this->boards = addId(array_filter($boardsData, $expansionFilter));
        $this->knowledgeTree = addId(array_filter($knowledgeTreeData, $expansionFilter));
        $this->items = addId(array_filter($itemsData, $expansionFilter));
        $this->upgrades = addId(array_filter($upgradesData, $expansionFilter));
        $this->expansion = addId(array_filter($expansionData, $expansionFilter));
    }
}
