<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
require_once dirname(__DIR__) . '/php/data/Utils.php';
require_once dirname(__DIR__) . '/php/data/Boards.php';
require_once dirname(__DIR__) . '/php/data/Characters.php';
require_once dirname(__DIR__) . '/php/data/Decks.php';
require_once dirname(__DIR__) . '/php/data/Expansion.php';
require_once dirname(__DIR__) . '/php/data/KnowledgeTree.php';
require_once dirname(__DIR__) . '/php/data/Items.php';
require_once dirname(__DIR__) . '/php/data/Tokens.php';
require_once dirname(__DIR__) . '/php/data/Upgrades.php';
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
        if (!isset($this->decks)) {
            $decksData = (new DecksData())->getData();
            $expansionData = (new ExpansionData())->getData();
            $charactersData = (new CharactersData())->getData();
            $tokensData = (new TokensData())->getData();
            $boardsData = (new BoardsData())->getData();
            $knowledgeTreeData = (new KnowledgeTreeData())->getData();
            $upgradesData = (new UpgradesData())->getData();
            $itemsData = (new ItemsData())->getData();
            $this->decks = array_merge(addId($decksData), addId($expansionData));
            $this->characters = addId($charactersData);
            $this->expansion = addId($expansionData);
            $this->tokens = addId($tokensData);
            $this->boards = addId($boardsData);
            $this->knowledgeTree = array_merge(addId($knowledgeTreeData), addId($upgradesData));
            $this->items = addId($itemsData);
            $this->upgrades = addId($upgradesData);
        }

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
        $data = $this->getBoards()['knowledge-tree-' . $this->game->getDifficulty()]['track'];
        $unlocks = $this->game->getUnlockedKnowledgeIds(false);
        $upgrades = $this->game->gameData->get('upgrades');
        $mapping = [];
        array_walk($upgrades, function ($v, $k) use (&$mapping) {
            $mapping[$v['replace']] = $this->getUpgrades()[$k];
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
