<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Closure;

include dirname(__DIR__) . '/data/Utils.php';
class ActInterrupt
{
    private Game $game;
    private array $activatableSkills = [];
    private array $calledFunction = [];

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->calledFunction = [];
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
            // var_dump(json_encode(['$startCallback', $this->activatableSkills]));
            $data = $startCallback($this->game, ...$args);
            $hook($data, false);
            $interruptData = [
                'data' => $data,
                'functionName' => $functionName,
                'args' => $args,
                'currentState' => $this->game->gamestate->state()['name'],
                'skills' => $this->activatableSkills,
                'stateNumber' => sizeof($entireState) + 1,
            ];
            if (sizeof($this->activatableSkills) == 0) {
                // var_dump(json_encode(['exitHook', $this->game->gamestate->state()['name'], 'noSkill']));
                // No skills can activate
                $endCallback($this->game, $data, ...$args);
            } else {
                // var_dump(json_encode($interruptData));
                $this->setState($functionName, $interruptData);
                // Goto the skill screen
                $this->game->gamestate->nextState('interrupt');
            }
        } else {
            // var_dump(json_encode(['exitHook', $this->game->gamestate->state()['name']]));
            // Don't need to re-check for interrupts
            $hook($existingData['data'], false);
            // Calling after skill screen
            $endCallback($this->game, $existingData['data'], ...$existingData['args']);
            $this->setState($functionName, null);
        }
    }
    public function isStateResolving(): bool
    {
        $state = $this->getDataForState();
        if (!$state) {
            return false;
        }
        $data = $state['data'];

        return $data && array_key_exists('currentState', $data) && $data['currentState'] == $this->game->gamestate->state()['name'];
    }
    public function checkForInterrupt(): bool
    {
        $state = $this->getDataForState();
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
        $data = $this->game->gameData->getGlobals('actInterruptState');
        return array_key_exists($functionName, $data) ? $data[$functionName] : null;
    }
    private function setState(string $functionName, ?array $data): void
    {
        $currentData = $this->game->gameData->getGlobals('actInterruptState');
        if ($data) {
            $currentData[$functionName] = $data;
        } else {
            unset($currentData[$functionName]);
        }
        $this->setEntireState($currentData);
    }
    public function getEntireState(): array
    {
        return $this->game->gameData->getGlobals('actInterruptState');
    }
    private function setEntireState(array $data): void
    {
        $this->game->gameData->set('actInterruptState', $data);
    }
    private function getSkillsPlayerIds(array $skills): array
    {
        $_this = $this;
        return array_unique(
            array_map(function ($skill) use ($_this) {
                $char = $_this->game->character->getCharacterData($skill['characterId']);
                return $char['player_id'];
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
        if (!$state) {
            return;
        }
        $functionName = $state['functionName'];
        $data = $state['data'];

        $skills = $data['skills'];
        $playerIds = $this->getSkillsPlayerIds($skills);
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
        $newPlayerIds = $this->getSkillsPlayerIds($skills);

        $array = array_diff($playerIds, $newPlayerIds);
        // var_dump(json_encode([$array, $playerIds, $newPlayerIds, $skills]));
        if (sizeof($array) > 0) {
            $this->game->gamestate->setPlayerNonMultiactive($array[0], $data['currentState']);
        }
    }
    public function onInterruptCancel()
    {
        $state = $this->getLatestInterruptState();
        if (!$state) {
            return;
        }
        $data = $state['data'];

        $playerId = $this->game->getCurrentPlayer();
        // var_dump(json_encode([$this->game->gamestate->state()['name'], $data['currentState']]));
        $this->game->gamestate->setPlayerNonMultiactive($playerId, $data['currentState']);
    }
    public function argInterrupt(): array
    {
        $state = $this->getLatestInterruptState();
        // if (!$state) {
        //     return [
        //         'character_name' => $this->game->getCharacterHTML(),
        //     ];
        // }
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
        $playerIds = $this->getSkillsPlayerIds($data['skills']);
        $this->game->gamestate->setPlayersMultiactive($playerIds, $data['currentState']);
    }
}
