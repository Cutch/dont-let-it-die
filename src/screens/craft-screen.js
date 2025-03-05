class CraftScreen {
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
    this.game.selector.hide('craft');
    if (this.cleanup) this.cleanup();
  }
  scroll() {
    const { y, height } = document.querySelector(`#craft-items .items`).getBoundingClientRect();
    this.arrowElem.style['top'] = `calc(${Math.max(
      0,
      window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
    )}px / var(--bga-game-zoom, 1))`;
    this.arrowElem.style['display'] =
      Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
  }
  show(gameData) {
    this.itemSelected = null;
    let craftElem = document.querySelector(`#craft-items .items`);
    if (!craftElem) {
      this.game.selector.show('craft');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="craft-screen" class="dlid__container">
            <div class="error"></div>
            <div id="craft-items" class="dlid__container"><h3>${_('Craftable Items')}</h3><div class="items"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      craftElem = document.querySelector(`#craft-items .items`);
      this.craftElem = craftElem;
      this.arrowElem = document.querySelector(`#craft-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    craftElem.innerHTML = '';
    const renderItem = (name, elem, count, selectCallback) => {
      const hasCost = gameData.availableEquipmentWithCost.includes(name);
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <div class="token ${name}"><div class="counter dot">${count()}</div></div>
            <div>`,
      );
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 1, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), this.game.data[name].options.name, () => selectCallback(count));
      if (hasCost) document.querySelector(`#craft-screen .token.${name}`).classList.remove('disabled');
      else document.querySelector(`#craft-screen .token.${name}`).classList.add('disabled');
    };
    Object.keys(gameData.availableEquipment).forEach((name) => {
      renderItem(
        name,
        craftElem,
        () => gameData.availableEquipment[name],
        (count) => {
          craftElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = count();

          if (this.itemSelected) {
            document.querySelector(`#craft-screen .token.${this.itemSelected} .card`).style['outline'] = '';
          }
          this.itemSelected = name;
          if (this.itemSelected) {
            document.querySelector(`#craft-screen .token.${name} .card`).style['outline'] = `5px solid #fff`;
          }
        },
      );
    });
  }
}
