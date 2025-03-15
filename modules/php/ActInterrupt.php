<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Closure;

include dirname(__DIR__) . '/data/Utils.php';
class ActInterrupt
{
    private Game $game;
    private array $activatableSkills = [];
    // private array $calledFunction = [];

    public function __construct(Game $game)
    {
        $this->game = $game;
        // $this->calledFunction = [];
    }
    public function interruptableFunction(
        string $functionName,
        array $args = [],
        callable $hook,
        callable $startCallback,
        callable $endCallback
    ) {
        $entireState = $this->getEntireState();
        $existingData = $this->getState($functionName);

        if (!$existingData) {
            // First time calling
            $data = $startCallback($this->game, ...$args);
            if (!$data) {
                return;
            }
            $res = $hook($data, false);
            $interrupt = $res && array_key_exists('interrupt', $res);
            $interruptData = [
                'data' => $data,
                'functionName' => $functionName,
                'args' => $args,
                'currentState' => $this->game->gamestate->state()['name'],
                'skills' => $this->activatableSkills,
                'stateNumber' => sizeof($entireState) + 1,
            ];
            $this->game->log($functionName, $data, $res, $interruptData);
            $this->activatableSkills = [];
            if (sizeof($interruptData['skills']) == 0 && !$interrupt) {
                $this->game->log('exitHook', 'not interrupted', $this->game->gamestate->state()['name'], 'noSkill');
                // No skills can activate
                $endCallback($this->game, false, $data, ...$args);
            } else {
                $this->setState($functionName, $interruptData);
                // Goto the skill screen
                $this->game->gamestate->nextState('interrupt');
            }
        } elseif (
            array_key_exists('cancelled', $existingData) &&
            sizeof($existingData['skills']) ==
                sizeof(
                    array_filter($existingData['skills'], function ($s) {
                        return array_key_exists('cancellable', $s);
                    })
                )
        ) {
            $this->setState($functionName, null);
        } elseif (!array_key_exists('activated', $existingData)) {
            $this->setState($functionName, ['activated' => true, ...$existingData]);
            $this->game->log('exitHook', 'finalize', $functionName, $this->game->gamestate->state()['name'], $existingData);
            // Don't need to re-check for interrupts
            $hook($existingData['data'], false);
            // Calling after skill screen
            $endCallback($this->game, true, $existingData['data'], ...$existingData['args']);
            $this->setState($functionName, null);
        } else {
            $this->setState($functionName, null);
        }
    }
    public function isStateResolving(): bool
    {
        $state = $this->getDataForState() ?? $this->getLatestInterruptState();
        if (!$state) {
            return false;
        }
        $data = $state['data'];

        return $data && array_key_exists('currentState', $data) && $data['currentState'] == $this->game->gamestate->state()['name'];
    }
    public function checkForInterrupt(): bool
    {
        $state = $this->getDataForState() ?? $this->getLatestInterruptState();
        $this->game->log('checkForInterrupt', $state);
        if (!$state) {
            return false;
        }
        $data = $state['data'];

        if ($data && array_key_exists('functionName', $data)) {
            $this->game->log('checkForInterrupt ' . $state['functionName'], $data['args']);
            call_user_func([$this->game, $data['functionName']], ...$data['args']);
            return true;
        }
        return false;
    }
    public function getState(string $functionName): ?array
    {
        $data = $this->getEntireState();
        return array_key_exists($functionName, $data) ? $data[$functionName] : null;
    }
    public function setState(string $functionName, ?array $data): void
    {
        $currentData = $this->getEntireState();
        if ($data) {
            $currentData[$functionName] = $data;
        } else {
            unset($currentData[$functionName]);
        }
        $this->setEntireState($currentData);
    }
    public function getEntireState(): array
    {
        return $this->game->gameData->get('actInterruptState');
    }
    private function setEntireState(array $data): void
    {
        $this->game->gameData->set('actInterruptState', $data);
    }
    private function getSkillsCharacterIds(array $skills): array
    {
        return array_unique(
            array_map(function ($skill) {
                return $skill['characterId'];
            }, $skills)
        );
    }
    public function addSkillInterrupt($skill): void
    {
        array_push($this->activatableSkills, $skill);
    }
    public function getLatestInterruptState(): ?array
    {
        $state = $this->getEntireState();
        $maxStateNumber = 0;
        array_walk($state, function ($v) use (&$maxStateNumber) {
            $maxStateNumber = max($maxStateNumber, $v['stateNumber']);
        });

        $data = array_keys(
            array_filter($state, function ($v) use ($maxStateNumber) {
                return $v['stateNumber'] == $maxStateNumber;
            })
        );
        if (sizeof($data) == 0) {
            return null;
        }
        $functionName = $data[0];
        $data = $state[$functionName];
        return ['functionName' => $functionName, 'data' => $data];
    }
    private function getDataForState(): ?array
    {
        $state = $this->getEntireState();
        $stateName = $this->game->gamestate->state()['name'];
        $data = array_keys(
            array_filter($state, function ($v) use ($stateName) {
                return $v['currentState'] == $stateName;
            })
        );
        if (sizeof($data) == 0) {
            return null;
        }
        $functionName = $data[0];
        $data = $state[$functionName];
        return ['functionName' => $functionName, 'data' => $data];
    }
    public function actInterrupt(string $skillId): void
    {
        $state = $this->getLatestInterruptState();
        $this->game->character->addExtraTime();
        $this->game->log('actInterrupt', ['action' => 'actInterrupt', 'state' => $state]);
        if (!$state) {
            return;
        }
        $functionName = $state['functionName'];
        $data = $state['data'];

        $skills = $data['skills'];
        $characterIds = $this->getSkillsCharacterIds($skills);
        // Remove skills so that we know there's nothing left to do
        array_walk($skills, function ($v, $k) use ($skillId, &$skills, &$data) {
            if ($skillId == $v['id']) {
                if (array_key_exists($k, $skills)) {
                    $data['skills'] = &$skills;
                    $this->game->hooks->onInterrupt($data, $v);
                }
                unset($skills[$k]);
                $skills = array_values($skills);
            }
        });
        $this->setState($functionName, [...$data, 'skills' => $skills]);
        $newCharacterIds = $this->getSkillsCharacterIds($skills);

        $array = array_unique(array_diff($characterIds, $newCharacterIds));
        // if (sizeof($array) > 0) {
        //     $this->game->gamestate->setPlayerNonMultiactive($array[0], $data['currentState']);
        // }
        $changeState = !$this->game->gamestate->isMutiactiveState();
        if (!$changeState) {
            foreach ($array as $k => $v) {
                $changeState |= $this->game->gameData->removeMultiActiveCharacter($v, $data['currentState']);
            }
        }
        if ($changeState) {
            // Check that the state change has not already handled the function call
            if ($data && array_key_exists('functionName', $data) && $this->getState($data['functionName'])) {
                $this->game->log('actInterrupt ' . $state['functionName'], $data['args']);
                call_user_func([$this->game, $data['functionName']], ...$data['args']);
                $this->game->log('actInterrupt changeState ', $data['functionName']);
            }
        }
    }
    public function onInterruptCancel()
    {
        $state = $this->getLatestInterruptState();
        $this->game->log('onInterruptCancel', ['action' => 'onInterruptCancel', 'state' => $state]);
        if (!$state) {
            return false;
        }
        $data = $state['data'];

        $playerId = $this->game->getCurrentPlayer();

        $characterIds = array_map(function ($char) {
            return $char['id'];
        }, $this->game->character->getAllCharacterDataForPlayer($playerId));
        // Remove skills so that we know there's nothing left to do
        // $skills = $data['skills'];
        // array_walk($skills, function (&$v, $k) use ($characterIds) {
        //     if (in_array($v['characterId'], $characterIds)) {
        //         unset($v);
        //     }
        // });
        // $this->setState($state['functionName'], [...$data, 'skills' => $skills, 'activated' => true]);
        // $this->setState($state['functionName'], null); // TODO: for items this doesnt work, but does work for player turn?

        // var_dump(json_encode([$this->game->gamestate->state()['name'], $data['currentState'], $data['currentState']]));
        // var_dump($this->game->gamestate->getActivePlayerList(), $this->game->gamestate->state()['name']);
        $changeState = false;
        foreach ($characterIds as $k => $v) {
            $changeState |= $this->game->gameData->removeMultiActiveCharacter($v, $data['currentState']);
        }
        if (sizeof($characterIds) > 0 && sizeof($this->game->gameData->getAllMultiActiveCharacter()) == 0 && !$changeState) {
            $this->game->gamestate->nextState($data['currentState']);
            $changeState = true;
        }
        if ($changeState) {
            if ($data && array_key_exists('functionName', $data) && $this->getState($state['functionName'])) {
                $this->setState($state['functionName'], [...$this->getState($state['functionName']), 'cancelled' => true]);
                $this->game->log('actInterruptCancel ' . $state['functionName'], $data['args']);
                call_user_func([$this->game, $data['functionName']], ...$data['args']);
                $this->game->log('onInterruptCancel 3 call ', $data['functionName']);
            }
        }
        $this->game->log('onInterruptCancel 2', [
            'action' => 'onInterruptCancel',
            'characterIds' => $characterIds,
            'changeState' => $changeState,
        ]);

        return true;
    }
    public function argInterrupt(): array
    {
        $state = $this->getLatestInterruptState();
        if (!$state) {
            return [
                'character_name' => $this->game->getCharacterHTML(),
            ];
        }
        $data = $state['data'];
        $this->game->getAllPlayers($data);
        $this->game->log('argInterrupt', ['action' => 'argInterrupt', 'state' => $data]);
        return [
            ...$data,
            'character_name' => $this->game->getCharacterHTML(),
            'actions' => [
                [
                    'action' => 'actUseSkill',
                    'type' => 'action',
                ],
                [
                    'action' => 'actUseItem',
                    'type' => 'action',
                ],
            ],
            'availableSkills' => $this->game->actions->wrapSkills(
                array_filter($data['skills'], function ($skill) {
                    return $skill['type'] == 'skill';
                }),
                'actUseSkill'
            ),
            'availableItemSkills' => $this->game->actions->wrapSkills(
                array_filter($data['skills'], function ($skill) {
                    return $skill['type'] == 'item-skill';
                }),
                'actUseItem'
            ),
        ];
    }
    public function stInterrupt(): void
    {
        $state = $this->getLatestInterruptState();
        $data = $state['data'];
        $characterIds = $this->getSkillsCharacterIds($data['skills']);
        // if (sizeof($characterIds) == 0) {
        //     array_push($characterIds, $this->game->character->getTurnCharacter()['id']);
        // }
        // var_dump(json_encode([$characterIds, 'stInterrupt', $data['skills']]));
        foreach ($characterIds as $k => $v) {
            $this->game->gameData->addMultiActiveCharacter($v);
        }
        $changeState = false;
        if (sizeof($characterIds) == 0) {
            $this->game->gamestate->nextState($data['currentState']);
            $changeState = true;
        }

        $this->game->log('stInterrupt', [
            'action' => 'stInterrupt',
            'state' => $state,
            'changeState' => $changeState,
            'state' => $data['currentState'],
        ]);
    }
}
