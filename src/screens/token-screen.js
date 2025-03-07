class TokenScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.tokenSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.tokenElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('tokens');
  }
  show(gameData) {
    this.tokenSelected = null;
    let tokenElem = document.querySelector(`#resource .tokens`);
    if (!tokenElem) {
      this.game.selector.show('tokens');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="token-screen" class="dlid__container">
            <div id="resource" class="dlid__container"><h3>${_(gameData?.title ?? 'Your Resources')}</h3><div class="tokens"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      tokenElem = document.querySelector(`#resource .tokens`);
      this.tokenElem = tokenElem;
      this.arrowElem = document.querySelector(`#token-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
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
          () => gameData.tokenSelection[name],
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
    this.scroll();
  }
}
