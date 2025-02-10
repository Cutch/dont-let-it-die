/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DontLetItDie implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dontletitdie.js
 *
 * DontLetItDie user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
], function (dojo, declare) {
  return declare("bgagame.dontletitdie", ebg.core.gamegui, {
    constructor: function () {
      console.log("dontletitdie constructor");

      // Here, you can init the global variables of your user interface
      // Example:
      // this.myGlobalValue = 0;
    },

    /*
          setup:
          
          This method must set up the game user interface according to current game situation specified
          in parameters.
          
          The method is called each time the game interface is displayed to a player, ie:
          _ when the game starts
          _ when a player refreshes the game page (F5)
          
          "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
      */
    renderPlayer: function (player) {
      document.querySelector(
        `player-side-${player.id} > .health > .value`
      ).innerHTML = 0;
      document.querySelector(
        `player-side-${player.id} > .stamina > .value`
      ).innerHTML = 0;
    },
    setupPlayer: function (player) {
      // Player side board
      this.getPlayerPanelElement(player.id).insertAdjacentHTML(
        "beforeend",
        `<div id="player-side-${player.id}">
          <div class="health"><span class="label">Health: </span><span class="value"></span></div>
          <div class="stamina"><span class="label">Stamina: </span><span class="value"></span></div>
          <div class="stamina"><span class="label">Equipment: </span><span class="value">None</span></div>
        </div>`
      );
      // Player main board
      document
        .getElementById("players-container")
        .insertAdjacentHTML(
          "beforeend",
          `<div id="player-${player.id}" class="player-card"></div>`
        );
      renderImage(
        `character-board`,
        document.getElementById(`player-${player.id}`),
        4
      );
    },
    setup: function (gamedatas) {
      const knowledgeTree = "normal";
      const mode = "normal";
      this.dontPreloadImage("decks-spritesheet.png");
      this.dontPreloadImage("items-spritesheet.png");
      this.dontPreloadImage("upgrades-spritesheet.png");
      console.log(gamedatas);
      document
        .getElementById("game_play_area")
        .insertAdjacentHTML("beforeend", `<div id="players-container"></div>`);
      renderImage(`board`, document.getElementById("game_play_area"));
      renderImage(`track-${mode}`, document.getElementById("game_play_area"));
      renderImage(`dice`, document.getElementById("game_play_area"));
      renderImage("bow-and-arrow", document.getElementById("game_play_area"));
      // Setting up player boards
      Object.values(gamedatas.players).forEach((player) => {
        this.setupPlayer(player);
      });
      renderImage(
        `knowledge-tree-${knowledgeTree}`,
        document.getElementById("game_play_area")
      );
      renderImage(`instructions`, document.getElementById("game_play_area"));

      // TODO: Set up your game interface here, according to "gamedatas"

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName, args);

      switch (stateName) {
        /* Example:
          
          case 'myGameState':
          
              // Show some HTML block at this game state
              dojo.style( 'my_html_block_id', 'display', 'block' );
              
              break;
         */

        case "dummy":
          break;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      switch (stateName) {
        /* Example:
          
          case 'myGameState':
          
              // Hide the HTML block we are displaying only during this game state
              dojo.style( 'my_html_block_id', 'display', 'none' );
              
              break;
         */

        case "dummy":
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      console.log("onUpdateActionButtons: " + stateName, args);

      if (this.isCurrentPlayerActive()) {
        switch (stateName) {
          case "playerTurn":
            const playableCardsIds = args.playableCardsIds; // returned by the argPlayerTurn

            // Add test action buttons in the action status bar, simulating a card click:
            playableCardsIds.forEach((cardId) =>
              this.statusBar.addActionButton(
                _("Play card with id ${card_id}").replace("${card_id}", cardId),
                () => this.onCardClick(cardId)
              )
            );

            this.statusBar.addActionButton(
              _("Pass"),
              () => this.bgaPerformAction("actPass"),
              { color: "secondary" }
            );
            break;
        }
      }
    },

    ///////////////////////////////////////////////////
    //// Utility methods

    /*
      
          Here, you can defines some utility methods that you can use everywhere in your javascript
          script.
      
      */

    ///////////////////////////////////////////////////
    //// Player's action

    /*
      
          Here, you are defining methods to handle player's action (ex: results of mouse click on 
          game objects).
          
          Most of the time, these methods:
          _ check the action is possible at this game state.
          _ make a call to the game server
      
      */

    // Example:

    onCardClick: function (card_id) {
      console.log("onCardClick", card_id);

      this.bgaPerformAction("actPlayCard", {
        card_id,
      }).then(() => {
        // What to do after the server call if it succeeded
        // (most of the time, nothing, as the game will react to notifs / change of state instead)
      });
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    /*
          setupNotifications:
          
          In this method, you associate each of your game notifications with your local method to handle it.
          
          Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                your dontletitdie.game.php file.
      
      */
    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      // TODO: here, associate your game notifications with local methods

      // Example 1: standard notification handling
      // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

      // Example 2: standard notification handling + tell the user interface to wait
      //            during 3 seconds after calling the method in order to let the players
      //            see what is happening in the game.
      // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
      // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
      //
    },

    // TODO: from this point and below, you can write your game notifications handling methods

    /*
      Example:
      
      notif_cardPlayed: function( notif )
      {
          console.log( 'notif_cardPlayed' );
          console.log( notif );
          
          // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
          
          // TODO: play the card in the user interface.
      },    
      
      */
  });
});
