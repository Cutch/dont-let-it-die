class TooManyItemsScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  getSelectedId() {
    return this.itemSelected.replace(/[0-9]$/, '');
  }
  hasError() {
    return this.error;
  }
  show(gameData) {
    console.log(gameData);
    let tmiElem = document.querySelector(`#tmi-items .items`);
    if (!tmiElem) {
      this.game.selector.show();
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
    const renderItem = (name, i, elem, selectCallback) => {
      elem.insertAdjacentHTML('beforeend', `<div class="token ${name + i}"></div>`);
      renderImage(name, document.querySelector(`#too-many-items-screen .token.${name + i}`), { scale: 2, pos: 'insert' });
      addClickListener(
        document.querySelector(`#too-many-items-screen .token.${name + i}`),
        this.game.data[name].options.name,
        selectCallback,
      );
    };
    gameData.items.forEach((name, i) => {
      renderItem(name, i, tmiElem, () => {
        if (this.itemSelected) {
          document.querySelector(`#too-many-items-screen .token.${this.itemSelected} .card`).style['outline'] = '';
        }
        this.itemSelected = name + i;
        if (this.itemSelected) {
          document.querySelector(`#too-many-items-screen .token.${name + i} .card`).style['outline'] = `5px solid #fff`;
        }
      });
    });
  }
}
