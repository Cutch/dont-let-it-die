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
        callable $endCallback,
        string $cancelState = null
    ) {
        $entireState = $this->getEntireState();
        $existingData = $this->getState($functionName);
        if (!$existingData) {
            // First time calling
            // var_dump(json_encode(['$startCallback', $this->activatableSkills]));
            $data = $startCallback($this->game, ...$args);
            $res = $hook($data, false);
            $interrupt = $res && array_key_exists('interrupt', $res);
            $interruptData = [
                'data' => $data,
                'functionName' => $functionName,
                'args' => $args,
                'currentState' => $this->game->gamestate->state()['name'],
                'cancelState' => $cancelState ?? $this->game->gamestate->state()['name'],
                'skills' => $this->activatableSkills,
                'stateNumber' => sizeof($entireState) + 1,
            ];
            if (sizeof($this->activatableSkills) == 0 && !$interrupt) {
                // var_dump(json_encode(['exitHook', $this->game->gamestate->state()['name'], 'noSkill']));
                // No skills can activate
                $endCallback($this->game, false, $data, ...$args);
            } else {
                // var_dump(json_encode($interruptData));
                $this->setState($functionName, $interruptData);
                // Goto the skill screen
                $this->game->gamestate->nextState('interrupt');
            }
        } else {
            // var_dump(json_encode(['exitHook', $functionName, $this->game->gamestate->state()['name'], $existingData]));

            // Don't need to re-check for interrupts
            $hook($existingData['data'], false);
            // Calling after skill screen
            $endCallback($this->game, true, $existingData['data'], ...$existingData['args']);
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
        // var_dump(json_encode('checkForInterrupt'));
        if (!$state) {
            return false;
        }
        $data = $state['data'];

        if ($data && array_key_exists('functionName', $data)) {
            call_user_func([$this->game, $data['functionName']], ...$data['args']);
            return true;
        }
        return false;
    }
    public function getState(string $functionName): ?array
    {
        $data = $this->game->gameData->get('actInterruptState');
        return array_key_exists($functionName, $data) ? $data[$functionName] : null;
    }
    public function setState(string $functionName, ?array $data): void
    {
        $currentData = $this->game->gameData->get('actInterruptState');
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
        // var_dump(json_encode(['actInterrupt', $state]));
        if (!$state) {
            return;
        }
        $functionName = $state['functionName'];
        $data = $state['data'];

        $skills = $data['skills'];
        $characterIds = $this->getSkillsCharacterIds($skills);
        array_walk($skills, function ($v, $k) use ($skillId, &$skills, &$data) {
            // var_dump(json_encode([$skillId, $v['id']]));
            if ($skillId == $v['id']) {
                // var_dump(json_encode(['onInterrupt']));
                $this->game->hooks->onInterrupt($data, $v);
                unset($skills[$k]);
                $skills = array_values($skills);
            }
        });
        $this->setState($functionName, [...$data, 'skills' => $skills]);
        $newCharacterIds = $this->getSkillsCharacterIds($skills);

        $array = array_unique(array_diff($characterIds, $newCharacterIds));
        // if (sizeof($array) > 0) {
        //     // var_dump(json_encode([$array, $playerIds, $newPlayerIds, $skills]));
        //     $this->game->gamestate->setPlayerNonMultiactive($array[0], $data['currentState']);
        // }
        foreach ($array as $k => $v) {
            $this->game->gameData->removeMultiActiveCharacter($v, $data['cancelState']);
        }
    }
    public function onInterruptCancel()
    {
        $state = $this->getLatestInterruptState();
        if (!$state) {
            return false;
        }
        $this->setState($state['functionName'], null);
        $data = $state['data'];

        $playerId = $this->game->getCurrentPlayer();

        $characterIds = array_map(
            function ($char) {
                return $char['id'];
            },
            array_filter($this->game->character->getAllCharacterData(), function ($char) use ($playerId) {
                return $char['player_id'] == $playerId;
            })
        );

        // var_dump(json_encode([$this->game->gamestate->state()['name'], $data['currentState'], $data['cancelState']]));
        // var_dump($this->game->gamestate->getActivePlayerList(), $this->game->gamestate->state()['name']);
        foreach ($characterIds as $k => $v) {
            $this->game->gameData->removeMultiActiveCharacter($v, $data['cancelState']);
        }
        // $bool = $this->game->gamestate->setPlayerNonMultiactive($playerId, $data['currentState']);
        // $bool = $this->game->gamestate->setPlayerNonMultiactive($playerId, $data['currentState']);
        // $bool2 = $this->game->gamestate->setAllPlayersNonMultiactive('playerTurn');
        // $this->game->gamestate->nextState('playerTurn');
        // var_dump(
        //     'onInterruptCancel ' . $playerId . ' ' . $data['currentState'],
        //     $this->game->gamestate->getActivePlayerList(),
        //     $bool,
        //     $this->game->gamestate->state()['name']
        // );
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
        return [
            ...$data,
            'character_name' => $this->game->getCharacterHTML(),
            'actions' => ['actUseSkill' => []],
            'availableSkills' => $this->game->actions->wrapSkills($data['skills']),
        ];
    }
    public function stInterrupt(): void
    {
        $state = $this->getLatestInterruptState();
        $data = $state['data'];
        $characterIds = $this->getSkillsCharacterIds($data['skills']);
        if (sizeof($characterIds) == 0) {
            array_push($characterIds, $this->game->character->getTurnCharacter()['id']);
        }
        // var_dump(json_encode([$characterIds, 'stInterrupt', $data['skills']]));
        foreach ($characterIds as $k => $v) {
            $this->game->gameData->addMultiActiveCharacter($v);
        }
    }
}
