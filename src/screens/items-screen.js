class ItemsScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.itemSelected;
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
  show(gameData) {
    this.itemSelected = null;
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
    const renderItem = (name, itemId, elem, selectCallback) => {
      elem.insertAdjacentHTML('beforeend', `<div class="token id${itemId}"></div>`);
      renderImage(name, document.querySelector(`#items-screen .token.id${itemId}`), { scale: 1, pos: 'insert' });
      addClickListener(document.querySelector(`#items-screen .token.id${itemId}`), this.game.data[name].options.name, selectCallback);
    };
    gameData.selectionState.items.forEach(({ itemId, name }) => {
      renderItem(name, itemId, isElem, () => {
        if (this.itemSelected) {
          document.querySelector(`#items-screen .token.id${this.itemSelected} .card`).style['outline'] = '';
        }
        this.itemSelected = itemId;
        if (this.itemSelected) {
          document.querySelector(`#items-screen .token.id${itemId} .card`).style['outline'] = `5px solid #fff`;
        }
      });
    });
    this.scroll();
  }
}
