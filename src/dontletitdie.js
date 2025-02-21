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
      this.selector = null;
      this.tooltip = null;
      this.decks = {};
      this.deckSelectionScreen = new DeckSelectionScreen(this);
      this.tradeScreen = new TradeScreen(this);
      this.craftScreen = new CraftScreen(this);
      this.eatScreen = new EatScreen(this);
      this.tooManyItemsScreen = new TooManyItemsScreen(this);
      this.resourcesForDisplay = [
        'wood',
        'rock',
        'fiber',
        'bone',
        'meat',
        'meat-cooked',
        'fish',
        'fish-cooked',
        'berry',
        'berry-cooked',
        'hide',
        'fkp',
      ];
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
      if (gameData.gamestate.name === 'characterSelect')
        document.querySelectorAll('.character-side-container').forEach((el) => el.remove());
      else {
        if (gameData.characters && !gameData.characters.some((d) => d.name === 'Sig'))
          this.resourcesForDisplay = this.resourcesForDisplay.filter((d) => !d.includes('fish'));
      }
      const scale = 3;
      Object.values(gameData?.characters ?? this.selectedCharacters).forEach((character, i) => {
        // Player side board
        const playerPanel = this.getPlayerPanelElement(character.playerId);
        const equipments = character.equipment.map((d) => this.data[d.id]);

        const characterSideId = `player-side-${character.playerId}-${character.name}`;
        const playerSideContainer = document.getElementById(characterSideId);
        if (!playerSideContainer) {
          playerPanel.insertAdjacentHTML(
            'beforeend',
            `<div id="${characterSideId}" class="character-side-container">
            <div class="character-name">${character.name}<span class="first-player-marker"></span></div>
            <div class="health line"><div class="fa fa-heart"></div><span class="label">Health: </span><span class="value">${
              character.health
            }</span></div>
            <div class="stamina line"><div class="fa fa-bolt"></div><span class="label">Stamina: </span><span class="value">${
              character.stamina
            }</span></div>
            <div class="equipment line"><div class="fa fa-cog"></div><span class="label">Equipment: </span><span class="value">${
              equipments.map((d) => d.options.name).join(', ') || 'None'
            }</span></div>
          </div>`,
          );
          renderImage('skull', document.querySelector(`#${characterSideId} .first-player-marker`), {
            scale: 10,
            pos: 'replace',
            card: false,
            css: 'side-panel-skull',
          });
        } else {
          playerSideContainer.querySelector(`.health .value`).innerHTML = character.health;
          playerSideContainer.querySelector(`.stamina .value`).innerHTML = character.stamina;
          playerSideContainer.querySelector(`.equipment .value`).innerHTML = equipments.map((d) => d.options.name).join(', ') || 'None';
          playerSideContainer.style['background-color'] = character?.isActive ? '#fff' : '';
        }
        document.querySelector(`#${characterSideId} .first-player-marker`).style['display'] = character?.isFirst ? 'inline-block' : 'none';
        // Player main board
        if (gameData.gamestate.name !== 'characterSelect') {
          const container = document.getElementById(`player-container-${Math.floor(i / 2) + 1}`);
          if (container && !document.getElementById(`player-${character.name}`)) {
            container.insertAdjacentHTML(
              'beforeend',
              `<div id="player-${character.name}" class="player-card">
              <div class="card"></div>
              <div class="color-marker" style="background-color: #${character.playerColor}"></div>
              <div class="character"></div>
              <div class="max-health max-marker"></div>
              <div class="health marker fa fa-heart"></div>
              <div class="max-stamina max-marker"></div>
              <div class="stamina marker fa fa-bolt"></div>
              <div class="weapon" style="top: ${(60 * 4) / scale}px;left: ${(125 * 4) / scale}px"></div>
              <div class="tool" style="top: ${(60 * 4) / scale}px;left: ${(242.5 * 4) / scale}px"></div>
              <div class="slot3" style="top: ${(80 * 4) / scale}px;left: ${(183 * 4) / scale}px"></div>
              <div class="first-player-marker"></div>
              </div>`,
            );
            renderImage(`character-board`, document.querySelector(`#player-${character.name} > .card`), { scale });
            renderImage('skull', document.querySelector(`#player-${character.name} > .first-player-marker`), { scale: 4, pos: 'replace' });
          }
          document.querySelector(`#player-${character.name} .card`).style['outline'] = character?.isActive
            ? `5px solid #fff` //#${character.playerColor}
            : '';
          document.querySelector(`#player-${character.name} > .first-player-marker`).style['display'] = character?.isFirst
            ? 'block'
            : 'none';

          document.querySelector(`#player-${character.name} .max-health.max-marker`).style = `left: ${Math.round(
            ((character.maxHealth ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale,
          )}px;top: ${Math.round((10 * 4) / scale)}px`;
          document.querySelector(`#player-${character.name} .health.marker`).style = `background-color: #${character.playerColor};left: ${
            Math.round(((character.health ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale) + 2
          }px;top: ${Math.round((10 * 4) / scale) + 2 + (character.health == 0 ? (3 * 4) / scale : 0)}px`;
          document.querySelector(`#player-${character.name} .max-stamina.max-marker`).style = `left: ${Math.round(
            ((character.maxStamina ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale,
          )}px;top: ${Math.round((34.5 * 4) / scale)}px`;
          document.querySelector(`#player-${character.name} .stamina.marker`).style = `background-color: #${character.playerColor};left: ${
            Math.round(((character.stamina ?? 0) * 20.75 * 4) / scale + (126.5 * 4) / scale) + 2
          }px;top: ${Math.round((34.5 * 4) / scale) + 2 - (character.stamina == 0 ? (3 * 4) / scale : 0)}px`;

          renderImage(character.name, document.querySelector(`#player-${character.name} > .character`), { scale, pos: 'replace' });
          addClickListener(document.querySelector(`#player-${character.name} > .character`), character.name, () => {
            this.tooltip.show();
            renderImage(character.name, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
          });
          const renderedItems = [];
          const weapon = equipments.find((d) => d.options.itemType === 'weapon');
          if (weapon) {
            renderedItems.push(weapon);
            renderImage(weapon.id, document.querySelector(`#player-${character.name} > .${weapon.options.itemType}`), {
              scale: scale / 2,
              pos: 'replace',
            });
            addClickListener(document.querySelector(`#player-${character.name} > .${weapon.options.itemType}`), weapon.options.name, () => {
              this.tooltip.show();
              renderImage(weapon.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
            });
          }

          const tool = equipments.find((d) => d.options.itemType === 'tool');
          if (tool) {
            renderedItems.push(tool);
            renderImage(tool.id, document.querySelector(`#player-${character.name} > .${tool.options.itemType}`), {
              scale: scale / 2,
              pos: 'replace',
            });
            addClickListener(document.querySelector(`#player-${character.name} > .${tool.options.itemType}`), tool.options.name, () => {
              this.tooltip.show();
              renderImage(tool.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
            });
          }

          const item3 = equipments.find((d) => !renderedItems.includes(d));
          if (item3) {
            renderedItems.push(item3);
            renderImage(item3.id, document.querySelector(`#player-${character.name} > .${item3.options.itemType}`), {
              scale: scale / 2,
              pos: 'replace',
            });
            addClickListener(document.querySelector(`#player-${character.name} > .${item3.options.itemType}`), item3.options.name, () => {
              this.tooltip.show();
              renderImage(item3.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
            });
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
    updateResources: function (gameData) {
      const firewoodElem = document.querySelector(`#board-container .fire-wood`);
      firewoodElem.innerHTML = '';
      this.updateResource('wood', firewoodElem, gameData.game['resources']['fireWood'] ?? 0, {
        warn: (gameData.game['resources']['fireWood'] ?? 0) < (gameData['fireWoodCost'] ?? 0),
      });
      // Shared Resource Pool
      let sharedElem = document.querySelector(`#shared-resource-container .tokens`);
      if (!sharedElem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="shared-resource-container" class="dlid__container"><h3>${_('Shared Resources')}</h3><div class="tokens"></div></div>`,
          );
        sharedElem = document.querySelector(`#shared-resource-container .tokens`);
      }
      sharedElem.innerHTML = '';
      this.resourcesForDisplay.forEach((name) => this.updateResource(name, sharedElem, gameData.game['resources'][name] ?? 0));

      // Available Resource Pool
      let availableElem = document.querySelector(`#discoverable-container .tokens`);
      if (!availableElem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="discoverable-container" class="dlid__container"><h3>${_(
              'Discoverable Resources',
            )}</h3><div class="tokens"></div></div>`,
          );
        availableElem = document.querySelector(`#discoverable-container .tokens`);
      }
      availableElem.innerHTML = '';
      this.resourcesForDisplay
        .filter((elem) => !elem.includes('-cooked'))
        .forEach((name) => this.updateResource(name, availableElem, gameData.resourcesAvailable?.[name] ?? 0));
      // this.resourcesForDisplay.forEach((name) => {
      //   this.tweening.addTween(sharedElem.querySelector(`.token.${name}`), availableElem.querySelector(`.token.${name}`), name);
      // });
      const prevResources = gameData.game['prevResources'];
      if (prevResources['fireWood'] != null && prevResources['fireWood'] < gameData.game['resources']['fireWood']) {
        // Wood to Firewood
        this.tweening.addTween(sharedElem.querySelector(`.token.wood`), firewoodElem.querySelector(`.token.wood`), 'wood');
      } else if (prevResources['fireWood'] != null && prevResources['fireWood'] > gameData.game['resources']['fireWood']) {
        // Firewood to Wood
        this.tweening.addTween(firewoodElem.querySelector(`.token.wood`), availableElem.querySelector(`.token.wood`), 'wood');
      }
      this.resourcesForDisplay.forEach((name) => {
        if (prevResources[name] != null && prevResources[name] < gameData.game['resources'][name]) {
          // Discard to Shared Resources
          this.tweening.addTween(
            availableElem.querySelector(`.token.${name.replace('-cooked', '')}`),
            sharedElem.querySelector(`.token.${name}`),
            name,
          );
        } else if (
          prevResources[name] != null &&
          prevResources[name] > gameData.game['resources'][name] &&
          prevResources[name + '-cooked'] > gameData.game['resources'][name]
        ) {
          // Shared Resources to Discard
          this.tweening.addTween(
            sharedElem.querySelector(`.token.${name}`),
            availableElem.querySelector(`.token.${name.replace('-cooked', '')}`),
            name,
          );
        }
      });
    },
    updateResource: function (name, elem, count, { warn = false } = {}) {
      elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"><div class="counter dot">${count}</div></div>`);
      if (warn) elem.insertAdjacentHTML('beforeend', `<div class="fa fa-exclamation-triangle warning dot"></div>`);
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
    },
    updateItems: function (gameData) {
      let campElem = document.querySelector(`#camp-items-container .items`);
      if (!campElem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="camp-items-container" class="dlid__container"><h3>${_('Camp Items')}</h3><div class="items"></div></div>`,
          );
        campElem = document.querySelector(`#camp-items-container .items`);
      }
      document.getElementById('camp-items-container').style.display = Object.keys(gameData.campEquipment).length > 0 ? '' : 'none';
      campElem.innerHTML = '';
      Object.keys(gameData.campEquipment).forEach((name) => {
        this.updateItem(name, campElem, gameData.campEquipment?.[name] ?? 0);
      });
      // Shared Resource Pool
      // Available Resource Pool
      let availableElem = document.querySelector(`#items-container .items`);
      if (!availableElem) {
        document
          .getElementById('game_play_area')
          .insertAdjacentHTML(
            'beforeend',
            `<div id="items-container" class="dlid__container"><h3>${_('Craftable Items')}</h3><div class="items"></div></div>`,
          );
        availableElem = document.querySelector(`#items-container .items`);
      }
      availableElem.innerHTML = '';
      const keys = Object.keys(gameData.availableEquipment);
      keys.forEach((name) => this.updateItem(name, availableElem, gameData.availableEquipment?.[name] ?? 0));
      if (keys.length === 0) {
        availableElem.innerHTML = `<b>${_('None Available')}</b>`;
      }
    },
    updateItem: function (name, elem, count) {
      elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"><div class="counter dot">${count}</div></div>`);
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 1, pos: 'insert' });
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
          `<div id="board-container" class="dlid__container"><div class="board"><div class="fire-wood"></div>${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );

      renderImage(`board`, document.querySelector(`#board-container > .board`), { scale: 2, pos: 'insert' });
      decks.forEach(({ name: deck }) => {
        if (!this.decks[deck]) {
          const uppercaseDeck = deck[0].toUpperCase() + deck.slice(1);
          this.decks[deck] = new Deck(this, deck, document.querySelector(`.board > .${deck}`), 2);
          this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name);

          addClickListener(document.querySelector(`.board .${deck}-back`), `${uppercaseDeck} Deck`, () => {
            this.bgaPerformAction(`actDraw${uppercaseDeck}`);
          });
        }
      });

      this.updateResources(gameData);
    },
    setupCharacterSelections: function (gameData) {
      const playArea = document.getElementById('game_play_area');
      playArea.parentElement.insertAdjacentHTML('beforeend', `<div id="character-selector" class="dlid__container"></div>`);
      const elem = document.getElementById('character-selector');
      if (gameData.gamestate.name === 'characterSelect') playArea.style.display = 'none';
      else elem.style.display = 'none';
      Object.keys(this.data)
        .filter((d) => this.data[d].options.type === 'character')
        .sort()
        .forEach((characterName) => {
          renderImage(characterName, elem, { scale: 2, pos: 'append' });
          addClickListener(elem.querySelector(`.${characterName}`), characterName, () => {
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
        { name: 'night-event', expansion: 'base', scale: 1.5 },
        { name: 'day-event', expansion: 'base', scale: 3 },
        { name: 'mental-hindrance', expansion: 'hindrance', scale: 3 },
        { name: 'physical-hindrance', expansion: 'hindrance', scale: 3 },
      ].filter((d) => this.expansions.includes(d.expansion));
      if (!trackContainer) {
        const playArea = document.getElementById('game_play_area');
        playArea.insertAdjacentHTML(
          'beforeend',
          `<div id="track-container" class="dlid__container"><div id="event-deck-container">${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );
        trackContainer = document.getElementById('track-container');
        renderImage(`track-${gameData.trackDifficulty}`, trackContainer, { scale: 2, pos: 'insert' });

        trackContainer
          .querySelector(`.track-${gameData.trackDifficulty}`)
          .insertAdjacentHTML('beforeend', `<div id="track-marker" class="marker"></div>`);
      }
      const marker = document.getElementById('track-marker');
      marker.style.top = `${(gameData.game.day - 1) * 35 + 236}px`;

      const eventDeckContainer = document.getElementById('event-deck-container');
      decks.forEach(({ name: deck, scale }) => {
        if (!this.decks[deck]) {
          this.decks[deck] = new Deck(this, deck, eventDeckContainer.querySelector(`.${deck}`), scale, 'horizontal');
          if (gameData.decksDiscards[deck]?.name) this.decks[deck].setDiscard(gameData.decksDiscards[deck].name);
        }
      });
    },
    setup: function (gameData) {
      document.getElementById('game_play_area_wrap').classList.add('dlid');
      document.getElementById('right-side').classList.add('dlid');

      expansionI = gameData.expansionList.indexOf(gameData.expansion);
      this.expansions = gameData.expansionList.slice(0, expansionI + 1);
      this.data = Object.keys(allSprites).reduce((acc, k) => {
        const d = allSprites[k];
        d.options = d.options ?? {};
        if (d.options.expansion && gameData.expansionList.indexOf(d.options.expansion) > expansionI) return acc;
        return { ...acc, [k]: d };
      }, {});

      const playArea = document.getElementById('game_play_area');
      this.tweening = new Tweening(playArea);
      this.selector = new Selector(playArea);
      this.tooltip = new Tooltip(playArea);
      this.setupCharacterSelections(gameData);
      playArea.insertAdjacentHTML(
        'beforeend',
        `<div id="players-container" class="dlid__container"><div id="player-container-1" class="inner-container"></div><div id="player-container-2" class="inner-container"></div></div>`,
      );
      this.updatePlayers(gameData);
      this.setupBoard(gameData);
      // renderImage(`board`, playArea);
      this.updateTrack(gameData);
      // Setting up player boards
      this.updateKnowledgeTree(gameData);
      this.updateItems(gameData);
      playArea.insertAdjacentHTML('beforeend', `<div id="instructions-container" class="dlid__container"></div>`);
      renderImage(`instructions`, document.getElementById('instructions-container'));
      // this.deckSelectionScreen.show(gameData);
      // TODO: Set up your game interface here, according to "gameData"

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();
    },
    updateKnowledgeTree(gameData) {
      let knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
      if (!knowledgeContainer) {
        const playArea = document.getElementById('game_play_area');
        playArea.insertAdjacentHTML(
          'beforeend',
          `<div id="knowledge-container" class="dlid__container"><div class="board"><div class="unlocked-tokens"></div></div></div>`,
        );
        renderImage(`knowledge-tree-${gameData.difficulty}`, document.querySelector('#knowledge-container .board'), { pos: 'insert' });
        knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
      }
      knowledgeContainer.innerHTML = '';

      gameData.game.unlocks.forEach((unlockName) => {
        const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[unlockName];
        knowledgeContainer.insertAdjacentHTML(
          'beforeend',
          `<div id="knowledge-${unlockName}" class="fkp" style="top: ${y}px; left: ${x}px;"></div>`,
        );
        renderImage(`fkp-unlocked`, document.getElementById(`knowledge-${unlockName}`), { scale: 2.5 });
      });
    },
    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args = {}) {
      args.args = args.args ?? {};
      args.args['gamestate'] = { name: stateName };
      const isActive = this.isCurrentPlayerActive();

      console.log('Entering state: ' + stateName, args);
      switch (stateName) {
        case 'tooManyItems':
          if (isActive) this.tooManyItemsScreen.show(args.args);
          break;
        case 'characterSelect':
          this.selectedCharacters = args.args.characters;
          this.updateCharacterSelections(args.args);
          break;
        case 'playerTurn':
          this.updatePlayers(args.args);
          this.updateResources(args.args);
          this.updateItems(args.args);
          this.updateKnowledgeTree(args.args);
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
        case 'tooManyItems':
          this.selector.hide();
          break;
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
    getActionCostHTML: function (action) {
      let cost = '';
      if (action['stamina'] != null) cost = ` <i class="fa fa-bolt dlid__stamina"></i> ${action['stamina']}`;
      else if (action['health'] != null) cost = ` <i class="fa fa-heart dlid__health"></i> ${action['health']}`;
      return cost;
    },
    onUpdateActionButtons: function (stateName, args) {
      const actions = args?.actions;
      // this.currentActions = actions;
      console.log('onUpdateActionButtons', args, actions, stateName);
      const isActive = this.isCurrentPlayerActive();
      if (isActive && stateName && actions != null) {
        this.removeActionButtons();

        // Add test action buttons in the action status bar, simulating a card click:
        if (actions)
          Object.keys(actions).forEach((action) => {
            if (action === 'actUseSkill' && stateName === 'postEncounter') {
              return Object.values(args.availableSkills).forEach((skill) => {
                const cost = this.getActionCostHTML(skill);
                this.statusBar.addActionButton(`${_(skill.name)}${cost}`, () => {
                  return this.bgaPerformAction(action, { skillId: skill.id });
                });
              });
            }
            if (action === 'actUseItem' && stateName === 'postEncounter') {
              return Object.values(args.availableItemSkills).forEach((skill) => {
                const cost = this.getActionCostHTML(skill);
                this.statusBar.addActionButton(`${_(skill.name)}${cost}`, () => {
                  return this.bgaPerformAction(action, { skillId: skill.id });
                });
              });
            }
            const cost = this.getActionCostHTML(actions[action]);
            return this.statusBar.addActionButton(`${_(actionMappings[action])}${cost}`, () => {
              if (action === 'actUseSkill') {
                this.removeActionButtons();
                Object.values(args.availableSkills).forEach((skill) => {
                  const cost = this.getActionCostHTML(skill);
                  this.statusBar.addActionButton(`${_(skill.name)}${cost}`, () => {
                    return this.bgaPerformAction(action, { skillId: skill.id });
                  });
                });
                this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
              } else if (action === 'actTrade') {
                this.removeActionButtons();
                this.tradeScreen.show(args);
                this.statusBar.addActionButton(_('Trade') + `${cost}`, () => {
                  if (!this.tradeScreen.hasError()) {
                    this.bgaPerformAction('actTrade', {
                      data: JSON.stringify({
                        offered: this.tradeScreen.getOffered(),
                        requested: this.tradeScreen.getRequested(),
                      }),
                    })
                      .then(() => this.selector.hide())
                      .catch(console.error);
                  }
                });
                this.statusBar.addActionButton(
                  _('Cancel'),
                  () => {
                    this.onUpdateActionButtons(stateName, args);
                    this.selector.hide();
                  },
                  { color: 'secondary' },
                );
              } else if (action === 'actCraft') {
                this.removeActionButtons();
                this.craftScreen.show(args);
                this.statusBar.addActionButton(_('Craft') + `${cost}`, () => {
                  if (!this.craftScreen.hasError()) {
                    this.bgaPerformAction('actCraft', {
                      item: this.craftScreen.getSelectedId(),
                    })
                      .then(() => this.selector.hide())
                      .catch(console.error);
                  }
                });
                this.statusBar.addActionButton(
                  _('Cancel'),
                  () => {
                    this.onUpdateActionButtons(stateName, args);
                    this.selector.hide();
                  },
                  { color: 'secondary' },
                );
              } else if (action === 'actEat') {
                this.removeActionButtons();
                this.eatScreen.show(args);
                this.statusBar.addActionButton(_('Eat') + `${cost}`, () => {
                  if (!this.eatScreen.hasError()) {
                    this.bgaPerformAction('actEat', {
                      resourceType: this.eatScreen.getSelectedId(),
                    })
                      .then(() => {
                        this.onUpdateActionButtons(stateName, args);
                        this.selector.hide();
                      })
                      .catch(console.error);
                  }
                });
                this.statusBar.addActionButton(
                  _('Cancel'),
                  () => {
                    this.onUpdateActionButtons(stateName, args);
                    this.selector.hide();
                  },
                  { color: 'secondary' },
                );
              } else {
                return this.bgaPerformAction(action);
              }
            });
          });
        switch (stateName) {
          case 'tooManyItems':
            this.statusBar.addActionButton(_('Send To Camp'), () => {
              this.bgaPerformAction('actSendToCamp', { sendToCampId: this.tooManyItemsScreen.getSelectedId() }).then(() =>
                this.selector.hide(),
              );
            });
            break;
          case 'tradePhase':
          case 'postEncounter':
            this.statusBar.addActionButton(_('Done'), () => this.bgaPerformAction('actDone'), { color: 'secondary' });
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
          default:
            if (isActive) this.statusBar.addActionButton(_('End Turn'), () => this.bgaPerformAction('actEndTurn'), { color: 'secondary' });
            break;
        }
      }
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
      console.log('notifications subscriptions setup');

      // TODO: here, associate your game notifications with local methods
      dojo.subscribe('characterClicked', this, 'notification_characterClicked');
      dojo.subscribe('updateGameData', this, 'notification_updateGameData');
      // Example 1: standard notification handling
      // dojo.subscribe( 'tokenUsed', this, "notification_tokenUsed" );

      // Example 2: standard notification handling + tell the user interface to wait
      //            during 3 seconds after calling the method in order to let the players
      //            see what is happening in the game.

      dojo.subscribe('activeCharacter', this, 'notification_tokenUsed');
      dojo.subscribe('tokenUsed', this, 'notification_tokenUsed');
      this.notifqueue.setSynchronous('tokenUsed', 500);
      //
    },
    notificationWrapper: function (notification) {
      notification.args = notification.args ?? {};
      if (notification.args.gameData) {
        notification.args.gameData.gamestate = notification.args.gamestate;
      }
    },
    notification_updateGameData: function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_updateGameData', notification);
      this.updatePlayers(notification.args.gameData);
      this.updateResources(notification.args.gameData);
      this.updateItems(notification.args.gameData);
      this.updateKnowledgeTree(notification.args.gameData);
      if (notification.args?.gamestate?.name) this.onUpdateActionButtons(notification.args.gamestate.name, notification.args.gameData);
    },

    notification_characterClicked: function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_characterClicked', notification);
      this.selectedCharacters = notification.args.gameData.characters;
      this.updateCharacterSelections(notification.args);
    },

    notification_tokenUsed: function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_tokenUsed', notification);
      this.updatePlayers(notification.args.gameData);
      this.updateResources(notification.args.gameData);
      this.updateItems(notification.args.gameData);
      this.updateKnowledgeTree(notification.args.gameData);
      if (notification.args?.gamestate?.name) this.onUpdateActionButtons(notification.args.gamestate.name, notification.args.gameData);
    },
  });
});
