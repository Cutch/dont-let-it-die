<?php
declare(strict_types=1);

namespace Bga\Games\DontLetItDie;

use Closure;

include dirname(__DIR__) . '/data/Utils.php';
class ActInterrupt
{
    private Game $game;
    private array $activatableSkills = [];

    public function __construct(Game $game)
    {
        $this->game = $game;
    }
    public function interruptableFunction(
        string $functionName,
        array $args = [],
        callable $hook,
        callable $startCallback,
        callable $endCallback
    ) {
        $existingData = $this->getState();
        if (!$existingData || !array_key_exists('functionName', $existingData)) {
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
            ];
            if (sizeof($this->activatableSkills) == 0) {
                // var_dump(json_encode(['exitHook', $this->game->gamestate->state()['name'], 'noSkill']));
                // No skills can activate
                $endCallback($this->game, $data, ...$args);
            } else {
                $this->setState($interruptData);
                // Goto the skill screen
                $this->game->gamestate->nextState('interrupt');
            }
        } else {
            // var_dump(json_encode(['exitHook', $this->game->gamestate->state()['name']]));
            $this->setState([]);
            // Don't need to re-check for interrupts
            $hook($existingData['data'], false);
            // Calling after skill screen
            $endCallback($this->game, $existingData['data'], ...$existingData['args']);
        }
    }
    public function isStateResolving(): bool
    {
        $existingData = $this->getState();
        return $existingData &&
            array_key_exists('currentState', $existingData) &&
            $existingData['currentState'] == $this->game->gamestate->state()['name'];
    }
    public function checkForInterrupt(): bool
    {
        $existingData = $this->getState();
        if ($existingData && array_key_exists('functionName', $existingData)) {
            // var_dump(json_encode([$existingData['functionName'], $existingData['args']]));
            call_user_func([$this->game, $existingData['functionName']], ...$existingData['args']);
            return true;
        }
        return false;
    }
    public function getState()
    {
        return $this->game->gameData->getGlobals('actInterruptState');
    }
    private function setState($data)
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
    public function actInterrupt(string $skillId): void
    {
        $data = $this->getState();
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
        $this->setState([...$data, 'skills' => $skills]);
        // var_dump([json_encode($skills)]);
        $newPlayerIds = $this->getSkillsPlayerIds($skills);

        $array = array_diff($playerIds, $newPlayerIds);
        if (sizeof($array) > 0) {
            $this->game->gamestate->setPlayerNonMultiactive($array[0], $data['currentState']);
        }
    }
    public function onInterruptCancel()
    {
        $data = $this->getState();
        $playerId = $this->game->getCurrentPlayer();
        // var_dump(json_encode([$this->game->gamestate->state()['name'], $data['currentState']]));
        $this->game->gamestate->setPlayerNonMultiactive($playerId, $data['currentState']);
    }
    public function argInterrupt(): array
    {
        $data = $this->getState();
        return [
            'currentCharacter' => $this->game->character->getTurnCharacter()['character_name'],
            ...$data,
            'actions' => ['actUseSkill' => []],
            'availableSkills' => array_map(function ($skill) {
                $actionCost = [
                    'action' => 'actUseSkill',
                    'subAction' => $skill['id'],
                    'stamina' => array_key_exists('stamina', $skill) ? $skill['stamina'] : null,
                    'health' => array_key_exists('health', $skill) ? $skill['health'] : null,
                ];
                $this->game->hooks->onGetActionCost($actionCost);
                if (array_key_exists('stamina', $actionCost)) {
                    $skill['stamina'] = $actionCost['stamina'];
                }
                if (array_key_exists('health', $actionCost)) {
                    $skill['health'] = $actionCost['health'];
                }
                return $skill;
            }, $data['skills']),
        ];
    }
    public function stInterrupt(): void
    {
        $data = $this->getState();
        $playerIds = $this->getSkillsPlayerIds($data['skills']);
        $this->game->gamestate->setPlayersMultiactive($playerIds, $data['currentState']);
    }
}
