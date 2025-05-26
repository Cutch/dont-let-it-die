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
 * states.inc.php
 *
 * DontLetItDie game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: $this->checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!
$gameSetup = 1;
$characterSelect = 2;
$startHindrance = 3;
$eatSelection = 9;
$playerTurn = 10;
$drawCard = 11;
$tooManyItems = 12;
$deckSelection = 13;
$resourceSelection = 14;
$nextCharacter = 15;
$characterSelection = 16;
$cardSelection = 17;
$hindranceSelection = 18;
$itemSelection = 19;
$resolveEncounter = 20;
$postEncounter = 21;
$interrupt = 22;
$whichWeapon = 23;
$dayEvent = 24;
$dinnerPhase = 27;
$dinnerPhasePost = 28;
$dinnerPhasePrivate = 29;
$nightPhase = 30;
$nightDrawCard = 31;
$morningPhase = 50;
$tradePhase = 60;
$tradePhaseActions = 61;
$confirmTradePhase = 62;
$waitTradePhase = 63;
$changeZombiePlayer = 97;
$gameEnd = 99;

$interruptScreens = [
    'eatSelection' => $eatSelection,
    'drawCard' => $drawCard,
    'tooManyItems' => $tooManyItems,
    'deckSelection' => $deckSelection,
    'resourceSelection' => $resourceSelection,
    'characterSelection' => $characterSelection,
    'hindranceSelection' => $hindranceSelection,
    'itemSelection' => $itemSelection,
    'interrupt' => $interrupt,
    'cardSelection' => $cardSelection,
];

$machinestates = [
    // The initial state. Please do not modify.

    $gameSetup => [
        'name' => 'gameSetup',
        'description' => '',
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => ['' => $characterSelect],
    ],
    $characterSelect => [
        'name' => 'characterSelect',
        'description' => clienttranslate('Waiting for other players'),
        'descriptionmyturn' => clienttranslate('Select a Character'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionCount',
        'possibleactions' => ['actChooseCharacters', 'actCharacterClicked'],
        'transitions' => ['playerTurn' => $playerTurn, 'startHindrance' => $startHindrance],
        'action' => 'stSelectCharacter',
    ],
    $startHindrance => [
        'name' => 'startHindrance',
        'description' => clienttranslate('Waiting for other players'),
        'descriptionmyturn' => clienttranslate('Place Discoveries'),
        'type' => 'multipleactiveplayer',
        'args' => 'argStartHindrance',
        'possibleactions' => ['actMoveDiscovery', 'actDone'],
        'transitions' => ['playerTurn' => $playerTurn],
        'action' => 'stStartHindrance',
    ],
    $eatSelection => [
        'name' => 'eatSelection',
        'description' => clienttranslate('Other characters are eating'),
        'descriptionmyturn' => clienttranslate('Selecting a food'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actEat', 'actCancel'],
        'transitions' => ['playerTurn' => $playerTurn],
    ],
    $playerTurn => [
        'name' => 'playerTurn',
        'description' => clienttranslate('${character_name} is playing'),
        'descriptionmyturn' => clienttranslate('${character_name} can'),
        'type' => 'activeplayer',
        'args' => 'argPlayerState',
        'action' => 'stPlayerTurn',
        'possibleactions' => [
            // these actions are called from the front with bgaPerformAction, and matched to the function on the game.php file
            'actDrawGather',
            'actDrawForage',
            'actDrawHarvest',
            'actDrawHunt',
            'actDrawExplore',
            'actSpendFKP',
            'actInvestigateFire',
            'actAddWood',
            'actUseHerb',
            'actEat',
            'actCook',
            'actCraft',
            'actTrade',
            'actEndTurn',
            'actUseSkill',
            'actUseItem',
            'actRevive',
            'actUndo',
        ],
        'transitions' => [
            'endGame' => $gameEnd,
            'drawCard' => $drawCard,
            'endTurn' => $nextCharacter,
            'changeZombiePlayer' => $changeZombiePlayer,
        ],
    ],
    $drawCard => [
        'name' => 'drawCard',
        'description' => clienttranslate('Drawing Card'),
        'descriptionmyturn' => clienttranslate('Drawing Card'),
        'type' => 'game',
        'args' => 'argDrawCard',
        'action' => 'stDrawCard',
        'transitions' => [
            'endGame' => $gameEnd,
            'resolveEncounter' => $resolveEncounter,
            'playerTurn' => $playerTurn,
            'drawCard' => $drawCard,
            'dayEvent' => $dayEvent,
        ],
    ],
    $tooManyItems => [
        'name' => 'tooManyItems',
        'description' => clienttranslate('Other characters are selecting an item'),
        'descriptionmyturn' => clienttranslate('Selecting an item'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSendToCamp'],
        'transitions' => ['playerTurn' => $playerTurn],
    ],
    $deckSelection => [
        'name' => 'deckSelection',
        'description' => clienttranslate('Other characters are selecting a deck'),
        'descriptionmyturn' => clienttranslate('Selecting a deck'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectDeck', 'actCancel'],
        'transitions' => ['playerTurn' => $playerTurn, 'deckSelection' => $deckSelection],
    ],
    $resourceSelection => [
        'name' => 'resourceSelection',
        'description' => clienttranslate('Other characters are selecting a resource'),
        'descriptionmyturn' => clienttranslate('Selecting a resource'),
        'type' => 'multipleactiveplayer',
        'args' => 'argResourceSelection',
        'possibleactions' => ['actSelectResource', 'actCancel'],
        'transitions' => ['playerTurn' => $playerTurn],
    ],
    $nextCharacter => [
        'name' => 'nextCharacter',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextCharacter',
        'updateGameProgression' => true,
        'transitions' => ['endGame' => $gameEnd, 'playerTurn' => $playerTurn, 'dinnerPhase' => $dinnerPhase],
    ],
    $characterSelection => [
        'name' => 'characterSelection',
        'description' => clienttranslate('Other characters are selecting a character'),
        'descriptionmyturn' => clienttranslate('Selecting a character'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectCharacter', 'actCancel'],
        'transitions' => [
            'playerTurn' => $playerTurn,
            'tradePhase' => $tradePhase,
            'morningPhase' => $morningPhase,
        ],
    ],
    $cardSelection => [
        'name' => 'cardSelection',
        'description' => clienttranslate('Other characters are selecting a card'),
        'descriptionmyturn' => clienttranslate('Selecting a card'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectCard', 'actCancel'],
        'transitions' => ['playerTurn' => $playerTurn, 'morningPhase' => $morningPhase],
    ],
    $hindranceSelection => [
        'name' => 'hindranceSelection',
        'description' => clienttranslate('Other characters are selecting a hindrance'),
        'descriptionmyturn' => clienttranslate('Selecting a hindrance'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectHindrance', 'actCancel'],
        'transitions' => [
            'playerTurn' => $playerTurn,
            'nightPhase' => $nightPhase,
            'morningPhase' => $morningPhase,
        ],
    ],
    $itemSelection => [
        'name' => 'itemSelection',
        'description' => clienttranslate('Other characters are selecting an item'),
        'descriptionmyturn' => clienttranslate('Selecting an item'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectItem', 'actCancel'],
        'transitions' => ['playerTurn' => $playerTurn, 'nightPhase' => $nightPhase],
    ],
    $resolveEncounter => [
        'name' => 'resolveEncounter',
        'description' => clienttranslate('Resolving Encounter'),
        'descriptionmyturn' => clienttranslate('Resolving Encounter'),
        'type' => 'multipleactiveplayer',
        'action' => 'stResolveEncounter',
        'args' => 'argResolveEncounter',
        'possibleactions' => ['actChooseResource', 'actUseItem'],
        'transitions' => [
            'endGame' => $gameEnd,
            'postEncounter' => $postEncounter,
            'whichWeapon' => $whichWeapon,
        ],
    ],
    $postEncounter => [
        'name' => 'postEncounter',
        'description' => clienttranslate('Resolving Encounter'),
        'descriptionmyturn' => clienttranslate('Resolving Encounter'),
        'type' => 'activeplayer',
        'action' => 'stPostEncounter',
        'args' => 'argPostEncounter',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => $gameEnd,
            'playerTurn' => $playerTurn,
            'drawCard' => $drawCard,
            'changeZombiePlayer' => $changeZombiePlayer,
        ],
    ],
    $interrupt => [
        'name' => 'interrupt',
        'description' => clienttranslate('Other players are looking at their skills'),
        'descriptionmyturn' => clienttranslate('Looking at skills'),
        'type' => 'multipleactiveplayer',
        'action' => 'stInterrupt',
        'args' => 'argInterrupt',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => $gameEnd,
            'eatSelection' => $eatSelection,
            'playerTurn' => $playerTurn,
            'drawCard' => $drawCard,
            'resourceSelection' => $resourceSelection,
            'endTurn' => $nextCharacter,
            'characterSelection' => $characterSelection,
            'cardSelection' => $cardSelection,
            'hindranceSelection' => $hindranceSelection,
            'resolveEncounter' => $resolveEncounter,
            'postEncounter' => $postEncounter,
            'nightDrawCard' => $nightDrawCard,
            'morningPhase' => $morningPhase,
            'tradePhase' => $tradePhase,
        ],
    ],
    $whichWeapon => [
        'name' => 'whichWeapon',
        'description' => clienttranslate('${character_name} is selecting a weapon'),
        'descriptionmyturn' => clienttranslate('Choose your weapon'),
        'type' => 'activeplayer',
        'args' => 'argWhichWeapon',
        'possibleactions' => ['actChooseWeapon'],
        'transitions' => ['resolveEncounter' => $resolveEncounter, 'changeZombiePlayer' => $changeZombiePlayer],
    ],
    $dayEvent => [
        'name' => 'dayEvent',
        'description' => clienttranslate('${character_name} is resolving an event'),
        'descriptionmyturn' => clienttranslate('What do you do'),
        'type' => 'activeplayer',
        'action' => 'stDayEvent',
        'args' => 'argDayEvent',
        'possibleactions' => ['actUseSkill', 'actUseItem'],
        'transitions' => [
            'endGame' => $gameEnd,
            'playerTurn' => $playerTurn,
            'drawCard' => $drawCard,
            'resolveEncounter' => $resolveEncounter,
            'changeZombiePlayer' => $changeZombiePlayer,
        ],
    ],
    $dinnerPhase => [
        'name' => 'dinnerPhase',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'multipleactiveplayer',
        'action' => 'stDinnerPhase',
        // 'args' => 'argDinnerPhase',
        'possibleactions' => [],
        'transitions' => ['dinnerPhasePost' => $dinnerPhasePost, 'nightPhase' => $nightPhase],
        'initialprivate' => $dinnerPhasePrivate,
    ],
    $dinnerPhasePost => [
        'name' => 'dinnerPhasePost',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'activeplayer',
        'action' => 'stDinnerPhasePost',
        'possibleactions' => [],
        'transitions' => ['nightPhase' => $nightPhase, 'changeZombiePlayer' => $changeZombiePlayer],
        'initialprivate' => $dinnerPhasePrivate,
    ],
    $dinnerPhasePrivate => [
        'name' => 'dinnerPhasePrivate',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'private',
        'args' => 'argDinnerPhase',
        'possibleactions' => ['actEat', 'actDone'],
    ],
    $nightPhase => [
        'name' => 'nightPhase',
        'description' => clienttranslate('It\'s night time'),
        'descriptionmyturn' => clienttranslate('It\'s night time'),
        'type' => 'game',
        'action' => 'stNightPhase',
        // 'args' => 'argNightPhase',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => $gameEnd,
            'nightDrawCard' => $nightDrawCard,
        ],
    ],
    $nightDrawCard => [
        'name' => 'nightDrawCard',
        'description' => clienttranslate('Drawing Night Card'),
        'descriptionmyturn' => clienttranslate('Drawing Night Card'),
        'type' => 'game',
        'args' => 'argNightDrawCard',
        'action' => 'stNightDrawCard',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => $gameEnd,
            'morningPhase' => $morningPhase,
            'nightPhase' => $nightPhase,
            'nightDrawCard' => $nightDrawCard,
        ],
    ],
    $morningPhase => [
        'name' => 'morningPhase',
        'description' => clienttranslate('Morning has arrived'),
        'descriptionmyturn' => clienttranslate('Morning has arrived'),
        'type' => 'game',
        'action' => 'stMorningPhase',
        'updateGameProgression' => true,
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => $gameEnd,
            'tradePhase' => $tradePhase,
        ],
    ],
    $tradePhase => [
        'name' => 'tradePhase',
        'description' => clienttranslate('Waiting for others to trade Items'),
        'descriptionmyturn' => clienttranslate('Trade Items'),
        'type' => 'multipleactiveplayer',
        'action' => 'stTradePhase',
        'args' => 'argTradePhase',
        'initialprivate' => $tradePhaseActions,
        'possibleactions' => [],
        'transitions' => ['nextCharacter' => $nextCharacter],
    ],
    $tradePhaseActions => [
        'name' => 'tradePhaseActions',
        'descriptionmyturn' => clienttranslate('Trade Items'),
        'type' => 'private',
        'action' => 'stTradePhaseWait',
        'args' => 'argTradePhaseActions',
        'possibleactions' => ['actTradeItem', 'actTradeDone'],
        'transitions' => ['confirmTradePhase' => $confirmTradePhase, 'waitTradePhase' => $waitTradePhase],
    ],
    $confirmTradePhase => [
        'name' => 'confirmTradePhase',
        'descriptionmyturn' => clienttranslate('Confirm Trade'),
        'type' => 'private',
        // 'action' => 'stTradePhaseWait',
        'args' => 'argConfirmTradePhase',
        'possibleactions' => ['actConfirmTradeItem', 'actCancelTrade'],
        'transitions' => ['tradePhaseActions' => $tradePhaseActions],
    ],
    $waitTradePhase => [
        'name' => 'waitTradePhase',
        'descriptionmyturn' => clienttranslate('Waiting for Trade Confirmation'),
        'type' => 'private',
        // 'action' => 'stTradePhaseWait',
        'args' => 'argWaitTradePhase',
        'possibleactions' => [],
        'transitions' => ['tradePhaseActions' => $tradePhaseActions],
    ],
    $changeZombiePlayer => [
        'name' => 'changeZombiePlayer',
        'descriptionmyturn' => clienttranslate('Waiting for other players'),
        'type' => 'game',
        'transitions' => [],
    ],
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    $gameEnd => [
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'descriptionmyturn' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd',
    ],
];

foreach ($machinestates as $key => $state) {
    $machinestates[$changeZombiePlayer]['transitions'][$state['name']] = $key;
}

$interruptableScreens = [$dayEvent, $resolveEncounter, $postEncounter, $nightPhase, $nightDrawCard, $morningPhase, $drawCard, $playerTurn];
$interruptableScreenNames = [];
foreach ($interruptableScreens as $stateId) {
    $interruptableScreenNames[$stateId] = $machinestates[$stateId]['name'];
    $machinestates[$stateId]['transitions'] = [...$machinestates[$stateId]['transitions'], ...$interruptScreens];
}

foreach ($interruptableScreenNames as $stateId => $stateName) {
    foreach ($interruptScreens as $interruptStateId) {
        $machinestates[$interruptStateId]['transitions'][$stateName] = $stateId;
    }
}
