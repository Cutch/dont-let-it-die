<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;
require_once dirname(__DIR__) . '/php/data-files/DLD_Utils.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Boards.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Characters.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Decks.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Expansion.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_KnowledgeTree.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Items.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Tokens.php';
require_once dirname(__DIR__) . '/php/data-files/DLD_Upgrades.php';
class DLD_Data
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
            $decksData = (new DLD_DecksData())->getData();
            $expansionData = (new DLD_ExpansionData())->getData();
            $charactersData = (new DLD_CharactersData())->getData();
            $tokensData = (new DLD_TokensData())->getData();
            $boardsData = (new DLD_BoardsData())->getData();
            $knowledgeTreeData = (new DLD_KnowledgeTreeData())->getData();
            $upgradesData = (new DLD_UpgradesData())->getData();
            $itemsData = (new DLD_ItemsData())->getData();
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
        return $this->tokens;
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
