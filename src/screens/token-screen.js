class TokenScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  getSelectedId() {
    return this.tokenSelected;
  }
  hasError() {
    return this.error;
  }
  show(gameData) {
    let tokenElem = document.querySelector(`#resource .tokens`);
    if (!tokenElem) {
      this.game.selector.show();
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="token-screen" class="dlid__container">
            <div class="error"></div>
            <div id="resource" class="dlid__container"><h3>${_('Your Resources')}</h3><div class="tokens"></div></div>
        </div>`,
      );
      tokenElem = document.querySelector(`#resource .tokens`);
    }
    tokenElem.innerHTML = '';
    const renderItem = (name, elem, count, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <div class="token ${name}"><div class="counter dot">${count()}</div></div>
            <div>`,
      );
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), this.game.data[name].options.name, () => selectCallback(count));
    };
    Object.keys(gameData.tokenSelection).forEach((name) => {
      if (gameData.tokenSelection[name])
        renderItem(
          name,
          tokenElem,
          () => gameData.game.resources[name],
          (count) => {
            tokenElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = count();
            if (this.tokenSelected) {
              document.querySelector(`#token-screen .token.${this.tokenSelected} .card`).style['outline'] = '';
            }
            this.tokenSelected = name;
            if (this.tokenSelected) {
              document.querySelector(`#token-screen .token.${name} .card`).style['outline'] = `5px solid #fff`;
            }
          },
        );
    });
  }
}
