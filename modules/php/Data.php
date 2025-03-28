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
        $this->decks = array_merge(addId($decksData), addId($expansionData));
        $this->characters = addId($charactersData);
        $this->expansion = addId($expansionData);
        $this->tokens = addId($tokensData);
        $this->boards = addId($boardsData);
        $this->knowledgeTree = addId($knowledgeTreeData);
        $this->items = addId($itemsData);
        $this->upgrades = addId($upgradesData);
    }
    private function expansionFilter(array $data)
    {
        if (!array_key_exists('expansion', $data)) {
            return true;
        }
        return $this->game->isValidExpansion($data['expansion']);
    }
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return array_filter($this->$property, [$this, 'expansionFilter']);
        }
    }
    public function getValidKnowledgeTree()
    {
        $data = $this->boards['knowledge-tree-' . $this->game->getDifficulty()]['track'];
        $unlocks = $this->game->getUnlockedKnowledgeIds();
        return array_map(
            function ($data) {
                $this->game->hooks->onGetUnlockCost($data);
                return $data;
            },
            array_filter(
                $data,
                function ($v, $k) use ($unlocks) {
                    return !in_array($k, $unlocks) && (!array_key_exists('requires', $v) || $v['requires']($this->game, $v));
                },
                ARRAY_FILTER_USE_BOTH
            )
        );
    }
}
