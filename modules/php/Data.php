<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
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
            if (!isset($data['expansion'])) {
                return true;
            }
            return array_search($data['expansion'], $expansionList) <= $expansionI;
        };
        $this->decks = $this->addId(array_filter($decksData, $expansionFilter));
        $this->characters = $this->addId(array_filter($charactersData, $expansionFilter));
        $this->tokens = $this->addId(array_filter($tokensData, $expansionFilter));
        $this->boards = $this->addId(array_filter($boardsData, $expansionFilter));
        $this->knowledgeTree = $this->addId(array_filter($knowledgeTreeData, $expansionFilter));
        $this->items = $this->addId(array_filter($itemsData, $expansionFilter));
        $this->upgrades = $this->addId(array_filter($upgradesData, $expansionFilter));
        $this->expansion = $this->addId(array_filter($expansionData, $expansionFilter));
    }
    function addId($data)
    {
        array_walk($data, function (&$v, $k) {
            $v['id'] = $k;
        });
        return $data;
    }
}
