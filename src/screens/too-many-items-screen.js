class TooManyItemsScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  getSelectedId() {
    return this.itemSelected;
  }
  hasError() {
    return this.error;
  }
  hide() {
    this.game.selector.hide('tooManyItems');
  }
  show(gameData) {
    this.itemSelected = null;
    let tmiElem = document.querySelector(`#tmi-items .items`);
    if (!tmiElem) {
      this.game.selector.show('tooManyItems');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="too-many-items-screen" class="dlid__container">
            <div class="error"></div>
            <div id="tmi-items" class="dlid__container"><h3>${_('Select 1 to Send To Camp')}</h3><div class="items"></div></div>
        </div>`,
      );
      tmiElem = document.querySelector(`#tmi-items .items`);
    }
    tmiElem.innerHTML = '';
    const renderItem = (name, itemId, elem, selectCallback) => {
      elem.insertAdjacentHTML('beforeend', `<div class="token id${itemId}"></div>`);
      renderImage(name, document.querySelector(`#too-many-items-screen .token.id${itemId}`), { scale: 1, pos: 'insert' });
      addClickListener(
        document.querySelector(`#too-many-items-screen .token.id${itemId}`),
        this.game.data[name].options.name,
        selectCallback,
      );
    };
    gameData.items.forEach(({ itemId, name }) => {
      renderItem(name, itemId, tmiElem, () => {
        if (this.itemSelected) {
          document.querySelector(`#too-many-items-screen .token.id${this.itemSelected} .card`).style['outline'] = '';
        }
        this.itemSelected = itemId;
        if (this.itemSelected) {
          document.querySelector(`#too-many-items-screen .token.id${itemId} .card`).style['outline'] = `5px solid #fff`;
        }
      });
    });
  }
}
