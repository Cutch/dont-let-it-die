<?php
namespace Bga\Games\DontLetItDie;

use Bga\Games\DontLetItDie\Game;
class DLD_KnowledgeTreeData
{
    public function getData(): array
    {
        return [
            'warmth-1' => [
                'name' => clienttranslate('Warmth'),
                'name_suffix' => ' 1',
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
                },
            ],
            'warmth-2' => [
                'name' => clienttranslate('Warmth'),
                'name_suffix' => ' 2',
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
                },
            ],
            'warmth-3' => [
                'name' => clienttranslate('Warmth'),
                'name_suffix' => ' 3',
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    $data['maxStamina'] = clamp($data['maxStamina'] + 1, 0, 10);
                },
            ],
            'spices' => [
                'name' => clienttranslate('Spices'),
                'onEat' => function (Game $game, $char, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 1;
                    }
                },
                'onGetEatData' => function (Game $game, $char, &$data) {
                    if (array_key_exists('health', $data)) {
                        $data['health'] += 1;
                    }
                },
            ],
            'cooking-1' => [
                'name' => clienttranslate('Cooking'),
                'name_suffix' => ' 1',
                'onGetActionSelectable' => function (Game $game, $obj, &$data) {
                    if ($data['action'] == 'actCook') {
                        array_push($data['selectable'], 'berry');
                    }
                },
            ],
            'cooking-2' => [
                'name' => clienttranslate('Cooking'),
                'name_suffix' => ' 2',
                'onGetActionSelectable' => function (Game $game, $obj, &$data) {
                    if ($data['action'] == 'actCook') {
                        array_push($data['selectable'], 'meat', 'fish', 'dino-egg');
                    }
                },
            ],
            'crafting-1' => [
                'name' => clienttranslate('Crafting'),
                'name_suffix' => ' 1',
                'onUse' => function (Game $game, $obj) {
                    $craftingLevel = $game->gameData->get('craftingLevel');
                    $game->gameData->set('craftingLevel', [...$craftingLevel, 1]);
                },
            ],
            'crafting-2' => [
                'name' => clienttranslate('Crafting'),
                'name_suffix' => ' 2',
                'onUse' => function (Game $game, $obj) {
                    $craftingLevel = $game->gameData->get('craftingLevel');
                    $game->gameData->set('craftingLevel', [...$craftingLevel, 2]);
                },
            ],
            'crafting-3' => [
                'name' => clienttranslate('Crafting'),
                'name_suffix' => ' 3',
                'onUse' => function (Game $game, $obj) {
                    $craftingLevel = $game->gameData->get('craftingLevel');
                    $game->gameData->set('craftingLevel', [...$craftingLevel, 3]);
                },
            ],
            'fire-starter' => [
                'name' => clienttranslate('Fire Starter'),
                'onUse' => function (Game $game, $obj) {
                    $game->notify('tree', clienttranslate('The tribe has discovered how to make fire!'));
                    $game->win();
                },
            ],
            'resource-1' => [
                'name' => clienttranslate('Resource'),
                'name_suffix' => ' 1',
                'onResolveDraw' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'rock') {
                        if ($game->adjustResource('rock', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $obj['name'],
                                'count' => 1,
                                'resource_type' => $card['resourceType'],
                            ]);
                        }
                    }
                },
            ],
            'resource-2' => [
                'name' => clienttranslate('Resource'),
                'name_suffix' => ' 2',
                'onResolveDraw' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'wood') {
                        if ($game->adjustResource('wood', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $obj['name'],
                                'count' => 1,
                                'resource_type' => $card['resourceType'],
                            ]);
                        }
                    }
                },
            ],
            'hunt-1' => [
                'name' => clienttranslate('Hunt'),
                'name_suffix' => ' 1',
                'onResolveDraw' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'meat') {
                        if ($game->adjustResource('meat', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $obj['name'],
                                'count' => 1,
                                'resource_type' => $card['resourceType'],
                            ]);
                        }
                    }
                },
            ],
            'forage-1' => [
                'name' => clienttranslate('Forage'),
                'name_suffix' => ' 1',
                'onResolveDraw' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'berry') {
                        if ($game->adjustResource('berry', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $obj['name'],
                                'count' => 1,
                                'resource_type' => $card['resourceType'],
                            ]);
                        }
                    }
                },
            ],
            'forage-2' => [
                'name' => clienttranslate('Forage'),
                'name_suffix' => ' 2',
                'onResolveDraw' => function (Game $game, $obj, &$data) {
                    $card = $data['card'];
                    if ($card['deckType'] == 'resource' && $card['resourceType'] == 'fiber') {
                        if ($game->adjustResource('fiber', 1)['changed'] > 0) {
                            $game->notify('tree', clienttranslate('Received ${count} ${resource_type} from ${action_name}'), [
                                'action_name' => $obj['name'],
                                'count' => 1,
                                'resource_type' => $card['resourceType'],
                            ]);
                        }
                    }
                },
            ],
            'relaxation' => [
                'name' => clienttranslate('Relaxation'),
                'onGetCharacterData' => function (Game $game, $item, &$data) {
                    $data['maxHealth'] += 2;
                },
                'onUse' => function (Game $game, $obj) {
                    $game->character->adjustAllHealth(2);
                },
            ],
        ];
    }
}
