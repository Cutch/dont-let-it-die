class CraftScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  setError() {
    // const resourceCount = Object.keys(this.itemSelected).reduce((a, name) => a + this.itemSelected[name], 0);
    // const requestedCount = Object.keys(this.resourceRequested).reduce((a, name) => a + this.resourceRequested[name], 0);
    // const error = document.querySelector(`#craft-screen .error`);
    // if (resourceCount < this.craftRatio) {
    //   error.innerHTML = _('Select ${craftRatio} of your resources').replace('${craftRatio}', this.craftRatio);
    //   error.style.visibility = '';
    //   this.error = true;
    // } else if (requestedCount != 1) {
    //   error.innerHTML = _('Select ${requestedCount} to craft for').replace('${requestedCount}', 1);
    //   error.style.visibility = '';
    //   this.error = true;
    // } else {
    //   error.style.visibility = 'hidden';
    //   error.innerHTML = '';
    //   this.error = false;
    // }
  }
  getSelectedId() {
    return this.itemSelected;
  }
  hasError() {
    return this.error;
  }
  show(gameData) {
    let craftElem = document.querySelector(`#craft-items .items`);
    if (!craftElem) {
      this.game.selector.show();
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="craft-screen" class="dlid__container">
            <div class="error"></div>
            <div id="craft-items" class="dlid__container"><h3>${_('Your Resources')}</h3><div class="items"></div></div>
        </div>`,
      );
      craftElem = document.querySelector(`#craft-items .items`);
    }
    craftElem.innerHTML = '';
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
    Object.keys(gameData.availableEquipment).forEach((name) => {
      renderItem(
        name,
        craftElem,
        () => gameData.availableEquipment[name],
        (count) => {
          craftElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = count();
          if (this.itemSelected) {
            document.querySelector(`#craft-screen .token.${name} .card`).style['outline'] = '';
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
