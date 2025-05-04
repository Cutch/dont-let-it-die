<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
include dirname(__DIR__) . '/data/Utils.php';
class Data
{
    private Game $game;
    private array $decks;
    private array $characters;
    private array $tokens;
    private array $boards;
    private array $knowledgeTree;
    private array $items;
    private array $upgrades;
    private array $expansion;

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
        $this->knowledgeTree = array_merge(addId($knowledgeTreeData), addId($upgradesData));
        $this->items = addId($itemsData);
        $this->upgrades = addId($upgradesData);
    }
    private function expansionFilter(array $data)
    {
        if (array_key_exists('disabled', $data)) {
            return false;
        }
        if (!array_key_exists('expansion', $data)) {
            return true;
        }
        return $this->game->isValidExpansion($data['expansion']);
    }
    private function get($name)
    {
        return array_filter($this->$name, [$this, 'expansionFilter']);
    }
    public function getDecks()
    {
        return $this->get('decks');
    }
    public function getCharacters()
    {
        return $this->get('characters');
    }
    public function getExpansion()
    {
        return $this->get('expansion');
    }
    public function getTokens()
    {
        return $this->get('tokens');
    }
    public function getBoards()
    {
        return $this->get('boards');
    }
    public function getKnowledgeTree()
    {
        return $this->get('knowledgeTree');
    }
    public function getItems()
    {
        return $this->get('items');
    }
    public function getUpgrades()
    {
        return $this->get('upgrades');
    }
    public function getValidKnowledgeTree()
    {
        $data = $this->boards['knowledge-tree-' . $this->game->getDifficulty()]['track'];
        $unlocks = $this->game->getUnlockedKnowledgeIds(false);
        $upgrades = $this->game->gameData->get('upgrades');
        $mapping = [];
        array_walk($upgrades, function ($v, $k) use (&$mapping) {
            $mapping[$v['replace']] = $this->upgrades[$k];
        });
        $hasRequiredData = array_filter(
            $data,
            function ($v, $k) use ($unlocks) {
                return !in_array($k, $unlocks) && (!array_key_exists('requires', $v) || $v['requires']($this->game, $v));
            },
            ARRAY_FILTER_USE_BOTH
        );
        array_walk($hasRequiredData, function ($v, $k) use ($mapping, &$hasRequiredData) {
            if (array_key_exists($k, $mapping)) {
                unset($hasRequiredData[$k]);
                $hasRequiredData[$mapping[$k]['id']] = $mapping[$k];
            }
        });
        return array_map(function ($data) {
            $this->game->hooks->onGetUnlockCost($data);
            return $data;
        }, $hasRequiredData);
    }
}
