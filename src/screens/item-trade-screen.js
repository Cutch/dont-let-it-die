import { getAllData } from '../assets';
import { addClickListener, addPassiveListener, renderImage, scrollArrow, Tweening } from '../utils/index';
export class ItemTradeScreen {
  constructor(game) {
    this.game = game;
    this.selection = [];
    this.scale = 3;
  }
  getTrade() {
    return { selection: this.selection.map(({ character, equipment }) => ({ character: character?.name, itemId: equipment?.itemId })) };
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.itemTradeContent, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('item-trade');
  }
  toggleSelection(query, selectable) {
    const doc = document.querySelector(query);
    if (selectable) {
      if (!doc.classList.contains('selected')) doc.classList.add('selected');
    } else if (doc.classList.contains('selected')) doc.classList.remove('selected');
  }
  clearSelection() {
    this.selection = [];
    document.querySelectorAll('#item-trade-screen .selected').forEach((d) => {
      d.classList.remove('selected');
    });
  }
  updateSelection(character, equipment) {
    const query = `.character-${character?.name ?? null}.item-${equipment?.itemId ?? null}`;
    const index = this.selection.findIndex((d) => d.query === query);
    if (index == -1) {
      // Check if we should remove the same characters item
      const removeIndex = this.selection.findIndex((d) => d.query.includes(`.character-${character?.name ?? null}`));
      if (removeIndex != -1) {
        const [{ query: oldQuery }] = this.selection.splice(removeIndex, 1);
        this.toggleSelection(oldQuery, false);
      }

      // Add new item
      this.selection.push({ character, equipment, query });
      this.toggleSelection(query, true);
      // Remove the last selected item
      if (this.selection.length > 2) {
        const [{ query: oldQuery }] = this.selection.splice(1, 1);
        this.toggleSelection(oldQuery, false);
      }
    } else {
      // Remove old item
      this.selection.splice(index, 1);
      this.toggleSelection(query, false);
    }
  }
  update({ itemId1, itemId2, itemName1, itemName2, character1, character2, gameData }) {
    this.clearSelection();
    const tween = new Tweening(this.game, document.querySelector(`#item-trade-screen`));
    this.show(gameData);
    const findParentImageContainer = (elem) => {
      if (elem.parentNode.classList.contains('tooltip-image-and-text')) return elem.parentNode;
      return elem;
    };
    setTimeout(() => {
      if (itemName1)
        tween.addDestroyTween(
          findParentImageContainer(document.querySelector(`.character-${character1 ?? null}.item-${itemId1 ?? null}`)),
          findParentImageContainer(document.querySelector(`.character-${character2 ?? null}.item-${itemId1 ?? null}`)),
        );
      if (itemName2)
        tween.addDestroyTween(
          findParentImageContainer(document.querySelector(`.character-${character2 ?? null}.item-${itemId2 ?? null}`)),
          findParentImageContainer(document.querySelector(`.character-${character1 ?? null}.item-${itemId2 ?? null}`)),
        );
    }, 0);
  }
  getElem({ character, itemId }) {
    if (character) {
      if (itemId == null) {
        return document.querySelector(`#item-trade-screen__${character.id} .items .empty`);
      } else {
        return document.querySelector(`#item-trade-screen__${character.id} .items .item-${itemId}`);
      }
    } else {
      if (itemId == null) {
        return document.querySelector(`#item-trade-screen__camp .items .empty`);
      } else {
        return document.querySelector(`#item-trade-screen__camp .items .item-${itemId}`);
      }
    }
  }
  showConfirm(gameData) {
    this.clearSelection();
    this.getElem(gameData.trade1).classList.add('selected');
    this.getElem(gameData.trade2).classList.add('selected');
  }
  show(gameData) {
    this.itemSelected = null;
    const {
      frame: { w, h },
    } = getAllData()['item-back'];
    const scaledWidth = Math.round(w / this.scale);
    const scaledHeight = Math.round(h / this.scale);

    let itemTradeElem = document.querySelector(`#item-trade-screen .items`);
    // Initial setup
    if (!itemTradeElem) {
      this.game.selector.show('item-trade');
      this.game.selector.renderByElement().insertAdjacentHTML(
        // gameui.gamedatas.players[gameui.player_id].color
        'beforeend',
        `<div id="item-trade-screen" class="dlid__container" style="--player-color:#${'000'}">
            <div id="item-trade-screen-content">
              <div id="item-trade-screen__camp"><div id="item-trade-screen__camp" class="dlid__container"><h3>${_(
                'Camp Items',
              )}</h3><div class="items"></div></div></div>
            </div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      // <div id="item-trade-screen__${gameui.player_id}" class="player-item-container"></div>
      this.itemTradeContent = document.querySelector(`#item-trade-screen-content`);

      itemTradeElem = document.querySelector(`#item-trade-screen`);
      this.itemTradeElem = itemTradeElem;
      this.arrowElem = document.querySelector(`#item-trade-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());

      // const playerIds = Array.from(new Set(gameData.characters.map((d) => d.playerId)));
      // Add the other player containers
      // playerIds
      //   .filter((playerId) => playerId != gameui.player_id)
      //   .forEach((playerId) => {
      //     this.itemTradeContent.insertAdjacentHTML(
      //       'beforeend',
      //       `<div id="item-trade-screen__${playerId}" class="player-item-container"></div>`,
      //     );
      //   });
      // Add header and character image
      gameData.characters.forEach((character) => {
        const container = document.querySelector(`#item-trade-screen-content`);
        container.insertAdjacentHTML(
          'beforeend',
          `<div id="item-trade-screen__${character.name}" class="dlid__container"><div class="character-image"></div><h3>${character.name}</h3><div class="items"></div></div>`,
        );
        renderImage(character.name, document.querySelector(`#item-trade-screen__${character.name} .character-image`), {
          scale: 3,
          baseCss: 'base-image',
          overridePos: {
            x: 0.2,
            y: 0.16,
            w: 0.8,
            h: 0.45,
          },
        });
        addClickListener(document.querySelector(`#item-trade-screen__${character.name} .character-image`), character.name, () => {
          this.game.tooltip.show();
          renderImage(character.name, this.game.tooltip.renderByElement(), { withText: true, type: 'tooltip-character', pos: 'replace' });
        });
      });
    }
    // Characters
    gameData.characters.forEach((character) => {
      const itemsElem = document.querySelector(`#item-trade-screen__${character.name} .items`);
      // itemsElem.innerHTML = '';
      character.equipment.forEach((equipment) => {
        if (!itemsElem.querySelector(`.item-${equipment.itemId}`)) {
          renderImage(equipment.id, itemsElem, {
            scale: this.scale,
            pos: 'insert',
            css: `character-${character.name} item-${equipment.itemId}`,
            baseCss: 'base-image',
          });
          this.game.addHelpTooltip({
            node: itemsElem.querySelector(`.item-${equipment.itemId}`),
            tooltipText: equipment.id,
          });
          addClickListener(itemsElem.querySelector(`.item-${equipment.itemId}`), equipment.name, () => {
            this.updateSelection(character, equipment);
          });
        }
        if (!itemsElem.querySelector(`.item-${equipment.itemId} .last-item-owner`)) {
          const characterName = this.game.gamedatas.lastItemOwners?.[equipment.itemId];

          if (characterName) {
            renderImage(characterName, itemsElem.querySelector(`.item-${equipment.itemId}`), {
              scale: 4,
              css: 'last-item-owner',
              overridePos: {
                x: 0.3,
                y: 0.16,
                w: 0.7,
                h: 0.4,
              },
            });
          }
        }
      });
      // });
      // gameData.characters.forEach((character) => {
      if (!itemsElem.querySelector(`#item-trade-screen__${character.name} .items .empty`)) {
        itemsElem.insertAdjacentHTML(
          'beforeend',
          `<div class="empty card character-${character.name} item-null" style="width: ${scaledWidth}px;height: ${scaledHeight}px;">
        <div style="margin-bottom: 0.5rem">${_('Give')}</div>
        <div>${_('Weapon Slots')}: ${(character.slotsAllowed.weapon ?? 0) - (character.slotsUsed.weapon ?? 0)}</div>
        <div>${_('Tool Slots')}: ${(character.slotsAllowed.tool ?? 0) - (character.slotsUsed.tool ?? 0)}</div>
        </div>`,
        );
        addClickListener(document.querySelector(`#item-trade-screen__${character.name} .items .empty`), _('Give'), () => {
          this.updateSelection(character, null);
        });
      }
    });

    // Camp
    if (true) {
      const itemsElem = document.querySelector(`#item-trade-screen__camp .items`);
      // itemsElem.innerHTML = '';
      gameData.campEquipment.forEach((equipment) => {
        if (!itemsElem.querySelector(`.item-${equipment.itemId}`)) {
          renderImage(equipment.name, itemsElem, {
            scale: this.scale,
            pos: 'insert',
            css: `character-null item-${equipment.itemId}`,
            baseCss: 'base-image',
          });
          this.game.addHelpTooltip({
            node: itemsElem.querySelector(`.item-${equipment.itemId}`),
            tooltipText: equipment.name,
          });
          addClickListener(itemsElem.querySelector(`.item-${equipment.itemId}`), equipment.name, () => {
            this.updateSelection(null, equipment);
          });
        }

        if (!itemsElem.querySelector(`.item-${equipment.itemId} .last-item-owner`)) {
          const characterName = this.game.gamedatas.lastItemOwners?.[equipment.itemId];

          if (characterName) {
            renderImage(characterName, itemsElem.querySelector(`.item-${equipment.itemId}`), {
              scale: 4,
              css: 'last-item-owner',
              overridePos: {
                x: 0.3,
                y: 0.16,
                w: 0.7,
                h: 0.4,
              },
            });
          }
        }
      });
      if (!document.querySelector(`#item-trade-screen__camp .items .empty`)) {
        itemsElem.insertAdjacentHTML(
          'beforeend',
          `<div class="empty card character-null item-null" style="width: ${scaledWidth}px;height: ${scaledHeight}px;">${_(
            'Send to Camp',
          )}</div>`,
        );
        addClickListener(document.querySelector(`#item-trade-screen__camp .items .empty`), _('Send to Camp'), () => {
          this.updateSelection(null, null);
        });
      }
    }
    this.scroll();
  }
}
