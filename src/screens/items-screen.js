import '../utils/index';
export class ItemsScreen {
  constructor(game) {
    this.game = game;
  }
  getSelection() {
    return this.selection;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.isElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('itemsScreen');
  }
  renderCharacter(container, itemId, characterId) {
    container.insertAdjacentHTML('beforeend', `<div class="character-image"></div>`);
    renderImage(characterId, container.querySelector(`.character-image`), {
      scale: 3,
      overridePos: {
        x: 0.2,
        y: 0.16,
        w: 0.8,
        h: 0.45,
      },
    });
    addClickListener(container.querySelector(`.character-image`), characterId, () => {
      this.game.tooltip.show();
      renderImage(characterId, this.game.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
    });
  }
  show(gameData) {
    this.selection = null;
    let isElem = document.querySelector(`#is-items .items`);
    if (!isElem) {
      this.game.selector.show('itemsScreen');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="items-screen" class="dlid__container">
            <div id="is-items" class="dlid__container"><h3>${_(
              gameData.selectionState.title ?? 'Select Item',
            )}</h3><div class="items"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      isElem = document.querySelector(`#is-items .items`);
      this.isElem = isElem;
      this.arrowElem = document.querySelector(`#items-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    isElem.innerHTML = '';
    const renderItem = (name, itemId, characterId, elem, selectCallback) => {
      elem.insertAdjacentHTML('beforeend', `<div class="token id${itemId}"></div>`);
      if (characterId) this.renderCharacter(document.querySelector(`#items-screen .token.id${itemId}`), itemId, characterId);
      renderImage(name, document.querySelector(`#items-screen .token.id${itemId}`), { scale: 1.5, pos: 'append' });
      addClickListener(document.querySelector(`#items-screen .token.id${itemId}`), this.game.data[name].options.name, selectCallback);
    };
    gameData.selectionState.items.forEach(({ itemId, name, characterId }) => {
      renderItem(name, itemId, characterId, isElem, () => {
        if (this.selection) {
          document.querySelector(`#items-screen .token.id${this.selection.itemId} .items-card`).style['outline'] = '';
        }
        this.selection = { itemId, characterId };
        if (this.selection) {
          document.querySelector(`#items-screen .token.id${itemId} .items-card`).style['outline'] = `5px solid #fff`;
        }
      });
    });
    this.scroll();
  }
}
