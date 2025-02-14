<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

class Decks
{
    private Game $game;
    private $decks;
    private static $decksNames = [
        'harvest',
        'forage',
        'hunt',
        'gather',
        'explore',
        'day-event',
        'night-event',
        'physical-hindrance',
        'mental-hindrance',
    ];
    public function __construct($game)
    {
        $this->game = $game;
        foreach (self::$decksNames as $i => $deck) {
            $this->decks[$deck] = $this->game->initDeck(str_replace('-', '', $deck));
        }
    }
    public function getAllDeckNames(): array
    {
        return self::$decksNames;
    }
    public function setup()
    {
        foreach (self::$decksNames as $i => $deck) {
            $this->createDeck($deck);
        }
    }
    public function pickCard(string $deck): array
    {
        $topCard = $this->decks[$deck]->getCardOnTop('deck');
        if (!$topCard) {
            $this->shuffleInDiscard($deck);
            $topCard = $this->decks[$deck]->getCardOnTop('deck');
        }
        $this->decks[$deck]->moveCards([$topCard['id']], 'discard');
        $card = $this->getCard($topCard['type_arg']);
        return $card;
    }
    public function getCard($id): array
    {
        $card = $this->game->data->decks[$id];
        $name = '';
        if (isset($card['resourceType'])) {
            $name = $this->game->data->tokens[$card['resourceType']]['name'];
        }
        if (isset($card['name'])) {
            $name = $card['name'];
        }
        return array_merge($this->game->data->decks[$id], ['id' => $id, 'name' => $name]);
    }
    protected function createDeck($type)
    {
        $filtered_cards = array_filter(
            $this->game->data->decks,
            function ($v, $k) use ($type) {
                return $v['deck'] == $type && $v['type'] == 'deck';
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
    public function getDecksData(): array
    {
        $result = ['decks' => [], 'decksDiscards' => []];
        foreach (self::$decksNames as $i => $deck) {
            $deck = str_replace('-', '', $deck);
            $result['decks'] = array_merge(
                $result['decks'],
                $this->game->getCollectionFromDb(
                    'SELECT `card_type` `type`, `card_location` `loc`, count(1) `count` FROM `' .
                        $deck .
                        '` GROUP BY card_type, card_location'
                )
            );
            $result['decksDiscards'] = array_merge(
                $result['decksDiscards'],
                $this->game->getCollectionFromDb(
                    "SELECT `card_type` `type`, `card_type_arg` `name`
                FROM `$deck` a
                WHERE `card_location` = 'discard' AND `card_location_arg` = (SELECT MAX(`card_location_arg`) FROM `$deck` b WHERE `card_location` = 'discard')"
                )
            );
        }
        return $result;
    }
    public function shuffleInDiscard($deck, $notify = true): void
    {
        $this->decks[$deck]->moveAllCardsInLocation('discard', 'deck');
        $this->decks[$deck]->shuffle('deck');
        $result = ['deck' => str_replace('-', ' ', $deck)];
        $this->game->getDecks($result);
        if ($notify) {
            $this->game->notify->all('shuffle', clienttranslate('The ${deck} deck is out of cards, shuffling'), $result);
        }
    }
    public function discardCards($deck, $callback): void
    {
        $deckCount = $this->decks[$deck]->countCardsInLocation('deck');
        $cards = $this->decks[$deck]->getCardOnTop($deckCount, 'deck');

        $cards = array_filter($cards, function ($card) use ($callback) {
            $callback($this->getCard($card['id']));
        });
        $this->decks[$deck]->moveCards(
            [
                array_map(function ($card) {
                    return $card['id'];
                }, $cards),
            ],
            'discard'
        );
        if ($deckCount - sizeof($cards) == 0) {
            $this->shuffleInDiscard($deck);
        }
    }
}
