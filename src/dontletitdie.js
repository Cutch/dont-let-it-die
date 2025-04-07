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

define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui', 'ebg/counter'], function (dojo, declare) {
  return declare('bgagame.dontletitdie', ebg.core.gamegui, {
    constructor: function () {
      this.actionMappings = {
        actInvestigateFire: _('Investigate Fire'),
        actCraft: _('Craft'),
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
      };
      // Used For character selection
      this.selectedCharacters = [];
      this.mySelectedCharacters = [];
      this.data = [];
      this.selector = null;
      this.tooltip = null;
      this.decks = {};
      this.cardSelectionScreen = new CardSelectionScreen(this);
      this.characterSelectionScreen = new CharacterSelectionScreen(this);
      this.deckSelectionScreen = new DeckSelectionScreen(this);
      this.hindranceSelectionScreen = new HindranceSelectionScreen(this);
      this.tradeScreen = new TradeScreen(this);
      this.itemTradeScreen = new ItemTradeScreen(this);
      this.craftScreen = new CraftScreen(this);
      this.cookScreen = new CookScreen(this);
      this.eatScreen = new EatScreen(this);
      this.reviveScreen = new ReviveScreen(this);
      this.tokenScreen = new TokenScreen(this);
      this.tooManyItemsScreen = new TooManyItemsScreen(this);
      this.upgradeSelectionScreen = new UpgradeSelectionScreen(this);
      this.weaponScreen = new WeaponScreen(this);
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
    getResourcesForDisplay: function (gameData) {
      return this.resourcesForDisplay.filter((d) => d in gameData.game.resources);
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
      if (gameData.gamestate.name === 'characterSelect')
        document.querySelectorAll('.character-side-container').forEach((el) => el.remove());

      const scale = 3;
      Object.values(gameData?.characters ?? this.selectedCharacters).forEach((character, i) => {
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
            <div class="character-image"></div>
          </div>`,
          );
          renderImage('skull', document.querySelector(`#${characterSideId} .first-player-marker`), {
            scale: 20,
            pos: 'replace',
            card: false,
            css: 'side-panel-skull',
          });
          playerSideContainer = $(characterSideId);
          addClickListener(playerSideContainer.querySelector(`.character-name`), character.name, () => {
            this.tooltip.show();
            renderImage(character.name, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
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
            renderImage(character.name, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
          });
        }
        playerSideContainer.querySelector(`.health .value`).innerHTML = `${character.health ?? 0}/${character.maxHealth ?? 0}`;
        playerSideContainer.querySelector(`.stamina .value`).innerHTML = `${character.stamina ?? 0}/${character.maxStamina ?? 0}`;
        playerSideContainer.querySelector(`.equipment .value`).innerHTML =
          [...equipments, ...character.dayEvent, ...character.necklaces]
            .map((d) => `<span class="equipment-item equipment-${d.itemId}">${_(d.name)}</span>`)
            .join(', ') || 'None';
        playerSideContainer.querySelector(`.hindrance .value`).innerHTML =
          hindrance.map((d) => `<span class="hindrance-item hindrance-${d.itemId}">${_(d.name)}</span>`).join(', ') || 'None';
        playerSideContainer.style['background-color'] = character?.isActive ? '#fff' : '';
        [...equipments, ...character.dayEvent, ...character.necklaces].forEach((d) => {
          addClickListener(playerSideContainer.querySelector(`.equipment-${d.itemId}`), _(d.name), () => {
            this.tooltip.show();
            renderImage(d.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace', rotate: d.rotate, centered: true });
          });
        });
        hindrance.forEach((d) => {
          addClickListener(playerSideContainer.querySelector(`.hindrance-${d.itemId}`), _(d.name), () => {
            this.tooltip.show();
            renderImage(d.id, this.tooltip.renderByElement(), { scale: 1.5, pos: 'replace', rotate: d.rotate, centered: true });
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
                <div class="card"><div class="first-player-marker"></div></div>
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
            renderImage(character.name, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
          });
          const coverElem = document.createElement('div');
          characterElem.appendChild(coverElem);
          // const coverElem = characterElem.querySelector(`.cover`);
          coverElem.classList.add('cover');
          if (character.incapacitated) {
            if ((character.health ?? 0) > 0) {
              coverElem.innerHTML = _('Recovering');
              if (!coverElem.classList.contains('healing')) coverElem.classList.add('healing');
            } else if (!coverElem.classList.contains('incapacitated')) {
              coverElem.innerHTML = _('Incapacitated');
              coverElem.classList.add('incapacitated');
            }
          } else {
            if ((character.health ?? 0) > 0) {
              if (coverElem.classList.contains('healing')) coverElem.classList.remove('healing');
            } else if (coverElem.classList.contains('incapacitated')) {
              coverElem.classList.remove('incapacitated');
            }
          }

          const renderedItems = [];
          const weapon = equipments.find((d) => d.itemType === 'weapon');
          if (weapon) {
            renderedItems.push(weapon);
            renderImage(weapon.id, document.querySelector(`#player-${character.name} > .${weapon.itemType}`), {
              scale: scale,
              pos: 'replace',
            });
            addClickListener(document.querySelector(`#player-${character.name} > .${weapon.itemType}`), _(weapon.name), () => {
              this.tooltip.show();
              renderImage(weapon.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
            });
          }

          const tool = equipments.find((d) => d.itemType === 'tool');
          if (tool) {
            renderedItems.push(tool);
            renderImage(tool.id, document.querySelector(`#player-${character.name} > .${tool.itemType}`), {
              scale: scale,
              pos: 'replace',
            });
            addClickListener(document.querySelector(`#player-${character.name} > .${tool.itemType}`), _(tool.name), () => {
              this.tooltip.show();
              renderImage(tool.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
            });
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
          //     renderImage(item3.id, this.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
          //   });
          // }
          const extraEquipmentElem = extraContainerButtons.querySelector(`.card-extra-equipment`);
          extraEquipmentElem.style['display'] = !!item3 || character.dayEvent.length > 0 || character.necklaces.length > 0 ? `` : 'none';
          extraEquipmentElem.querySelector('span').innerHTML = (!!item3 ? 1 : 0) + character.dayEvent.length + character.necklaces.length;
          addClickListener(extraEquipmentElem, _('Extra Equipment'), () => {
            this.tooltip.show();
            if (item3)
              renderImage(item3.id, this.tooltip.renderByElement(), { scale: 1, pos: 'append', rotate: item3.rotate, centered: true });
            [...character.dayEvent, ...character.necklaces].forEach((dayEvent) => {
              renderImage(dayEvent.id, this.tooltip.renderByElement(), {
                scale: 1,
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
              renderImage(hindrance.id, this.tooltip.renderByElement(), { scale: 1.5, pos: 'append' });
            });
            character.mentalHindrance.forEach((hindrance) => {
              renderImage(hindrance.id, this.tooltip.renderByElement(), { scale: 1.5, pos: 'append' });
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
      if (!gameData || !gameData.resourcesAvailable || !gameData.game) return;

      const firewoodElem = document.querySelector(`.fire-wood`);

      // Shared Resource Pool
      let sharedElem = document.querySelector(`#shared-resource-container .tokens`);
      if (!sharedElem) {
        $('game_play_area').insertAdjacentHTML(
          'beforeend',
          `<div id="shared-resource-container" class="dlid__container"><h3>${_('Shared Resources')}</h3><div class="tokens"></div></div>`,
        );
        sharedElem = document.querySelector(`#shared-resource-container .tokens`);
      }
      sharedElem.innerHTML = '';
      const resourcesForDisplay = this.getResourcesForDisplay(gameData);
      resourcesForDisplay.forEach((name) => this.updateResource(name, sharedElem, gameData.game['resources'][name] ?? 0));

      // Available Resource Pool
      let availableElem = document.querySelector(`#discoverable-container .tokens`);
      if (!availableElem) {
        $('game_play_area').insertAdjacentHTML(
          'beforeend',
          `<div id="discoverable-container" class="dlid__container"><h3>${_(
            'Discoverable Resources',
          )}</h3><div class="tokens"></div></div>`,
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
      const prevResources = gameData.game['prevResources'];
      let skipWood = false;
      if (prevResources['fireWood'] != null && prevResources['fireWood'] < gameData.game['resources']['fireWood']) {
        // Wood to Firewood
        this.tweening.addTween(
          sharedElem.querySelector(`.token.wood`),
          firewoodElem.querySelector(`.token.wood`),
          'wood',
          2,
          gameData.game['resources']['fireWood'] - prevResources['fireWood'],
        );
        skipWood = true;
      } else if (prevResources['fireWood'] != null && prevResources['fireWood'] > gameData.game['resources']['fireWood']) {
        // Firewood to Wood
        this.tweening.addTween(
          firewoodElem.querySelector(`.token.wood`),
          availableElem.querySelector(`.token.wood`),
          'wood',
          2,
          prevResources['fireWood'] - gameData.game['resources']['fireWood'],
        );
      }
      resourcesForDisplay.forEach((name) => {
        const rawName = name.replace('-cooked', '');
        if (
          prevResources[rawName] - 1 === gameData.game['resources'][rawName] &&
          prevResources[rawName + '-cooked'] + 1 === gameData.game['resources'][rawName + '-cooked']
        ) {
          if (rawName === name) {
            // Move resource to cooked resource
            this.tweening.addTween(
              sharedElem.querySelector(`.token.${name}`),
              sharedElem.querySelector(`.token.${name + '-cooked'}`),
              name + '-cooked',
              2,
              1,
            );
          }
        } else if (prevResources[name] != null && prevResources[name] < gameData.game['resources'][name]) {
          // Discard to Shared Resources
          this.tweening.addTween(
            availableElem.querySelector(`.token.${rawName}`),
            sharedElem.querySelector(`.token.${name}`),
            name,
            2,
            gameData.game['resources'][name] - prevResources[name],
          );
        } else if (
          prevResources[name] != null &&
          prevResources[name] > gameData.game['resources'][name] &&
          (name !== 'wood' || !skipWood)
          // prevResources[name + '-cooked'] > gameData.game['resources'][name]
        ) {
          // Shared Resources to Discard
          this.tweening.addTween(
            sharedElem.querySelector(`.token.${name}`),
            availableElem.querySelector(`.token.${rawName}`),
            name,
            2,
            prevResources[name] - gameData.game['resources'][name],
          );
        }
      });
      // if (gameData.game.buildings.length > 0) {
      //   const div = document.querySelector(`#board-container .buildings`);
      //   if (div.childNodes.length == 0) {
      //     gameData.game.buildings.forEach((building) => {
      //       renderImage(building.name, div, { scale: 2, pos: 'append' });
      //       addClickListener(div, 'Buildings', () => {
      //         this.tooltip.show();
      //         renderImage(building.name, this.tooltip.renderByElement(), { scale: 0.5, pos: 'replace' });
      //       });
      //     });
      //   }
      // }
    },
    updateResource: function (name, elem, count, { warn = false } = {}) {
      elem.insertAdjacentHTML('beforeend', `<div class="token ${name}"><div class="counter dot">${count}</div></div>`);
      if (warn) elem.insertAdjacentHTML('beforeend', `<div class="fa fa-fire warning dot"></div>`);
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
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
      $('building-items-container').style.display = gameData.game.buildings.length > 0 ? '' : 'none';
      if (gameData.game.buildings.length > 0) {
        gameData.game.buildings.forEach((building) => {
          this.updateItem(building.name, buildingItems, null);
        });
      }
      // Shared Resource Pool
      // Available Resource Pool
      let availableElem = document.querySelector(`#items-container .items`);
      if (!availableElem) {
        $('game_play_area').insertAdjacentHTML(
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
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token ${name}">${count != null ? `<div class="counter dot">${count}</div>` : ''}</div>`,
      );
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), name, () => {
        this.tooltip.show();
        renderImage(name, this.tooltip.renderByElement(), {
          pos: 'insert',
          scale: 1,
        });
      });
    },
    setupBoard: function (gameData) {
      this.firstPlayer = gameData.playerorder[0];
      const decks = [
        { name: 'gather', expansion: 'base' },
        { name: 'forage', expansion: 'base' },
        { name: 'harvest', expansion: 'base' },
        { name: 'hunt', expansion: 'base' },
        { name: 'explore', expansion: 'hindrance' },
      ].filter((d) => this.expansions.includes(d.expansion));
      // Main board
      document
        .getElementById('game_play_area')
        .insertAdjacentHTML(
          'beforeend',
          `<div id="board-container" class="dlid__container"><div class="board"><div class="buildings"></div>${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );

      renderImage(`board`, document.querySelector(`#board-container > .board`), { scale: 2, pos: 'insert' });
      decks.forEach(({ name: deck }) => {
        if (!this.decks[deck] && gameData.decks[deck]) {
          const uppercaseDeck = deck[0].toUpperCase() + deck.slice(1);
          this.decks[deck] = new Deck(this, deck, gameData.decks[deck], document.querySelector(`.board > .${deck}`), 2);
          this.decks[deck].setDiscard(gameData.decksDiscards[deck]?.name);
          if (gameData.game.partials && gameData.game.partials[deck]) {
            this.decks[deck].drawCard(gameData.game.partials[deck].id, true);
          }
          this.decks[deck].updateMarker(gameData.decks[deck]);

          addClickListener(document.querySelector(`.board .${deck}-back`), `${uppercaseDeck} Deck`, () => {
            this.bgaPerformAction(`actDraw${uppercaseDeck}`);
          });
        }
      });

      this.updateResources(gameData);
    },
    setupCharacterSelections: function (gameData) {
      const playArea = $('game_play_area');
      playArea.parentElement.insertAdjacentHTML('beforeend', `<div id="character-selector" class="dlid__container"></div>`);
      const elem = $('character-selector');
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
        { name: 'night-event', expansion: 'base', scale: 1.5 },
        { name: 'day-event', expansion: 'mini-expansion', scale: 3 },
        { name: 'mental-hindrance', expansion: 'hindrance', scale: 3 },
        { name: 'physical-hindrance', expansion: 'hindrance', scale: 3 },
      ].filter((d) => this.expansions.includes(d.expansion));
      if (!trackContainer) {
        const playArea = $('game_play_area');
        playArea.insertAdjacentHTML(
          'beforeend',
          `<div id="track-container" class="dlid__container"><div id="event-deck-container">${decks
            .map((d) => `<div class="${d.name}"></div>`)
            .join('')}</div></div>`,
        );
        trackContainer = $('track-container');
        renderImage(`track-${gameData.trackDifficulty}`, trackContainer, { scale: 2, pos: 'insert' });

        trackContainer
          .querySelector(`.track-${gameData.trackDifficulty}`)
          .insertAdjacentHTML('beforeend', `<div id="track-marker" class="marker"></div>`);
        trackContainer
          .querySelector(`.track-${gameData.trackDifficulty}`)
          .insertAdjacentHTML('beforeend', `<div id="fire-pit" class="fire-pit"><div class="fire-wood"></div></div>`);
        renderImage(`fire`, $('fire-pit'), { scale: 4, pos: 'insert' });

        addClickListener(document.querySelector(`#fire-pit .fire-wood`), 'Add Fire Wood', () => {
          this.bgaPerformAction(`actAddWood`);
        });
      }
      const firewoodElem = document.querySelector(`#fire-pit .fire-wood`);
      firewoodElem.innerHTML = '';
      this.updateResource('wood', firewoodElem, gameData.game['resources']['fireWood'] ?? 0, {
        warn: (gameData.game['resources']['fireWood'] ?? 0) < (gameData['fireWoodCost'] ?? 0),
      });

      const marker = $('track-marker');
      marker.style.top = `${(gameData.game.day - 1) * 35 + 236}px`;

      const eventDeckContainer = $('event-deck-container');
      decks.forEach(({ name: deck, scale }) => {
        if (gameData.decks[deck])
          if (!this.decks[deck]) {
            this.decks[deck] = new Deck(
              this,
              deck,
              gameData.decks[deck],
              eventDeckContainer.querySelector(`.${deck}`),
              scale,
              'horizontal',
            );
            if (gameData.decksDiscards[deck]?.name) this.decks[deck].setDiscard(gameData.decksDiscards[deck].name);
          } else {
            this.decks[deck].updateDeckCounts(gameData.decks[deck]);
          }
      });
    },
    setup: function (gameData) {
      $('game_play_area_wrap').classList.add('dlid');
      $('right-side').classList.add('dlid');

      expansionI = gameData.expansionList.indexOf(gameData.expansion);
      this.expansions = gameData.expansionList.slice(0, expansionI + 1);
      this.data = Object.keys(allSprites).reduce((acc, k) => {
        const d = allSprites[k];
        d.options = d.options ?? {};
        if (d.options.expansion && gameData.expansionList.indexOf(d.options.expansion) > expansionI) return acc;
        return { ...acc, [k]: d };
      }, {});

      const playArea = $('game_play_area');
      this.tweening = new Tweening(this, playArea);
      this.selector = new Selector(playArea);
      this.tooltip = new Tooltip(playArea);
      this.setupCharacterSelections(gameData);
      this.setupBoard(gameData);
      this.dice = new Dice($('board-container'));
      window.dice = this.dice;
      // this.dice.roll(5);
      // renderImage(`board`, playArea);
      this.updateTrack(gameData);
      playArea.insertAdjacentHTML(
        'beforeend',
        `<div id="players-container" class="dlid__container"><div id="player-container-1" class="inner-container"></div><div id="player-container-2" class="inner-container"></div></div>`,
      );
      this.updatePlayers(gameData);
      // Setting up player boards
      this.updateKnowledgeTree(gameData);
      this.updateItems(gameData);
      playArea.insertAdjacentHTML('beforeend', `<div id="instructions-container" class="dlid__container"></div>`);
      renderImage(`instructions`, $('instructions-container'));

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();
    },
    updateKnowledgeTree(gameData) {
      let knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
      if (!knowledgeContainer) {
        const playArea = $('game_play_area');
        playArea.insertAdjacentHTML(
          'beforeend',
          `<div id="knowledge-container" class="dlid__container"><div class="board"><div class="selections"></div><div class="unlocked-tokens"></div></div></div>`,
        );
        renderImage(`knowledge-tree-${gameData.difficulty}`, document.querySelector('#knowledge-container .board'), {
          pos: 'insert',
          scale: 1.25,
        });
        knowledgeContainer = document.querySelector('#knowledge-container .unlocked-tokens');
      }

      const selections = document.querySelector(`#knowledge-container .selections`);
      selections.innerHTML = '';
      // Hindrance show new discoveries
      if (gameData.upgrades) {
        Object.keys(gameData.upgrades).forEach((unlockId) => {
          const unlockSpot = gameData.upgrades[unlockId].replace;
          if (unlockSpot) {
            const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[unlockSpot];
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
              renderImage(unlockId, this.tooltip.renderByElement(), {
                pos: 'insert',
                scale: 0.75,
              });
            });
          }
        });
      }

      knowledgeContainer.innerHTML = '';
      gameData.game.unlocks.forEach((unlockName) => {
        const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[unlockName];
        knowledgeContainer.insertAdjacentHTML(
          'beforeend',
          `<div id="knowledge-${unlockName}" class="fkp" style="top: ${y}px; left: ${x}px;"></div>`,
        );
        renderImage(`fkp-unlocked`, $(`knowledge-${unlockName}`), { scale: 2 });
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
      this.updateResources(args.args);
      switch (stateName) {
        case 'tooManyItems':
          if (isActive) this.tooManyItemsScreen.show(args.args);
          break;
        case 'startHindrance':
          this.upgradeSelectionScreen.show(args.args);
          break;
        case 'deckSelection':
          if (isActive) this.deckSelectionScreen.show(args.args);
          break;
        case 'resourceSelection':
          if (isActive) this.tokenScreen.show(args.args);
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
          this.selectedCharacters = args.args.characters;
          this.updateCharacterSelections(args.args);
          break;
        case 'playerTurn':
          this.updatePlayers(args.args);
          this.updateItems(args.args);
          this.updateKnowledgeTree(args.args);
          this.updateTrack(args.args);
          break;
        case 'tradePhase':
          this.itemTradeScreen.show(args.args);
          break;
        case 'confirmTradePhase':
        case 'waitTradePhase':
          this.itemTradeScreen.showConfirm(args.args);
          break;
        // case 'nightDrawCard':
        // case 'drawCard':
        //   if (!args.args.resolving) {
        //     this.decks[args.args.deck].drawCard(args.args.card.id);
        //     this.decks[args.args.deck].updateDeckCounts(args.args.decks[args.args.deck]);
        //   }
        //   break;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log('Leaving state: ' + stateName);
      switch (stateName) {
        case 'startHindrance':
          this.upgradeSelectionScreen.hide();
          break;
        case 'deckSelection':
          this.deckSelectionScreen.hide();
          break;
        case 'whichWeapon':
          this.weaponScreen.hide();
          break;
        case 'characterSelection':
          this.characterSelectionScreen.hide();
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
          this.itemTradeScreen.hide();
          break;
        case 'characterSelect':
          dojo.style('character-selector', 'display', 'none');
          dojo.style('game_play_area', 'display', '');
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    getActionSuffixHTML: function (action) {
      let suffix = '';
      if (action['character'] != null && !action['global']) suffix += ` (${action['character']})`;
      else if (action['characterId'] != null && !action['global']) suffix += ` (${action['characterId']})`;
      if (action['stamina'] != null) suffix += ` <i class="fa fa-bolt dlid__stamina"></i> ${action['stamina']}`;
      if (action['health'] != null) suffix += ` <i class="fa fa-heart dlid__health"></i> ${action['health']}`;
      if (action['unlockCost'] != null) suffix += ` <i class="fa fa-graduation-cap dlid__fkp"></i> ${action['unlockCost']}`;
      if (action['perDay'] != null)
        suffix += ` <i class="fa fa-sun-o dlid__sun"></i> ` + _('${remaining} left').replace(/\$\{remaining\}/, action['perDay']);
      if (action['perForever'] != null)
        suffix +=
          ` <i class="fa fa-circle-o-notch dlid__forever"></i> ` + _('${remaining} left').replace(/\$\{remaining\}/, action['perForever']);
      return suffix;
    },
    onUpdateActionButtons: function (stateName, args) {
      const actions = args?.actions;
      // this.currentActions = actions;
      console.log('onUpdateActionButtons', args, actions, stateName);
      this.updateResources(args);
      const isActive = this.isCurrentPlayerActive();
      if (isActive && stateName && actions != null) {
        this.removeActionButtons();

        // Add test action buttons in the action status bar, simulating a card click:
        if (actions)
          actions
            .sort((a, b) => (a?.stamina ?? 9) - (b?.stamina ?? 9))
            .forEach((action) => {
              const actionId = action.action;
              if (['interrupt', 'postEncounter', 'dayEvent'].includes(stateName)) {
                if (actionId === 'actUseSkill' || actionId === 'actUseItem') {
                  return (actionId === 'actUseSkill' ? args.availableSkills : args.availableItemSkills)?.forEach((skill) => {
                    const suffix = this.getActionSuffixHTML(skill);
                    this.statusBar.addActionButton(`${skill.name}${suffix}`, () => {
                      return this.bgaPerformAction(actionId, { skillId: skill.id });
                    });
                  });
                }
              }
              const suffix = this.getActionSuffixHTML(action);
              return this.statusBar.addActionButton(`${this.actionMappings[actionId]}${suffix}`, () => {
                if (actionId === 'actSpendFKP') {
                  this.removeActionButtons();
                  Object.values(args.availableUnlocks).forEach((unlock) => {
                    const suffix = this.getActionSuffixHTML(unlock);
                    this.statusBar.addActionButton(`${unlock.name}${suffix}`, () => {
                      return this.bgaPerformAction(actionId, { knowledgeId: unlock.id });
                    });
                  });
                  this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                } else if (actionId === 'actUseSkill' || actionId === 'actUseItem') {
                  this.removeActionButtons();
                  Object.values(actionId === 'actUseSkill' ? args.availableSkills : args.availableItemSkills).forEach((skill) => {
                    const suffix = this.getActionSuffixHTML(skill);
                    this.statusBar.addActionButton(`${skill.name}${suffix}`, () => {
                      return this.bgaPerformAction(actionId, { skillId: skill.id });
                    });
                  });
                  this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                } else if (actionId === 'actTrade') {
                  this.removeActionButtons();
                  this.tradeScreen.show(args);
                  this.statusBar.addActionButton(this.actionMappings.actTradeItem + `${suffix}`, () => {
                    if (!this.tradeScreen.hasError()) {
                      this.bgaPerformAction('actTrade', {
                        data: JSON.stringify({
                          offered: this.tradeScreen.getOffered(),
                          requested: this.tradeScreen.getRequested(),
                        }),
                      })
                        .then(() => this.tradeScreen.hide())
                        .catch(console.error);
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
                } else if (actionId === 'actTradeItem') {
                  // if (!this.itemTradeScreen.hasError()) {
                  this.bgaPerformAction('actTradeItem', {
                    data: JSON.stringify(this.itemTradeScreen.getTrade()),
                  }).catch(console.error);
                  // .then(() => this.itemTradeScreen.hide())
                  // .catch(console.error);
                  // }
                } else if (actionId === 'actCraft') {
                  this.removeActionButtons();
                  this.craftScreen.show(args);
                  this.statusBar.addActionButton(this.actionMappings.actCraft + `${suffix}`, () => {
                    if (!this.craftScreen.hasError()) {
                      this.bgaPerformAction('actCraft', {
                        itemName: this.craftScreen.getSelectedId(),
                      })
                        .then(() => {
                          this.craftScreen.hide();
                        })
                        .catch(console.error);
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
                  this.removeActionButtons();
                  this.cookScreen.show(args);
                  this.statusBar.addActionButton(this.actionMappings.actEat + `${suffix}`, () => {
                    if (!this.cookScreen.hasError()) {
                      this.bgaPerformAction('actCook', {
                        resourceType: this.cookScreen.getSelectedId(),
                      })
                        .then(() => {
                          this.cookScreen.hide();
                        })
                        .catch(console.error);
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
                  this.removeActionButtons();
                  this.reviveScreen.show(args);
                  this.statusBar.addActionButton(this.actionMappings.actRevive + `${suffix}`, () => {
                    if (!this.reviveScreen.hasError()) {
                      const { characterSelected, foodSelected } = this.reviveScreen.getSelected();
                      this.bgaPerformAction('actRevive', {
                        character: characterSelected,
                        food: foodSelected,
                      })
                        .then(() => {
                          this.reviveScreen.hide();
                        })
                        .catch(console.error);
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
                }
                // else if (actionId === 'actUseHerb') {
                //   this.removeActionButtons();
                //   this.eatScreen.show(args);
                //   this.statusBar.addActionButton(this.actionMappings.actUseHerb + `${suffix}`, () => {
                //     if (!this.eatScreen.hasError()) {
                //       this.bgaPerformAction('actEat', {
                //         resourceType: this.eatScreen.getSelectedId(),
                //       })
                //         .then(() => {
                //           this.eatScreen.hide();
                //         })
                //         .catch(console.error);
                //     }
                //   });
                //   this.statusBar.addActionButton(
                //     _('Cancel'),
                //     () => {
                //       this.onUpdateActionButtons(stateName, args);
                //       this.eatScreen.hide();
                //     },
                //     { color: 'secondary' },
                //   );
                // }
                else if (actionId === 'actEat') {
                  this.removeActionButtons();
                  this.eatScreen.show(args);
                  this.statusBar.addActionButton(_('Eat') + `${suffix}`, () => {
                    if (!this.eatScreen.hasError()) {
                      this.bgaPerformAction('actEat', {
                        resourceType: this.eatScreen.getSelectedId(),
                      })
                        .then(() => {
                          this.eatScreen.hide();
                        })
                        .catch(console.error);
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
                } else if (actionId === 'actInvestigateFire' && args.activeCharacter === 'Cali') {
                  this.removeActionButtons();
                  [0, 1, 2, 3].forEach((i) => {
                    this.statusBar.addActionButton(`${_('Guess')} ${i}`, () => {
                      return this.bgaPerformAction(actionId, { guess: i });
                    });
                  });
                  this.statusBar.addActionButton(_('Cancel'), () => this.onUpdateActionButtons(stateName, args), { color: 'secondary' });
                } else {
                  return this.bgaPerformAction(actionId);
                }
              });
            });
        switch (stateName) {
          case 'startHindrance':
            this.statusBar.addActionButton(_('Done'), () => {
              this.bgaPerformAction('actDone').then(() => this.upgradeSelectionScreen.hide());
            });
            break;
          case 'deckSelection':
            this.statusBar.addActionButton(_('Select Deck'), () => {
              this.bgaPerformAction('actSelectDeck', { deckName: this.deckSelectionScreen.getSelectedId() }).then(() =>
                this.deckSelectionScreen.hide(),
              );
            });
            this.statusBar.addActionButton(
              _('Cancel'),
              () => {
                this.bgaPerformAction('actSelectDeckCancel').then(() => this.deckSelectionScreen.hide());
              },
              { color: 'secondary' },
            );
            break;
          case 'hindranceSelection':
            this.statusBar.addActionButton(args.hindranceSelection?.button ?? _('Remove Hindrance'), () => {
              this.bgaPerformAction('actSelectHindrance', { data: JSON.stringify(this.hindranceSelectionScreen.getSelected()) }).then(() =>
                this.hindranceSelectionScreen.hide(),
              );
            });
            this.statusBar.addActionButton(
              _('Cancel'),
              () => {
                this.bgaPerformAction('actSelectHindranceCancel').then(() => this.hindranceSelectionScreen.hide());
              },
              { color: 'secondary' },
            );
            break;
          case 'characterSelection':
            this.statusBar.addActionButton(this.actionMappings.actSelectCharacter, () => {
              this.bgaPerformAction('actSelectCharacter', { characterId: this.characterSelectionScreen.getSelectedId() }).then(() =>
                this.characterSelectionScreen.hide(),
              );
            });
            if (args.cancellable !== false)
              this.statusBar.addActionButton(
                _('Cancel'),
                () => {
                  this.bgaPerformAction('actSelectCharacterCancel').then(() => this.selector.hide());
                },
                { color: 'secondary' },
              );
            break;
          case 'cardSelection':
            this.statusBar.addActionButton(this.actionMappings.actSelectCard, () => {
              this.bgaPerformAction('actSelectCard', { cardId: this.cardSelectionScreen.getSelectedId() }).then(() =>
                this.cardSelectionScreen.hide(),
              );
            });
            if (args.cancellable !== false)
              this.statusBar.addActionButton(
                _('Cancel'),
                () => {
                  this.bgaPerformAction('actSelectCardCancel').then(() => this.selector.hide());
                },
                { color: 'secondary' },
              );
            break;
          case 'resourceSelection':
            this.statusBar.addActionButton(_('Select Resource'), () => {
              this.bgaPerformAction('actSelectResource', { resourceType: this.tokenScreen.getSelectedId() }).then(() =>
                this.tokenScreen.hide(),
              );
            });
            if (args.cancellable !== false)
              this.statusBar.addActionButton(
                _('Cancel'),
                () => {
                  this.bgaPerformAction('actSelectResourceCancel').then(() => this.selector.hide());
                },
                { color: 'secondary' },
              );
            break;
          case 'tooManyItems':
            this.statusBar.addActionButton(_('Send To Camp'), () => {
              this.bgaPerformAction('actSendToCamp', { sendToCampId: this.tooManyItemsScreen.getSelectedId() }).then(() =>
                this.tooManyItemsScreen.hide(),
              );
            });
            break;
          case 'whichWeapon':
            this.statusBar.addActionButton(_('Confirm'), () =>
              this.bgaPerformAction('actChooseWeapon', { weaponId: this.weaponScreen.getSelectedId() }).then(() =>
                this.weaponScreen.hide(),
              ),
            );
            break;
          case 'tradePhaseActions':
            this.statusBar.addActionButton(_('Pass'), () => this.bgaPerformAction('actTradeDone'), { color: 'secondary' });
            break;
          case 'confirmTradePhase':
            this.statusBar.addActionButton(_('Cancel'), () => this.bgaPerformAction('actCancelTrade'), { color: 'secondary' });
            break;
          case 'interrupt':
            this.statusBar.addActionButton(_('Skip'), () => this.bgaPerformAction('actDone'), { color: 'secondary' });
            break;
          case 'dayEvent':
            // No Cancel Button
            break;
          case 'dinnerPhase':
          case 'dinnerPhasePrivate':
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
      dojo.subscribe('characterClicked', this, 'notification_characterClicked');
      dojo.subscribe('updateGameData', this, 'notification_updateGameData');
      // Example 1: standard notification handling
      // dojo.subscribe( 'tokenUsed', this, "notification_tokenUsed" );

      // Example 2: standard notification handling + tell the user interface to wait
      //            during 3 seconds after calling the method in order to let the players
      //            see what is happening in the game.

      dojo.subscribe('activeCharacter', this, 'notification_tokenUsed');
      dojo.subscribe('tokenUsed', this, 'notification_tokenUsed');
      dojo.subscribe('shuffle', this, 'notification_shuffle');
      dojo.subscribe('cardDrawn', this, 'notification_cardDrawn');
      dojo.subscribe('rollFireDie', this, 'notification_rollFireDie');
      this.notifqueue.setSynchronous('cardDrawn', 1000);
      this.notifqueue.setSynchronous('rollFireDie', 1000);
      this.notifqueue.setSynchronous('shuffle', 1500);
    },
    notificationWrapper: function (notification) {
      notification.args = notification.args ?? {};
      if (notification.args.gameData) {
        notification.args.gameData.gamestate = notification.args.gamestate;
      }
      this.updateResources(notification.args.gameData);
    },
    notification_rollFireDie: async function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_rollFireDie', notification);
      return this.dice.roll(notification.args.roll);
    },
    notification_cardDrawn: async function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_cardDrawn', notification);
      this.decks[notification.args.deck].updateDeckCounts(notification.args.decks[notification.args.deck]);
      return this.decks[notification.args.deck].drawCard(notification.args.card.id, notification.args.partial);
    },
    notification_shuffle: async function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_shuffle', notification);
      this.decks[notification.args.deck].updateDeckCounts(notification.args.decks[notification.args.deck]);
      return this.decks[notification.args.deck].shuffle();
    },
    notification_updateGameData: function (notification) {
      this.notificationWrapper(notification);
      console.log('notification_updateGameData', notification);
      this.updatePlayers(notification.args.gameData);
      this.updateItems(notification.args.gameData);
      this.updateKnowledgeTree(notification.args.gameData);
      if (notification.args?.gamestate?.name) this.onUpdateActionButtons(notification.args.gamestate.name, notification.args.gameData);
      if (notification.args?.gamestate?.name == 'tradePhase') this.itemTradeScreen.update(notification.args);
      if (notification.args?.gamestate?.name == 'startHindrance') this.upgradeSelectionScreen.update(notification.args.gameData);
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
      this.updateItems(notification.args.gameData);
      this.updateKnowledgeTree(notification.args.gameData);
      if (notification.args?.gamestate?.name) this.onUpdateActionButtons(notification.args.gamestate.name, notification.args.gameData);
    },
  });
});
