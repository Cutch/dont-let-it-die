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
        'descriptionmyturn' => clienttranslate('Select a character'),
        'type' => 'multipleactiveplayer',
        'args' => 'argSelectionCount',
        'possibleactions' => ['actChooseCharacters', 'actCharacterClicked'],
        'transitions' => ['start' => 10],
        'action' => 'stSelectCharacter',
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
            'actSpendFKP',
            'actInvestigateFire',
            'actAddWood',
            'actEat',
            'actCook',
            'actCraft',
            'actTrade',
            'actEndTurn',
            'actUseSkill',
            'actUseItem',
        ],
        'transitions' => [
            'drawCard' => 11,
            'tooManyItems' => 12,
            'deckSelection' => 13,
            'resourceSelection' => 14,
            'endTurn' => 15,
            'interrupt' => 22,
        ],
    ],
    11 => [
        'name' => 'drawCard',
        'description' => clienttranslate('Drawing Card'),
        'descriptionmyturn' => clienttranslate('Drawing Card'),
        'type' => 'game',
        'args' => 'argDrawCard',
        'action' => 'stDrawCard',
        'transitions' => ['resolveEncounter' => 20, 'playerTurn' => 10, 'interrupt' => 22],
    ],
    12 => [
        'name' => 'tooManyItems',
        'description' => clienttranslate('${character_name} is selecting an item'),
        'descriptionmyturn' => clienttranslate('Selecting an item'),
        'type' => 'activeplayer',
        'args' => 'argTooManyItems',
        'possibleactions' => ['actSendToCamp'],
        'transitions' => ['playerTurn' => 10],
    ],
    13 => [
        'name' => 'deckSelection',
        'description' => clienttranslate('${character_name} is selecting a deck'),
        'descriptionmyturn' => clienttranslate('Selecting a deck'),
        'type' => 'activeplayer',
        'args' => 'argDeckSelection',
        'possibleactions' => ['actSelectDeck', 'actSelectDeckCancel'],
        'transitions' => ['playerTurn' => 10],
    ],
    14 => [
        'name' => 'resourceSelection',
        'description' => clienttranslate('${character_name} is selecting a resource'),
        'descriptionmyturn' => clienttranslate('Selecting a resource'),
        'type' => 'activeplayer',
        'args' => 'argResourceSelection',
        'possibleactions' => ['actSelectResource', 'actSelectResourceCancel'],
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
        'transitions' => ['postEncounter' => 21, 'interrupt' => 22],
    ],
    21 => [
        'name' => 'postEncounter',
        'description' => clienttranslate('Resolving Encounter'),
        'descriptionmyturn' => clienttranslate('Resolving Encounter'),
        'type' => 'activeplayer',
        'action' => 'stPostEncounter',
        'args' => 'argPostEncounter',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['playerTurn' => 10],
    ],
    22 => [
        'name' => 'interrupt',
        'description' => clienttranslate('${playersString} looking at their skills'),
        'descriptionmyturn' => clienttranslate('Looking at skills'),
        'type' => 'multipleactiveplayer',
        'action' => 'stInterrupt',
        'args' => 'argInterrupt',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => [
            'playerTurn' => 10,
            'drawCard' => 11,
            'resourceSelection' => 14,
            'endTurn' => 15,
            'resolveEncounter' => 20,
            'postEncounter' => 21,
            'nightPhase' => 30,
            'nightDrawCard' => 31,
            'morningPhase' => 50,
            'tradePhase' => 60,
        ],
    ],
    15 => [
        'name' => 'nextCharacter',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextCharacter',
        'updateGameProgression' => true,
        'transitions' => ['endGame' => 99, 'nextCharacter' => 10, 'nightPhase' => 30],
    ],
    30 => [
        'name' => 'nightPhase',
        'description' => clienttranslate('It\'s night time'),
        'type' => 'game',
        'action' => 'stNightPhase',
        // 'args' => 'argNightPhase',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'morningPhase' => 50, 'interrupt' => 22, 'nightDrawCard' => 31],
    ],
    31 => [
        'name' => 'nightDrawCard',
        'description' => clienttranslate('Drawing Night Card'),
        'descriptionmyturn' => clienttranslate('Drawing Night Card'),
        'type' => 'game',
        'args' => 'argNightDrawCard',
        'action' => 'stNightDrawCard',
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'morningPhase' => 50, 'interrupt' => 22],
    ],
    50 => [
        'name' => 'morningPhase',
        'description' => clienttranslate('Morning has arrived'),
        'type' => 'game',
        'action' => 'stMorningPhase',
        'updateGameProgression' => true,
        'possibleactions' => ['actUseSkill', 'actUseItem', 'actDone'],
        'transitions' => ['endGame' => 99, 'tradePhase' => 60, 'interrupt' => 22],
    ],
    60 => [
        'name' => 'tradePhase',
        'description' => clienttranslate('Trade Items'),
        'type' => 'multipleactiveplayer',
        'action' => 'stTradePhase',
        'possibleactions' => ['actEquipItem', 'actUnEquipItem', 'actTradeItem', 'actConfirmTradeItem', 'actDone'],
        'transitions' => ['playerTurn' => 10],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    98 => [
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd',
    ],
];
