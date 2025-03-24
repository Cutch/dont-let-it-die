<?php

use Bga\Games\DontLetItDie\Game;

if (!function_exists('getUsePerDay')) {
    function getUsePerDay(string $itemId, $game)
    {
        $dailyUseItems = $game->gameData->get('dailyUseItems');
        return array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] : 0;
    }
    function usePerDay(string $itemId, $game)
    {
        $dailyUseItems = $game->gameData->get('dailyUseItems');
        $dailyUseItems[$itemId] = array_key_exists($itemId, $dailyUseItems) ? $dailyUseItems[$itemId] + 1 : 1;
        $game->gameData->set('dailyUseItems', $dailyUseItems);
    }
    function getUsePerForever(string $itemId, $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        return array_key_exists($itemId, $foreverUseItems) ? $foreverUseItems[$itemId] : 0;
    }
    function usePerForever(string $itemId, $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        $foreverUseItems[$itemId] = array_key_exists($itemId, $foreverUseItems) ? $foreverUseItems[$itemId] + 1 : 1;
        $game->gameData->set('foreverUseItems', $foreverUseItems);
    }
    function clearUsePerForever(string $itemId, $game)
    {
        $foreverUseItems = $game->gameData->get('foreverUseItems');
        $foreverUseItems[$itemId] = 0;
        $game->gameData->set('foreverUseItems', $foreverUseItems);
    }
    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = [];
                foreach ($data as $key => $row) {
                    $tmp[$key] = $row[$field] ?? '0';
                }
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
    function clearItemSkills(&$skills, $itemId)
    {
        array_walk($skills, function ($v, $k) use (&$skills, $itemId) {
            if ($v['itemId'] == $itemId) {
                unset($skills[$k]);
            }
        });
    }
}
$itemsData = [
    'bow-and-arrow' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => clienttranslate('Bow And Arrow'),
        'itemType' => 'weapon',
        'damage' => 3,
        'range' => 2,
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'wood' => 1,
            'hide' => 1,
        ],
    ],
    'medical-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Medical Hut'),
        'expansion' => 'hindrance',
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 3,
            'bone' => 2,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Remove 2 Physical Hindrances'),
                'state' => ['interrupt'],
                'interruptState' => ['morningPhase'],
                'perDay' => 1,
                'onMorning' => function (Game $game, $skill, &$data) {
                    $game->actInterrupt->addSkillInterrupt($skill);
                },
                'onUseSkill' => function (Game $game, $skill, &$data) {
                    if ($data['skillId'] == $skill['id']) {
                        $characters = $game->character->getAllCharacterData(true);
                        $charactersWithHindrances = array_values(
                            array_map(
                                function ($character) {
                                    return $character['id'];
                                },
                                array_filter($characters, function ($character) {
                                    sizeof($character['physicalHindrance']) > 0;
                                })
                            )
                        );
                        $game->gameData->set('characterSelectionState', [
                            'selectableCharacters' => $charactersWithHindrances,
                            'cancellable' => false,
                            'id' => $skill['id'],
                        ]);
                        $data['interrupt'] = true;
                        $game->gamestate->nextState('characterSelection');
                    }
                },
                'onCharacterSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('characterSelectionState');
                    if ($state && $state['id'] == $skill['id']) {
                        usePerDay($skill['id'], $game);
                        $data['nextState'] = false;
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $characters = $game->character->getAllCharacterData(true);
                    return getUsePerDay($skill['id'], $game) < 1 &&
                        sizeof(
                            array_filter($characters, function ($character) {
                                return sizeof($character['physicalHindrance']) > 0;
                            })
                        ) > 0;
                },
            ],
        ],
    ],
    'bone-club' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => clienttranslate('Bone Club'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 3,
        'cost' => [
            'fiber' => 1,
            'rock' => 1,
            'wood' => 1,
            'bone' => 2,
        ],
    ],
    'bone-scythe' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => clienttranslate('Bone Scythe'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'bone' => 2,
        ],
        'onDraw' => function (Game $game, $item, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'fiber') {
                $game->gameData->setResource('fiber', $game->gameData->getResource('fiber') + 1);
                $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and received one ${resource_type}'), [
                    'item_name' => $item['name'],
                    'resource_type' => $card['resourceType'],
                ]);
            }
        },
    ],
    'bag' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => clienttranslate('Bag'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'hide' => 1,
        ],
        'onDraw' => function (Game $game, $item, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'berry') {
                $game->gameData->setResource('berry', $game->gameData->getResource('berry') + 1);
                $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and received one ${resource_type}'), [
                    'item_name' => $item['name'],
                    'resource_type' => $card['resourceType'],
                ]);
            }
        },
    ],
    'bone-armor' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => clienttranslate('Bone Armor'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 1,
            'bone' => 3,
            'rock' => 1,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Ignore Damage'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'perDay' => 2,
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    $interruptState = $game->actInterrupt->getState('actUseItem');
                    if (
                        !$char['isActive'] &&
                        $data['action'] == 'actUseItem' &&
                        $data['subAction'] == $skill['id'] &&
                        $interruptState &&
                        array_key_exists('data', $interruptState) &&
                        $interruptState['data']['skillId'] == $skill['id']
                    ) {
                        $data['perDay'] = 2 - getUsePerDay($char['id'] . $skill['id'], $game);
                    }
                },
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($skill['characterId']);

                    if ($char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'] . $skill['id'], $game);
                        $game->log('onInterrupt', $data);
                        $data['data']['willTakeDamage'] = 0;

                        $game->activeCharacterEventLog('used ${item_name} to block the damage', [
                            'item_name' => clienttranslate('Bone Armor'),
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 2;
                },
            ],
        ],
    ],
    'camp-walls' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Camp Walls'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'bone' => 2,
        ],
        'onNight' => function (Game $game, $item, &$data) {
            if (array_key_exists('eventType', $data['card']) && $data['card']['eventType'] == 'rival-tribe') {
                $game->character->adjustHealth($item['characterId'], 1);
                $game->activeCharacterEventLog('Camp walls protect against the rival tribe');
            }
        },
    ],
    'fire' => [
        'type' => 'game-piece',
        'name' => 'Fire',
    ],
    'hide-armor' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => clienttranslate('Hide Armor'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 1,
            'hide' => 2,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Ignore Damage'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'perDay' => 1,
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($skill['characterId']);

                    if ($char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'] . $skill['id'], $game);
                        $game->log('onInterrupt', $data);
                        $data['data']['willTakeDamage'] = 0;

                        $game->activeCharacterEventLog('used ${item_name} to block the damage', [
                            'item_name' => clienttranslate('Hide Armor'),
                        ]);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                },
            ],
        ],
    ],
    'knowledge-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Knowledge Hut'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
        'onUse' => function (Game $game, $item) {
            usePerDay($item, $game);
        },
        'requires' => function (Game $game, $item) {
            return getUsePerDay($item, $game) < 1;
        },
        'onInvestigateFire' => function (Game $game, $item, &$data) {
            $char = $game->character->getTurnCharacter();
            if (getUsePerDay($item['name'] . $char['id'] . 'investigateFire', $game) < 1) {
                usePerDay($item['name'] . $char['id'] . 'investigateFire', $game);

                if ($game->adjustResource('fkp', 1) == 0) {
                    $game->notify->all('usedItem', clienttranslate('The ${item_name} grants an extra fkp'), [
                        'item_name' => $item['name'],
                    ]);
                }
            }
        },
    ],
    'skull' => [
        'type' => 'game-piece',
        'name' => 'Skull',
    ],
    'hatchet' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'name' => clienttranslate('Hatchet'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'wood' => 1,
            'rock' => 2,
        ],
        'onDraw' => function (Game $game, $item, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'wood') {
                $game->gameData->setResource('wood', $game->gameData->getResource('wood') + 1);
                $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and received one ${resource_type}'), [
                    'item_name' => $item['name'],
                    'resource_type' => $card['resourceType'],
                ]);
            }
        },
    ],
    'club' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'name' => clienttranslate('Club'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
        'cost' => [
            'wood' => 1,
        ],
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            if ($data['character_name'] == $item['character_name']) {
                $data['maxStamina'] -= 1;
                $data['stamina'] = min($data['maxStamina'], $data['stamina']);
            }
        },
    ],
    'cooking-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Cooking Hut'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
        'onEat' => function (Game $game, $item, &$data) {
            $data['health'] += 2;
        },
    ],
    'carving-knife' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => clienttranslate('Carving Knife'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'bone' => 1,
        ],
        'onDraw' => function (Game $game, $item, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'meat') {
                $game->adjustResource('meat', 1);
                $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and received one ${resource_type}'), [
                    'item_name' => $item['name'],
                    'resource_type' => $card['resourceType'],
                ]);
            }
        },
    ],
    'item-back' => [
        'type' => 'item',
        'type' => 'back',
        'name' => 'Item Back',
    ],
    'sling-shot' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => clienttranslate('Sling Shot'),
        'itemType' => 'weapon',
        'damage' => 3,
        'range' => 2,
        'useCost' => [
            'rock' => 1,
        ],
        'cost' => [
            'fiber' => 1,
            'hide' => 1,
            'wood' => 1,
        ],
    ],
    'pick-axe' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => clienttranslate('Pick Axe'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'wood' => 1,
            'rock' => 1,
        ],
        'onDraw' => function (Game $game, $item, &$data) {
            $card = $data['card'];
            if ($card['deckType'] == 'resource' && $card['resourceType'] == 'rock') {
                $game->gameData->setResource('rock', $game->gameData->getResource('rock') + 1);
                $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and received one ${resource_type}'), [
                    'item_name' => $item['name'],
                    'resource_type' => $card['resourceType'],
                ]);
            }
        },
    ],
    'planning-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Planning Hut'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Draw 2 Pick 1 (') . clienttranslate('Planning Hut') . ')',
                'state' => ['interrupt'],
                'interruptState' => ['playerTurn'],
                'perDay' => 2,
                'onGetActionCost' => function (Game $game, $skill, &$data) {
                    if ($data['action'] == 'actUseItem' && $data['subAction'] == $skill['id']) {
                        $data['perDay'] = 2 - getUsePerDay($skill['id'], $game);
                    }
                },
                'onDraw' => function (Game $game, $skill, &$data) {
                    $deck = $data['deck'];
                    if (
                        in_array($deck, ['forage', 'gather', 'harvest', 'hunt', 'explore']) &&
                        getUsePerDay($skill['id'], $game) < 2 &&
                        !$game->actInterrupt->getState('actDraw')
                    ) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onUseSkill' => function (Game $game, $skill, &$data) {
                    if ($data['skillId'] == $skill['id']) {
                        $existingData = $game->actInterrupt->getState('actDraw');
                        if (array_key_exists('data', $existingData)) {
                            $deck = $existingData['data']['deck'];
                            $card1 = $existingData['data']['card'];
                            $card2 = $game->decks->pickCard($deck);
                            $game->gameData->set('cardSelectionState', [
                                'cards' => [$card1, $card2],
                                'deck' => $deck,
                                'cancellable' => false,
                                'id' => $skill['id'],
                            ]);
                            $data['interrupt'] = true;
                            $game->gamestate->nextState('cardSelection');
                        }
                    }
                },
                'onCardSelection' => function (Game $game, $skill, &$data) {
                    $state = $game->gameData->get('cardSelectionState');
                    if ($state && $state['id'] == $skill['id']) {
                        usePerDay($skill['id'], $game);
                        $discardCard = array_values(
                            array_filter($state['cards'], function ($card) use ($data) {
                                return $card['id'] != $data['cardId'];
                            })
                        )[0];
                        $game->cardDrawEvent($discardCard, $state['deck']);

                        $drawState = $game->actInterrupt->getState('actDraw');
                        $drawState['data']['card'] = $game->decks->getCard($data['cardId']);
                        $game->actInterrupt->setState('actDraw', $drawState);
                        $game->actInterrupt->actInterrupt($skill['id']);
                        $data['nextState'] = false;
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerDay($skill['id'], $game) < 2;
                },
            ],
        ],
    ],
    'spear' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'name' => clienttranslate('Spear'),
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
        'cost' => [
            'fiber' => 1,
            'rock' => 2,
            'wood' => 1,
        ],
    ],
    'sharp-stick' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => clienttranslate('Sharp Stick'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
        'cost' => [
            'wood' => 1,
            'rock' => 1,
        ],
    ],
    'shelter' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'name' => clienttranslate('Shelter'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
        'onMorning' => function (Game $game, $nightCard, &$data) {
            $data['health'] = 0;
            $game->nightEventLog('No damage taken in the morning');
        },
    ],
    'rock-knife' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'name' => clienttranslate('Rock Knife'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 2,
        'cost' => [
            'fiber' => 1,
            'rock' => 2,
        ],
    ],
    'stone-hammer' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Stone Hammer'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'wood' => 1,
        ],
        'onGetActionCost' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && $data['action'] == 'actCraft') {
                $data['action'] -= 2;
            }
        },
    ],
    'mortar-and-pestle' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Mortar And Pestle'),
        'itemType' => 'tool',
        'cost' => [
            'rock' => 3,
        ],
    ],
    'bandage' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Bandage'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 1,
            'hide' => 1,
            'herb' => 1,
        ],
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            if ($item['characterId'] == $data['id']) {
                $data['maxHealth'] += 1;
                $data['health'] = min($data['maxHealth'], $data['health']);
            }
        },
        'onIncapacitation' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive']) {
                $game->activeCharacterEventLog('used their bandage to revive');
                $game->destroyItem($item['itemId']);
            }
        },
    ],
    'skull-shield' => [
        'type' => 'item',
        'craftingLevel' => 4,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Skull Shield'),
        'itemType' => 'tool',
        // 'character' => 'Faye',
        'cost' => [],
        'onGetCharacterData' => function (Game $game, $item, &$data) {
            if ($item['characterId'] == $data['id']) {
                $data['maxHealth'] += 1;
                $data['health'] = min($data['maxHealth'], $data['health']);
            }
        },
        'onEncounter' => function (Game $game, $item, &$data) {
            $damageTaken = $game->encounter->countDamageTaken($data);
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && $damageTaken > 0 && $game->rollFireDie($item['characterId']) == 1) {
                $data['willTakeDamage'] -= 1;
            }
        },
    ],
    'cooking-pot' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Cooking Pot'),
        'itemType' => 'tool',
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
            'bone' => 2,
        ],
        'onGetActionCost' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && $data['action'] == 'actCook' && getUsePerDay($char['id'] . $item['itemId'], $game) % 2 == 0) {
                $data['stamina'] = 0;
            }
        },
        'onCook' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && getUsePerDay($char['id'] . $item['itemId'], $game) % 2 == 0) {
                $game->activeCharacterEventLog('another cook action can be used for free');
                usePerDay($char['id'] . $item['itemId'], $game);
                $data['stamina'] = 0;
            }
        },
    ],
    'bone-claws' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Bone Claws'),
        'itemType' => 'tool',
        'cost' => [
            'rock' => 2,
            'bone' => 2,
        ],
        'onGetActionCost' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && $data['action'] == 'actExplore') {
                $data['action'] -= 2;
            }
        },
    ],
    'bone-flute' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Bone Flute'),
        'itemType' => 'tool',
        'cost' => [
            'hide' => 1,
            'bone' => 2,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Soothe'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'perDay' => 1,
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $damageTaken = $game->encounter->countDamageTaken($data);
                    $char = $game->character->getCharacterData($skill['characterId']);

                    if ($char['isActive'] && $damageTaken > 0) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'] . $skill['id'], $game);
                        $data['data']['soothe'] = true;
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                },
            ],
        ],
    ],
    'stock-hut' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Stock Hut'),
        'itemType' => 'building',
        'cost' => [
            'fiber' => 3,
            'rock' => 3,
            'hide' => 2,
            'bone' => 2,
        ],
        'onGetTradeRatio' => function (Game $game, $item, &$data) {
            $data['ratio'] = 2;
        },
    ],
    'whip' => [
        'type' => 'item',
        'craftingLevel' => 2,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Whip'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 2,
        'cost' => [
            'fiber' => 3,
            'hide' => 2,
        ],
    ],
    'fire-stick' => [
        'type' => 'item',
        'craftingLevel' => 4,
        'count' => 1,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Fire Stick'),
        'itemType' => 'weapon',
        'range' => 1,
        'damage' => 1,
        // 'character' => 'Rex',
        'cost' => [],
        'onEncounter' => function (Game $game, $item, &$data) {
            $data['characterDamage'] = $game->rollFireDie($item['characterId']);
        },
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Increase Attack (3 fkp)'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'onEncounter' => function (Game $game, $skill, &$data) {
                    if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'rock-weapon' => [
        'type' => 'item',
        'craftingLevel' => 0,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Rock'),
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 1,
        'cost' => [
            'rock' => 1,
        ],
        'onUse' => function (Game $game, $item) {
            $game->character->unequipEquipment($item['characterId'], [$item['id']]);
            $game->notify->all('usedItem', clienttranslate('${character_name} used ${item_name} and lost their ${item_name}'), [
                'item_name' => $item['name'],
            ]);
        },
        'onGetActionCost' => function (Game $game, $item, &$data) {
            $char = $game->character->getCharacterData($item['characterId']);
            if ($char['isActive'] && $data['action'] == 'actCraft' && $data['subAction'] == 'rock') {
                $data['stamina'] = 1;
            }
        },
    ],
    'bola' => [
        'type' => 'item',
        'craftingLevel' => 1,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Bola'),
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
        'cost' => [
            'fiber' => 2,
            'rock' => 2,
        ],
        'useCost' => [], //Makes this optional
        // 'onEncounter' => function (Game $game, $item, &$data) {
        //     $char = $game->character->getCharacterData($item['characterId']);
        //     if ($char['isActive'] && ($data['soothe'] || $data['escape']) && in_array($item['itemId'], $data['itemIds'])) {
        //         $data['willTakeDamage'] -= 1;
        //     }
        // },
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Keep Bola'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'stamina' => 2,
                'onEncounter' => function (Game $game, $skill, &$data) {
                    if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        clearItemSkills($data['skills'], $skill['itemId']);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
            'skill2' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Discard Bola'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'onEncounter' => function (Game $game, $skill, &$data) {
                    if (!($data['soothe'] || $data['escape']) && in_array($skill['itemId'], $data['itemIds'])) {
                        $game->actInterrupt->addSkillInterrupt($skill);
                    }
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $game->log('$skill', $skill);
                        $game->character->unequipEquipment($skill['characterId'], [$skill['itemId']]);
                        clearItemSkills($data['skills'], $skill['itemId']);
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'];
                },
            ],
        ],
    ],
    'boomerang' => [
        'type' => 'item',
        'craftingLevel' => 3,
        'count' => 2,
        'expansion' => 'hindrance',
        'name' => clienttranslate('Boomerang'),
        'itemType' => 'weapon',
        'range' => 2,
        'damage' => 2,
        'cost' => [
            'rock' => 2,
            'hide' => 2,
            'wood' => 1,
        ],
        'skills' => [
            'skill1' => [
                'type' => 'item-skill',
                'name' => clienttranslate('Reduce Life By 1'),
                'state' => ['interrupt'],
                'interruptState' => ['resolveEncounter'],
                'perDay' => 1,
                'onEncounter' => function (Game $game, $skill, &$data) {
                    $game->actInterrupt->addSkillInterrupt($skill);
                },
                'onInterrupt' => function (Game $game, $skill, &$data, $activatedSkill) {
                    if ($skill['id'] == $activatedSkill['id']) {
                        $char = $game->character->getCharacterData($skill['characterId']);
                        usePerDay($char['id'] . $skill['id'], $game);
                        $data['data']['encounterHealth'] -= 1;
                    }
                },
                'requires' => function (Game $game, $skill) {
                    $char = $game->character->getCharacterData($skill['characterId']);
                    return $char['isActive'] && getUsePerDay($char['id'] . $skill['id'], $game) < 1;
                },
            ],
        ],
    ],
];
array_walk($itemsData, function (&$item) {
    $item['totalCost'] = array_sum(array_values(array_key_exists('cost', $item) ? $item['cost'] : []));
});
$itemsData = array_orderby($itemsData, 'craftingLevel', SORT_ASC, 'itemType', SORT_DESC, 'totalCost', SORT_ASC);
