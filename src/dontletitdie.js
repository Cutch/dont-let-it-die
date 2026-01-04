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
/// <amd-module name="bgagame/dontletitdie"/>
import dojo from 'dojo'; // Loads the dojo object using dojoConfig if needed
import declare from 'dojo/_base/declare'; // Add 'declare' to dojo if needed
import Gamegui from 'ebg/core/gamegui'; // Loads Gamegui class onto ebg.core.gamegui if needed
import 'ebg/counter'; // Loads Counter class onto ebg.counter if needed
import { getAllData } from './assets';
import { CardSelectionScreen } from './screens/card-selection-screen';
import { CharacterSelectionScreen } from './screens/character-selection-screen';
import { DeckSelectionScreen } from './screens/deck-selection-screen';
import { HindranceSelectionScreen } from './screens/hindrance-screen';
import { TradeScreen } from './screens/trade-screen';
import { ItemTradeScreen } from './screens/item-trade-screen';
import { CraftScreen } from './screens/craft-screen';
import { CookScreen } from './screens/cook-screen';
import { EatScreen } from './screens/eat-screen';
import { ItemsScreen } from './screens/items-screen';
import { ReviveScreen } from './screens/revive-screen';
import { TokenReduceScreen } from './screens/token-reduce-screen';
import { TokenScreen } from './screens/token-screen';
import { TooManyItemsScreen } from './screens/too-many-items-screen';
import { UpgradeSelectionScreen } from './screens/upgrade-selection-screen';
import { WeaponScreen } from './screens/weapon-screen';
import { addClickListener, Deck, Dice, InfoOverlay, isStudio, renderImage, renderText, Selector, Tooltip, Tweening } from './utils/index';

declare('bgagame.dontletitdie', Gamegui, {
  constructor: function () {
    // Used For character selection
    this.reloadShown = false;
    this.selectedCharacters = [];
    this.mySelectedCharacters = [];
    this.data = [];
    this.selector = null;
    this.tooltip = null;
    this.decks = {};
    this.clickListeners = [];
    this.cardSelectionScreen = new CardSelectionScreen(this);
    this.characterSelectionScreen = new CharacterSelectionScreen(this);
    this.deckSelectionScreen = new DeckSelectionScreen(this);
    this.hindranceSelectionScreen = new HindranceSelectionScreen(this);
    this.tradeScreen = new TradeScreen(this);
    this.itemTradeScreen = new ItemTradeScreen(this);
    this.craftScreen = new CraftScreen(this);
    this.cookScreen = new CookScreen(this);
    this.eatScreen = new EatScreen(this);
    this.itemsScreen = new ItemsScreen(this);
    this.reviveScreen = new ReviveScreen(this);
    this.tokenReduceScreen = new TokenReduceScreen(this);
    this.tokenScreen = new TokenScreen(this);
    this.tooManyItemsScreen = new TooManyItemsScreen(this);
    this.upgradeSelectionScreen = new UpgradeSelectionScreen(this);
    this.weaponScreen = new WeaponScreen(this);
    this.currentResources = { prevResources: {}, resources: {} };
    this.animations = [];
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
      'herb',
      'dino-egg',
      'dino-egg-cooked',
      'trap',
      'stew',
      'gem-y',
      'gem-b',
      'gem-p',
    ];
  },
  getActionMappings() {
    return {
      actInvestigateFire: _('Investigate Fire'),
      actCraft: _('Craft'),
      actDraw: _('Draw'),
      actDrawGather: _('Gather'),
      actDrawForage: _('Forage'),
      actDrawHarvest: _('Harvest'),
      actDrawHunt: _('Hunt'),
      actDrawExplore: _('Explore'),
      actSpendFKP: _('Spend FKP'),
      actAddWood: _('Add Wood'),
      actRevive: _('Revive'),
      actEat: _('Eat'),
      actCook: _('Cook'),
      actUseHerb: _('Use Herb'),
      actTrade: _('Trade Resources'),
      actUseSkill: _('Use Skill'),
      actUseItem: _('Use Item'),
      actTradeItem: _('Trade'),
      actConfirmTradeItem: _('Confirm Trade'),
      actSelectCharacter: _('Select Character'),
      actSelectCard: _('Select Card'),
      actUndo: _('Undo'),
      actEndTurn: _('End Turn'),
    };
  },
  getResourcesForDisplay: function (gameData) {
    return this.resourcesForDisplay.filter((d) => d in (gameData?.resources ?? this.gamedata.resources) && d !== 'fireWood');
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
    let characters = gameData?.characters
      ? Object.values(gameData?.characters)
      : Object.values(this.selectedCharacters).sort((a, b) => a.name.localeCompare(b.name));
    if (gameData.gamestate?.name === 'characterSelect' || this.refreshCharacters) {
      document.querySelectorAll('.character-side-container').forEach((el) => el.remove());
      document.querySelectorAll('.player-card').forEach((el) => el.remove());
      if (this.gamedatas.characters) characters = this.gamedatas.characters;
      this.refreshCharacters = false;
    }
    const scale = 3;
    characters.forEach((character, i) => {
      // Player side board
      const playerPanel = this.getPlayerPanelElement(character.playerId);
      const equipments = character.equipment;
      const hindrance = [...character.physicalHindrance, ...character.mentalHindrance];
      const characterSideId = `player-side-${character.playerId}-${character.name}`;
      const playerSideContainer = $(characterSideId);
      if (!playerSideContainer) {
        playerPanel.insertAdjacentHTML(
          'beforeend',
          `<div id="${characterSideId}" class="character-side-container">
            <div class="character-name">${character.name}<span class="first-player-marker"></span></div>
            <div class="status line"></div>
            <div class="health line"><div class="fa fa-heart"></div><span class="label">${_(
              'Health',
            )}: </span><span class="value"></span></div>
            <div class="stamina line"><div class="fa fa-bolt"></div><span class="label">${_(
              'Stamina',
            )}: </span><span class="value"></span></div>
            <div class="equipment line"><div class="fa fa-cog"></div><span class="label">${_(
              'Equipment',
            )}: </span><span class="value"></span></div>
            <div class="hindrance line" style="${
              this.expansions.includes('hindrance') ? '' : 'display:none'
            }"><div class="fa fa-ban"></div><span class="label">${_('Hindrance')}: </span><span class="value"></span></div>
            <div class="character-image"><div class="cover"></div></div>
          </div>`,
        );
        if (gameData.gamestate?.name !== 'characterSelect')
          renderImage('skull', document.querySelector(`#${characterSideId} .first-player-marker`), {
            scale: 20,
            pos: 'replace',
            card: false,
            css: 'side-panel-skull',
          });
        playerSideContainer = $(characterSideId);
        addClickListener(playerSideContainer.querySelector(`.character-name`), character.name, () => {
          this.tooltip.show();
          renderImage(character.name, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-character', pos: 'replace' });
        });
        renderImage(character.name, playerSideContainer.querySelector(`.character-image`), {
          scale: 3,
          overridePos: {
            x: 0.2,
            y: 0.16,
            w: 0.8,
            h: 0.45,
          },
        });
        addClickListener(playerSideContainer.querySelector(`.character-image`), character.name, () => {
          this.tooltip.show();
          renderImage(character.name, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-character', pos: 'replace' });
        });
      }
      playerSideContainer.querySelector(`.health .value`).innerHTML = `${character.health ?? 0}/${character.maxHealth ?? 0}`;
      playerSideContainer.querySelector(`.stamina .value`).innerHTML = `${character.stamina ?? 0}/${character.maxStamina ?? 0}`;
      const statusLine = playerSideContainer.querySelector(`.status`);
      if (character.incapacitated) {
        if (character.recovering) {
          statusLine.innerHTML = _('Recovering');
          if (!statusLine.classList.contains('healing')) statusLine.classList.add('healing');
        } else if (!statusLine.classList.contains('incapacitated')) {
          statusLine.innerHTML = _('Incapacitated');
          statusLine.classList.add('incapacitated');
        }
      } else {
        if (character.recovering) {
          if (statusLine.classList.contains('healing')) {
            statusLine.innerHTML = '';
            statusLine.classList.remove('healing');
          }
        } else if (statusLine.classList.contains('incapacitated')) {
          statusLine.classList.remove('incapacitated');
        } else if (statusLine.classList.contains('healing')) {
          statusLine.innerHTML = '';
          statusLine.classList.remove('healing');
        }
      }

      playerSideContainer.querySelector(`.equipment .value`).innerHTML =
        [
          ...(gameData.gamestate?.name === 'characterSelect' && character.startsWith ? [character.startsWith] : []),
          ...equipments,
          ...character.dayEvent,
          ...character.necklaces,
          ...(gameData.foreverUseItems?.['hide-token'] && character.name == 'Loka'
            ? [{ itemId: 'hide', name: `${_('Hide')} (${gameData.foreverUseItems['hide-token']})` }]
            : []),
        ]
          .map((d) => `<span class="equipment-item equipment-${d.itemId}">${_(d.name)}</span>`)
          .join(', ') || _('None');
      playerSideContainer.querySelector(`.hindrance .value`).innerHTML =
        [
          ...hindrance,
          ...(gameData.gamestate?.name === 'characterSelect' && character.startsWithHindrance ? [character.startsWithHindrance] : []),
        ]
          .map((d) => `<span class="hindrance-item hindrance-${d.id}">${_(d.name)}</span>`)
          .join(', ') || _('None');
      if (gameData.gamestate?.name !== 'characterSelect') playerSideContainer.style['background-color'] = character?.isActive ? '#fff' : '';
      [
        ...(gameData.gamestate?.name === 'characterSelect' && character.startsWith ? [character.startsWith] : []),
        ...equipments,
        ...character.dayEvent,
        ...character.necklaces,
      ].forEach((d) => {
        addClickListener(playerSideContainer.querySelector(`.equipment-${d.itemId}`), _(d.name), () => {
          this.tooltip.show();
          renderImage(d.id, this.tooltip.renderByElement(), {
            withText: true,
            type: 'tooltip-item',
            pos: 'replace',
            rotate: d.rotate,
            centered: true,
          });
        });
      });
      [
        ...hindrance,
        ...(gameData.gamestate?.name === 'characterSelect' && character.startsWithHindrance ? [character.startsWithHindrance] : []),
      ].forEach((d) => {
        addClickListener(playerSideContainer.querySelector(`.hindrance-${d.id}`), _(d.name), () => {
          this.tooltip.show();
          renderImage(d.id, this.tooltip.renderByElement(), {
            withText: true,
            type: 'tooltip-hindrance',
            pos: 'replace',
            rotate: d.rotate,
            centered: true,
          });
        });
      });

      document.querySelector(`#${characterSideId} .first-player-marker`).style['display'] = character?.isFirst ? 'inline-block' : 'none';
      // Player main board
      if (gameData.gamestate.name !== 'characterSelect') {
        const container = $(`player-container-${Math.floor(i / 2) + 1}`);
        if (container && !$(`player-${character.name}`)) {
          container.insertAdjacentHTML(
            'beforeend',
            `<div id="player-${character.name}" class="player-card">
                <div class="card-extra-container"></div>
                <div class="card"><div class="first-player-marker"></div><div class="extra-token"></div></div>
                <div class="color-marker" style="background-color: #${character.playerColor}"></div>
                <div class="character"><div class="cover"></div></div>
                <div class="max-health max-marker"></div>
                <div class="health marker fa fa-heart"></div>
                <div class="max-stamina max-marker"></div>
                <div class="stamina marker fa fa-bolt"></div>
                <div class="weapon" style="top: ${(60 * 4) / scale}px;left: ${(125 * 4) / scale}px"></div>
                <div class="tool" style="top: ${(60 * 4) / scale}px;left: ${(242.5 * 4) / scale}px"></div>
              </div>`,
          );
          // <div class="slot3" style="top: ${(80 * 4) / scale}px;left: ${(183 * 4) / scale}px"></div>
          renderImage(`character-board`, document.querySelector(`#player-${character.name} > .card`), { scale, pos: 'insert' });
          renderImage('skull', document.querySelector(`#player-${character.name} .first-player-marker`), { scale: 8, pos: 'replace' });
        }

        document.querySelector(`#player-${character.name} .card`).style['outline'] = character?.isActive
          ? `5px solid #fff` //#${character.playerColor}
          : '';
        document.querySelector(`#player-${character.name} .first-player-marker`).style['display'] = character?.isFirst ? 'block' : 'none';

        const extraTokenElem = document.querySelector(`#player-${character.name} .extra-token`);
        extraTokenElem.innerHTML = '';
        if (gameData.foreverUseItems?.['hide-token'] && character.name == 'Loka') {
          this.updateResource('hide', extraTokenElem, gameData.foreverUseItems['hide-token']);
        }

        document.querySelector(`#player-${character.name} .max-health.max-marker`).style = `left: ${Math.round(
          ((character.maxHealth ?? 0) * 20.85 * 4) / scale + (126.5 * 4) / scale,
        )}px;top: ${Math.round((10 * 4) / scale)}px`;
        document.querySelector(`#player-${character.name} .health.marker`).style = `background-color: #${character.playerColor};left: ${
          Math.round(((character.health ?? 0) * 20.85 * 4) / scale + (126.5 * 4) / scale) + 2
        }px;top: ${Math.round((10 * 4) / scale) + 2 + (character.health == 0 ? (3 * 4) / scale : 0)}px`;
        document.querySelector(`#player-${character.name} .max-stamina.max-marker`).style = `left: ${Math.round(
          ((character.maxStamina ?? 0) * 20.85 * 4) / scale + (126.5 * 4) / scale,
        )}px;top: ${Math.round((34.5 * 4) / scale)}px`;
        document.querySelector(`#player-${character.name} .stamina.marker`).style = `background-color: #${character.playerColor};left: ${
          Math.round(((character.stamina ?? 0) * 20.85 * 4) / scale + (126.5 * 4) / scale) + 2
        }px;top: ${Math.round((34.5 * 4) / scale) + 2 - (character.stamina == 0 ? (3 * 4) / scale : 0)}px`;
        const characterElem = document.querySelector(`#player-${character.name} > .character`);
        renderImage(character.name, characterElem, { scale, pos: 'replace' });
        addClickListener(characterElem, character.name, () => {
          this.tooltip.show();
          renderImage(character.name, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-character', pos: 'replace' });
        });
        const coverElem = document.createElement('div');
        characterElem.appendChild(coverElem);
        // const coverElem = characterElem.querySelector(`.cover`);
        coverElem.classList.add('cover');
        if (character.incapacitated) {
          if (character.recovering) {
            coverElem.innerHTML = _('Recovering');
            if (!coverElem.classList.contains('healing')) coverElem.classList.add('healing');
          } else if (!coverElem.classList.contains('incapacitated')) {
            coverElem.innerHTML = _('Incapacitated');
            coverElem.classList.add('incapacitated');
          }
        } else {
          if (character.recovering) {
            if (coverElem.classList.contains('healing')) coverElem.classList.remove('healing');
          } else if (coverElem.classList.contains('incapacitated')) {
            coverElem.classList.remove('incapacitated');
          }
        }

        const renderedItems = [];
        const weapon = equipments.find((d) => d.itemType === 'weapon');
        if (weapon) {
          renderedItems.push(weapon);
          renderImage(weapon.id, document.querySelector(`#player-${character.name} > .weapon`), {
            scale: scale,
            pos: 'replace',
          });
          addClickListener(document.querySelector(`#player-${character.name} > .weapon`), _(weapon.name), () => {
            this.tooltip.show();
            renderImage(weapon.id, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-item', pos: 'replace' });
          });
        } else {
          document.querySelector(`#player-${character.name} > .weapon`).innerHTML = '';
        }

        const tool = equipments.find((d) => d.itemType === 'tool');
        if (tool) {
          renderedItems.push(tool);
          renderImage(tool.id, document.querySelector(`#player-${character.name} > .tool`), {
            scale: scale,
            pos: 'replace',
          });
          addClickListener(document.querySelector(`#player-${character.name} > .tool`), _(tool.name), () => {
            this.tooltip.show();
            renderImage(tool.id, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-item', pos: 'replace' });
          });
        } else {
          document.querySelector(`#player-${character.name} > .tool`).innerHTML = '';
        }
        const item3 = equipments.find((d) => !renderedItems.includes(d));
        const extraContainerButtons = document.querySelector(`#player-${character.name} .card-extra-container`);
        extraContainerButtons.innerHTML = '';
        extraContainerButtons.insertAdjacentHTML(
          'beforeend',
          `<div class="card-extra-equipment">${_('Extra Equipment')} (<span>0</span>)</div>
              <div class="card-hindrance">${_('Hindrance')} (<span>0</span>)</div>`,
        );
        // if (item3) {
        //   renderedItems.push(item3);
        //   renderImage(item3.id, document.querySelector(`#player-${character.name} > .slot3`), {
        //     scale: scale / 2,
        //     pos: 'replace',
        //   });
        //   addClickListener(document.querySelector(`#player-${character.name} > .slot3`), item3.name, () => {
        //     this.tooltip.show();
        //     renderImage(item3.id, this.tooltip.renderByElement(), {withText: true,  type: 'tooltip-item', pos: 'replace' });
        //   });
        // }
        const extraEquipmentElem = extraContainerButtons.querySelector(`.card-extra-equipment`);
        extraEquipmentElem.style['display'] = !!item3 || character.dayEvent.length > 0 || character.necklaces.length > 0 ? `` : 'none';
        extraEquipmentElem.querySelector('span').innerHTML = (!!item3 ? 1 : 0) + character.dayEvent.length + character.necklaces.length;
        addClickListener(extraEquipmentElem, _('Extra Equipment'), () => {
          this.tooltip.show();
          if (item3)
            renderImage(item3.id, this.tooltip.renderByElement(), {
              withText: true,
              type: 'tooltip-item',
              pos: 'append',
              rotate: item3.rotate,
              centered: true,
            });
          [...character.dayEvent, ...character.necklaces].forEach((dayEvent) => {
            renderImage(dayEvent.id, this.tooltip.renderByElement(), {
              withText: true,
              type: 'tooltip-item',
              pos: 'append',
              rotate: dayEvent.rotate,
              centered: true,
            });
          });
        });

        const hindranceElem = extraContainerButtons.querySelector(`.card-hindrance`);
        hindranceElem.style['display'] = this.expansions.includes('hindrance') && hindrance.length > 0 ? `` : 'none';
        hindranceElem.querySelector('span').innerHTML = hindrance.length;
        addClickListener(hindranceElem, _('Hindrance'), () => {
          this.tooltip.show();
          character.physicalHindrance.forEach((hindrance) => {
            renderImage(hindrance.id, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-hindrance', pos: 'append' });
          });
          character.mentalHindrance.forEach((hindrance) => {
            renderImage(hindrance.id, this.tooltip.renderByElement(), { withText: true, type: 'tooltip-hindrance', pos: 'append' });
          });
        });
        const displayContainer = !hindranceElem.style['display'] || !extraEquipmentElem.style['display'];
        extraContainerButtons.style['display'] = displayContainer ? `` : 'none';
        const cardElem = document.querySelector(`#player-${character.name}`);
        if (displayContainer) {
          if (!cardElem.classList.contains('has-card-extra-container')) cardElem.classList.add('has-card-extra-container');
        } else {
          if (cardElem.classList.contains('has-card-extra-container')) cardElem.classList.remove('has-card-extra-container');
        }
      }
    });

    const selections = $('player_boards');
    [...selections.children].forEach((elem) => {
      if (elem.id?.includes('overall_player_board_')) {
        elem.style.order = this.gamedatas.players[elem.id.replace('overall_player_board_', '')].player_no;
      } else if (elem.id == 'token-container') {
        elem.style.order = 5;
      }
    });

    this.infoOverlay.updateTurnOrder();
  },
  enableClick: function (elem) {
    if (elem.classList.contains('disabled')) {
      elem.classList.remove('disabled');
    }
  },
  disableClick: function (elem) {
    if (!elem.classList.contains('disabled')) elem.classList.add('disabled');
  },
  noForResourceChange: function (gameData, resourceName) {
    const prevResources = gameData.prevResources;
    return (
      this.currentResources['prevResources'][resourceName] == prevResources[resourceName] &&
      this.currentResources['resources'][resourceName] == gameData.resources[resourceName] &&
      this.currentResources['prevResources'][resourceName + '-cooked'] == prevResources[resourceName + '-cooked'] &&
      this.currentResources['resources'][resourceName + '-cooked'] == gameData.resources[resourceName + '-cooked']
    );
  },
  updateResources: async function (gameData) {
    if (!gameData || !gameData.resourcesAvailable) return;
    const promises = [];
    const resourcesForDisplay = this.getResourcesForDisplay(gameData);

    // let sideTokenContainer = document.querySelector(`#token-container .resources`);
    // if (!sideTokenContainer) {
    //   $('player_boards').insertAdjacentHTML(
    //     'beforeend',
    //     `<div id="token-container" class="player-board"><div class="resource-title">${_('Resources')}</div><div class="resources"></div></div>`,
    //   );
    //   sideTokenContainer = document.querySelector(`#token-container .resources`);
    // }
    // sideTokenContainer.innerHTML = '';
    // const sideResources = resourcesForDisplay.filter((elem) => !elem.includes('trap') && gameData.resources[elem] > 0);
    // sideResources.forEach((name) => this.updateResource(name, sideTokenContainer, gameData.resources[name] ?? 0, { scale: 4 }));
    // if (sideResources.length === 0) {
    //   sideTokenContainer.classList.add('no-resource');
    //   sideTokenContainer.innerHTML = _('None');
    // } else {
    //   sideTokenContainer.classList.remove('no-resource');
    // }

    // Shared Resource Pool
    let sharedElem = document.querySelector(`#shared-resource-container .tokens`);
    if (!sharedElem) {
      $('board-resource-wrapper').insertAdjacentHTML(
        'beforeend',
        `<div id="shared-resource-container" class="dlid__container"><h3>${_('Shared Resources')}</h3><div class="tokens"></div></div>`,
      );
      sharedElem = document.querySelector(`#shared-resource-container .tokens`);
    }
    sharedElem.innerHTML = '';
    resourcesForDisplay
      .filter((elem) => !elem.includes('trap'))
      .forEach((name) => this.updateResource(name, sharedElem, gameData.resources[name] ?? 0));

    const firewoodElem = document.querySelector(`#fire-pit .fire-wood`);
    firewoodElem.innerHTML = '<div class="action-cost" style="display:none"></div>';
    this.updateResource('wood', firewoodElem, this.gamedatas.resources['fireWood'] ?? 0, {
      warn: (this.gamedatas.resources['fireWood'] ?? 0) <= (this.gamedatas['fireWoodCost'] ?? 0),
    });
    if ((this.gamedatas.resources['fireWood'] ?? 0) <= (this.gamedatas['fireWoodCost'] ?? 0)) {
      this.addHelpTooltip({
        node: document.querySelector(`.wood-alert`),
        text: _(
          'Warning, the morning phase will cause ${count} fire wood to be removed. If there is not enough fire wood the game is lost.',
        ).replace('${count}', this.gamedatas['fireWoodCost'] ?? 0),
        iconCSS: 'fa fa-fire dld-warning',
      });
    } else {
      document.querySelector(`.wood-alert`).innerHTML = '';
    }

    // Available Resource Pool
    let availableElem = document.querySelector(`#discoverable-container .tokens`);
    if (!availableElem) {
      $('board-resource-wrapper').insertAdjacentHTML(
        'beforeend',
        `<div id="discoverable-container" class="dlid__container"><h3>${_('Discoverable Resources')}</h3><div class="tokens"></div></div>`,
      );
      availableElem = document.querySelector(`#discoverable-container .tokens`);
    }
    availableElem.innerHTML = '';
    resourcesForDisplay
      .filter((elem) => !elem.includes('-cooked'))
      .forEach((name) => this.updateResource(name, availableElem, gameData.resourcesAvailable?.[name] ?? 0));
    // this.resourcesForDisplay.forEach((name) => {
    //   this.tweening.addTween(sharedElem.querySelector(`.token.${name}`), availableElem.querySelector(`.token.${name}`), name);
    // });
    const prevResources = gameData.prevResources;
    let skipWood = false;

    if (!this.noForResourceChange(gameData, 'fireWood')) {
      if (prevResources['fireWood'] != null && prevResources['fireWood'] < gameData.resources['fireWood']) {
        // Wood to Firewood
        promises.push(
          this.tweening.addTween(
            sharedElem.querySelector(`.token.wood`),
            firewoodElem.querySelector(`.token.wood`),
            'wood',
            2,
            gameData.resources['fireWood'] - prevResources['fireWood'],
          ),
        );
        skipWood = true;
      } else if (prevResources['fireWood'] != null && prevResources['fireWood'] > gameData.resources['fireWood']) {
        // Firewood to Wood
        promises.push(
          this.tweening.addTween(
            firewoodElem.querySelector(`.token.wood`),
            sharedElem.querySelector(`.token.wood`),
            'wood',
            2,
            prevResources['fireWood'] - gameData.resources['fireWood'],
          ),
        );
        gameData.resources['wood']--;
      }
    }
    resourcesForDisplay.forEach((name) => {
      const rawName = name.replace('-cooked', '');
      if (this.noForResourceChange(gameData, rawName)) return;
      if (
        prevResources[rawName] - 1 === gameData.resources[rawName] &&
        prevResources[rawName + '-cooked'] + 1 === gameData.resources[rawName + '-cooked']
      ) {
        if (rawName === name) {
          // Move resource to cooked resource
          promises.push(
            this.tweening.addTween(
              sharedElem.querySelector(`.token.${name}`),
              sharedElem.querySelector(`.token.${name + '-cooked'}`),
              name + '-cooked',
              2,
              1,
            ),
          );
        }
      } else if (prevResources[name] != null && prevResources[name] < gameData.resources[name]) {
        // Discard to Shared Resources
        promises.push(
          this.tweening.addTween(
            availableElem.querySelector(`.token.${rawName}`),
            sharedElem.querySelector(`.token.${name}`),
            name,
            2,
            gameData.resources[name] - prevResources[name],
          ),
        );
      } else if (
        prevResources[name] != null &&
        prevResources[name] > gameData.resources[name] &&
        (name !== 'wood' || !skipWood)
        // prevResources[name + '-cooked'] > gameData.resources[name]
      ) {
        // Shared Resources to Discard
        promises.push(
          this.tweening.addTween(
            sharedElem.querySelector(`.token.${name}`),
            availableElem.querySelector(`.token.${rawName}`),
            name,
            2,
            prevResources[name] - gameData.resources[name],
          ),
        );
      }
    });
    this.currentResources['prevResources'] = prevResources;
    this.currentResources['resources'] = gameData.resources;
    // if (gameData.buildings.length > 0) {
    //   const div = document.querySelector(`#board-container .buildings`);
    //   if (div.childNodes.length == 0) {
    //     gameData.buildings.forEach((building) => {
    //       renderImage(building.name, div, { scale: 2, pos: 'append' });
    //       addClickListener(div, 'Buildings', () => {
    //         this.tooltip.show();
    //         renderImage(building.name, this.tooltip.renderByElement(), {withText: true,  scale: 0.5, pos: 'replace' });
    //       });
    //     });
    //   }
    // }
    await Promise.all(promises);
  },
  updateResource: function (name, elem, count, { warn = false, scale = 2 } = {}) {
    elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"><div class="counter dot dot--number">${count}</div></div>`);
    if (warn) {
      this.addHelpTooltip({
        node: elem.querySelector(`.token.${name}`),
        text: _(
          'Warning, the morning phase will cause ${count} fire wood to be removed. If there is not enough fire wood the game is lost.',
        ).replace('${count}', count),
        iconCSS: 'fa fa-fire dld-warning',
      });
    }
    renderImage(name, elem.querySelector(`.token.${name}`), { scale, pos: 'insert' });
  },
  updateItems: function (gameData) {
    let campElem = document.querySelector(`#camp-items-container .items`);
    if (!campElem) {
      $('game_play_area').insertAdjacentHTML(
        'beforeend',
        `<div id="camp-items-container" class="dlid__container"><h3>${_('Camp Items')}</h3><div class="items"></div></div>`,
      );
      campElem = document.querySelector(`#camp-items-container .items`);
    }
    $('camp-items-container').style.display = Object.keys(gameData.campEquipmentCounts).length > 0 ? '' : 'none';
    campElem.innerHTML = '';
    Object.keys(gameData.campEquipmentCounts).forEach((name) => {
      this.updateItem(name, campElem, gameData.campEquipmentCounts?.[name] ?? 0);
    });
    let buildingItems = document.querySelector(`#building-items-container .items`);
    if (!buildingItems) {
      $('game_play_area').insertAdjacentHTML(
        'beforeend',
        `<div id="building-items-container" class="dlid__container"><h3>${_('Buildings')}</h3><div class="items"></div></div>`,
      );
      buildingItems = document.querySelector(`#building-items-container .items`);
    }
    buildingItems.innerHTML = '';
    $('building-items-container').style.display = gameData.buildings.length > 0 ? '' : 'none';
    if (gameData.buildings.length > 0) {
      gameData.buildings.forEach((building) => {
        this.updateItem(building.name, buildingItems, null);
      });
    }
    // Shared Resource Pool
    // Available Resource Pool
    let availableElem = document.querySelector(`#items-container .items`);
    if (!availableElem) {
      $('game_play_area').insertAdjacentHTML(
        'beforeend',
        `<div id="items-container" class="dlid__container"><h3>${_('Craftable Items')}</h3><div class="items"></div><div id="items-see-all" class="see-all see-all-button">${_('See all Craftable Items')}</div></div>`,
      );
      availableElem = document.querySelector(`#items-container .items`);
      addClickListener($('items-see-all'), 'See All', () => {
        this.tooltip.show();
        const innerTooltip = new Tooltip(this.tooltip.renderByElement());
        const renderItem = (name, elem) => {
          elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"></div>`);
          renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'replace' });
          this.addHelpTooltip({
            node: elem.querySelector(`.token.${name}`),
            tooltipText: name,
            tooltipElem: innerTooltip,
          });
        };
        this.gamedatas.allItems.forEach((name) => {
          renderItem(name, this.tooltip.renderByElement());
        });
      });
    }
    availableElem.innerHTML = '';
    const keys = Object.keys(gameData.availableEquipmentCount);
    keys.forEach((name) => this.updateItem(name, availableElem, gameData.availableEquipmentCount?.[name] ?? 0));
    if (keys.length === 0) {
      availableElem.innerHTML = `<b>${_('None')}</b>`;
    }
  },
  updateItem: function (name, elem, count) {
    elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"></div>`);
    renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert', css: count === 0 ? 'cannot-build' : '' });
    if (count != null)
      elem.querySelector(`.token.${name} .image`).insertAdjacentHTML('beforeend', `<div class="counter dot dot--number">${count}</div>`);
    addClickListener(elem.querySelector(`.token.${name}`), name, () => {
      this.tooltip.show();
      renderImage(name, this.tooltip.renderByElement(), { withText: true, pos: 'insert', type: 'tooltip-item' });
      if (count != null)
        this.tooltip
          .renderByElement()
          .querySelector(`.image`)
          .insertAdjacentHTML('beforeend', `<div class="counter dot dot--number">${count}</div>`);
    });
  },
  setupBoard: function (gameData) {
    this.firstPlayer = Object.values(gameui.gamedatas.players).find((d) => d.player_no == 1).id;
    const decks = [
      { name: 'explore', expansion: 'hindrance' },
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
        `<div id="board-track-wrapper"><div id="board-resource-wrapper"><div id="board-container" class="dlid__container"><div class="board"><div class="wood-alert"></div><div class="buildings"></div>${decks
          .map((d) => `<div class="${d.name}"></div>`)
          .join('')}</div></div></div></div>`,
      );

    renderImage(`board`, document.querySelector(`#board-container > .board`), { scale: 2, pos: 'insert' });
    decks.forEach(({ name: deck }) => {
      if (!this.decks[deck] && gameData.decks[deck]) {
        this.decks[deck] = new Deck(this, deck, gameData.decks[deck], document.querySelector(`.board > .${deck}`), 4);
        if (!this.decks[deck].isAnimating() && gameData.decksDiscards)
          this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name ?? gameData.decksDiscards[deck]?.[0]);
        if (gameData.game.partials && gameData.game.partials[deck]) {
          this.decks[deck].drawCard(gameData.game.partials[deck].id, true);
        }
        this.decks[deck].updateMarker(gameData.decks[deck]);
      }
    });

    this.updateTrack(gameData);
    this.updateResources(gameData);
    document.getElementById('game_play_area').insertAdjacentHTML(
      'beforeend',
      `<div id="day-event-popup" class="day-event-popup">
      <div id="day-event-text" style="display: none">
        <div id="day-event-popup-close" class="day-event-popup-close">
          <i class="fa6 fa6-xmark" aria-hidden="true"></i>
        </div>
        <i class="fa fa-sun-o day-background" aria-hidden="true"></i>
        <i class="fa fa-cloud day-background-cloud" aria-hidden="true"></i>
        <div class="day-card"></div>
      </div></div>`,
    );
    document.getElementById('game_play_area').insertAdjacentHTML(
      'beforeend',
      `<div id="day-popup" class="day-popup"><div id="day-text" style="display: none">
        <i class="fa fa-sun-o day-background" aria-hidden="true"></i>
        <i class="fa fa-cloud day-background-cloud" aria-hidden="true"></i>
        <span></span>
        <div style="font-size: 36px;display:none;" class="last-day">${_('The Last Day')}</div>
        </div></div>`,
    );
    document.getElementById('game_play_area').insertAdjacentHTML(
      'beforeend',
      `<div id="night-popup" class="night-popup"><div id="night-text" style="display: none">
        <i class="fa6 fa6-solid fa6-moon night-background" aria-hidden="true"></i>
        <i class="fa fa-cloud night-background-cloud" aria-hidden="true"></i>
        <span>${_('Night Phase')}</span><div class="night-card"></div></div></div>`,
    );
  },

  showDayTracker: async function () {
    await Promise.all(this.animations);
    this.animations = [];
    const elem = $('day-text');
    elem.classList.add('day-' + this.gamedatas.game.day);
    elem.classList.remove('day-' + (this.gamedatas.game.day - 1));
    elem.querySelector('span').innerText = _('Day') + ' ' + this.gamedatas.game.day;
    const anim = dojo.fx.chain([
      dojo.fadeIn({ node: elem, duration: 500 }),
      dojo.animateProperty({
        node: elem,
        duration: 1000,
      }),
      dojo.fadeOut({ node: elem, duration: 500 }),
    ]);
    elem.style.display = '';
    const promise = this.bgaPlayDojoAnimation(anim);
    this.animations.push(promise);
    await promise;
    elem.style.display = 'none';
  },
  showDayEvent: async function (cardId = 'day-event_1_3') {
    await Promise.all(this.animations);
    this.animations = [];
    const elem = $('day-event-text');
    renderImage(cardId, elem.querySelector('.day-card'), { scale: 1, pos: 'replace' });
    this.addHelpTooltip({
      node: elem.querySelector('.day-card .card'),
      tooltipText: cardId,
    });
    addClickListener(
      elem.querySelector('.day-card .tooltip-image-and-text'),
      'Tooltip',
      () => {
        this.tooltip.show();
        this.tooltip
          .renderByElement()
          .insertAdjacentHTML(
            'beforeend',
            `<div class="tooltip-box"><i class="fa fa-question-circle-o fa-2x" aria-hidden="true"></i><span>${renderText({ name: cardId })}</span></div>`,
          );
      },
      true,
    );
    // elem.querySelector('span').innerText = _('Day') + ' ' + this.gamedatas.game.day;
    const promise = this.bgaPlayDojoAnimation(dojo.fadeIn({ node: elem, duration: 500 }));
    elem.style.display = '';
    this.animations.push(promise);
    this.closeDayPopup = async () => {
      this.animations = [];
      const promise = this.bgaPlayDojoAnimation(dojo.fadeOut({ node: elem, duration: 500 }));
      this.animations.push(promise);
      await promise;
      elem.style.display = 'none';
      this.cleanupDayPopup = null;
      this.closeDayPopup = null;
    };
    this.cleanupDayPopup = addClickListener($('day-event-popup-close'), 'Close', this.closeDayPopup);
  },
  showNightTracker: async function (cardId) {
    await Promise.all(this.animations);
    this.animations = [];
    const elem = $('night-text');
    renderImage(cardId, elem.querySelector('.night-card'), { scale: 1, pos: 'replace' });
    this.addHelpTooltip({
      node: elem.querySelector('.night-card .card'),
      tooltipText: cardId,
    });
    addClickListener(
      elem.querySelector('.night-card .tooltip-image-and-text'),
      'Tooltip',
      () => {
        this.tooltip.show();
        this.tooltip
          .renderByElement()
          .insertAdjacentHTML(
            'beforeend',
            `<div class="tooltip-box"><i class="fa fa-question-circle-o fa-2x" aria-hidden="true"></i><span>${renderText({ name: cardId })}</span></div>`,
          );
      },
      true,
    );
    elem.style.display = '';
    const anim = dojo.fx.chain([
      dojo.fadeIn({ node: elem, duration: 500 }),
      dojo.animateProperty({
        node: elem,
        duration: 4000,
      }),
      dojo.fadeOut({ node: elem, duration: 500 }),
    ]);
    const promise = this.bgaPlayDojoAnimation(anim);
    this.animations.push(promise);
    await promise;
    elem.style.display = 'none';
  },
  setupCharacterSelections: function (gameData) {
    const playArea = $('game_play_area');
    playArea.parentElement.insertAdjacentHTML(
      'beforeend',
      `<div id="character-selector" class="dlid__container"><div class="characters"></div></div>`,
    );
    const elem = $('character-selector');
    if (gameData.gamestate.name === 'characterSelect') playArea.style.display = 'none';
    else elem.style.display = 'none';
    Object.keys(this.data)
      .filter((d) => this.data[d].options.type === 'character')
      .sort()
      .forEach((characterName) => {
        renderImage(characterName, elem.querySelector('.characters'), { scale: 2, pos: 'append' });
        addClickListener(elem.querySelector(`.${characterName}`), characterName, () => {
          const saved = [...this.mySelectedCharacters];
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
            character3: this.mySelectedCharacters?.[2],
            character4: this.mySelectedCharacters?.[3],
          })?.catch(() => {
            this.mySelectedCharacters = saved;
          });
        });

        this.addHelpTooltip({
          node: elem.querySelector(`.${characterName}`),
          tooltipText: characterName,
          tooltipText2: this.data[characterName].options.startsWith ?? this.data[characterName].options.startsWithHindrance,
        });
      });
    if (gameData.showUpgrades && this.expansions.includes('hindrance')) {
      elem.insertAdjacentHTML('beforeend', `<h3>${_('Upgrades')}</h3><div class="character-tokens"></div>`);
      const container = elem.querySelector('.character-tokens');
      Object.keys(gameData.upgrades).forEach((unlockId) => {
        // Render the new discovery
        renderImage(unlockId, container, { scale: 1, pos: 'append' });
      });
    }
  },
  updateCharacterSelections: function (gameData) {
    const elem = $('character-selector');
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
    let trackContainer = $('track-container');
    const decks = [
      { name: 'night-event', expansion: 'base', scale: 3 },
      { name: 'day-event', expansion: 'mini-expansion', scale: 3 },
      { name: 'mental-hindrance', expansion: 'hindrance', scale: 3 },
      { name: 'physical-hindrance', expansion: 'hindrance', scale: 3 },
    ].filter((d) => this.expansions.includes(d.expansion));
    if (!trackContainer) {
      const playArea = $('board-track-wrapper');
      playArea.insertAdjacentHTML(
        'beforeend',
        `<div id="track-container" class="dlid__container"><div id="event-deck-container">${decks
          .map((d) => `<div class="${d.name}"></div>`)
          .join('')}</div></div>`,
      );
      trackContainer = $('track-container');
      renderImage(`track-${this.trackDifficulty}`, trackContainer, { scale: 2, pos: 'insert' });

      trackContainer
        .querySelector(`.track-${this.trackDifficulty}`)
        .insertAdjacentHTML('beforeend', `<div id="track-marker" class="marker"><i class="fa fa-sun-o dlid__sun"></i></div>`);
      trackContainer.querySelector(`.track-${this.trackDifficulty}`).insertAdjacentHTML('beforeend', `<div class="marker"></div>`);

      this.addHelpTooltip({
        node: trackContainer.querySelector(`.track-${this.trackDifficulty}`),
        tooltipText: `track-${this.trackDifficulty}`,
      });

      trackContainer
        .querySelector(`.track-${this.trackDifficulty}`)
        .insertAdjacentHTML(
          'beforeend',
          `<div id="fire-pit" class="fire-pit"><div class="fire-wood"><div class="action-cost" style="display:none"></div></div></div>`,
        );
      renderImage(`fire`, $('fire-pit'), { scale: 4, pos: 'insert' });
    }
    // const firewoodElem = document.querySelector(`#fire-pit .fire-wood`);
    // firewoodElem.innerHTML = '';
    // this.updateResource('wood', firewoodElem, gameData.resources['fireWood'] ?? 0, {
    //   warn: (gameData.resources['fireWood'] ?? 0) < (gameData['fireWoodCost'] ?? 0),
    // });

    const marker = $('track-marker');
    marker.style.top = `${(gameData.game.day - 1) * 33.3 + 236}px`;

    const eventDeckContainer = $('event-deck-container');
    decks.forEach(({ name: deck, scale }) => {
      if (gameData.decks[deck]) {
        if (!this.decks[deck]) {
          this.decks[deck] = new Deck(this, deck, gameData.decks[deck], eventDeckContainer.querySelector(`.${deck}`), scale, 'horizontal');
          if (gameData.decksDiscards && (gameData.decksDiscards[deck]?.name || gameData.decksDiscards[deck]?.[0])) {
            if (!this.decks[deck].isAnimating())
              this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name ?? gameData.decksDiscards[deck]?.[0]);
          }
        } else {
          this.decks[deck].updateDeckCounts(gameData.decks[deck]);
        }
        this.decks[deck].updateMarker(gameData.decks[deck]);
      }
    });

    const drawDecks = [
      { name: 'explore', expansion: 'hindrance' },
      { name: 'gather', expansion: 'base' },
      { name: 'forage', expansion: 'base' },
      { name: 'harvest', expansion: 'base' },
      { name: 'hunt', expansion: 'base' },
    ].filter((d) => this.expansions.includes(d.expansion));

    drawDecks.forEach(({ name: deck }) => {
      if (this.decks[deck] && gameData.decks[deck]) {
        if (gameData.decksDiscards && (gameData.decksDiscards[deck]?.name || gameData.decksDiscards[deck]?.[0])) {
          if (!this.decks[deck].isAnimating())
            this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name ?? gameData.decksDiscards[deck]?.[0]);
        }
        this.decks[deck].updateMarker(gameData.decks[deck]);
      }
    });
  },
  setup: function (gameData) {
    dojo.subscribe('addMoveToLog', gameui, () => {
      const addButtonListener = (node) => {
        addClickListener(node, 'Card', () => {
          this.tooltip.show();
          renderImage(node.getAttribute('data-id'), this.tooltip.renderByElement(), {
            withText: true,
            ...(node.getAttribute('data-type') ? { type: 'tooltip-' + node.getAttribute('data-type') } : {}),
            pos: 'replace',
          });
        });
      };
      const nodes = document.querySelectorAll(`.dlid__log-button:not(.dlid__clickable)`);
      setTimeout(() => {
        nodes.forEach((node) => {
          node.innerHTML = _(node.innerHTML);
          addButtonListener(node);
        });
      }, 0);
    });

    $('game_play_area_wrap').classList.add('dlid');
    $('right-side').classList.add('dlid');
    this.replayFrom = new URLSearchParams(window.location.search).get('replayFrom');
    this.expansionList = gameData.expansionList;
    this.expansion = gameData.expansion;
    const expansionI = this.expansionList.indexOf(this.expansion);
    this.expansions = this.expansionList.slice(0, expansionI + 1);
    this.difficulty = gameData.difficulty;
    this.trackDifficulty = gameData.trackDifficulty;
    this.data = Object.keys(getAllData()).reduce((acc, k) => {
      const d = getAllData()[k];
      d.options = d.options ?? {};
      if (d.options.expansion && this.expansionList.indexOf(d.options.expansion) > expansionI) return acc;
      return { ...acc, [k]: d };
    }, {});

    const playArea = $('game_play_area');
    this.tweening = new Tweening(this, playArea);
    this.selector = new Selector(playArea);
    this.tooltip = new Tooltip($('game_play_area_wrap'));
    this.infoOverlay = new InfoOverlay(this, $('game_play_area_wrap'));
    this.setupCharacterSelections(gameData);
    this.setupBoard(gameData);
    this.dice = new Dice(this, $('board-container'));
    window.dice = this.dice;
    // this.dice.roll(5);
    // renderImage(`board`, playArea);
    playArea.insertAdjacentHTML(
      'beforeend',
      `<div id="players-container" class="dlid__container"><div id="player-container-1" class="inner-container"></div><div id="player-container-2" class="inner-container"></div></div>`,
    );
    this.updatePlayers(gameData);
    // Setting up player boards
    this.updateKnowledgeTree(gameData);
    this.updateItems(gameData);

    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();
    this.firstRender = true;
  },
  updateKnowledgeTree(gameData) {
    let knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
    if (!knowledgeContainer) {
      const playArea = $('game_play_area');
      playArea.insertAdjacentHTML(
        'beforeend',
        `<div id="knowledge-container" class="dlid__container"><div class="board"><div class="upgrade-selections"></div><div class="unlocked-tokens"></div></div></div>`,
      );
      renderImage(`knowledge-tree-${this.difficulty}`, document.querySelector('#knowledge-container .board'), {
        pos: 'insert',
        scale: 1.25,
      });
      knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
    }

    const selections = document.querySelector(`#knowledge-container .upgrade-selections`);
    const upgradeData = getAllData()[`knowledge-tree-${this.difficulty}`].upgrades;
    selections.innerHTML = '';
    // Hindrance show new discoveries
    if (gameData.upgrades) {
      Object.keys(gameData.upgrades).forEach((unlockId) => {
        const unlockSpot = gameData.upgrades[unlockId].replace;
        if (unlockSpot) {
          const { x, y } = upgradeData[unlockSpot];
          selections.insertAdjacentHTML(
            'beforeend',
            `<div class="discovery-spot ${unlockSpot}" style="position: absolute;top: ${(y - 7) * 1.2}px; left: ${
              (x - 103) * 1.2
            }px;"></div>`,
          );
          const elem = selections.querySelector(`.discovery-spot.${unlockSpot}`);
          renderImage(unlockId, elem, { scale: 1.7 / 1.2 });
          addClickListener(document.querySelector(`#knowledge-container *[name="${unlockId}"]`), 'Unlocks', () => {
            this.tooltip.show();
            renderImage(unlockId, this.tooltip.renderByElement(), { withText: true, pos: 'insert', type: 'tooltip-unlock' });
          });
        }
      });
    }

    knowledgeContainer.innerHTML = '';
    gameData.unlocks.forEach((unlockName) => {
      const unlockSpot = gameData.upgrades[unlockName]?.replace ?? unlockName;
      const { x, y } = upgradeData[unlockSpot];
      knowledgeContainer.insertAdjacentHTML(
        'beforeend',
        `<div id="knowledge-${unlockSpot}" class="fkp" style="top: ${y * 1.2}px; left: ${x * 1.2}px;"></div>`,
      );
      renderImage(`fkp-unlocked`, $(`knowledge-${unlockSpot}`), { scale: 2 / 1.2, extraCss: 'fkp-unlocked' });
    });

    gameData.allUnlocks.forEach((unlockId) => {
      if (gameData.upgrades[unlockId]) return;
      const { x, y } = upgradeData[unlockId];
      selections.insertAdjacentHTML(
        'beforeend',
        `<div class="fkp-spot ${unlockId}" style="top: ${(y - 7) * 1.2}px; left: ${(x - 103) * 1.2}px;"></div>`,
      );
      const elem = selections.querySelector(`.fkp-spot.${unlockId}`);

      addClickListener(elem, 'Select', () => {
        this.tooltip.show();
        renderImage(unlockId, this.tooltip.renderByElement(), { textOnly: true, pos: 'insert', type: 'tooltip-unlock' });
      });
    });

    // Sort the nodes in the selection for tab indexing
    const items = selections.children;
    const itemsArr = [];
    for (const i in items) {
      if (items[i].nodeType == 1) {
        itemsArr.push(items[i]);
      }
    }

    itemsArr.sort((a, b) => {
      const dx = Math.round((parseInt(a.style?.left ?? 0, 10) - parseInt(b.style?.left ?? 0, 10)) / 10);
      const dy = Math.round((parseInt(a.style?.top ?? 0, 10) - parseInt(b.style?.top ?? 0, 10)) / 10);
      if (dy !== 0) return dy;
      else return dx;
    });

    for (const i = 0; i < itemsArr.length; ++i) {
      selections.appendChild(itemsArr[i]);
    }
  },
  format_string_recursive(log, args) {
    const saved = {};
    try {
      if (log && args && !args.processed) {
        args.processed = true;
        if (args.i18n) {
          for (const key of args.i18n) {
            if (args[key] && Array.isArray(args[key]) && args[key].length > 0 && typeof args[key][0] === 'object') {
              const arr = [];
              for (const obj of args[key]) {
                arr.push(_(obj.value) + (obj.suffix ? ` (${obj.suffix})` : ''));
              }
              saved[key] = arr.join(', ');
              args[key] = arr.join(', ');
            }
          }
        }
        if (args.i18n_suffix) {
          log += (args.i18n_suffix.prefix ?? '') + this.format_string_recursive(args.i18n_suffix.message, args.i18n_suffix.args);
        }
      }
    } catch (e) {
      console.error(log, args, 'Exception thrown', e.stack);
    }
    try {
      return this.inherited(this.format_string_recursive, [log, args]);
    } finally {
      for (const k in saved) {
        args[k] = saved[k];
      }
    }
  },
  updateGameDatas: function (gameData) {
    if (gameData?.version && this.gamedatas.version < gameData?.version && !this.reloadShown && !this.replayFrom) {
      this.infoDialog(_('There is a new version available.'), _('Reload'), () => window.location.reload());
      this.reloadShown = true;
    }
    const clone = { ...gameData };
    delete clone.gamestate;
    Object.assign(this.gamedatas, clone);
  },
  isActive: function () {
    return (
      this.getActivePlayers()
        .map((d) => d.toString())
        .includes(this.player_id.toString()) || gameui.isPlayerActive()
    );
  },
  ///////////////////////////////////////////////////
  //// Game & client states

  // onEnteringState: this method is called each time we are entering into a new game state.
  //                  You can use this method to perform some user interface changes at this moment.
  //
  onEnteringState: async function (stateName, args = {}) {
    args.args = args.args ?? {};
    args.args['gamestate'] = { name: stateName };
    if (args.args) {
      this.updateGameDatas(args.args);
    }
    const isActive = this.isActive();
    if (isStudio())
      console.log('Entering state: ' + stateName, args, isActive, this.getActivePlayers(), gameui.isPlayerActive(), this.player_id);
    // this.updateResources(args.args);
    switch (stateName) {
      case 'tooManyItems':
        if (isActive) this.tooManyItemsScreen.show(args.args);
        break;
      case 'itemSelection':
        if (isActive) this.itemsScreen.show(args.args);
        break;
      case 'eatSelection':
        if (isActive) this.eatScreen.show(args.args);
        break;
      case 'startHindrance':
        this.upgradeSelectionScreen.show(this.player_id == args.active_player, args.args);
        break;
      case 'deckSelection':
        if (isActive) this.deckSelectionScreen.show(args.args);
        break;
      case 'resourceSelection':
        if (isActive) this.tokenScreen.show(args.args);
        break;
      case 'tokenReduceSelection':
        if (isActive) this.tokenReduceScreen.show(args.args);
        break;
      case 'characterSelection':
        if (isActive) this.characterSelectionScreen.show(args.args);
        break;
      case 'hindranceSelection':
        if (isActive) this.hindranceSelectionScreen.show(args.args);
        break;
      case 'cardSelection':
        if (isActive) this.cardSelectionScreen.show(args.args);
        break;
      case 'whichWeapon':
        if (isActive) this.weaponScreen.show(args.args);
        break;
      case 'characterSelect':
        this.selectedCharacters = args.args.characters ?? [];
        this.updateCharacterSelections(args.args);
        break;
      case 'playerTurn':
        if (args.args.characters) this.updatePlayers(args.args);
        this.updateItems(args.args);
        this.updateKnowledgeTree(args.args);
        this.updateTrack(args.args);

        if (this.leftTradePhase) {
          this.leftTradePhase = false;
          this.showDayTracker();
        }
        break;
      case 'tradeSelect':
      case 'tradePhase':
        this.updateResources(args.args);
        if (this.firstRender && args.args.drawNightCard) await this.showNightTracker(args.args.drawNightCard.card.id);

        this.leftTradePhase = false;
        await Promise.all(this.animations);
        if (this.leftMorningPhase == 'morning') {
          if (this.leftMorningPhase != 'skip') {
            this.itemTradeScreen.show(args.args);
          }
          this.leftMorningPhase = null;
        } else if (!this.leftMorningPhase) {
          this.itemTradeScreen.show(args.args);
        }
        break;
      case 'confirmTradePhase':
      case 'waitTradePhase':
        this.itemTradeScreen.showConfirm(args.args);
        break;
      case 'nightDrawCard':
        this.showNightTracker(args.args.card.id);
        break;
      // case 'drawCard':
      //   if (!args.args.resolving) {
      //     this.decks[args.args.deck].drawCard(args.args.card.id);
      //     this.decks[args.args.deck].updateDeckCounts(args.args.decks[args.args.deck]);
      //   }
      //   break;
    }
    this.firstRender = false;
  },

  // onLeavingState: this method is called each time we are leaving a game state.
  //                 You can use this method to perform some user interface changes at this moment.
  //
  onLeavingState: async function (stateName) {
    if (isStudio()) console.log('Leaving state: ' + stateName);
    switch (stateName) {
      case 'morningPhase':
        this.leftMorningPhase = 'morning';
        // await this.wait(500);
        break;
      case 'startHindrance':
        this.upgradeSelectionScreen.hide();
        break;
      case 'deckSelection':
        this.deckSelectionScreen.hide();
        break;
      case 'eatSelection':
        this.eatScreen.hide();
        break;
      case 'itemSelection':
        this.itemsScreen.hide();
        break;
      case 'whichWeapon':
        this.weaponScreen.hide();
        break;
      case 'characterSelection':
        this.characterSelectionScreen.hide();
        break;
      case 'tokenReduceSelection':
        this.tokenReduceScreen.hide();
        break;
      case 'hindranceSelection':
        this.hindranceSelectionScreen.hide();
        break;
      case 'cardSelection':
        this.cardSelectionScreen.hide();
        break;
      case 'resourceSelection':
        this.tokenScreen.hide();
        break;
      case 'tooManyItems':
        this.tooManyItemsScreen.hide();
        break;
      case 'tradePhase':
        this.leftMorningPhase = 'skip';
        this.itemTradeScreen.hide();
        this.leftTradePhase = true;
        break;
      case 'characterSelect':
        dojo.style('character-selector', 'display', 'none');
        dojo.style('game_play_area', 'display', '');
        this.refreshCharacters = true;
        this.selectedCharacters = [];
        break;
      case 'confirmTradePhase':
      case 'waitTradePhase':
        this.itemTradeScreen.clearSelection();
        break;
    }
  },

  // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
  //                        action status bar (ie: the HTML links in the status bar).
  //
  getActionSuffixHTML: function (action) {
    let suffix = '';
    if (action['name_suffix']) suffix += ` ${action['name_suffix']}`;
    if (action['skillOption']) suffix += ` (${_(action['skillOption'].name)})`;
    else if (action['character'] != null && !action['global']) suffix += ` (${action['character']})`;
    else if (action['characterId'] != null && !action['global']) suffix += ` (${action['characterId']})`;
    if (action['stamina'] != null) suffix += ` <i class="fa fa-bolt dlid__stamina"></i> ${action['stamina']}`;
    if (action['health'] != null) suffix += ` <i class="fa fa-heart dlid__health"></i> ${action['health']}`;
    if (action['unlockCost'] != null) suffix += ` <i class="fa fa-graduation-cap dlid__fkp"></i> ${action['unlockCost']}`;
    if (action['random'] != null) suffix += ` <i class="fa6 fa6-solid fa6-dice-d6 dlid__dice"></i>`;
    if (action['perDay'] != null)
      suffix += ` <i class="fa fa-sun-o dlid__sun"></i> ` + _('${remaining} left').replace(/\$\{remaining\}/, action['perDay']);
    if (action['perForever'] != null)
      suffix +=
        ` <i class="fa fa-circle-o-notch dlid__forever"></i> ` + _('${remaining} left').replace(/\$\{remaining\}/, action['perForever']);
    return suffix;
  },
  clearActionButtons: function () {
    this.removeActionButtons();
    this.clickListeners.forEach((clear) => clear());
    this.clickListeners = [];
    document.querySelectorAll(`.action-cost`).forEach((d) => {
      d.innerHTML = '';
      d.style.display = 'none';
    });
  },
  onUpdateActionButtons: async function (stateName, args) {
    this.updateGameDatas(args);
    const actions = args?.actions;
    const isActive = this.isActive();
    if (isStudio()) console.log('onUpdateActionButtons', isActive, stateName, actions);
    if (isActive && stateName && actions != null) {
      this.clearActionButtons();
      let renderedDrawMenu = false;

      // Add test action buttons in the action status bar, simulating a card click:
      if (actions) {
        const colorLookup = {
          // actSpendFKP: 'darkgray',
          // actAddWood: 'darkgray',
          // actRevive: 'darkgray',
          // actEat: 'darkgray',
          // actDraw: 'green',
          // actUseSkill: 'green',
          // actUseItem: 'green',
        };
        actions
          .sort((a, b) => {
            const d1 = a.action.includes('actDraw') ? 8 : null;
            const d2 = b.action.includes('actDraw') ? 8 : null;
            return (d1 ?? a?.stamina ?? 9) - (d2 ?? b?.stamina ?? 9);
          })
          .forEach((action) => {
            const actionId = action.action;
            if (stateName == 'eatSelection') return;
            if (stateName !== 'playerTurn') {
              if (actionId === 'actUseSkill' || actionId === 'actUseItem') {
                return (actionId === 'actUseSkill' ? this.gamedatas.availableSkills : this.gamedatas.availableItemSkills)
                  ?.filter((d) => d.id !== '12-Bskill1')
                  ?.forEach((skill) => {
                    const suffix = this.getActionSuffixHTML(skill);
                    this.statusBar.addActionButton(
                      `${_(skill.name)}${suffix}`,
                      () => {
                        this.closeDayPopup?.();
                        return this.bgaPerformAction(actionId, { skillId: skill.id });
                      },
                      { disabled: skill.playerId && skill.playerId != gameui.player_id },
                    );
                  });
              }
            }
            if (actionId === 'actAddWood') {
              const elemCost = document.querySelector(`#fire-pit .fire-wood .action-cost`);
              const suffix = this.getActionSuffixHTML(action);
              elemCost.innerHTML = suffix;
              elemCost.style.display = '';
              this.clickListeners.push(
                addClickListener(document.querySelector(`#fire-pit .fire-wood`), 'Add Fire Wood', () => {
                  this.bgaPerformAction(`actAddWood`, { characterId: action.hiddenCharacterId ?? null });
                }),
              );
            } else if (['actDrawGather', 'actDrawForage', 'actDrawHarvest', 'actDrawHunt', 'actDrawExplore'].includes(actionId)) {
              const uppercaseDeck = actionId.slice(7);

              const elemCost = document.querySelector(`.board .${uppercaseDeck.toLowerCase()}-back .action-cost`);
              const suffix = this.getActionSuffixHTML(action);
              elemCost.innerHTML = `${this.getActionMappings()[actionId]}${suffix}`;
              elemCost.style.display = '';
              this.clickListeners.push(
                addClickListener(document.querySelector(`.board .${uppercaseDeck.toLowerCase()}-back`), `${uppercaseDeck} Deck`, () => {
                  this.bgaPerformAction(`actDraw${uppercaseDeck}`);
                }),
              );
            }
            if (actionId.includes('actDraw')) {
              if (!renderedDrawMenu) {
                const suffix = this.getActionSuffixHTML({ random: true });
                this.statusBar.addActionButton(
                  `${this.getActionMappings()['actDraw']}${suffix}`,
                  () => {
                    this.clearActionButtons();

                    actions
                      .sort((a, b) => (a?.stamina ?? 9) - (b?.stamina ?? 9))
                      .filter((d) => d.action.includes('actDraw'))
                      .forEach((action) => {
                        const suffix = this.getActionSuffixHTML(action);
                        this.statusBar.addActionButton(`${this.getActionMappings()[action.action]}${suffix}`, () => {
                          return this.bgaPerformAction(action.action);
                        });
                      });
                    this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                  },
                  stateName === 'playerTurn' ? { classes: 'bgabutton_' + (colorLookup['actDraw'] ?? 'blue') } : null,
                );
              }
              renderedDrawMenu = true;
              return;
            }
            const suffix = this.getActionSuffixHTML(action);
            return this.statusBar.addActionButton(
              `${this.getActionMappings()[actionId]}${suffix}`,
              async () => {
                if (actionId === 'actSpendFKP') {
                  this.clearActionButtons();
                  Object.values(this.gamedatas.availableUnlocks).forEach((unlock) => {
                    const suffix = this.getActionSuffixHTML(unlock);
                    this.statusBar.addActionButton(
                      `${_(unlock.name)}${suffix}`,
                      () => {
                        return this.bgaPerformAction(actionId, { knowledgeId: unlock.id, characterId: action.hiddenCharacterId ?? null });
                      },
                      {
                        disabled:
                          unlock.unlockCost >
                          (action.selectableValues ?? []).reduce((acc, d) => (this.gamedatas.resources[d] ?? 0) + acc, 0),
                      },
                    );
                  });
                  this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                } else if (actionId === 'actUseSkill' || actionId === 'actUseItem') {
                  this.clearActionButtons();
                  Object.values(actionId === 'actUseSkill' ? this.gamedatas.availableSkills : this.gamedatas.availableItemSkills)
                    .filter((d) => d.id !== '12-Bskill1')
                    .forEach((skill) => {
                      const suffix = this.getActionSuffixHTML(skill);
                      this.statusBar.addActionButton(
                        `${_(skill.name)}${suffix}`,
                        () => {
                          this.closeDayPopup?.();
                          return this.bgaPerformAction(actionId, { skillId: skill.id, skillSecondaryId: skill.secondaryId });
                        },
                        { disabled: skill.playerId && skill.playerId != gameui.player_id },
                      );
                    });
                  this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                } else if (actionId === 'actTrade') {
                  this.clearActionButtons();
                  this.tradeScreen.show(this.gamedatas);
                  this.statusBar.addActionButton(this.getActionMappings().actTradeItem + `${suffix}`, () => {
                    if (!this.tradeScreen.hasError()) {
                      this.bgaPerformAction('actTrade', {
                        data: JSON.stringify({
                          offered: this.tradeScreen.getOffered(),
                          requested: this.tradeScreen.getRequested(),
                        }),
                      })
                        ?.then(() => this.tradeScreen.hide())
                        ?.catch(console.error);
                    }
                  });
                  this.statusBar.addActionButton(
                    _('Cancel'),
                    () => {
                      this.onUpdateActionButtons(stateName, args);
                      this.tradeScreen.hide();
                    },
                    { color: 'secondary' },
                  );
                } else if (actionId === 'actSpendFKP') {
                  this.bgaPerformAction('actSpendFKP', { characterId: action.hiddenCharacterId ?? null });
                } else if (actionId === 'actAddWood') {
                  this.bgaPerformAction('actAddWood', { characterId: action.hiddenCharacterId ?? null });
                } else if (actionId === 'actTradeItem') {
                  this.bgaPerformAction('actTradeItem', {
                    data: JSON.stringify(this.itemTradeScreen.getTrade()),
                  });
                } else if (actionId === 'actCraft') {
                  this.clearActionButtons();
                  this.craftScreen.show(this.gamedatas);

                  this.statusBar.addActionButton(this.getActionMappings().actCraft + `${suffix}`, () => {
                    if (!this.craftScreen.hasError()) {
                      const makeCall = () =>
                        this.bgaPerformAction('actCraft', {
                          itemName: this.craftScreen.getSelectedId(),
                        })
                          ?.then(() => {
                            this.craftScreen.hide();
                          })
                          ?.catch(console.error);

                      if (this.gamedatas.allBuildings.includes(this.craftScreen.getSelectedId()))
                        this.confirmationDialog(
                          dojo.string.substitute(_('Only ${count} building(s) can be created this game') + '.', {
                            count: this.gamedatas.maxBuildingCount,
                          }),
                          makeCall,
                        );
                      else makeCall();
                    }
                  });
                  this.statusBar.addActionButton(
                    _('Cancel'),
                    () => {
                      this.onUpdateActionButtons(stateName, args);
                      this.craftScreen.hide();
                    },
                    { color: 'secondary' },
                  );
                } else if (actionId === 'actCook') {
                  this.clearActionButtons();
                  this.cookScreen.show(this.gamedatas);
                  this.statusBar.addActionButton(this.getActionMappings().actCook + `${suffix}`, () => {
                    if (!this.cookScreen.hasError()) {
                      this.bgaPerformAction('actCook', {
                        resourceType: this.cookScreen.getSelectedId(),
                      })
                        ?.then(() => {
                          this.cookScreen.hide();
                        })
                        ?.catch(console.error);
                    }
                  });
                  this.statusBar.addActionButton(
                    _('Cancel'),
                    () => {
                      this.onUpdateActionButtons(stateName, args);
                      this.cookScreen.hide();
                    },
                    { color: 'secondary' },
                  );
                } else if (actionId === 'actRevive') {
                  this.clearActionButtons();
                  this.reviveScreen.show(this.gamedatas);
                  this.statusBar.addActionButton(this.getActionMappings().actRevive + `${suffix}`, () => {
                    if (!this.reviveScreen.hasError()) {
                      const { characterSelected, foodSelected } = this.reviveScreen.getSelected();
                      this.bgaPerformAction('actRevive', {
                        character: characterSelected,
                        food: foodSelected,
                      })
                        ?.then(() => {
                          this.reviveScreen.hide();
                        })
                        ?.catch(console.error);
                    }
                  });
                  this.statusBar.addActionButton(
                    _('Cancel'),
                    () => {
                      this.onUpdateActionButtons(stateName, args);
                      this.reviveScreen.hide();
                    },
                    { color: 'secondary' },
                  );
                } else if (actionId === 'actEat') {
                  this.clearActionButtons();
                  this.eatScreen.show(this.gamedatas, action.character && this.gamedatas.dinnerEatableFoods?.[action.character]);

                  this.statusBar.addActionButton(_('Eat') + `${suffix}`, () => {
                    if (!this.eatScreen.hasError()) {
                      const eatAction = () =>
                        this.bgaPerformAction('actEat', {
                          resourceType: this.eatScreen.getSelectedId(),
                          characterId: action.character ?? null,
                        })
                          ?.then(() => {
                            if (this.gamedatas.gamestate.name !== 'eatSelection') this.eatScreen.hide();
                          })
                          ?.catch(console.error);
                      const character = this.gamedatas.characters.find(({ name }) => name === action.character);

                      (stateName === 'dinnerPhase' || stateName === 'dinnerPhasePrivate') &&
                      action.character &&
                      this.eatScreen.getSelected()['health'] > character.maxHealth - character.health
                        ? this.confirmationDialog(
                            _('This will heal you more than needed. Are you sure you want to continue? This cannot be undone.'),
                            eatAction,
                          )
                        : eatAction();
                    }
                  });
                  this.statusBar.addActionButton(
                    _('Cancel'),
                    () => {
                      this.onUpdateActionButtons(stateName, args);
                      this.eatScreen.hide();
                    },
                    { color: 'secondary' },
                  );
                } else if (
                  actionId === 'actInvestigateFire' &&
                  (this.gamedatas.activeCharacter === 'Cali' || this.gamedatas.availableSkills.some((d) => d.id === '12-Bskill1'))
                ) {
                  let guess = null;
                  let focus = null;
                  const chain = [];
                  const focusFunc = () => {
                    return new Promise((resolve, reject) => {
                      this.clearActionButtons();
                      Object.values(this.gamedatas.availableSkills)
                        .filter((d) => d.id === '12-Bskill1')
                        .forEach((skill) => {
                          const suffix = this.getActionSuffixHTML(skill);
                          this.statusBar.addActionButton(`${_(skill.name)}${suffix}`, () => ((focus = true), resolve()));
                        });
                      this.statusBar.addActionButton(_('Skip'), () => ((focus = false), resolve()));
                      this.statusBar.addActionButton(_('Cancel'), reject, {
                        color: 'secondary',
                      });
                    });
                  };
                  const guessFunc = () => {
                    return new Promise((resolve, reject) => {
                      this.clearActionButtons();
                      [1, 2, 3].forEach((i) => {
                        this.statusBar.addActionButton(`${_('Guess')} ${i}`, () => {
                          guess = i;
                          resolve();
                        });
                      });
                      this.statusBar.addActionButton(_('Cancel'), reject, {
                        color: 'secondary',
                      });
                    });
                  };
                  const lastFunction = () => {
                    this.bgaPerformAction(actionId, { guess: guess, focus: focus });
                  };
                  if (this.gamedatas.availableSkills.some((d) => d.id === '12-Bskill1')) {
                    chain.push(focusFunc);
                  }
                  if (this.gamedatas.activeCharacter === 'Cali') {
                    chain.push(guessFunc);
                  }
                  chain.push(lastFunction);
                  for (const c of chain) {
                    try {
                      await c();
                    } catch (e) {
                      this.onUpdateActionButtons(stateName, args);
                      break;
                    }
                  }
                } else {
                  return this.bgaPerformAction(actionId);
                }
              },
              stateName === 'playerTurn' ? { classes: 'bgabutton_' + (colorLookup[actionId] ?? 'blue') } : null,
            );
          });
      }
      const addSelectionCancelButton = () => {
        if (isActive && this.gamedatas.selectionState?.cancellable === true)
          this.statusBar.addActionButton(
            _('Cancel'),
            () => {
              this.bgaPerformAction('actCancel')?.then(() => this.selector.hide());
            },
            { color: 'secondary' },
          );
      };
      switch (stateName) {
        case 'startHindrance':
          this.statusBar.addActionButton(_('Done'), () => {
            this.bgaPerformAction('actDone');
          });
          break;
        case 'deckSelection':
          this.statusBar.addActionButton(
            args.deckSelection?.title
              ? _(args.deckSelection.title)
              : args.selectionState?.title
                ? _(args.selectionState.title)
                : _('Select Deck'),
            () => {
              this.bgaPerformAction('actSelectDeck', { deckName: this.deckSelectionScreen.getSelectedId() });
            },
          );
          addSelectionCancelButton();
          break;
        case 'eatSelection':
          this.statusBar.addActionButton(args.selectionState?.title ? _(args.selectionState.title) : _('Eat'), () => {
            this.bgaPerformAction('actSelectEat', {
              resourceType: this.eatScreen.getSelectedId(),
            });
          });
          addSelectionCancelButton();
          break;
        case 'hindranceSelection':
          this.statusBar.addActionButton(args.selectionState?.button ? _(args.selectionState.button) : _('Remove Hindrance'), () => {
            this.bgaPerformAction('actSelectHindrance', { data: JSON.stringify(this.hindranceSelectionScreen.getSelected()) });
          });
          addSelectionCancelButton();
          break;
        case 'characterSelection':
          this.statusBar.addActionButton(this.getActionMappings().actSelectCharacter, () => {
            this.bgaPerformAction('actSelectCharacter', { characterId: this.characterSelectionScreen.getSelectedId() });
          });
          addSelectionCancelButton();
          break;
        case 'cardSelection':
          this.statusBar.addActionButton(this.getActionMappings().actSelectCard, () => {
            this.bgaPerformAction('actSelectCard', { cardId: this.cardSelectionScreen.getSelectedId() });
          });
          addSelectionCancelButton();
          break;
        case 'resourceSelection':
          this.statusBar.addActionButton(_('Select Resource'), () => {
            this.bgaPerformAction('actSelectResource', { resourceType: this.tokenScreen.getSelectedId() });
          });
          addSelectionCancelButton();
          break;
        case 'tokenReduceSelection':
          this.statusBar.addActionButton(args.selectionState?.button ? _(args.selectionState.button) : _('Select'), () => {
            this.bgaPerformAction('actTokenReduceSelection', { data: JSON.stringify(this.tokenReduceScreen.getSelection()) });
          });
          addSelectionCancelButton();
          break;
        case 'tooManyItems':
          this.statusBar.addActionButton(_('Send to Camp'), () => {
            this.bgaPerformAction('actSendToCamp', { sendToCampId: this.tooManyItemsScreen.getSelectedId() });
          });
          break;
        case 'buttonSelection':
          this.gamedatas.selectionState?.items?.forEach(({ name, value }) => {
            this.statusBar.addActionButton(_(name), () => {
              this.bgaPerformAction('actSelectButton', { buttonValue: value });
            });
          });
          addSelectionCancelButton();
          break;
        case 'itemSelection':
          this.statusBar.addActionButton(_('Select Item'), () => {
            this.bgaPerformAction('actSelectItem', { ...this.itemsScreen.getSelection() });
          });
          addSelectionCancelButton();
          break;
        case 'whichWeapon':
          this.statusBar.addActionButton(_('Confirm'), () =>
            this.bgaPerformAction('actChooseWeapon', { weaponId: this.weaponScreen.getSelectedId() }),
          );
          break;
        case 'tradePhase':
        case 'tradePhaseActions':
          this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction('actTradeDone'), { color: 'secondary' });
          this.statusBar.addActionButton(_('Yield to All Changes'), () => this.bgaPerformAction('actTradeYield'), { color: 'secondary' });
          break;
        case 'confirmTradePhase':
          this.statusBar.addActionButton(_('Cancel'), () => this.bgaPerformAction('actCancelTrade'), { color: 'secondary' });
          break;
        case 'interrupt':
          if (![...this.gamedatas.availableSkills, ...this.gamedatas.availableItemSkills].some((d) => d.cancellable === false))
            this.statusBar.addActionButton(_('Skip'), () => this.bgaPerformAction('actDone'), { color: 'secondary' });
          break;
        case 'dayEvent':
          // No Cancel Button
          break;
        case 'dinnerPhase':
        case 'dinnerPhasePrivate':
          this.statusBar.addActionButton(
            _('Done'),
            () =>
              (this.gamedatas.resources['fireWood'] ?? 0) <= (this.gamedatas['fireWoodCost'] ?? 0)
                ? this.confirmationDialog(_('There is not enough wood for the morning phase. You will lose the game!'), () =>
                    this.bgaPerformAction('actDone'),
                  )
                : this.bgaPerformAction('actDone'),
            { color: 'secondary' },
          );
          break;
        case 'postEncounter':
          this.statusBar.addActionButton(_('Done'), () => this.bgaPerformAction('actDone'), { color: 'secondary' });
          break;
        case 'characterSelect':
          const playerCount = Object.keys(args.players).length;
          if (playerCount === 3) {
            this.selectCharacterCount = gameui.player_id == this.firstPlayer ? 2 : 1;
          } else if (playerCount === 1) {
            this.selectCharacterCount = 4;
          } else if (playerCount === 2) {
            this.selectCharacterCount = 2;
          } else if (playerCount === 4) {
            this.selectCharacterCount = 1;
          }
          this.statusBar.addActionButton(_('Confirm ${x} character(s)').replace('${x}', this.selectCharacterCount), () =>
            this.bgaPerformAction('actChooseCharacters'),
          );
          this.statusBar.addActionButton(_('Randomize') + ` <i class="fa6 fa6-solid fa6-dice-d6 dlid__dice"></i>`, () => {
            const saved = [...this.mySelectedCharacters];
            const otherCharacters = this.selectedCharacters.filter((d) => d.playerId != gameui.player_id).map((d) => d.name);
            const validCharacters = Object.keys(this.data).filter(
              (d) => this.data[d].options.type === 'character' && !otherCharacters.includes(d),
            );
            this.mySelectedCharacters = [];
            for (let i = 0; i < this.selectCharacterCount; i++) {
              let choice = validCharacters[Math.floor(Math.random() * validCharacters.length)];
              while (!choice || this.mySelectedCharacters.includes(choice)) {
                choice = validCharacters[Math.floor(Math.random() * validCharacters.length)];
              }
              this.mySelectedCharacters.push(choice);
            }
            this.bgaPerformAction('actCharacterClicked', {
              character1: this.mySelectedCharacters?.[0],
              character2: this.mySelectedCharacters?.[1],
              character3: this.mySelectedCharacters?.[2],
              character4: this.mySelectedCharacters?.[3],
            })?.catch(() => {
              this.mySelectedCharacters = saved;
            });
          });
          break;
        case 'playerTurn':
          if (isActive) {
            if (this.gamedatas.canUndo)
              this.statusBar.addActionButton(_('Undo'), () => this.bgaPerformAction('actUndo'), { color: 'secondary' });
            this.statusBar.addActionButton(
              _('End Turn'),
              () => this.confirmationDialog(_('End Turn'), () => this.bgaPerformAction('actEndTurn')),
              { color: 'secondary' },
            );
          }
          break;
        default:
          if (isActive)
            this.statusBar.addActionButton(
              _('End Turn'),
              () => this.confirmationDialog(_('End Turn'), () => this.bgaPerformAction('actEndTurn')),
              { color: 'secondary' },
            );
          break;
      }
      // if (isActive && this.gamedatas.cancellable === true)
      //   this.statusBar.addActionButton(
      //     _('Cancel'),
      //     () => {
      //       this.bgaPerformAction('actCancel').then(() => this.selector.hide());
      //     },
      //     { color: 'secondary' },
      //   );
    } else if (!this.isSpectator && stateName) {
      const skipOthersActions = () => {
        if (!this.gamedatas.isRealTime)
          this.statusBar.addActionButton(
            _("Skip Other's Selection"),
            () => {
              this.bgaPerformAction('actForceSkip', null, { checkAction: false });
            },
            { color: 'red' },
          );
      };
      const backAction = () => {
        this.statusBar.addActionButton(
          _('Back'),
          () => {
            this.bgaPerformAction('actUnBack', null, { checkAction: false });
          },
          { color: 'secondary' },
        );
      };
      switch (stateName) {
        case 'dinnerPhase':
        case 'dinnerPhasePrivate':
          backAction();
          if (!this.gamedatas.availableSkills.some((d) => d.cancellable === false)) skipOthersActions();
          break;
        case 'tradePhase':
          backAction();
          skipOthersActions();
          break;
        case 'waitTradePhase':
          this.statusBar.addActionButton(
            _('Cancel'),
            () => {
              this.bgaPerformAction('actForceSkip', null, { checkAction: false });
            },
            { color: 'secondary' },
          );
          break;
        case 'characterSelect':
          backAction();
          break;
        case 'interrupt':
          if (![...this.gamedatas.availableSkills, ...this.gamedatas.availableItemSkills].some((d) => d.cancellable === false))
            if (gameui.gamedatas.activeTurnPlayerId == gameui.player_id && !this.gamedatas.isRealTime) {
              actions
                .sort((a, b) => (a?.stamina ?? 9) - (b?.stamina ?? 9))
                .forEach((action) => {
                  const actionId = action.action;
                  if (actionId === 'actUseSkill' || actionId === 'actUseItem') {
                    return (actionId === 'actUseSkill' ? this.gamedatas.availableSkills : this.gamedatas.availableItemSkills)?.forEach(
                      (skill) => {
                        const suffix = this.getActionSuffixHTML(skill);
                        this.statusBar.addActionButton(`${_(skill.name)}${suffix}`, () => {}, { disabled: true });
                      },
                    );
                  }
                });
              skipOthersActions();
            }
          break;
      }
    }
  },

  addHelpTooltip: function ({
    node,
    text = '',
    tooltipText = '',
    tooltipText2 = '',
    iconCSS,
    tooltipElem = this.tooltip,
    wrapNode = false,
  }) {
    // game.addTooltip(id, helpString, actionString);
    if (!node.querySelector('.tooltip')) {
      node.insertAdjacentHTML(
        'beforeend',
        `<div class="tooltip">${wrapNode ? '' : `<div class="dot"><i class="${iconCSS ?? 'fa fa-question'}"></i></div>`}</div>`,
      );

      addClickListener(
        wrapNode ? node : node.querySelector('.tooltip'),
        'Tooltip',
        () => {
          tooltipElem.show();
          tooltipElem
            .renderByElement()
            .insertAdjacentHTML(
              'beforeend',
              `<div class="tooltip-box"><i class="fa fa-question-circle-o fa-2x" aria-hidden="true"></i><span>${tooltipText ? renderText({ name: tooltipText }) : text}</span></div>`,
            );
          if (tooltipText2)
            tooltipElem
              .renderByElement()
              .insertAdjacentHTML(
                'beforeend',
                `<div class="tooltip-box"><i class="fa fa-question-circle-o fa-2x" aria-hidden="true"></i><span>${renderText({ name: tooltipText2 })}</span></div>`,
              );
        },
        true,
      );
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
    // this.bgaSetupPromiseNotifications({
    //   prefix: 'notif_', // default is 'notif_'
    //   minDuration: 500,
    //   minDurationNoText: 1,
    //   logger: console.log, // show notif debug informations on console. Could be console.warn or any custom debug function (default null = no logs)
    //   ignoreNotifications: ['updateAutoPlay'], // the notif_updateAutoPlay function will be ignored by bgaSetupPromiseNotifications. You'll need to subscribe to it manually
    //   // onStart: (notifName, msg, args) => $('pagemaintitletext').innerHTML = `${_('Animation for:')} ${msg}`,
    //   // onEnd: (notifName, msg, args) => $('pagemaintitletext').innerHTML = '',
    // });
    // dojo.subscribe('startSelection', this, 'notif_startSelection');
    dojo.subscribe('characterClicked', this, 'notif_characterClicked');
    dojo.subscribe('updateCharacterData', this, 'notif_updateCharacterData');
    dojo.subscribe('updateKnowledgeTree', this, 'notif_updateKnowledgeTree');
    dojo.subscribe('updateActionButtons', this, 'notif_updateActionButtons');
    dojo.subscribe('notify', this, 'notif_actionNotification');

    // Example 1: standard notification handling
    // dojo.subscribe( 'tokenUsed', this, "notif_tokenUsed" );

    // Example 2: standard notification handling + tell the user interface to wait
    //            during 3 seconds after calling the method in order to let the players
    //            see what is happening in the game.

    dojo.subscribe('zombieBackDLD', this, 'notif_zombieBack');
    dojo.subscribe('zombieChange', this, 'notif_zombieChange');
    dojo.subscribe('activeCharacter', this, 'notif_tokenUsed');
    dojo.subscribe('tradeItem', this, 'notif_tradeItem');
    dojo.subscribe('tokenUsed', this, 'notif_tokenUsed');
    dojo.subscribe('shuffle', this, 'notif_shuffle');
    dojo.subscribe('cardDrawn', this, 'notif_cardDrawn');
    dojo.subscribe('rollFireDie', this, 'notif_rollFireDie');
    dojo.subscribe('resetNotifications', this, 'notif_resetNotifications');
    this.notifqueue.setSynchronous('cardDrawn', 1000);
    this.notifqueue.setSynchronous('rollFireDie', 3250);
    this.notifqueue.setSynchronous('shuffle', 1500);
    this.notifqueue.setSynchronous('tokenUsed', 300);
  },
  notificationWrapper: async function (notification) {
    notification.args = notification.args ?? {};
    const state = notification.gamestate ?? notification.args.gamestate;
    if (notification.gameData) {
      notification.gameData.gamestate = state;
    }
    if (notification.args) {
      notification.args.gamestate = state;
    }
    if (notification.args.gameData) {
      notification.args.gameData.gamestate = state;
    }
    if (notification.gameData) {
      notification.gameData.gamestate = state;
    }
    if (notification.args.gameData) {
      this.updateGameDatas(notification.args.gameData);
    }
    return this.replayFrom > notification.move_id;
  },
  notif_actionNotification: async function (notification) {
    const usedActionId = notification.args.usedActionId;
    if (usedActionId) {
      this.infoOverlay.addMessage(notification.args);
    }
  },
  // notif_startSelection: async function (notification) {
  //   await this.notificationWrapper(notification);
  //   if (isStudio()) console.log('notif_startSelection', notification);
  //   this.onEnteringState(notification.args.stateName, notification.args.gameData);
  // },
  notif_resetNotifications: async function (notification) {
    await this.notificationWrapper(notification);
    this.infoOverlay.addMessage({ ...notification.args, usedActionId: 'actUndo' });
    const lastMoveId = parseInt(notification.args.moveId, 10);
    for (const logId of Object.keys(gameui.log_to_move_id)) {
      const moveId = parseInt(gameui.log_to_move_id[logId], 10);
      if (moveId > lastMoveId) {
        try {
          $(`log_${logId}`).remove();
        } catch (e) {}
        try {
          $(`dockedlog_${logId - 1}`).remove();
        } catch (e) {}
      }
    }
  },
  notif_rollFireDie: async function (notification) {
    if (await this.notificationWrapper(notification)) return;
    if (isStudio()) console.log('notif_rollFireDie', notification);
    return this.dice.roll(notification.args);
  },
  notif_cardDrawn: async function (notification) {
    if (isStudio()) console.log('notif_cardDrawn', notification);
    const gameData = notification.args.gameData;
    if (await this.notificationWrapper(notification)) {
      if (!notification.args.partial) this.decks[notification.args.deck].setDiscard(notification.args.card.id);
    } else {
      if (notification.args.deck === 'day-event' && !notification.args.partial) {
        this.showDayEvent(notification.args.card.id);
      }
      await this.decks[notification.args.deck].drawCard(notification.args.card.id, notification.args.partial);
    }
    this.decks[notification.args.deck].updateDeckCounts(gameData.decks[notification.args.deck]);
    this.decks[notification.args.deck].updateMarker(gameData.decks[notification.args.deck]);
  },
  notif_shuffle: async function (notification) {
    if (await this.notificationWrapper(notification)) return;
    if (isStudio()) console.log('notif_shuffle', notification);
    const gameData = notification.args.gameData;
    this.decks[notification.args.deck].updateDeckCounts(gameData.decks[notification.args.deck]);
    return this.decks[notification.args.deck].shuffle(notification.args);
  },
  notif_zombieChange: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_zombieChange', notification);
    document.querySelectorAll('.character-side-container').forEach((node) => node.remove());
    document.querySelectorAll('#players-container .player-card').forEach((node) => node.remove());

    this.updatePlayers(notification.args.gameData);
  },
  notif_zombieBack: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_zombieBack', notification);
    $('zombieBack').style.display = 'none';
    document.querySelectorAll('.character-side-container').forEach((node) => node.remove());
    document.querySelectorAll('#players-container .player-card').forEach((node) => node.remove());

    this.updatePlayers(notification.args.gameData);
  },

  notif_updateActionButtons: async function (notification) {
    if (isStudio()) console.log('notif_updateActionButtons', notification);
    await this.notificationWrapper(notification);
    await this.onUpdateActionButtons(notification.args.gamestate.name, notification.args.gameData);
  },
  notif_updateKnowledgeTree: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_updateKnowledgeTree', notification);
    this.updateItems(notification.args.gameData);
    this.updateKnowledgeTree(notification.args.gameData);
    if (notification.args?.gamestate?.name == 'startHindrance') this.upgradeSelectionScreen.update(notification.args.gameData);
  },

  notif_characterClicked: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_characterClicked', notification);
    this.selectedCharacters = notification.args.gameData.characters ?? [];
    this.updateCharacterSelections(notification.args);
  },
  notif_tradeItem: async function (notification) {
    this.itemTradeScreen.update(notification.args);
  },
  notif_updateCharacterData: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_updateCharacterData', notification);
    this.updatePlayers(notification.args.gameData);
    this.updateItems(notification.args.gameData);
  },
  notif_tokenUsed: async function (notification) {
    await this.notificationWrapper(notification);
    if (isStudio()) console.log('notif_tokenUsed', notification);
    this.updateResources(notification.args.gameData);
    this.updateItems(notification.args.gameData);
  },
});
