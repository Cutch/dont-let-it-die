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
  actUseSkill: 'Use Skill',
};
define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui', 'ebg/counter'], function (dojo, declare) {
  return declare('bgagame.dontletitdie', ebg.core.gamegui, {
    constructor: function () {
      // Used For character selection
      this.selectedCharacters = [];
      this.mySelectedCharacters = [];
      this.data = [];
      this.decks = {};
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
    updatePlayers: function (gameData) {
      // If character selection, keep removing characters
      console.log(gameData);
      if (gameData.gamestate.name !== 'characterSelect')
        document.querySelectorAll('.character-side-container').forEach((el) => el.remove());
      const scale = 3;
      Object.values(gameData?.characters ?? this.selectedCharacters).forEach((character) => {
        // Player side board
        const playerPanel = this.getPlayerPanelElement(character.playerId);

        const characterSideId = `player-side-${character.playerId}-${character.name}`;
        const playerSideContainer = document.getElementById(characterSideId);
        if (!playerSideContainer) {
          playerPanel.insertAdjacentHTML(
            'beforeend',
            `<div id="${characterSideId}" class="character-side-container">
            <div class="character-name">${character.name}</div>
            <div class="health"><span class="label">Health: </span><span class="value">${character.health}</span></div>
            <div class="stamina"><span class="label">Stamina: </span><span class="value">${character.stamina}</span></div>
            <div class="equipment"><span class="label">Equipment: </span><span class="value">${
              character.equipment.map((d) => this.data[d].options.name).join(', ') || 'None'
            }</span></div>
          </div>`,
          );
        } else {
          playerSideContainer.querySelector(`.health .value`).innerHTML = character.health;
          playerSideContainer.querySelector(`.stamina .value`).innerHTML = character.stamina;
          playerSideContainer.querySelector(`.equipment .value`).innerHTML =
            character.equipment.map((d) => this.data[d].options.name).join(', ') || 'None';
        }
        // Player main board
        if (gameData.gamestate.name !== 'characterSelect') {
          if (!document.getElementById(`player-${character.name}`)) {
            document.getElementById('players-container').insertAdjacentHTML(
              'beforeend',
              `<div id="player-${character.name}" class="player-card">
              <div class="card"></div>
              <div class="color-marker" style="background-color: #${character.playerColor}"></div>
              <div class="character"></div>
              <div class="max-health max-marker"></div>
              <div class="health marker fa fa-heart"></div>
              <div class="max-stamina max-marker"></div>
              <div class="stamina marker fa fa-bolt"></div>
              <div class="weapon" style="top: ${(60 * 4) / scale}px;left: ${(122 * 4) / scale}px"></div>
              <div class="tool" style="top: ${(60 * 4) / scale}px;left: ${(241 * 4) / scale}px"></div>
              <div class="first-player-marker"></div>
              </div>`,
            );
            renderImage(`character-board`, document.querySelector(`#player-${character.name} > .card`), scale);
            renderImage('skull', document.querySelector(`#player-${character.name} > .first-player-marker`), 8, 'replace');
          }
          document.querySelector(`#player-${character.name} > .first-player-marker`).style['display'] = character?.isFirst
            ? 'block'
            : 'none';

          document.querySelector(`#player-${character.name} .max-health.max-marker`).style = `left: ${Math.round(
            ((character.maxHealth ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale,
          )}px;top: ${Math.round((10 * 4) / scale)}px`;
          document.querySelector(`#player-${character.name} .health.marker`).style = `background-color: #${character.playerColor};left: ${
            Math.round(((character.health ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale) + 2
          }px;top: ${Math.round((10 * 4) / scale) + 2}px`;
          document.querySelector(`#player-${character.name} .max-stamina.max-marker`).style = `left: ${Math.round(
            ((character.maxStamina ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale,
          )}px;top: ${Math.round((34.5 * 4) / scale)}px`;
          document.querySelector(`#player-${character.name} .stamina.marker`).style = `background-color: #${character.playerColor};left: ${
            Math.round(((character.stamina ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale) + 2
          }px;top: ${Math.round((34.5 * 4) / scale) + 2}px`;

          renderImage(character.name, document.querySelector(`#player-${character.name} > .character`), scale, 'replace');
          let usedSlot;
          const item1 = this.data[character.equipment[0]];
          const item2 = this.data[character.equipment[1]];
          if (item1) {
            usedSlot = item1.options.itemType;
            renderImage(
              character.equipment[0],
              document.querySelector(`#player-${character.name} > .${item1.options.itemType}`),
              scale,
              'replace',
            );
          }
          if (item2) {
            const otherSlot = usedSlot === 'tool' ? 'weapon' : 'tool';
            renderImage(
              character.equipment[1],
              document.querySelector(
                `#player-${character.name} > .${usedSlot === item1.options.itemType ? otherSlot : item1.options.itemType}`,
              ),
              scale,
              'replace',
            );
          }
        }
      });
    },
    enableClick: function (elem) {
      if (elem.classList.contains('disabled')) {
        elem.classList.remove('disabled');
      }
    },
    disableClick: function (elem) {
      if (!elem.classList.contains('disabled')) elem.classList.add('disabled');
    },
    addClickListener: function (elem, name, callback) {
      elem.tabIndex = '0';
      elem.addEventListener('click', () => {
        if (!elem.classList.contains('disabled')) callback();
      });
      elem.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !elem.classList.contains('disabled')) callback();
      });
      elem.classList.add('clickable');
      elem.role = 'button';
      elem['aria-label'] = name;
    },
    updateResources: function (gameData) {
      let elem = document.querySelector(`#board-container .fire-wood`);
      elem.innerHTML = '';
      this.updateResource('wood', elem, gameData.game?.['fireWood'] ?? 0);

      // Shared Resource Pool
      const resources = ['wood', 'rock', 'fiber', 'bone', 'meat', 'berry', 'hide'];
      elem = document.querySelector(`#shared-resource-container .tokens`);
      if (!elem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="shared-resource-container" class="dlid-container"><h3>Shared Resources</h3><div class="tokens"></div></div>`,
          );
        elem = document.querySelector(`#shared-resource-container .tokens`);
      }
      elem.innerHTML = '';
      resources.forEach((name) => this.updateResource(name, elem, gameData.game?.[name] ?? 0));

      // Available Resource Pool
      elem = document.querySelector(`#discoverable-container .tokens`);
      if (!elem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="discoverable-container" class="dlid-container"><h3>Discoverable Resources</h3><div class="tokens"></div></div>`,
          );
        elem = document.querySelector(`#discoverable-container .tokens`);
      }
      elem.innerHTML = '';
      resources.forEach((name) => this.updateResource(name, elem, gameData.resourcesAvailable?.[name] ?? 0));
    },
    updateResource: function (name, elem, count) {
      elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"><div class="counter">${count}</div></div>`);
      renderImage(name, elem.querySelector(`.token.${name}`), 2, 'insert');
    },
    setupBoard: function (gameData) {
      this.firstPlayer = gameData.playerorder[0];
      const decks = [
        { name: 'gather', expansion: 'base' },
        { name: 'forage', expansion: 'base' },
        { name: 'harvest', expansion: 'base' },
        { name: 'hunt', expansion: 'base' },
      ].filter((d) => this.expansions.includes(d.expansion));
      // Main board
      document
        .getElementById('game_play_area')
        .insertAdjacentHTML(
          'beforeend',
          `<div id="board-container" class="dlid-container"><div class="board"><div class="fire-wood"></div>${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );

      renderImage(`board`, document.querySelector(`#board-container > .board`), 2, 'insert');
      decks.forEach(({ name: deck }) => {
        if (!this.decks[deck]) {
          const uppercaseDeck = deck[0].toUpperCase() + deck.slice(1);
          this.decks[deck] = new Deck(deck, document.querySelector(`.board > .${deck}`), 4);
          this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name);

          this.addClickListener(document.querySelector(`.board .${deck}-back`), `${uppercaseDeck} Deck`, () => {
            this.bgaPerformAction(`actDraw${uppercaseDeck}`);
          });
        }
      });

      this.updateResources(gameData);
    },
    setupCharacterSelections: function (gameData) {
      const playArea = document.getElementById('game_play_area');
      playArea.parentElement.insertAdjacentHTML('beforeend', `<div id="character-selector" class="dlid-container"></div>`);
      const elem = document.getElementById('character-selector');
      if (gameData.gamestate.name === 'characterSelect') playArea.style.display = 'none';
      else elem.style.display = 'none';
      Object.keys(this.data)
        .filter((d) => this.data[d].options.type === 'character')
        .sort()
        .forEach((characterName) => {
          renderImage(characterName, elem, 2, 'append');
          this.addClickListener(elem.querySelector(`.${characterName}`), characterName, () => {
            const i = this.mySelectedCharacters.indexOf(characterName);
            if (i >= 0) {
              // Remove selection
              this.mySelectedCharacters.splice(i, 1);
            } else {
              if (this.mySelectedCharacters.length >= this.selectCharacterCount) {
                this.mySelectedCharacters[this.mySelectedCharacters.length - 1] = characterName;
              } else {
                this.mySelectedCharacters.push(characterName);
              }
            }
            this.bgaPerformAction('actCharacterClicked', {
              character1: this.mySelectedCharacters?.[0],
              character2: this.mySelectedCharacters?.[1],
            });
          });
        });
    },
    updateCharacterSelections: function (gameData) {
      const elem = document.getElementById('character-selector');
      const myCharacters = this.selectedCharacters
        .filter((d) => d.playerId == gameui.player_id)
        .map((d) => d.name)
        .sort((a, b) => this.mySelectedCharacters.indexOf(a) - this.mySelectedCharacters.indexOf(b));
      this.mySelectedCharacters = myCharacters;
      const characterLookup = this.selectedCharacters.reduce((acc, d) => ({ ...acc, [d.name]: d }), {});
      elem.querySelectorAll('.characters-card').forEach((card) => {
        const character = characterLookup[card.getAttribute('name')];
        if (character) {
          card.style.setProperty('--player-color', '#' + character.playerColor);
          card.classList.add('selected');
          if (character.playerId != gameui.player_id) this.disableClick(card);
        } else {
          card.classList.remove('selected');
          this.enableClick(card);
        }
      });
      this.updatePlayers(gameData);
    },
    updateTrack: function (gameData) {
      let trackContainer = document.getElementById('track-container');
      const decks = [
        { name: 'night-event', expansion: 'base' },
        { name: 'day-event', expansion: 'base' },
        { name: 'mental-hindrance', expansion: 'hindrance' },
        { name: 'physical-hindrance', expansion: 'hindrance' },
      ].filter((d) => this.expansions.includes(d.expansion));
      if (!trackContainer) {
        const playArea = document.getElementById('game_play_area');
        playArea.insertAdjacentHTML(
          'beforeend',
          `<div id="track-container" class="dlid-container"><div id="event-deck-container">${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );
        trackContainer = document.getElementById('track-container');
        renderImage(`track-${gameData.trackDifficulty}`, trackContainer, 2, 'insert');

        trackContainer
          .querySelector(`.track-${gameData.trackDifficulty}`)
          .insertAdjacentHTML('beforeend', `<div id="track-marker" class="marker"></div>`);
      }
      const marker = document.getElementById('track-marker');
      marker.style.top = `${(gameData.game.day - 1) * 35 + 236}px`;

      const eventDeckContainer = document.getElementById('event-deck-container');
      decks.forEach(({ name: deck }) => {
        if (!this.decks[deck]) {
          this.decks[deck] = new Deck(deck, eventDeckContainer.querySelector(`.${deck}`), 3, 'horizontal');
          if (gameData.decksDiscards[deck]?.name) this.decks[deck].setDiscard(gameData.decksDiscards[deck].name);
        }
      });
    },
    setup: function (gameData) {
      console.log(gameData);
      expansionI = gameData.expansionList.indexOf(gameData.expansion);
      this.expansions = gameData.expansionList.slice(0, expansionI + 1);
      this.data = Object.keys(allSprites).reduce((acc, k) => {
        const d = allSprites[k];
        d.options = d.options ?? {};
        if (d.options.expansion && gameData.expansionList.indexOf(d.options.expansion) > expansionI) return acc;
        return { ...acc, [k]: d };
      }, {});
      console.log(this.data);
      this.dontPreloadImage('upgrades-spritesheet.png');

      this.setupCharacterSelections(gameData);
      const playArea = document.getElementById('game_play_area');

      playArea.insertAdjacentHTML('beforeend', `<div id="players-container" class="dlid-container"></div>`);
      this.updatePlayers(gameData);
      this.setupBoard(gameData);
      // renderImage(`board`, playArea);
      this.updateTrack(gameData);
      // Setting up player boards
      playArea.insertAdjacentHTML('beforeend', `<div id="knowledge-container" class="dlid-container"></div>`);
      renderImage(`knowledge-tree-${gameData.difficulty}`, document.getElementById('knowledge-container'));
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
    onEnteringState: function (stateName, args = {}) {
      args.args = args.args ?? {};
      args.args['gamestate'] = { name: stateName };

      console.log('Entering state: ' + stateName, args);
      switch (stateName) {
        case 'characterSelect':
          this.selectedCharacters = args.args.characters;
          this.updateCharacterSelections(args.args);
          break;
        case 'playerTurn':
          this.updatePlayers(args.args);
          this.updateTrack(args.args);
          break;
        case 'drawCard':
          this.decks[args.args.deck].drawCard(args.args.card.id);
          break;
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
          dojo.style('character-selector', 'display', 'none');
          dojo.style('game_play_area', 'display', '');

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
            const actions = args.actions; // returned by the argPlayerState

            // Add test action buttons in the action status bar, simulating a card click:
            if (actions)
              Object.keys(actions).forEach((action) =>
                this.statusBar.addActionButton(`${_(actionMappings[action])} <i class="fa fa-bolt stamina"></i> ${actions[action]}`, () =>
                  this.bgaPerformAction(action),
                ),
              );

            this.statusBar.addActionButton(_('End Turn'), () => this.bgaPerformAction('actEndTurn'), { color: 'secondary' });
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
    notificationWrapper: function (notif, state) {
      notif.args = notif.args ?? {};
      notif.args.gamestate = { name: state };
      if (notif.args.gameData) {
        notif.args.gameData.gamestate = { name: state };
      }
    },
    // TODO: from this point and below, you can write your game notifications handling methods
    notif_characterClicked: function (notif) {
      this.notificationWrapper(notif, 'characterSelect');
      console.log('notif_characterClicked', notif);
      this.selectedCharacters = notif.args.characters;
      this.updateCharacterSelections(notif.args);
    },

    // TODO: from this point and below, you can write your game notifications handling methods
    notif_tokenUsed: function (notif) {
      this.notificationWrapper(notif, 'playerTurn');
      console.log('notif_tokenUsed', notif);
      this.updatePlayers(notif.args.gameData);
      this.updateResources(notif.args.gameData);

      // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call

      // TODO: play the card in the user interface.
    },
  });
});
