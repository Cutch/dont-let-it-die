<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
use BgaUserException;

if (!function_exists('rivalTribe')) {
    function rivalTribe(Game $game, array $nightCard, int $roll)
    {
        $resourceType = $nightCard['resourceType'];
        $left = $roll;
        if ($resourceType == 'gem') {
            $left = $game->adjustResource('gem-y', -$roll)['left'];
            $left = $game->adjustResource('gem-p', $left)['left'];
            $left = $game->adjustResource('gem-b', $left)['left'];
            $resourceType = 'gem-y';
        } else {
            if (array_key_exists($resourceType . '-cooked', $game->data->getTokens())) {
                $left = $game->adjustResource($resourceType . '-cooked', -$roll)['left'];
            }
            $game->adjustResource($resourceType, $left);
        }
        $game->eventLog(clienttranslate('A rival tribe tried to steal ${number} ${resource_type}'), [
            'number' => $roll,
            'resource_type' => $resourceType,
        ]);
    }
}

class DLD_DecksData
{
    public function getData(): array
    {
        return [
            'explore-7_0' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'physical-hindrance',
                'expansion' => 'hindrance',
            ],
            'explore-7_1' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Pterodactyl'),
                'health' => 3,
                'damage' => 4,
                'noEscape' => true,
                // Cannot be blocked, soothed or escaped
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
                'name' => clienttranslate('Bat'),
                'health' => 1,
                'damage' => 2,
                'requiresRange' => 2,
                // Can only be killed with a range 2 weapon
            ],
            'explore-7_12' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'resource',
                'count' => 1,
                'resourceType' => 'gem-b',
            ],
            'explore-7_13' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'resource',
                'count' => 1,
                'resourceType' => 'gem-p',
            ],
            'explore-7_14' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'resource',
                'count' => 1,
                'resourceType' => 'gem-y',
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
                'name' => clienttranslate('Bear'),
                'health' => 2,
                'damage' => 3,
            ],
            'explore-7_5' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Boar'),
                'health' => 2,
                'damage' => 2,
            ],
            'explore-7_6' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Carnivorous Plant'),
                'health' => 2,
                'damage' => 2,
                'damageStamina' => true,
                // Instead of doing damage reduces your stamina
            ],
            'explore-7_7' => [
                'deck' => 'explore',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Dino'),
                'health' => 2,
                'damage' => 3,
                // Take a raw egg after killing
                'loot' => ['dino-egg' => 1],
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
            'explore-back' => [
                'deck' => 'explore',
                'type' => 'back',
            ],
            'forage-7_10' => [
                'deck' => 'forage',
                'type' => 'deck',
                'deckType' => 'physical-hindrance',
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
                'name' => clienttranslate('Beast'),
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
                'name' => clienttranslate('Sabertooth'),
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
                'name' => clienttranslate('Sabertooth'),
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
                'name' => clienttranslate('Beast'),
                'health' => 1,
                'damage' => 1,
            ],
            'gather-7_13' => [
                'deck' => 'gather',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Boar'),
                'health' => 2,
                'damage' => 2,
            ],
            'gather-7_14' => [
                'deck' => 'gather',
                'type' => 'deck',
                'deckType' => 'physical-hindrance',
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
                'name' => clienttranslate('Sabertooth'),
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
                'name' => clienttranslate('Beast'),
                'health' => 1,
                'damage' => 1,
            ],
            'harvest-7_13' => [
                'deck' => 'harvest',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Boar'),
                'health' => 2,
                'damage' => 2,
            ],
            'harvest-7_14' => [
                'deck' => 'harvest',
                'type' => 'deck',
                'deckType' => 'physical-hindrance',
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
                'name' => clienttranslate('Mammoth'),
                'health' => 3,
                'damage' => 3,
            ],
            'hunt-7_12' => [
                'deck' => 'hunt',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Bear'),
                'health' => 2,
                'damage' => 3,
            ],
            'hunt-7_13' => [
                'deck' => 'hunt',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Beast'),
                'health' => 1,
                'damage' => 1,
            ],
            'hunt-7_14' => [
                'deck' => 'hunt',
                'type' => 'deck',
                'deckType' => 'encounter',
                'name' => clienttranslate('Boar'),
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
                'name' => clienttranslate('Sabertooth'),
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
                'eventType' => 'rival-tribe',
                'resourceType' => 'fiber',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-7_1' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'eventType' => 'rival-tribe',
                'resourceType' => 'berry',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-7_10' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onMorning' => function (Game $game, $nightCard, &$data) {
                    $data['health'] = 0;
                    $game->eventLog(clienttranslate('No damage taken in the morning'));
                },
            ],
            'night-event-7_11' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onDraw' => function (Game $game, $nightCard, &$data) {
                    $card = $data['card'];
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    if ($roll == 0) {
                        $game->character->adjustActiveHealth(-1);
                        $game->eventLog(clienttranslate('${character_name} was struck by lightning (1 damage)'));
                    }
                },
            ],
            'night-event-7_12' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $currentCharacter = $game->character->getTurnCharacter();
                    $items = array_merge(
                        ...array_map(function ($character) {
                            return array_map(function ($d) use ($character) {
                                return ['name' => $d['id'], 'itemId' => $d['itemId'], 'characterId' => $character['id']];
                            }, $character['equipment']);
                        }, $game->character->getAllCharacterData())
                    );
                    if (sizeof($items) > 0) {
                        $game->selectionStates->initiateState(
                            'itemSelection',
                            [
                                'items' => $items,
                                'id' => $nightCard['id'],
                            ],
                            $currentCharacter['id'],
                            false,
                            'morningPhase'
                        );

                        return ['nextState' => false];
                    }
                },
                'onItemSelection' => function (Game $game, $skill, &$data) {
                    $itemSelectionState = $game->selectionStates->getState('itemSelection');
                    if ($itemSelectionState && $itemSelectionState['id'] == $skill['id']) {
                        $itemId = $itemSelectionState['selectedItemId'];
                        $game->destroyItem($itemId);
                    }
                },
            ],
            'night-event-7_13' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Mammoths storm the camp'));
                    $game->character->updateAllCharacterData(function ($character) use ($game) {
                        $roll = $game->rollFireDie(clienttranslate('Night Event'), $character['character_name']);
                        // On blank roll take a damage
                        if ($roll == 0) {
                            $game->character->adjustHealth($character['character_name'], -1);
                            $game->eventLog(clienttranslate('${character_name} took 1 damage'), [
                                'character_name' => $game->getCharacterHTML($character['character_name']),
                            ]);
                            return false;
                        }
                        return true;
                    });
                },
            ],
            'night-event-7_14' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onMorning' => function (Game $game, $nightCard, &$data) {
                    $data['health'] = min($data['health'] - 1, 0);
                    $game->eventLog(clienttranslate('Everyone takes an additional 1 damage from the morning phase'));
                },
            ],
            'night-event-7_15' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $charactersWithStamina = array_values(
                        array_filter($game->character->getAllCharacterData(), function ($data) {
                            return $data['stamina'] > 0;
                        })
                    );
                    if (sizeof($charactersWithStamina) > 0) {
                        $game->eventLog(clienttranslate('${character_name} had some extra stamina and saved the wood'), [
                            'character_name' => $charactersWithStamina[0]['character_name'],
                        ]);
                    } else {
                        $game->adjustResource('fireWood', -1);
                        $game->eventLog(clienttranslate('1 firewood was lost'));
                        $game->gameData->set('morningState', [
                            ...$game->gameData->get('morningState') ?? [],
                            'allowFireWoodAddition' => true,
                        ]);
                    }
                },
            ],
            'night-event-7_2' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    unset($data['actDrawHarvest']);
                    unset($data['actDrawHunt']);
                },
            ],
            'night-event-7_3' => [
                'deck' => 'night-event',
                'type' => 'deck',
                // Can't gain stamina next day (not including morning)
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Can\'t gain stamina tomorrow'));
                },
                'onCheckSkillRequirements' => function (Game $game, $nightCard, $data, &$requires) {
                    // Stamina skills can't be used
                    if (array_key_exists('type', $data)) {
                        $requires['requires'] = $requires['requires'] && $data['type'] == 'skill' && array_key_exists('stamina', $data);
                    }
                },
                'onAdjustStamina' => function (Game $game, $nightCard, &$data) {
                    if ($data['change'] > 0) {
                        $data['change'] = 0;
                        $game->eventLog(clienttranslate('Food poisoning prevented the gain of stamina'));
                    }
                    return $data;
                },
            ],
            'night-event-7_4' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'eventType' => 'rival-tribe',
                'resourceType' => 'wood',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-7_5' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'eventType' => 'rival-tribe',
                'resourceType' => 'rock',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-7_6' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'eventType' => 'rival-tribe',
                'resourceType' => 'meat',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-7_7' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->character->adjustAllHealth(2);
                    $game->eventLog(clienttranslate('Everyone gained ${count} ${character_resource}'), [
                        'count' => 2,
                        'character_resource' => clienttranslate('Health'),
                    ]);
                },
            ],
            'night-event-7_8' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Eating during the day phase tomorrow heals for 1 extra'));
                },
                'onEat' => function (Game $game, $nightCard, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 1;
                    }
                },
                'onGetEatData' => function (Game $game, $nightCard, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 1;
                    }
                },
            ],
            'night-event-7_9' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Lack of sleep lessens everyone\'s stamina'));
                },
                'onMorning' => function (Game $game, $nightCard, &$data) {
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
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Everyone is injured'));
                    // Everyone take physical hindrance
                    foreach ($game->character->getAllCharacterIds() as $char) {
                        $game->checkHindrance(true, $char);
                    }
                },
            ],
            'night-event-8_1' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    // Add 3 raw eggs to supply
                    // If 3 raw eggs are not there by night, everyone takes 1 damage
                    $count = $game->adjustResource('dino-egg', 3)['changed'];
                    $game->eventLog(clienttranslate('Received ${count} ${resource_type}'), [
                        'count' => $count,
                        'resource_type' => 'dino-egg',
                    ]);
                },
                'onNight' => function (Game $game, $nightCard, &$data) {
                    if (in_array($nightCard['id'], $game->getActiveNightCardIds()) && $game->gameData->getResource('dino-egg') < 3) {
                        $game->character->adjustAllHealth(-1);
                        $game->notify('morningPhase', clienttranslate('Everyone lost ${count} ${character_resource}'), [
                            'count' => 1,
                            'character_resource' => clienttranslate('Health'),
                        ]);
                    }
                },
            ],
            'night-event-8_10' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(
                        clienttranslate('Each character can only perform 1 Investigate Fire action tomorrow, due to the increased heat')
                    );
                },
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    if (getUsePerDay($nightCard['id'] . $game->character->getTurnCharacterId(), $game) >= 1) {
                        unset($data['actInvestigateFire']);
                    }
                },
                'onInvestigateFirePost' => function (Game $game, $nightCard, &$data) {
                    usePerDay($nightCard['id'] . $game->character->getTurnCharacterId(), $game);
                },
            ],
            'night-event-8_11' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Skills that require stamina to activate cannot be used tomorrow'));
                },
                'onGetCharacterSkills' => function (Game $game, $nightCard, &$data) {
                    // Stamina skills can't be used
                    $data = array_filter($data, function ($d) {
                        return !array_key_exists('stamina', $d) || $d['stamina'] == 0;
                    });
                },
            ],
            'night-event-8_12' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->adjustResource('meat', 2);
                    $game->eventLog(clienttranslate('Group receives 2 meat'));
                },
            ],
            'night-event-8_13' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Everyone takes 1 damage and starts the morning with -2 stamina'));
                    $game->character->adjustAllHealth(-1);
                },
                'onMorning' => function (Game $game, $nightCard, &$data) {
                    $data['stamina'] -= 2;
                },
            ],
            'night-event-8_14' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $berries = $game->gameData->getResource('berry');
                    if ($berries > 0) {
                        $lostBerries = floor($berries / 2);
                        $game->adjustResource('berry', -$lostBerries);
                        if ($lostBerries == 1) {
                            $game->eventLog(clienttranslate('Boars steal ${count} berry'), ['count' => $lostBerries]);
                        } else {
                            $game->eventLog(clienttranslate('Boars steal ${count} berries'), ['count' => $lostBerries]);
                        }
                    } else {
                        $game->eventLog(clienttranslate('Boars attack everyone without a weapon for 2 damage'));
                        $game->character->updateAllCharacterData(function ($character) use ($game) {
                            if (
                                sizeof(
                                    array_filter($character['equipment'], function ($equipment) {
                                        return $equipment['itemType'] == 'weapon';
                                    })
                                ) == 0
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
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('It was a calm night'));
                },
            ],
            'night-event-8_2' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $charactersWithStamina = array_values(
                        array_filter($game->character->getAllCharacterData(), function ($data) {
                            return $data['stamina'] >= 2;
                        })
                    );
                    if (sizeof($charactersWithStamina) > 0) {
                        $left = $game->adjustResource('gem-y', 1)['left'];
                        $left = $game->adjustResource('gem-p', $left)['left'];
                        $left = $game->adjustResource('gem-b', $left)['left'];
                        $game->eventLog(clienttranslate('${character_name} found ${count} ${name}(s)'), [
                            'character_name' => $charactersWithStamina[0]['character_name'],
                            'count' => 1,
                            'name' => clienttranslate('Gem'),
                        ]);
                    } else {
                        $game->adjustResource('fireWood', -1);
                        $game->eventLog(clienttranslate('1 firewood was lost'));
                        $game->gameData->set('morningState', [
                            ...$game->gameData->get('morningState') ?? [],
                            'allowFireWoodAddition' => true,
                        ]);
                    }
                },
            ],
            'night-event-8_3' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('All items have disappeared'));
                    foreach ($game->character->getAllCharacterData() as $char) {
                        if (sizeof($char['equipment']) > 0) {
                            $game->character->unequipEquipment(
                                $char['character_name'],
                                array_map(function ($item) {
                                    return $item['itemId'];
                                }, $char['equipment']),
                                true
                            );
                        }
                    }
                    $game->skip('tradePhase');
                },
            ],
            'night-event-8_4' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('You may not perform the ${action} action today'), [
                        'action' => clienttranslate('Investigate Fire'),
                    ]);
                },
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    unset($data['actInvestigateFire']);
                },
            ],
            'night-event-8_5' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $meat = $game->gameData->getResource('meat');
                    if ($meat > 0) {
                        $game->adjustResource('meat', -1);
                        $game->eventLog(clienttranslate('${count} meat is used to distract some sabertooths'), ['count' => 1]);
                    } else {
                        $game->eventLog(clienttranslate('Sabertooths attack everyone for 2 damage'));
                        $game->character->adjustAllHealth(-2);
                    }
                },
            ],
            'night-event-8_6' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    // Item selection, destroy 2 unequipped

                    $items = $game->gameData->getCreatedItems();
                    $campEquipment = array_map(function ($d) use ($items, $game) {
                        return ['type' => $game->data->getItems()[$items[$d]]['itemType'], 'itemId' => $d];
                    }, $game->gameData->get('campEquipment'));
                    $tools = array_values(
                        array_filter($campEquipment, function ($d) {
                            return $d['type'] == 'tool';
                        })
                    );
                    if (sizeof($tools)) {
                        $game->destroyItem($tools[random_int(0, sizeof($tools) - 1)]['itemId']);
                    }
                    $weapons = array_values(
                        array_filter($campEquipment, function ($d) {
                            return $d['type'] == 'weapon';
                        })
                    );
                    if (sizeof($weapons)) {
                        $game->destroyItem($weapons[random_int(0, sizeof($weapons) - 1)]['itemId']);
                    }
                },
            ],
            'night-event-8_7' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'disabled' => true,
                'onUse' => function (Game $game, $nightCard) {
                    // TODO: Pick a deck used this turn and show the top 3 cards
                },
            ],
            'night-event-8_8' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Each character gets 1 FKP token'));
                    $game->adjustResource('fkp', 4);
                },
            ],
            'night-event-9_9' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('All fire die rolls will be reduced tomorrow'));
                },
                'onRollDie' => function (Game $game, $nightCard, &$data) {
                    if ($data['value'] >= 1) {
                        $data['sendNotification']();
                        $game->eventLog(clienttranslate('Roll reduced by 1'));
                        $data['value'] = $data['value'] - 1;
                    }
                },
            ],
            'night-event-9_10' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    // Remove physical hindrance from each character
                    // Skip morning phase damage

                    if (
                        sizeof(
                            array_filter($game->character->getAllCharacterData(false), function ($d) {
                                return sizeof($d['physicalHindrance']) > 0 && !in_array('hindrance_2_5', toId($d['physicalHindrance']));
                            })
                        ) > 0
                    ) {
                        $game->selectionStates->initiateHindranceSelection(
                            $nightCard['id'],
                            $game->character->getAllCharacterIds(false),
                            null,
                            false
                        );
                        return ['nextState' => false];
                    }
                },
                'onMorning' => function (Game $game, $nightCard, &$data) {
                    $turnOrder = $game->gameData->get('turnOrder');
                    $turnOrder = array_values(array_filter($turnOrder));
                    array_push($data['skipMorningDamage'], ...$turnOrder);
                    $game->eventLog(clienttranslate('No damage taken in the morning'));
                },
                'onHindranceSelection' => function (Game $game, $nightCard, &$data) {
                    $state = $game->selectionStates->getState('hindranceSelection');
                    if ($state && $state['id'] == $nightCard['id']) {
                        $characterTotal = sizeof(
                            array_filter($game->character->getAllCharacterData(false), function ($d) {
                                return sizeof($d['physicalHindrance']) > 0 && !in_array('hindrance_2_5', toId($d['physicalHindrance']));
                            })
                        );
                        $characterCount = sizeof(
                            array_unique(
                                array_map(function ($d) {
                                    return $d['characterId'];
                                }, $state['selections'])
                            )
                        );
                        $count = 0;
                        foreach ($state['characters'] as $char) {
                            $cardIds = array_map(
                                function ($d) {
                                    return $d['cardId'];
                                },
                                array_filter($data['selections'], function ($d) use ($char) {
                                    return $d['characterId'] == $char['characterId'];
                                })
                            );
                            foreach ($char['physicalHindrance'] as $card) {
                                if (in_array($card['id'], $cardIds)) {
                                    $count++;
                                    $game->character->removeHindrance($char['characterId'], $card);
                                }
                            }
                        }
                        if ($characterTotal != $characterCount || $characterTotal != $count) {
                            throw new BgaUserException(clienttranslate('Remove 1 hindrance from each character'));
                        }
                        $data['nextState'] = 'playerTurn';
                    }
                },
            ],
            'night-event-9_11' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('You may not perform the ${action} action today'), [
                        'action' => clienttranslate('Explore'),
                    ]);
                },
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    unset($data['actDrawExplore']);
                },
            ],
            'night-event-9_12' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('Berries can\'t be found until the forage deck runs out of cards'));
                    // Need to add a globally active card
                    $game->decks->discardCards('forage', function ($data) {
                        return array_key_exists('resourceType', $data) && $data['resourceType'] == 'berry';
                    });
                },
            ],
            'night-event-9_13' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(
                        clienttranslate(
                            'Foraging, Gathering, Harvesting, Hunting, and Exploring cost +1 Stamina to perform during the Day Phase tomorrow'
                        )
                    );
                },
                'onGetActionCost' => function (Game $game, $char, &$data) {
                    if (in_array($data['action'], ['actDrawForage', 'actDrawExplore', 'actDrawHunt', 'actDrawGather', 'actDrawHarvest'])) {
                        $data['stamina'] += 1;
                    }
                },
            ],
            'night-event-9_14' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('You may not perform the ${action} action today'), [
                        'action' => clienttranslate('Craft'),
                    ]);
                },
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    unset($data['actCraft']);
                },
            ],
            'night-event-9_15' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'eventType' => 'rival-tribe',
                'resourceType' => 'dino-egg',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-9_4' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    foreach ($game->decks->getAllDeckNames() as $deck) {
                        $game->decks->shuffleInDiscard($deck, false);
                    }
                    $game->eventLog(clienttranslate('All decks have been shuffled'));
                },
            ],
            'night-event-9_5' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $card1 = $game->decks->pickCard('hunt');
                    $game->cardDrawEvent($card1, 'hunt');
                    $card2 = $game->decks->pickCard('hunt');
                    $game->cardDrawEvent($card2, 'hunt');
                    $game->eventLog(clienttranslate('Draws 2 from the ${deck} deck ${buttons}'), [
                        'deck' => 'hunt',
                        'buttons' => notifyButtons([
                            ['name' => clienttranslate('Hunt'), 'dataId' => $card1['id'], 'dataType' => 'card'],
                            ['name' => clienttranslate('Hunt'), 'dataId' => $card2['id'], 'dataType' => 'card'],
                        ]),
                    ]);
                    $maxDamage = max(
                        array_key_exists('damage', $card1) ? $card1['damage'] : 0,
                        array_key_exists('damage', $card2) ? $card2['damage'] : 0
                    );
                    if ($maxDamage > 0) {
                        $game->selectionStates->initiateState(
                            'characterSelection',
                            [
                                'id' => $nightCard['id'],
                                'maxDamage' => $maxDamage,
                                'selectableCharacters' => $game->character->getAllCharacterIds(),
                            ],
                            $game->character->getTurnCharacterId(),
                            false,
                            'morningPhase',
                            clienttranslate('Who is attacked')
                        );
                        return ['nextState' => false];
                    } else {
                        $game->eventLog(clienttranslate('No predator\'s visited the camp'));
                    }
                },
                'onCharacterSelection' => function (Game $game, $nightCard, &$data) {
                    $characterSelectionState = $game->selectionStates->getState('characterSelection');
                    if ($characterSelectionState && $characterSelectionState['id'] == $nightCard['id']) {
                        $characterId = $characterSelectionState['selectedCharacterId'];
                        $game->character->adjustHealth($characterId, -$characterSelectionState['maxDamage']);
                        $game->eventLog(clienttranslate('${character_name} lost ${count} ${character_resource}'), [
                            'count' => $characterSelectionState['maxDamage'],
                            'character_resource' => clienttranslate('Health'),
                            'character_name' => $game->getCharacterHTML($characterId),
                        ]);
                    }
                },
            ],
            'night-event-9_6' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'onUse' => function (Game $game, $nightCard) {
                    $game->eventLog(clienttranslate('You may not perform the ${action} action today'), [
                        'action' => clienttranslate('Trade'),
                    ]);
                },
                'onGetValidActions' => function (Game $game, $nightCard, &$data) {
                    unset($data['actTrade']);
                },
            ],
            'night-event-9_8' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'eventType' => 'rival-tribe',
                'resourceType' => 'gem',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
            'night-event-10_9' => [
                'deck' => 'night-event',
                'type' => 'deck',
                'expansion' => 'hindrance',
                'eventType' => 'rival-tribe',
                'resourceType' => 'herb',
                'onUse' => function (Game $game, $nightCard) {
                    $roll = $game->rollFireDie(clienttranslate('Night Event'));
                    rivalTribe($game, $nightCard, $roll);
                },
            ],
        ];
    }
}
