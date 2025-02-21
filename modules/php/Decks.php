<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

class Decks
{
    private Game $game;
    private array $decks;
    private array $cachedData = [];
    private static $decksNames = [
        'harvest',
        'forage',
        'hunt',
        'gather',
        'day-event',
        'night-event',
        'explore',
        'physical-hindrance',
        'mental-hindrance',
    ];
    public function __construct($game)
    {
        $this->game = $game;
        foreach ($this->getAllDeckNames() as $i => $deck) {
            $this->decks[$deck] = $this->game->initDeck(str_replace('-', '', $deck));
        }
    }
    public function getAllDeckNames(): array
    {
        $_this = $this;
        return array_filter(self::$decksNames, function ($name) use ($_this) {
            return array_key_exists($name . '-back', $_this->game->data->decks);
        });
    }
    public function setup()
    {
        foreach ($this->getAllDeckNames() as $i => $deck) {
            $this->createDeck($deck);
        }
    }
    protected function createDeck($type)
    {
        $filtered_cards = array_filter(
            $this->game->data->decks,
            function ($v, $k) use ($type) {
                return $v['type'] == 'deck' && $v['deck'] == $type;
            },
            ARRAY_FILTER_USE_BOTH
        );
        $cards = array_map(
            function ($k, $v) {
                return [
                    'type' => $v['deck'],
                    'card_location' => 'deck',
                    'type_arg' => $k,
                    'nbr' => $v['count'] ?? 1,
                ];
            },
            array_keys($filtered_cards),
            $filtered_cards
        );
        $this->decks[$type]->createCards($cards, 'deck');
        $this->decks[$type]->shuffle('deck');
    }
    public function getCard($id): array
    {
        $card = $this->game->data->decks[$id];
        $name = '';
        if (array_key_exists('resourceType', $card)) {
            $name = $this->game->data->tokens[$card['resourceType']]['name'];
        }
        if (array_key_exists('name', $card)) {
            $name = $card['name'];
        }
        return array_merge($this->game->data->decks[$id], ['id' => $id, 'name' => $name]);
    }
    public function getDecksData(): array
    {
        $result = ['decks' => [], 'decksDiscards' => []];
        foreach ($this->getAllDeckNames() as $i => $deck) {
            $deckData = null;
            $discardData = null;
            if (array_key_exists($deck, $this->cachedData)) {
                $deckData = $this->cachedData[$deck]['decks'];
                $discardData = $this->cachedData[$deck]['decksDiscards'];
            } else {
                $sqlName = str_replace('-', '', $deck);
                $deckData = $this->game->getCollectionFromDb(
                    'SELECT `card_type` `type`, sum(CASE WHEN `card_location` = "deck" THEN 1 ELSE 0 END) `count`, sum(CASE WHEN `card_location` = "discard" THEN 1 ELSE 0 END) `discardCount` FROM `' .
                        $sqlName .
                        '`'
                );
                $discardData = $this->game->getCollectionFromDb(
                    "SELECT `card_type` `type`, `card_type_arg` `name`
                FROM `$sqlName` a
                WHERE `card_location` = 'discard' AND `card_location_arg` = (SELECT MAX(`card_location_arg`) FROM `$sqlName` b WHERE `card_location` = 'discard')"
                );
                $this->cachedData[$deck] = ['decks' => $deckData, 'decksDiscards' => $discardData];
            }
            $result['decks'] = array_merge($result['decks'], $deckData);
            $result['decksDiscards'] = array_merge($result['decksDiscards'], $discardData);
        }
        return $result;
    }
    public function shuffleInDiscard($deck, $notify = true): void
    {
        $this->decks[$deck]->moveAllCardsInLocation('discard', 'deck');
        $this->decks[$deck]->shuffle('deck');
        unset($this->cachedData[$deck]);
        $results = [];
        $this->game->getDecks($results);
        if ($notify) {
            $this->game->notify->all('shuffle', clienttranslate('The ${deck_name} deck is out of cards, shuffling'), [
                'gameData' => $results,
                'deck' => str_replace('-', ' ', $deck),
                'deckName' => $deck,
            ]);
        } else {
            $this->game->notify->all('shuffle', '', [
                'gameData' => $results,
                'deck' => $deck,
                'deck_name' => str_replace('-', ' ', $deck),
            ]);
        }
    }
    public function pickCard(string $deck): array
    {
        $topCard = $this->decks[$deck]->getCardOnTop('deck');
        if (!$topCard) {
            $this->shuffleInDiscard($deck);
            $topCard = $this->decks[$deck]->getCardOnTop('deck');
        }
        $this->decks[$deck]->insertCardOnExtremePosition($topCard['id'], 'discard', true);
        $card = $this->getCard($topCard['type_arg']);
        unset($this->cachedData[$deck]);
        return $card;
    }
    public function discardCards($deck, $callback): void
    {
        $deckCount = $this->decks[$deck]->countCardsInLocation('deck');
        $cards = $this->decks[$deck]->getCardOnTop($deckCount, 'deck');

        $cards = array_filter($cards, function ($card) use ($callback) {
            $callback($this->getCard($card['id']));
        });
        array_walk(function ($card) use ($deck) {
            $this->decks[$deck]->insertCardOnExtremePosition($card['id'], 'discard', true);
        }, $cards);
        unset($this->cachedData[$deck]);
        if ($deckCount - sizeof($cards) == 0) {
            $this->shuffleInDiscard($deck);
        }
    }
}
