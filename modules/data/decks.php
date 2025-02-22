<?php

use Bga\Games\DontLetItDie\Game;

$decksData = [
    'explore-7_0' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'hindrance',
        'expansion' => 'hindrance',
    ],
    'explore-7_1' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Pterodactyl',
        'health' => 3,
        'damage' => 4,
    ],
    'explore-7_10' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'herb',
    ],
    'explore-7_11' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Bat',
        'health' => 1,
        'damage' => 2,
    ],
    'explore-7_12' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'gem-1',
    ],
    'explore-7_13' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'gem-2',
    ],
    'explore-7_14' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'gem-3',
    ],
    'explore-7_15' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'dino-egg',
    ],
    'explore-7_4' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Bear',
        'health' => 2,
        'damage' => 3,
    ],
    'explore-7_5' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Boar',
        'health' => 2,
        'damage' => 2,
    ],
    'explore-7_6' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Carnivorous Plant',
        'health' => 2,
        'damage' => 3,
    ],
    'explore-7_7' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Dino',
        'health' => 2,
        'damage' => 3,
    ],
    'explore-7_8' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'herb',
    ],
    'explore-7_9' => [
        'deck' => 'explore',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'dino-egg',
    ],
    'explore-back-7_2' => [
        'deck' => 'explore',
        'type' => 'back',
    ],
    'forage-7_10' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'hindrance',
        'expansion' => 'hindrance',
    ],
    'forage-7_11' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'nothing',
    ],
    'forage-7_12' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Beast',
        'health' => 1,
        'damage' => 1,
    ],
    'forage-7_13' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'berry',
    ],
    'forage-7_14' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'berry',
    ],
    'forage-7_15' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'berry',
    ],
    'forage-7_4' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Sabertooth',
        'health' => 1,
        'damage' => 2,
    ],
    'forage-7_8' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'fiber',
    ],
    'forage-7_9' => [
        'deck' => 'forage',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'fiber',
    ],
    'forage-back' => [
        'deck' => 'forage',
        'type' => 'back',
    ],
    'gather-7_10' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Sabertooth',
        'health' => 1,
        'damage' => 2,
    ],
    'gather-7_11' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'wood',
    ],
    'gather-7_12' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Beast',
        'health' => 1,
        'damage' => 1,
    ],
    'gather-7_13' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Boar',
        'health' => 2,
        'damage' => 2,
    ],
    'gather-7_14' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'hindrance',
        'expansion' => 'hindrance',
    ],
    'gather-7_15' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'nothing',
    ],
    'gather-7_4' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'wood',
    ],
    'gather-7_8' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'rock',
    ],
    'gather-7_9' => [
        'deck' => 'gather',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'rock',
    ],
    'gather-back' => [
        'deck' => 'gather',
        'type' => 'back',
    ],
    'harvest-7_10' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Sabertooth',
        'health' => 1,
        'damage' => 2,
    ],
    'harvest-7_11' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'wood',
    ],
    'harvest-7_12' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Beast',
        'health' => 1,
        'damage' => 1,
    ],
    'harvest-7_13' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Boar',
        'health' => 2,
        'damage' => 2,
    ],
    'harvest-7_14' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'hindrance',
        'expansion' => 'hindrance',
    ],
    'harvest-7_15' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'nothing',
    ],
    'harvest-7_4' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'wood',
    ],
    'harvest-7_5' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 4,
        'resourceType' => 'wood',
    ],
    'harvest-7_8' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'rock',
    ],
    'harvest-7_9' => [
        'deck' => 'harvest',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'rock',
    ],
    'harvest-back' => [
        'deck' => 'harvest',
        'type' => 'back',
    ],
    'hunt-7_10' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'hide',
    ],
    'hunt-7_11' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Mammoth',
        'health' => 3,
        'damage' => 3,
    ],
    'hunt-7_12' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Bear',
        'health' => 2,
        'damage' => 3,
    ],
    'hunt-7_13' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Beast',
        'health' => 1,
        'damage' => 1,
    ],
    'hunt-7_14' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Boar',
        'health' => 2,
        'damage' => 2,
    ],
    'hunt-7_15' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'bone',
    ],
    'hunt-7_4' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'meat',
    ],
    'hunt-7_5' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'meat',
    ],
    'hunt-7_6' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 3,
        'resourceType' => 'meat',
    ],
    'hunt-7_7' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'encounter',
        'name' => 'Sabertooth',
        'health' => 1,
        'damage' => 2,
    ],
    'hunt-7_8' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 2,
        'resourceType' => 'bone',
    ],
    'hunt-7_9' => [
        'deck' => 'hunt',
        'type' => 'deck',
        'deckType' => 'resource',
        'count' => 1,
        'resourceType' => 'hide',
    ],
    'hunt-back' => [
        'deck' => 'hunt',
        'type' => 'back',
    ],
    'night-event-7_0' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('fiber', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'fiber',
            ]);
        },
    ],
    'night-event-7_1' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $left = $this->adjustResource('berry-cooked', -$roll);
            $this->adjustResource('berry', $left);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'berry',
            ]);
        },
    ],
    'night-event-7_10' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onMorning' => function (Game $game, &$data) {
            $data['health'] = min($data['health'] + 1, 0);
            $game->nightEventLog('No damage taken in the morning');
        },
    ],
    'night-event-7_11' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onDraw' => function (Game $game, $object, $deck, $card) {
            $roll = $game->rollFireDie();
            if ($roll == 0) {
                $game->character->adjustActiveHealth(-1);
                $game->nightEventLog('${player_name} - ${character_name} was struck by lightning (1 damage)');
            }
        },
    ],
    'night-event-7_12' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            // TODO discard item on item trade screen
        },
    ],
    'night-event-7_13' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->character->updateAllCharacterData(function ($character) use ($game) {
                $roll = $game->rollFireDie($character['character_name'], $character)['value'];
                if ($roll == 0) {
                    $game->character->adjustHealth($character['character_name'], -1);
                    $game->nightEventLog('${player_name} - ${character_name} took 1 damage');
                    return false;
                }
                return true;
            });
        },
    ],
    'night-event-7_14' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onMorning' => function (Game $game, &$data) {
            $data['health'] = min($data['health'] - 1, 0);
            $game->nightEventLog('Volcano causes an additional health damage');
        },
    ],
    'night-event-7_15' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, &$data) {
            $charactersWithStamina = array_filter($game->character->getAllCharacterData(), function ($data) {
                return $data['stamina'] > 0;
            });
            if (sizeof($charactersWithStamina) > 0) {
                $game->nightEventLog('${character_name} saved the wood', ['character_name' => $charactersWithStamina[0]['character_name']]);
            } else {
                $game->adjustResource('fireWood', -1);
                $game->nightEventLog('1 firewood was lost');
            }
        },
    ],
    'night-event-7_2' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onGetValidActions' => function (Game $game, $object, &$data) {
            $charactersWithStamina = array_filter($game->character->getAllCharacterData(), function ($data) {
                return $data['stamina'] > 0;
            });
            if (sizeof($charactersWithStamina) > 0) {
                $game->nightEventLog('${character_name} saved the wood', ['character_name' => $charactersWithStamina[0]['character_name']]);
            } else {
                $game->adjustResource('fireWood', -1);
                $game->nightEventLog('1 firewood was lost');
            }
        },
    ],
    'night-event-7_3' => [
        'deck' => 'night-event',
        'type' => 'deck',
    ],
    'night-event-7_4' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('wood', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'wood',
            ]);
        },
    ],
    'night-event-7_5' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('rock', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'rock',
            ]);
        },
    ],
    'night-event-7_6' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $left = $this->adjustResource('meat-cooked', -$roll);
            $this->adjustResource('meat', $left);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'meat',
            ]);
        },
    ],
    'night-event-7_7' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->character->adjustAllHealth(2);
            $game->nightEventLog('Everyone heals 2');
        },
    ],
    'night-event-7_8' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Everyone heals 1 extra when eating tomorrow');
        },
        'onEat' => function (Game $game, &$data) {
            $data['health'] += 1;
        },
        'onGetEatData' => function (Game $game, &$data) {
            $data['health'] += 1;
        },
    ],
    'night-event-7_9' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Lack of sleep lessens everyone\'s stamina');
        },
        'onMorning' => function (Game $game, &$data) {
            $data['stamina'] -= 3;
        },
    ],
    'night-event-back' => [
        'deck' => 'night-event',
        'type' => 'back',
    ],
    'night-event-8_0' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            // Everyone take physical hindrance
        },
    ],
    'night-event-8_1' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            // Add 3 raw eggs to supply
            // If 3 raw eggs are not there by night, everyone takes 1 damage
        },
    ],
    'night-event-8_10' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Only 2 investigate fire action can be taken tomorrow');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            if ($game->actions->getTurnActions()['actInvestigateFire'] > 0) {
                unset($data['actInvestigateFire']);
            }
        },
    ],
    'night-event-8_11' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onGetValidActions' => function (Game $game, $object, &$data) {
            // Stamina skills can't be used
            $game->nightEventLog('Bad mushrooms make some character skills not work tomorrow');
        },
    ],
    'night-event-8_12' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->adjustResource('meat', 2);
            $game->nightEventLog('The tribe receives 2 meat');
        },
    ],
    'night-event-8_13' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Freezing winds deal 1 damage and lessens everyone\'s stamina');
            $game->character->adjustAllHealth(-1);
        },
        'onMorning' => function (Game $game, &$data) {
            $data['stamina'] -= 2;
        },
    ],
    'night-event-8_14' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $berries = $game->gameData->getResource('berry');
            if ($berries > 0) {
                $lostBerries = floor($berries / 2);
                $game->adjustResource('berry', -$lostBerries);
                if ($lostBerries == 1) {
                    $game->nightEventLog('Boars steal ${count} berry', ['count' => $lostBerries]);
                } else {
                    $game->nightEventLog('Boars steal ${count} berries', ['count' => $lostBerries]);
                }
            } else {
                $game->nightEventLog('Boars attack everyone without a weapon for 2 damage');
                $game->character->updateAllCharacterData(function ($character) use ($game) {
                    if (
                        sizeof(
                            array_filter($character['equipment'], function ($equipment) {
                                return $equipment['weapon'];
                            })
                        )
                    ) {
                        $game->character->adjustHealth($character['character_name'], -2);
                    }
                    return true;
                });
            }
        },
    ],
    'night-event-8_15' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('The night was peaceful');
        },
    ],
    'night-event-8_2' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $charactersWithStamina = array_filter($game->character->getAllCharacterData(), function ($data) {
                return $data['stamina'] >= 2;
            });
            if (sizeof($charactersWithStamina) > 0) {
                $game->adjustResource('gem', 1);
                $game->nightEventLog('${character_name} found one gem stone', [
                    'character_name' => $charactersWithStamina[0]['character_name'],
                ]);
            } else {
                $game->nightEventLog('1 firewood was lost');
            }
        },
    ],
    'night-event-8_3' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('All items have disappeared');
            foreach ($game->character->getAllCharacterData() as $i => $char) {
                $game->character->unequipEquipment($char['character_name'], $char['equipment']);
            }
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            unset($data['actItems']);
        },
    ],
    'night-event-8_4' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Can\'t investigate the fire tomorrow, it\'s too hot');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            unset($data['actInvestigateFire']);
        },
    ],
    'night-event-8_5' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $meat = $game->gameData->getResource('meat');
            if ($meat > 0) {
                $game->adjustResource('meat', -1);
                $game->nightEventLog('${count} meat is used to distract some sabertooths', ['count' => 1]);
            } else {
                $game->nightEventLog('Sabertooths attack everyone for 2 damage');
                $game->character->adjustAllHealth(-2);
            }
        },
    ],
    'night-event-8_6' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            // Item selection, destroy 2 unequipped
        },
    ],
    'night-event-8_7' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            // Pick a deck used this turn and show the top 3 cards
        },
    ],
    'night-event-8_8' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Sharing knowledge increases fire knowledge by 4');
            $game->adjustResource('fkp', 4);
        },
    ],
    'night-event-9_9' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('All fire die rolls will be reduced tomorrow');
        },
        'onRollDie' => function (Game $game, &$data) {
            $data -= 1;
        },
    ],
    'night-event-9_10' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            // Remove physical hindrance from each character
            // Skip morning phase damage
        },
        'onMorning' => function (Game $game, &$data) {
            $data['health'] = min($data['health'] + 1, 0);
            $game->nightEventLog('No damage taken in the morning');
        },
    ],
    'night-event-9_11' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('No exploring tomorrow');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            unset($data['actDrawExplore']);
        },
    ],
    'night-event-9_12' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Berries can\'t be found until the forage deck runs out of cards');
            // Need to add a globally active card
            $game->decks->discardCards('forage', function ($data) {
                return $data['resourceType'] == 'berry';
            });
        },
    ],
    'night-event-9_13' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Actions outside of camp are harder tomorrow');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            $data['actDrawForage'] += 1;
            $data['actDrawExplore'] += 1;
            $data['actDrawHunt'] += 1;
            $data['actDrawGather'] += 1;
            $data['actDrawHarvest'] += 1;
        },
    ],
    'night-event-9_14' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Unable to craft tomorrow');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            unset($data['actCraft']);
        },
    ],
    'night-event-9_15' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('dino-egg', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'dino-egg',
            ]);
        },
    ],
    'night-event-9_4' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            foreach ($game->decks->getAllDeckNames() as $i => $deck) {
                $game->decks->shuffleInDiscard($deck, false);
            }
            $game->nightEventLog('All decks have been shuffled');
        },
    ],
    'night-event-9_5' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $card1 = $game->decks->pickCard('hunt');
            $card2 = $game->decks->pickCard('hunt');
            $this->notify->all('cardDrawn', clienttranslate('Drew 2 from the ${deck} deck'), [
                'deck' => 'hunt',
            ]);
            $maxDamage = max(
                array_key_exists('damage', $card1) ? $card1['damage'] : 0,
                array_key_exists('damage', $card2) ? $card2['damage'] : 0
            );
            if ($maxDamage > 0) {
                $game->nightEventLog('Received ${damage}', ['damage' => $maxDamage]);
                // Choose tribe member to receive damage
            } else {
                $game->nightEventLog('No predator\'s visited the camp');
            }
        },
    ],
    'night-event-9_6' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $game->nightEventLog('Unable to trade with tribes tomorrow');
        },
        'onGetValidActions' => function (Game $game, $object, &$data) {
            unset($data['actTrade']);
        },
    ],
    'night-event-9_8' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('gem', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'gem',
            ]);
        },
    ],
    'night-event-10_9' => [
        'deck' => 'night-event',
        'type' => 'deck',
        'expansion' => 'hindrance',
        'onUse' => function (Game $game, $object) {
            $roll = $game->rollFireDie();
            $this->adjustResource('herb', -$roll);
            $game->nightEventLog('A rival tribe stole ${number} ${resource_type}', [
                'number' => $roll,
                'resource_type' => 'herb',
            ]);
        },
    ],
];
