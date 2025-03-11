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

class Encounter
{
    private Game $game;
    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function argPostEncounter()
    {
        $result = [...$this->game->getAllDatas()];
        return $result;
    }
    public function stPostEncounter()
    {
        $validActions = $this->game->actions->getValidActions();
        $this->game->gameData->set('encounterState', []);
        if (sizeof($validActions) == 0) {
            $this->game->gamestate->nextState('playerTurn');
        }
    }
    public function countDamageTaken($data)
    {
        if ($data['escape']) {
            return 0;
        } elseif ($data['encounterHealth'] <= $data['characterDamage'] || $data['killed']) {
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
                return $item['id'] == $weaponId;
            })
        );
        if (sizeof($selectedWeapon) == 0) {
            throw new BgaUserException($this->game->translate('That weapon choice is not available'));
        }
        $this->game->gameData->set('chooseWeapons', [$selectedWeapon[0]]);

        $this->game->gamestate->nextState('resolveEncounter');
    }
    public function argWhichWeapon()
    {
        $chooseWeapons = $this->game->gameData->get('chooseWeapons');
        $result = [
            'chooseWeapons' => $chooseWeapons,
            ...$this->game->getAllDatas(),
        ];
        return $result;
    }
    public function stResolveEncounter()
    {
        $this->game->actInterrupt->interruptableFunction(
            __FUNCTION__,
            func_get_args(),
            [$this->game->hooks, 'onEncounter'],
            function (Game $_this) {
                $card = $_this->gameData->get('state')['card'];
                // $tools = array_filter($_this->character->getActiveEquipment(), function ($item) {
                //     return array_key_exists('onEncounter', $item) && !(!array_key_exists('requires', $item) || $item['requires']($item));
                // });
                $weapons = array_filter($this->game->character->getActiveEquipment(), function ($item) {
                    return $item['itemType'] == 'weapon';
                });
                // if (sizeof($tools) >= 2) {
                //     $weapon = $_this->gameData->get('useTools');
                //     if ($weapon) {
                //         $_this->gameData->set('chooseWeapon', null);
                //     } else {
                //         // TODO: Ask if want to use tools
                //         $_this->gameData->set('useTools', $weapons);
                //         $_this->gamestate->nextState('whichTool');
                //         return;
                //     }
                // }
                $weapon = null;
                if (sizeof($weapons) >= 2) {
                    $chooseWeapons = $_this->gameData->get('chooseWeapons');
                    if ($chooseWeapons && sizeof($chooseWeapons) == 1) {
                        $_this->gameData->set('chooseWeapons', null);
                        $weapon = $chooseWeapons[0];
                    } else {
                        // TODO: Ask gronk if you want to combine two weapons or pick one
                        // Highest range, lowest damage for combine
                        $_this->gameData->set('chooseWeapons', [
                            ...$weapons,
                            [
                                'id' => 'both',
                                'name' => clienttranslate('Both'),
                                'damage' => $weapons[0]['damage'] + $weapons[1]['damage'],
                                'range' => min($weapons[0]['damage'], $weapons[1]['damage']),
                            ],
                        ]);
                        $_this->gamestate->nextState('whichWeapon');
                        return;
                    }
                } elseif (sizeof($weapons) >= 1) {
                    $weapon = $weapons[0];
                } else {
                    $weapon = [
                        'damage' => 0,
                        'range' => 1,
                    ];
                }
                return [
                    'name' => $card['name'],
                    'encounterDamage' => $card['damage'], // Unused, maybe in logging
                    'encounterHealth' => $card['health'],
                    'escape' => false,
                    'characterRange' => $weapon['range'],
                    'characterDamage' => $weapon['damage'],
                    'willTakeDamage' => $card['damage'],
                    'willReceiveMeat' => $card['health'],
                    'stamina' => 0,
                    'killed' => false,
                ];
            },
            function ($_this, bool $finalizeInterrupt, $data) {
                $this->game->log('stResolveEncounter', $data);
                if ($data['stamina'] != 0) {
                    $_this->character->adjustActiveStamina($data['stamina']);
                }
                if ($data['escape']) {
                    $_this->activeCharacterEventLog('escaped from a ${name}', $data);
                } elseif ($data['encounterHealth'] <= $data['characterDamage']) {
                    $damageTaken = $this->countDamageTaken($data);
                    $data['killed'] = true;
                    if ($damageTaken != 0) {
                        $_this->character->adjustActiveHealth(-$damageTaken);
                    }
                    if ($_this->character->getActiveHealth() != 0) {
                        $_this->adjustResource('meat', $data['willReceiveMeat']);
                        if ($damageTaken > 0) {
                            $_this->activeCharacterEventLog(
                                'defeated a ${name}, gained ${willReceiveMeat} meat and lost ${damageTaken} health',
                                [...$data, 'damageTaken' => $damageTaken]
                            );
                        } else {
                            $_this->activeCharacterEventLog('defeated a ${name} and gained ${willReceiveMeat} meat', [...$data]);
                        }
                    }
                } else {
                    $damageTaken = $this->countDamageTaken($data);
                    if ($damageTaken > 0) {
                        $_this->character->adjustActiveHealth(-$data['willTakeDamage']);
                        $_this->activeCharacterEventLog('was attacked by a ${name} and lost ${willTakeDamage} health', $data);
                    } else {
                        $_this->activeCharacterEventLog('was attacked by a ${name} but lost no health', $data);
                    }
                }
                $_this->gameData->set('encounterState', $data);
                $_this->gamestate->nextState('postEncounter');
            }
        );
    }
    public function argResolveEncounter()
    {
        $result = [...$this->game->getAllDatas()];
        return $result;
    }
}
