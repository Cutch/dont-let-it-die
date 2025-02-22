<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
include dirname(__DIR__) . '/data/Utils.php';
class Data
{
    private Game $game;
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
        $this->game = $game;
        include dirname(__DIR__) . '/data/Boards.php';
        include dirname(__DIR__) . '/data/Characters.php';
        include dirname(__DIR__) . '/data/Decks.php';
        include dirname(__DIR__) . '/data/Expansion.php';
        include dirname(__DIR__) . '/data/KnowledgeTree.php';
        include dirname(__DIR__) . '/data/Items.php';
        include dirname(__DIR__) . '/data/Tokens.php';
        include dirname(__DIR__) . '/data/Upgrades.php';
        $expansionFilter = function ($data) use ($game) {
            if (!array_key_exists('expansion', $data)) {
                return true;
            }
            return $game->isValidExpansion($data['expansion']);
        };
        $this->decks = array_merge(
            addId(array_filter($decksData, $expansionFilter)),
            addId(array_filter($expansionData, $expansionFilter))
        );
        $this->characters = addId(array_filter($charactersData, $expansionFilter));
        $this->tokens = addId(array_filter($tokensData, $expansionFilter));
        $this->boards = addId(array_filter($boardsData, $expansionFilter));
        $this->knowledgeTree = addId(array_filter($knowledgeTreeData, $expansionFilter));
        $this->items = addId(array_filter($itemsData, $expansionFilter));
        $this->upgrades = addId(array_filter($upgradesData, $expansionFilter));
    }
    public function getValidKnowledgeTree()
    {
        $data = $this->boards['knowledge-tree-' . $this->game->getDifficulty()]['track'];
        return array_filter($data, function ($v) {
            return !array_key_exists('requires', $v) || $v['requires']($this->game, $v);
        });
    }
}
