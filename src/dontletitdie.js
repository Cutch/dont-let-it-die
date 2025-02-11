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

const actionMappings = {
  actInvestigateFire: 'Investigate Fire',
  actCraft: 'Craft',
  actDrawGather: 'Gather',
  actDrawForage: 'Forage',
  actDrawHarvest: 'Harvest',
  actDrawHunt: 'Hunt',
  actSpendFKP: 'Spend FKP',
  actAddWood: 'Add Wood',
  actEat: 'Eat',
  actCook: 'Cook',
  actTrade: 'Trade',
};
define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui', 'ebg/counter'], function (dojo, declare) {
  return declare('bgagame.dontletitdie', ebg.core.gamegui, {
    constructor: function () {
      // Here, you can init the global variables of your user interface
      // Example:
      // this.myGlobalValue = 0;/
      this.selectedCharacters = [];
      this.mySelectedCharacters = [];
    },

    /*
          setup:
          
          This method must set up the game user interface according to current game situation specified
          in parameters.
          
          The method is called each time the game interface is displayed to a player, ie:
          _ when the game starts
          _ when a player refreshes the game page (F5)
          
          "gameData" argument contains all datas retrieved by your "getAllDatas" PHP method.
      */
    renderPlayer: function (player) {
      document.querySelector(`player-side-${player.id} > .health > .value`).innerHTML = 0;
      document.querySelector(`player-side-${player.id} > .stamina > .value`).innerHTML = 0;
    },
    updatePlayer: function (player, gameData) {
      // Player side board
      const playerSideContainer = document.getElementById(`player-side-${player.id}`);
      if (!playerSideContainer) {
        this.getPlayerPanelElement(player.id).insertAdjacentHTML(
          'beforeend',
          `<div id="player-side-${player.id}">
          <div class="health"><span class="label">Health: </span><span class="value"></span></div>
          <div class="stamina"><span class="label">Stamina: </span><span class="value"></span></div>
          <div class="equipment"><span class="label">Equipment: </span><span class="value">None</span></div>
        </div>`,
        );
      } else {
        // playerSideContainer.querySelector(`#player-${player.id} .health .value`).innerHTML = gameData.characters[0].health;
        // playerSideContainer.querySelector(`#player-${player.id} .stamina .value`).innerHTML = gameData.characters[0].stamina;
        // playerSideContainer.querySelector(`#player-${player.id} .equipment .value`).innerHTML =
        //   gameData.characters[0].equipment?.join(', ') ?? 'None';
      }
      // Player main board
      if (!document.getElementById(`player-${player.id}`)) {
        document.getElementById('players-container').insertAdjacentHTML(
          'beforeend',
          `<div id="player-${player.id}" class="player-card">
            <div class="card"></div>
            <div class="color-marker" style="background-color: #${player.color}"></div>
            <div class="character"></div>
            <div class="health" style="background-color: #${player.color};left: ${
            (gameData.characters?.[0]?.health ?? 0) * 21 + 127
          }px"></div>
            <div class="stamina" style="background-color: #${player.color};left: ${
            (gameData.characters?.[0]?.stamina ?? 0) * 21 + 127
          }px"></div>
            <div class="weapon"></div>
            <div class="tool"></div>
            </div>`,
        );
        renderImage(`character-board`, document.querySelector(`#player-${player.id} > .card`), 4);
      }
      renderImage(`Gronk`, document.querySelector(`#player-${player.id} > .character`), 4, 'replace');
      renderImage(`club`, document.querySelector(`#player-${player.id} > .weapon`), 4, 'replace');
      renderImage(`club`, document.querySelector(`#player-${player.id} > .tool`), 4, 'replace');
    },
    addClickListener: function (elem, name, callback) {
      elem.tabIndex = '0';
      elem.addEventListener('click', callback);
      elem.addEventListener('onKeyDown', (e) => {
        if (e.key === 'Enter') callback();
      });
      elem.style.cursor = 'pointer';
      elem.role = 'button';
      elem['aria-label'] = name;
    },
    updateResources: function (gameData) {
      let elem = document.querySelector(`#discoverable-container .tokens`);
      if (!elem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="discoverable-container" class="dlid-container"><h3>Discoverable Resources</h3><div class="tokens"></div></div>`,
          );
        elem = document.querySelector(`#discoverable-container .tokens`);
      }
      this.updateResource('wood', elem, gameData);
      this.updateResource('stone', elem, gameData);
      this.updateResource('fiber', elem, gameData);
      this.updateResource('bone', elem, gameData);
      this.updateResource('meat', elem, gameData);
      this.updateResource('berry', elem, gameData);
      this.updateResource('hide', elem, gameData);
    },
    updateResource: function (name, elem, gameData) {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token ${name}"><div class="counter">${gameData.resourcesAvailable?.[name] ?? 0}</div></div>`,
      );
      renderImage(name, elem.querySelector(`#discoverable-container .token.${name}`), 2, 'insert');
    },
    setupBoard: function (gameData) {
      this.firstPlayer = gameData.playerorder[0];
      // Main board
      document
        .getElementById('game_play_area')
        .insertAdjacentHTML(
          'beforeend',
          `<div id="board-container" class="dlid-container"><div class="board"><div class="tokens"></div><div class="gather"></div><div class="forage"></div><div class="harvest"></div><div class="hunt"></div></div></div>`,
        );

      renderImage(`board`, document.querySelector(`#board-container > .board`), 2, 'insert');
      renderImage(`gather-back`, document.querySelector(`.board > .gather`), 4, 'replace');
      renderImage(`forage-back`, document.querySelector(`.board > .forage`), 4, 'replace');
      renderImage(`harvest-back`, document.querySelector(`.board > .harvest`), 4, 'replace');
      renderImage(`hunt-back`, document.querySelector(`.board > .hunt`), 4, 'replace');
      this.addClickListener(document.querySelector(`.board > .gather`), 'Gather Deck', () => {
        this.bgaPerformAction('actDrawGather');
      });
      this.addClickListener(document.querySelector(`.board > .forage`), 'Forage Deck', () => {
        this.bgaPerformAction('actDrawForage');
      });
      this.addClickListener(document.querySelector(`.board > .harvest`), 'Harvest Deck', () => {
        this.bgaPerformAction('actDrawHarvest');
      });
      this.addClickListener(document.querySelector(`.board > .hunt`), 'Hunt Deck', () => {
        this.bgaPerformAction('actDrawHunt');
      });

      this.updateResources(gameData);
    },
    setupCharacterSelections: function (elem, gameData) {
      Object.keys(charactersSprites.sprites)
        .filter((d) => charactersSprites.sprites[d].options.type === 'character')
        .sort()
        .forEach((characterName) => {
          renderImage(characterName, elem, 2, 'append');
          this.addClickListener(elem.querySelector(`.${characterName}`), characterName, () => {
            const i = this.mySelectedCharacters.indexOf(characterName);
            if (i >= 0) {
              // Remove selection
              this.mySelectedCharacters.splice(i, 1);
            } else {
              this.mySelectedCharacters.push(characterName);
              this.bgaPerformAction('actCharacterClicked', {
                character1: this.mySelectedCharacters?.[0],
                character2: this.mySelectedCharacters?.[1],
              });
            }
          });
        });
    },
    setup: function (gameData) {
      const knowledgeTree = 'normal';
      const mode = 'normal';
      this.dontPreloadImage('upgrades-spritesheet.png');
      console.log(gameData);
      const playArea = document.getElementById('game_play_area');
      playArea.style.display = 'none';
      playArea.parentElement.insertAdjacentHTML('beforeend', `<div id="character-container" class="dlid-container"></div>`);

      this.setupCharacterSelections(document.getElementById('character-container'), gameData);

      playArea.insertAdjacentHTML('beforeend', `<div id="players-container" class="dlid-container"></div>`);
      Object.values(gameData.players).forEach((player) => {
        this.updatePlayer(player, gameData);
      });
      this.setupBoard(gameData);
      // renderImage(`board`, playArea);
      playArea.insertAdjacentHTML('beforeend', `<div id="track-container" class="dlid-container"></div>`);
      renderImage(`track-${mode}`, document.getElementById('track-container'));
      // renderImage(`dice`, document.getElementById('track-container'));
      // renderImage("bow-and-arrow", playArea);
      // Setting up player boards
      playArea.insertAdjacentHTML('beforeend', `<div id="knowledge-container" class="dlid-container"></div>`);
      renderImage(`knowledge-tree-${knowledgeTree}`, document.getElementById('knowledge-container'));
      playArea.insertAdjacentHTML('beforeend', `<div id="instructions-container" class="dlid-container"></div>`);
      renderImage(`instructions`, document.getElementById('instructions-container'));

      // TODO: Set up your game interface here, according to "gameData"

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
      console.log('Entering state: ' + stateName, args);

      switch (stateName) {
        case 'dummy':
          break;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log('Leaving state: ' + stateName);

      switch (stateName) {
        case 'characterSelect':
          dojo.style('character-select', 'display', 'none');

          break;

        // case 'dummy':
        //   break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      console.log('onUpdateActionButtons: ' + stateName, args);

      if (this.isCurrentPlayerActive()) {
        switch (stateName) {
          case 'playerTurn':
            const actions = args.actions; // returned by the argPlayableActions

            // Add test action buttons in the action status bar, simulating a card click:
            if (actions)
              Object.keys(actions).forEach((action) =>
                this.statusBar.addActionButton(`${_(actionMappings[action])} <i class="fa fa-bolt stamina"></i> ${actions[action]}`, () =>
                  this.bgaPerformAction(action),
                ),
              );

            this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction('actPass'), { color: 'secondary' });
            break;
          case 'characterSelect':
            const playerCount = Object.keys(args.players).length;
            if (playerCount === 3) {
              this.selectCharacterCount = gamegui.player_id == this.firstPlayer ? 2 : 1;
            } else if (playerCount === 1) {
              this.selectCharacterCount = 4;
            } else if (playerCount === 2) {
              this.selectCharacterCount = 2;
            } else if (playerCount === 4) {
              this.selectCharacterCount = 1;
            }
            if (this.selectCharacterCount == 1)
              this.statusBar.addActionButton(_('Confirm 1 character'), () => this.bgaPerformAction('actChooseCharacters'));
            else
              this.statusBar.addActionButton(_('Confirm ${x} characters').replace('${x}', this.selectCharacterCount), () =>
                this.bgaPerformAction('actChooseCharacters'),
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
    //// Reaction to cometD notifications

    /*
          setupNotifications:
          
          In this method, you associate each of your game notifications with your local method to handle it.
          
          Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                your dontletitdie.game.php file.
      
      */
    setupNotifications: function () {
      console.log('notifications subscriptions setup');

      // TODO: here, associate your game notifications with local methods
      dojo.subscribe('characterClicked', this, 'notif_characterClicked');
      // Example 1: standard notification handling
      // dojo.subscribe( 'tokenUsed', this, "notif_tokenUsed" );

      // Example 2: standard notification handling + tell the user interface to wait
      //            during 3 seconds after calling the method in order to let the players
      //            see what is happening in the game.
      dojo.subscribe('tokenUsed', this, 'notif_tokenUsed');
      this.notifqueue.setSynchronous('tokenUsed', 1000);
      //
    },

    // TODO: from this point and below, you can write your game notifications handling methods
    notif_characterClicked: function (notif) {
      console.log('notif_characterClicked');
      console.log(notif);
    },

    // TODO: from this point and below, you can write your game notifications handling methods
    notif_tokenUsed: function (notif) {
      console.log('notif_tokenUsed');
      console.log(notif);
      Object.values(notif.args.gameData.players).forEach((player) => {
        this.updatePlayer(player, notif.args.gameData);
      });
      this.updateResources(notif.args.gameData);

      // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

      // TODO: play the card in the user interface.
    },
  });
});
