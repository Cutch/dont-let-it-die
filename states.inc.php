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

$machinestates = [
    // The initial state. Please do not modify.

    1 => [
        'name' => 'gameSetup',
        'description' => '',
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => ['' => 2],
    ],
    2 => [
        'name' => 'characterSelect',
        'description' => clienttranslate('Waiting for other players'),
        'descriptionmyturn' => clienttranslate('Select a Character'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionCount',
        'possibleactions' => ['actChooseCharacters', 'actCharacterClicked'],
        'transitions' => ['playerTurn' => 10, 'startHindrance' => 3],
        'action' => 'stSelectCharacter',
    ],
    3 => [
        'name' => 'startHindrance',
        'description' => clienttranslate('Waiting for other players'),
        'descriptionmyturn' => clienttranslate('Place Discoveries'),
        'type' => 'multipleactiveplayer',
        'args' => 'argStartHindrance',
        'possibleactions' => ['actMoveDiscovery', 'actDone'],
        'transitions' => ['playerTurn' => 10],
        'action' => 'stStartHindrance',
    ],
    10 => [
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
        ],
        'transitions' => [
            'endGame' => 99,
            'drawCard' => 11,
            'tooManyItems' => 12,
            'deckSelection' => 13,
            'resourceSelection' => 14,
            'endTurn' => 15,
            'characterSelection' => 16,
            'hindranceSelection' => 18,
            'tradeSelection' => 19,
            'interrupt' => 22,
            'cardSelection' => 17,
        ],
    ],
    11 => [
        'name' => 'drawCard',
        'description' => clienttranslate('Drawing Card'),
        'descriptionmyturn' => clienttranslate('Drawing Card'),
        'type' => 'game',
        'args' => 'argDrawCard',
        'action' => 'stDrawCard',
        'transitions' => [
            'endGame' => 99,
            'resolveEncounter' => 20,
            'playerTurn' => 10,
            'drawCard' => 11,
            'interrupt' => 22,
            'dayEvent' => 24,
        ],
    ],
    12 => [
        'name' => 'tooManyItems',
        'description' => clienttranslate('${character_name} is selecting an item'),
        'descriptionmyturn' => clienttranslate('Selecting an item'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSendToCamp'],
        'transitions' => ['playerTurn' => 10],
    ],
    13 => [
        'name' => 'deckSelection',
        'description' => clienttranslate('${character_name} is selecting a deck'),
        'descriptionmyturn' => clienttranslate('Selecting a deck'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectDeck', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'deckSelection' => 13],
    ],
    14 => [
        'name' => 'resourceSelection',
        'description' => clienttranslate('${character_name} is selecting a resource'),
        'descriptionmyturn' => clienttranslate('Selecting a resource'),
        'type' => 'activeplayer',
        'args' => 'argResourceSelection',
        'possibleactions' => ['actSelectResource', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'interrupt' => 22],
    ],
    15 => [
        'name' => 'nextCharacter',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextCharacter',
        'updateGameProgression' => true,
        'transitions' => ['endGame' => 99, 'playerTurn' => 10, 'dinnerPhase' => 27],
    ],
    16 => [
        'name' => 'characterSelection',
        'description' => clienttranslate('${character_name} is selecting a character'),
        'descriptionmyturn' => clienttranslate('Selecting a character'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectCharacter', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'morningPhase' => 50, 'interrupt' => 22],
    ],
    17 => [
        'name' => 'cardSelection',
        'description' => clienttranslate('${character_name} is selecting a card'),
        'descriptionmyturn' => clienttranslate('Selecting a card'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectCard', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'morningPhase' => 50, 'interrupt' => 22],
    ],
    18 => [
        'name' => 'hindranceSelection',
        'description' => clienttranslate('${character_name} is selecting a hindrance'),
        'descriptionmyturn' => clienttranslate('Selecting a hindrance'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actSelectHindrance', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'characterSelection' => 16, 'morningPhase' => 50, 'interrupt' => 22],
    ],
    19 => [
        'name' => 'tradeSelection',
        'description' => clienttranslate('${character_name} is selecting an item'),
        'descriptionmyturn' => clienttranslate('Selecting an item'),
        'type' => 'activeplayer',
        'args' => 'argSelectionState',
        'possibleactions' => ['actTrade', 'actCancel'],
        'transitions' => ['playerTurn' => 10, 'interrupt' => 22],
    ],
    20 => [
        'name' => 'resolveEncounter',
        'description' => clienttranslate('Resolving Encounter'),
        'descriptionmyturn' => clienttranslate('Resolving Encounter'),
        'type' => 'multipleactiveplayer',
        'action' => 'stResolveEncounter',
        'args' => 'argResolveEncounter',
        'possibleactions' => ['actChooseResource', 'actUseItem'],
        'transitions' => ['endGame' => 99, 'postEncounter' => 21, 'interrupt' => 22, 'whichWeapon' => 23],
    ],
    21 => [
        'name' => 'postEncounter',
        'description' => clienttranslate('Resolving Encounter'),
        'descriptionmyturn' => clienttranslate('Resolving Encounter'),
        'type' => 'activeplayer',
        'action' => 'stPostEncounter',
        'args' => 'argPostEncounter',
        'possibleactions' => [],
        'transitions' => ['endGame' => 99, 'playerTurn' => 10, 'drawCard' => 11],
    ],
    22 => [
        'name' => 'interrupt',
        'description' => clienttranslate('Other players are looking at their skills'),
        'descriptionmyturn' => clienttranslate('Looking at skills'),
        'type' => 'multipleactiveplayer',
        'action' => 'stInterrupt',
        'args' => 'argInterrupt',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => 99,
            'playerTurn' => 10,
            'drawCard' => 11,
            'resourceSelection' => 14,
            'endTurn' => 15,
            'characterSelection' => 16,
            'cardSelection' => 17,
            'hindranceSelection' => 18,
            'resolveEncounter' => 20,
            'postEncounter' => 21,
            'nightDrawCard' => 31,
            'morningPhase' => 50,
            'tradePhase' => 60,
        ],
    ],
    23 => [
        'name' => 'whichWeapon',
        'description' => clienttranslate('${character_name} is selecting a weapon'),
        'descriptionmyturn' => clienttranslate('Choose your weapon'),
        'type' => 'activeplayer',
        'args' => 'argWhichWeapon',
        'possibleactions' => ['actChooseWeapon'],
        'transitions' => ['resolveEncounter' => 20],
    ],
    24 => [
        'name' => 'dayEvent',
        'description' => clienttranslate('${character_name} is resolving an event'),
        'descriptionmyturn' => clienttranslate('What do you do'),
        'type' => 'activeplayer',
        'action' => 'stDayEvent',
        'args' => 'argDayEvent',
        'possibleactions' => ['actUseSkill', 'actUseItem'],
        'transitions' => [
            'endGame' => 99,
            'playerTurn' => 10,
            'drawCard' => 11,
            'deckSelection' => 13,
            'resourceSelection' => 14,
            'interrupt' => 22,
            'characterSelection' => 16,
            'cardSelection' => 17,
            'hindranceSelection' => 18,
            'resolveEncounter' => 20,
        ],
    ],
    27 => [
        'name' => 'dinnerPhase',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'multipleactiveplayer',
        'action' => 'stDinnerPhase',
        // 'args' => 'argDinnerPhase',
        'possibleactions' => [],
        'transitions' => ['dinnerPhasePost' => 28],
        'initialprivate' => 29,
    ],
    28 => [
        'name' => 'dinnerPhasePost',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'activeplayer',
        'action' => 'stDinnerPhasePost',
        'possibleactions' => [],
        'transitions' => ['nightPhase' => 30],
        'initialprivate' => 29,
    ],
    29 => [
        'name' => 'dinnerPhasePrivate',
        'description' => clienttranslate('Waiting for everyone to eat'),
        'descriptionmyturn' => clienttranslate('It\'s dinner time'),
        'type' => 'private',
        'args' => 'argDinnerPhase',
        'possibleactions' => ['actEat', 'actDone'],
    ],
    30 => [
        'name' => 'nightPhase',
        'description' => clienttranslate('It\'s night time'),
        'descriptionmyturn' => clienttranslate('It\'s night time'),
        'type' => 'game',
        'action' => 'stNightPhase',
        // 'args' => 'argNightPhase',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'interrupt' => 22, 'nightDrawCard' => 31],
    ],
    31 => [
        'name' => 'nightDrawCard',
        'description' => clienttranslate('Drawing Night Card'),
        'descriptionmyturn' => clienttranslate('Drawing Night Card'),
        'type' => 'game',
        'args' => 'argNightDrawCard',
        'action' => 'stNightDrawCard',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'endGame' => 99,
            'morningPhase' => 50,
            'interrupt' => 22,
            'nightPhase' => 30,
            'nightDrawCard' => 31,
            'nightPhasePost' => 32,
        ],
    ],
    32 => [
        'name' => 'nightPhasePost',
        'description' => clienttranslate('It\'s night time'),
        'descriptionmyturn' => clienttranslate('It\'s night time'),
        'type' => 'activeplayer',
        'action' => 'stNightPhasePost',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'morningPhase' => 50, 'interrupt' => 22],
    ],
    50 => [
        'name' => 'morningPhase',
        'description' => clienttranslate('Morning has arrived'),
        'descriptionmyturn' => clienttranslate('Morning has arrived'),
        'type' => 'game',
        'action' => 'stMorningPhase',
        'updateGameProgression' => true,
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'tradePhase' => 60, 'morningPhasePost' => 51, 'interrupt' => 22],
    ],
    51 => [
        'name' => 'morningPhasePost',
        'description' => clienttranslate('Morning has arrived'),
        'descriptionmyturn' => clienttranslate('Morning has arrived'),
        'type' => 'activeplayer',
        'action' => 'stMorningPhasePost',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'tradePhase' => 60, 'interrupt' => 22],
    ],
    60 => [
        'name' => 'tradePhase',
        'description' => clienttranslate('Waiting for others to trade Items'),
        'descriptionmyturn' => clienttranslate('Trade Items'),
        'type' => 'multipleactiveplayer',
        'action' => 'stTradePhase',
        'args' => 'argTradePhase',
        'initialprivate' => 61,
        'possibleactions' => [],
        'transitions' => ['nextCharacter' => 15],
    ],
    61 => [
        'name' => 'tradePhaseActions',
        'descriptionmyturn' => clienttranslate('Trade Items'),
        'type' => 'private',
        'action' => 'stTradePhaseWait',
        'args' => 'argTradePhaseActions',
        'possibleactions' => ['actTradeItem', 'actTradeDone'],
        'transitions' => ['confirmTradePhase' => 62, 'waitTradePhase' => 63],
    ],
    62 => [
        'name' => 'confirmTradePhase',
        'descriptionmyturn' => clienttranslate('Confirm Trade'),
        'type' => 'private',
        // 'action' => 'stTradePhaseWait',
        'args' => 'argConfirmTradePhase',
        'possibleactions' => ['actConfirmTradeItem', 'actCancelTrade'],
        'transitions' => ['tradePhaseActions' => 61],
    ],
    63 => [
        'name' => 'waitTradePhase',
        'descriptionmyturn' => clienttranslate('Waiting for Trade Confirmation'),
        'type' => 'private',
        // 'action' => 'stTradePhaseWait',
        'args' => 'argWaitTradePhase',
        'possibleactions' => [],
        'transitions' => ['tradePhaseActions' => 61],
    ],
    97 => [
        'name' => 'postActionPhase',
        'descriptionmyturn' => clienttranslate('Resolving'),
        'type' => 'activePlayer',
        'action' => 'stPostActionPhase',
        'possibleactions' => ['actUseSkill', 'actUseItem'],
        'transitions' => [],
    ],
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    98 => [
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'descriptionmyturn' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd',
    ],
];
foreach ($machinestates as $key => $state) {
    $machinestates[97]['transitions'][$key] = $state['name'];
}
