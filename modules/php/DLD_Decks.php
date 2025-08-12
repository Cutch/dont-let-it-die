<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Deck;
use Exception;

class DLD_Decks
{
    private Game $game;
    private array $decks;
    private array $cachedData = [];
    private array $decksNames;
    public function __construct(Game $game)
    {
        $this->decksNames = [
            'explore' => clienttranslate('Explore'),
            'harvest' => clienttranslate('Harvest'),
            'forage' => clienttranslate('Forage'),
            'hunt' => clienttranslate('Hunt'),
            'gather' => clienttranslate('Gather'),
            'day-event' => clienttranslate('Day Event'),
            'night-event' => clienttranslate('Night Event'),
            'physical-hindrance' => clienttranslate('Physical Hindrance'),
            'mental-hindrance' => clienttranslate('Mental Hindrance'),
        ];
        $this->game = $game;
        foreach ($this->getAllDeckNames() as $deck) {
            $this->decks[$deck] = $this->game->initDeck(str_replace('-', '', $deck));
        }
    }
    public function getDeck(string $name): Deck
    {
        return $this->decks[$name];
    }
    public function getDeckName(string $name): string
    {
        return $this->decksNames[$name];
    }
    public function getAllDeckNames(): array
    {
        return array_values(
            array_filter(array_keys($this->decksNames), function ($name) {
                return array_key_exists($name . '-back', $this->game->data->getDecks());
            })
        );
    }
    public function setup()
    {
        foreach ($this->getAllDeckNames() as $deck) {
            $this->createDeck($deck);
        }
    }
    protected function createDeck(string $type)
    {
        $filtered_cards = array_filter(
            $this->game->data->getDecks(),
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
        $this->getDeck($type)->createCards($cards, 'deck');
        $this->getDeck($type)->shuffle('deck');
    }
    public function getCard(string $id): array
    {
        $card = $this->game->data->getDecks()[$id];
        $name = '';
        if (array_key_exists('resourceType', $card) && array_key_exists($card['resourceType'], $this->game->data->getTokens())) {
            $name = $this->game->data->getTokens()[$card['resourceType']]['name'];
        }
        if (array_key_exists('name', $card)) {
            $name = $card['name'];
        }
        return array_merge($this->game->data->getDecks()[$id], ['id' => $id, 'name' => $name]);
    }
    public function listDeckDiscards(array $decks): array
    {
        $decksDiscards = [];
        foreach ($decks as $deck) {
            $sqlName = str_replace('-', '', $deck);
            $discardData = array_map(
                function ($data) {
                    return $this->game->data->getDecks()[$data['id']];
                },
                array_values(
                    $this->game->getCollectionFromDb(
                        "SELECT `card_type_arg` `id`
                    FROM `$sqlName` a
                    WHERE `card_location` = 'discard'"
                    )
                )
            );

            $decksDiscards = array_merge($decksDiscards, $discardData);
        }
        return $decksDiscards;
    }
    public function getDecksData(): array
    {
        $result = ['decks' => [], 'decksDiscards' => []];
        foreach ($this->getAllDeckNames() as $deck) {
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
                    "SELECT `card_type_arg` `name`, `card_type` `type`
                FROM `$sqlName` a
                WHERE `card_location` = 'discard'
                ORDER BY card_location_arg DESC"
                );
                $map = [];
                foreach ($discardData as $element) {
                    $map[$element['type']][] = $element['name'];
                }
                $discardData = $map;
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
        $cards = array_values(
            array_filter($this->getDeck($deck)->getCardsInLocation('hand'), function ($card) use ($cardName) {
                return $card['type_arg'] == $cardName;
            })
        );
        if (sizeof($cards) > 0) {
            $this->getDeck($deck)->moveCard($cards[0]['id'], 'discard');
        } else {
            throw new Exception('Missing card id');
        }
    }
    public function removeFromDeck(string $deck, string $cardName): void
    {
        $cards = [...$this->getDeck($deck)->getCardsInLocation('discard'), ...$this->getDeck($deck)->getCardsInLocation('deck')];
        //swapCharacter(Yurt)
        $cards = array_values(
            array_filter($cards, function ($card) use ($cardName) {
                return $card['type_arg'] == $cardName;
            })
        );
        if (sizeof($cards) > 0) {
            $this->getDeck($deck)->moveCard($cards[0]['id'], 'hand');
        } else {
            throw new Exception('Missing card id');
        }
    }
    public function shuffleInCard(string $deck, string $cardName): void
    {
        $cards = array_values(
            array_filter($this->getDeck($deck)->getCardsInLocation('discard'), function ($card) use ($cardName) {
                return $card['type_arg'] == $cardName;
            })
        );
        if (sizeof($cards) > 0) {
            $this->getDeck($deck)->moveCard($cards[0]['id'], 'deck');
            $gameData = [];
            $this->game->getDecks($gameData);
            $results = [
                'i18n' => ['deckName'],
                'deck' => $deck,
                'deckName' => $this->getDeckName($deck),
                'gameData' => $gameData,
            ];
            $this->game->notify('shuffle', '', $results);
        } else {
            throw new Exception('Missing card id');
        }
    }
    public function shuffleInDiscard(string $deck, bool $notify = true): void
    {
        if ($this->getDeck($deck)->countCardsByLocationArgs('discard') > 0) {
            $this->getDeck($deck)->moveAllCardsInLocation('discard', 'deck');
            $this->getDeck($deck)->shuffle('deck');
            unset($this->cachedData[$deck]);
            $gameData = [];
            $this->game->getDecks($gameData);
            $results = [
                'i18n' => ['deckName'],
                'deck' => $deck,
                'deckName' => $this->getDeckName($deck),
                'gameData' => $gameData,
            ];
            if ($notify) {
                $this->game->notify('shuffle', clienttranslate('The ${deckName} deck is out of cards, shuffling'), $results);
            } else {
                $this->game->notify('shuffle', '', $results);
            }
        }
    }
    public function pickCard(string $deck): array
    {
        // $partials = $this->game->gameData->get('partials');
        // if (!array_key_exists($deck, $partials)) {
        // Would need to store all the decks in the undo data
        $this->game->markRandomness();
        // }

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
        $cards = $this->getDeck($deck)->getCardsOnTop($deckCount, 'deck');
        $cards = array_filter($cards, function ($card) use ($callback) {
            return $callback($this->getCard($card['type_arg']));
        });
        array_walk($cards, function ($card) use ($deck) {
            $this->getDeck($deck)->insertCardOnExtremePosition($card['id'], 'discard', true);
        });
        unset($this->cachedData[$deck]);
        if ($deckCount - sizeof($cards) == 0) {
            $this->shuffleInDiscard($deck);
        }
    }
}
