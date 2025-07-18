<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DontLetItDie implementation : © Cutch <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use BgaUserException;

class DLD_Encounter
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function argPostEncounter()
    {
        $result = [...$this->game->getArgsData()];
        return $result;
    }
    public function stPostEncounter()
    {
        $validActions = $this->game->actions->getValidActions();

        $encounterState = $this->game->gameData->get('encounterState');

        if ($encounterState['damageTaken'] > 0 && $this->game->isValidExpansion('hindrance')) {
            $this->game->checkHindrance(true, $encounterState['damagedCharacter']);
        }
        if (sizeof($validActions) == 0) {
            $this->game->nextState('playerTurn');
        }
    }
    public function killCheck(array $data)
    {
        return $data['encounterHealth'] <= $data['characterDamage'] && $data['characterRange'] >= $data['requiresRange'];
    }
    public function countDamageTaken($data)
    {
        if ($data['soothe']) {
            return 0;
        } elseif ($data['escape']) {
            return 0;
        } elseif ($this->killCheck($data)) {
            $damageTaken = 0;
            if ($data['characterRange'] > 1) {
                $damageTaken = 0;
            } else {
                $damageTaken = min($data['willTakeDamage'], 1);
            }
            return $damageTaken;
        } else {
            return $data['willTakeDamage'];
        }
    }
    public function actChooseWeapon($weaponId)
    {
        $chooseWeapons = $this->game->gameData->get('chooseWeapons');
        $selectedWeapon = array_values(
            array_filter($chooseWeapons, function ($item) use ($weaponId) {
                return $item['itemId'] == $weaponId;
            })
        );
        if (sizeof($selectedWeapon) == 0) {
            throw new BgaUserException(clienttranslate('That weapon choice is not available'));
        }
        $selectedWeapon = $selectedWeapon[0];
        $items = $this->game->gameData->getCreatedItems();
        if ($weaponId == 'none') {
            // pass
        } elseif ($weaponId == 'both') {
            $bothWeapons = array_values(
                array_filter($chooseWeapons, function ($item) {
                    return $item['itemId'] != 'both' && $item['itemId'] != 'none';
                })
            );
            foreach ($bothWeapons as $k => $weapon) {
                $itemObj = $this->game->data->getItems()[$items[$weapon['itemId']]];
                if (!(!array_key_exists('requires', $itemObj) || $itemObj['requires']($this->game, $itemObj))) {
                    throw new BgaUserException(clienttranslate('A weapon is missing its requirements'));
                }
            }
        } else {
            $itemObj = $this->game->data->getItems()[$items[$weaponId]];
            if (!(!array_key_exists('requires', $itemObj) || $itemObj['requires']($this->game, $itemObj))) {
                throw new BgaUserException(clienttranslate('A weapon is missing its requirements'));
            }
        }

        if (array_key_exists('useCost', $selectedWeapon)) {
            foreach ($selectedWeapon['useCost'] as $key => $value) {
                if ($this->game->adjustResource($key, -$value)['left'] > 0) {
                    throw new BgaUserException(clienttranslate('Missing resources'));
                }
            }
        }
        $this->game->gameData->set('chooseWeapons', [$selectedWeapon]);

        $this->game->nextState('resolveEncounter');
    }
    public function argWhichWeapon()
    {
        $chooseWeapons = $this->game->gameData->get('chooseWeapons');
        $result = [
            'chooseWeapons' => $chooseWeapons,
            ...$this->game->getArgsData(),
        ];
        $this->game->getResources($result);
        return $result;
    }
    public function stResolveEncounter()
    {
        $this->game->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->game->hooks, 'onEncounter'],
            function (Game $_this) {
                $state = $_this->gameData->get('state');
                $card = $state['card'];
                $deck = $state['deck'];
                $weapons = array_values(
                    array_filter($this->game->character->getActiveEquipment(), function ($item) {
                        return $item['itemType'] == 'weapon';
                    })
                );
                $weapon = null;
                $chooseWeapons = $_this->gameData->get('chooseWeapons');
                $noneChoice = [
                    'itemId' => 'none',
                    'name' => clienttranslate('None'),
                    'damage' => 0,
                    'range' => 1,
                    'itemIds' => [],
                ];
                if ($chooseWeapons && sizeof($chooseWeapons) >= 2) {
                    // TODO is this state reached? and how/why
                    $_this->gameData->set('chooseWeapons', null);
                    $weapon = $chooseWeapons[0];
                } elseif (sizeof($weapons) >= 2) {
                    // This resolved the weapon choice after the change of state
                    if ($chooseWeapons && sizeof($chooseWeapons) == 1) {
                        $_this->gameData->set('chooseWeapons', null);
                        $weapon = $chooseWeapons[0];
                        $weapon['itemIds'] = [$weapon['itemId']];
                    } else {
                        // Highest range, lowest damage for combine
                        $bothUseCost = array_merge_count(
                            array_key_exists('useCost', $weapons[0]) ? $weapons[0]['useCost'] : [],
                            array_key_exists('useCost', $weapons[1]) ? $weapons[1]['useCost'] : []
                        );
                        $choices = [
                            ...array_map(function ($weapon) {
                                if (array_key_exists('useCost', $weapon)) {
                                    $weapon['useCostString'] = $this->game->costToString($weapon['useCost']);
                                }
                                return $weapon;
                            }, $weapons),
                            [
                                'itemId' => 'both',
                                'name' => clienttranslate('Both'),
                                'damage' => $weapons[0]['damage'] + $weapons[1]['damage'],
                                'range' => min($weapons[0]['range'], $weapons[1]['range']),
                                'useCost' => $bothUseCost,
                                'useCostString' => $this->game->costToString($bothUseCost),
                            ],
                        ];
                        // Add a none choice if everything is optional
                        if (
                            sizeof(
                                array_filter($weapons, function ($weapon) {
                                    return array_key_exists('useCost', $weapon);
                                })
                            ) == sizeof($weapons)
                        ) {
                            array_push($choices, $noneChoice);
                        }
                        $_this->gameData->set('chooseWeapons', $choices);
                        $_this->nextState('whichWeapon');
                        return;
                    }
                } elseif (sizeof($weapons) >= 1) {
                    $weapon = $weapons[0];
                    // If a single weapon is optional, ask if it or nothing should be used
                    if (
                        !$chooseWeapons &&
                        array_key_exists('useCost', $weapon) &&
                        (!array_key_exists('requires', $weapon) || $weapon['requires']($this->game, $weapon))
                    ) {
                        $_this->gameData->set('chooseWeapons', [
                            ...array_map(function ($weapon) {
                                if (array_key_exists('useCost', $weapon)) {
                                    $weapon['useCostString'] = $this->game->costToString($weapon['useCost']);
                                }
                                return $weapon;
                            }, $weapons),
                            $noneChoice,
                        ]);
                        $_this->nextState('whichWeapon');
                        return;
                    } elseif ($chooseWeapons) {
                        $weapon = $chooseWeapons[0];
                        $weapon['itemIds'] = [$weapon['itemId']];
                    } else {
                        // Select the single weapon
                        $weapon['itemIds'] = [$weapon['itemId']];
                    }
                } else {
                    // Choose nothing as the weapon
                    $weapon = [
                        'damage' => 0,
                        'range' => 1,
                        'itemIds' => [],
                    ];
                }

                return [
                    'cardId' => $card['id'],
                    'itemIds' => array_values(
                        array_filter($weapon['itemIds'], function ($id) {
                            return $id != 'both' && $id != 'none';
                        })
                    ),
                    'itemIdUsed' =>
                        sizeof($weapon['itemIds']) == 0
                            ? []
                            : ($weapon['itemIds'][0] == 'both'
                                ? [$weapons[0]['id'], $weapons[1]['id']]
                                : [$weapons[0]['id']]),
                    'deck' => $deck,
                    'name' => $card['name'],
                    'encounterDamage' => $card['damage'], // Unused, maybe in logging
                    'encounterHealth' => $card['health'],
                    'escape' => false,
                    'soothe' => false,
                    'trap' => false,
                    'requiresRange' => array_key_exists('requiresRange', $card) ? $card['requiresRange'] : 1,
                    'noEscape' => array_key_exists('noEscape', $card) ? $card['noEscape'] : false,
                    'damageStamina' => array_key_exists('damageStamina', $card) ? $card['damageStamina'] : false,
                    'loot' => array_key_exists('loot', $card) ? $card['loot'] : [],
                    'characterRange' => $weapon['range'],
                    'characterDamage' => $weapon['damage'],
                    'willTakeDamage' => $card['damage'],
                    'willReceiveMeat' => $card['health'],
                    'damagedCharacter' => $this->game->character->getSubmittingCharacterId(),
                    'originalDamagedCharacter' => $this->game->character->getSubmittingCharacterId(),
                    'stamina' => 0,
                    'damageTaken' => 0,
                ];
            },
            function (Game $_this, bool $finalizeInterrupt, $data) {
                if ($data['stamina'] != 0) {
                    $_this->character->adjustActiveStamina($data['stamina']);
                }
                if ($data['soothe']) {
                    $_this->eventLog(clienttranslate('${character_name} soothed a ${name}'), $data);
                    $deck = $_this->decks->getDeck($data['deck']);
                    $deck->insertCard($data['cardId'], 'deck', 0);
                    // TODO: Need to test
                } elseif ($data['escape']) {
                    $_this->eventLog(clienttranslate('${character_name} escaped from a ${name}'), $data);
                } else {
                    $items = $this->game->gameData->getCreatedItems();
                    foreach ($data['itemIds'] as $k => $itemId) {
                        $itemObj = $this->game->data->getItems()[$items[$itemId]];
                        if (array_key_exists('onUse', $itemObj)) {
                            $itemObj['characterId'] = $this->game->character->getSubmittingCharacterId();
                            $itemObj['onUse']($this->game, $itemObj, $itemId);
                        }
                    }
                    $damageTaken = $this->countDamageTaken($data);
                    if ($data['characterDamage'] > 0) {
                        $this->game->incStat(
                            $data['characterDamage'],
                            'damage_done',
                            $this->game->character->getSubmittingCharacter()['playerId']
                        );
                    }
                    $data['damageTaken'] = $damageTaken;
                    if ($this->killCheck($data)) {
                        // Killed
                        $change = 0;
                        if ($damageTaken != 0 && $data['damagedCharacter'] == $data['originalDamagedCharacter']) {
                            if ($data['damageStamina']) {
                                $change = $_this->character->adjustActiveStamina(-$damageTaken);
                            } else {
                                $change = $_this->character->adjustActiveHealth(-$damageTaken);
                            }
                        }
                        if ($_this->character->getActiveHealth() != 0) {
                            $_this->adjustResource('meat', $data['willReceiveMeat']);

                            if ($change != 0 && $data['damagedCharacter'] == $data['originalDamagedCharacter']) {
                                $_this->eventLog(
                                    '${character_name} defeated a ${name}, gained ${willReceiveMeat} meat and lost ${damageTaken} ${resource}',
                                    [
                                        ...$data,
                                        'damageTaken' => -$change,
                                        'resource' => $data['damageStamina'] ? clienttranslate('Stamina') : clienttranslate('Health'),
                                    ]
                                );
                            } else {
                                $_this->eventLog(
                                    clienttranslate('${character_name} defeated a ${name} and gained ${willReceiveMeat} meat'),
                                    [...$data]
                                );
                            }
                            foreach ($data['loot'] as $k => $num) {
                                $_this->adjustResource($k, $num);
                                $this->game->eventLog(clienttranslate('${character_name} received ${count} ${resource_type}'), [
                                    'count' => $num,
                                    'resource_type' => $k,
                                ]);
                            }
                        }
                    } else {
                        if ($damageTaken > 0 && $data['damagedCharacter'] == $data['originalDamagedCharacter']) {
                            $change = $_this->character->adjustActiveHealth(-$damageTaken);
                            $_this->eventLog(
                                clienttranslate('${character_name} was attacked by a ${name} and lost ${damageTaken} ${resource}'),
                                [
                                    ...$data,
                                    'damageTaken' => -$change,
                                    'resource' => $data['damageStamina'] ? clienttranslate('Stamina') : clienttranslate('Health'),
                                ]
                            );
                        } else {
                            $_this->eventLog(clienttranslate('${character_name} was attacked by a ${name} but lost no ${resource}'), [
                                ...$data,
                                'resource' => $data['damageStamina'] ? clienttranslate('Stamina') : clienttranslate('Health'),
                            ]);
                        }
                    }
                }
                $_this->gameData->set('chooseWeapons', null);
                $_this->gameData->set('encounterState', $data);
                $_this->nextState('postEncounter');
            }
        );
    }
    public function argResolveEncounter()
    {
        $result = [...$this->game->getArgsData()];
        return $result;
    }
}
