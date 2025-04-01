<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Deck;
use Exception;

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
    public function __construct(Game $game)
    {
        $this->game = $game;
        foreach ($this->getAllDeckNames() as $i => $deck) {
            $this->decks[$deck] = $this->game->initDeck(str_replace('-', '', $deck));
        }
        $type = 'explore';
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
                    'nbr' => 1,
                ];
            },
            array_keys($filtered_cards),
            $filtered_cards
        );
        $this->game->log($cards);
    }
    public function getDeck(string $name): Deck
    {
        return $this->decks[$name];
    }
    public function getAllDeckNames(): array
    {
        return array_values(
            array_filter(self::$decksNames, function ($name) {
                return array_key_exists($name . '-back', $this->game->data->decks);
            })
        );
    }
    public function setup()
    {
        foreach ($this->getAllDeckNames() as $i => $deck) {
            $this->createDeck($deck);
        }
    }
    protected function createDeck(string $type)
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
        $this->getDeck($type)->createCards($cards, 'deck');
        $this->getDeck($type)->shuffle('deck');
    }
    public function getCard(string $id): array
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
        $tokens = $this->game->gameData->get('tokens');
        foreach ($result['decks'] as $deckName => &$data) {
            if (array_key_exists($deckName, $tokens)) {
                $data['tokens'] = $tokens[$deckName];
            }
        }
        return $result;
    }
    public function addBackToDeck(string $deck, string $cardName): void
    {
        $cards = array_filter($this->getDeck($deck)->getCardsInLocation('hand'), function ($card) use ($cardName) {
            return $card['type_arg'] == $cardName;
        });
        if (sizeof($cards) > 0) {
            $this->getDeck($deck)->moveCard(array_values($cards)[0]['id'], 'discard');
        } else {
            throw new Exception('Missing card id');
        }
    }
    public function removeFromDeck(string $deck, string $cardName): void
    {
        $cards = array_filter($this->getDeck($deck)->getCardsInLocation('discard'), function ($card) use ($cardName) {
            return $card['type_arg'] == $cardName;
        });
        if (sizeof($cards) > 0) {
            $this->getDeck($deck)->moveCard(array_values($cards)[0]['id'], 'hand');
        } else {
            throw new Exception('Missing card id');
        }
    }
    public function shuffleInDiscard(string $deck, bool $notify = true): void
    {
        $this->getDeck($deck)->moveAllCardsInLocation('discard', 'deck');
        $this->getDeck($deck)->shuffle('deck');
        unset($this->cachedData[$deck]);
        $results = [
            'deck' => $deck,
            'deckName' => str_replace('-', ' ', $deck),
        ];
        $this->game->getDecks($results);
        if ($notify) {
            $this->game->notify->all('shuffle', clienttranslate('The ${deckName} deck is out of cards, shuffling'), $results);
        } else {
            $this->game->notify->all('shuffle', '', $results);
        }
    }
    public function pickCard(string $deck): array
    {
        $topCard = $this->getDeck($deck)->getCardOnTop('deck');
        if (!$topCard) {
            $this->shuffleInDiscard($deck);
            $topCard = $this->getDeck($deck)->getCardOnTop('deck');
        }
        $this->getDeck($deck)->insertCardOnExtremePosition($topCard['id'], 'discard', true);
        $card = $this->getCard($topCard['type_arg']);
        unset($this->cachedData[$deck]);
        return $card;
    }
    public function discardCards(string $deck, $callback): void
    {
        $deckCount = $this->getDeck($deck)->countCardsInLocation('deck');
        $cards = $this->getDeck($deck)->getCardOnTop($deckCount, 'deck');

        $cards = array_filter($cards, function ($card) use ($callback) {
            $callback($this->getCard($card['id']));
        });
        array_walk(function ($card) use ($deck) {
            $this->getDeck($deck)->insertCardOnExtremePosition($card['id'], 'discard', true);
        }, $cards);
        unset($this->cachedData[$deck]);
        if ($deckCount - sizeof($cards) == 0) {
            $this->shuffleInDiscard($deck);
        }
    }
}
