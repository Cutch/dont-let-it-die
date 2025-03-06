class TooManyItemsScreen {
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
    const { y, height } = this.tmiElem.getBoundingClientRect();
    this.arrowElem.style['top'] = `calc(${Math.max(
      0,
      window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
    )}px / var(--bga-game-zoom, 1))`;
    this.arrowElem.style['display'] =
      Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
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
            <div id="tmi-items" class="dlid__container"><h3>${_('Select 1 to Send To Camp')}</h3><div class="items"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      tmiElem = document.querySelector(`#tmi-items .items`);
      this.tmiElem = tmiElem;
      this.arrowElem = document.querySelector(`#too-many-items-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
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
