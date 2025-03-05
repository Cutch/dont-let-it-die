class EatScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  getSelectedId() {
    return this.foodSelected;
  }
  hasError() {
    return this.error;
  }
  scroll() {
    const { y, height } = this.eatElem.getBoundingClientRect();
    this.arrowElem.style['top'] = `calc(${Math.max(
      0,
      window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
    )}px / var(--bga-game-zoom, 1))`;
    this.arrowElem.style['display'] =
      Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
  }
  hide() {
    this.game.selector.hide('eat');
  }
  show(gameData) {
    this.foodSelected = null;
    if (gameData.characters && !gameData.characters.some((d) => d.name === 'Sig')) {
      gameData.eatableFoods = gameData.eatableFoods.filter((d) => !d['id'].includes('fish'));
    }
    let eatElem = document.querySelector(`#eat-resource .tokens`);
    if (!eatElem) {
      this.resourceSelected = this.game.resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = this.game.resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show('eat');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="eat-screen" class="dlid__container">
              <div class="error"></div>
              <div id="eat-resource" class="dlid__container"><h3>${_('Food')}</h3><div class="tokens"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
          </div>`,
      );
      eatElem = document.querySelector(`#eat-resource .tokens`);
      this.eatElem = eatElem;
      this.arrowElem = document.querySelector(`#eat-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    eatElem.innerHTML = '';
    const renderResource = (food, elem, selectCallback) => {
      const available = gameData.game.resources[food['id']];
      const requires = food['count'];
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-block ${food['id']}">
            <div class="name">${allSprites[food['id']].options.name}</div>
            <div class="available line"><span class="label">Available: </span><span class="value">${available}</span></div>
            <div class="requires line"><span class="label">Requires: </span><span class="value">${requires}</span></div>
            <div class="health line ${food['health'] ? '' : 'hidden'}"><span class="label">Health: </span><span class="value">${
          food['health']
        } <i class="fa fa-heart"></i></span></div>
            <div class="stamina line ${food['stamina'] ? '' : 'hidden'}"><span class="label">Stamina: </span><span class="value">${
          food['stamina']
        } <i class="fa fa-bolt"></i></span></div>
            <div class="margin"></div>
            <div class="token ${food['id']}"></div>
        <div>`,
      );
      renderImage(food['id'], elem.querySelector(`.token.${food['id']}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token-block.${food['id']}`), this.game.data[food['id']].options.name, () => selectCallback());
    };
    gameData.eatableFoods.forEach((food, i) => {
      const available = gameData.game.resources[food['id']];
      const requires = food['count'];
      if (available > requires) {
        renderResource(food, eatElem, () => {
          if (this.foodSelected) {
            document.querySelector(`#eat-screen .token-block.${this.foodSelected}`).style['outline'] = '';
          }
          this.foodSelected = food['id'];
          if (this.foodSelected) {
            document.querySelector(`#eat-screen .token-block.${food['id']}`).style['outline'] = `5px solid #fff`;
          }
        });
      }
    });
  }
}
