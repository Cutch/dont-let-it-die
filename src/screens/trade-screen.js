class TradeScreen {
  constructor(game) {
    this.game = game;
    this.updateFunctions = [];
    this.error = false;
  }
  setError() {
    const resourceCount = Object.keys(this.resourceSelected).reduce((a, name) => a + this.resourceSelected[name], 0);
    const requestedCount = Object.keys(this.resourceRequested).reduce((a, name) => a + this.resourceRequested[name], 0);
    const error = document.querySelector(`#trade-screen .error`);
    if (resourceCount != this.tradeRatio) {
      error.innerHTML = _('Select ${tradeRatio} of your resources').replace('${tradeRatio}', this.tradeRatio);
      error.style.visibility = '';
      this.error = true;
    } else if (requestedCount != 1) {
      error.innerHTML = _('Select ${requestedCount} to trade for').replace('${requestedCount}', 1);
      error.style.visibility = '';
      this.error = true;
    } else {
      error.style.visibility = 'hidden';
      error.innerHTML = '';
      this.error = false;
    }
  }
  getOffered() {
    return this.resourceSelected;
  }
  getRequested() {
    return this.resourceRequested;
  }
  hasError() {
    return this.error;
  }
  updateMinMax(plusElem, minusElem, count, max) {
    this.setError();
    if (count === 0) {
      if (!minusElem.classList.contains('disabled')) minusElem.classList.add('disabled');
    } else {
      if (minusElem.classList.contains('disabled')) minusElem.classList.remove('disabled');
    }
    if (count === max) {
      if (!plusElem.classList.contains('disabled')) plusElem.classList.add('disabled');
    } else {
      if (plusElem.classList.contains('disabled')) plusElem.classList.remove('disabled');
    }
  }
  show(gameData) {
    this.updateFunctions = [];
    this.tradeRatio = gameData.tradeRatio;
    let tradeElem = document.querySelector(`#trade-resource .tokens`);
    let tradeForElem = document.querySelector(`#trade-for .tokens`);
    if (!tradeElem) {
      this.resourceSelected = this.game.resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = this.game.resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show();
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="trade-screen" class="dlid__container">
            <div class="error"></div>
            <div id="trade-resource" class="dlid__container"><h3>${_('Your Resources')}</h3><div class="tokens"></div></div>
            <div id="trade-for" class="dlid__container"><h3>${_('Trade For')}</h3><div class="tokens"></div></div>
        </div>`,
      );
      tradeElem = document.querySelector(`#trade-resource .tokens`);
      tradeForElem = document.querySelector(`#trade-for .tokens`);
    }
    tradeElem.innerHTML = '';
    tradeForElem.innerHTML = '';
    const renderResource = (name, elem, count, max, addCallback, minusCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <i class="fa fa-plus-circle fa-4x"></i>
            <div class="token ${name}"><div class="counter dot">${count()}/${max()}</div></div>
            <i class="fa fa-minus-circle fa-4x"></i>
            <div>`,
      );
      const plusElem = elem.querySelector(`.token-number-counter.${name} .fa-plus-circle`);
      const minusElem = elem.querySelector(`.token-number-counter.${name} .fa-minus-circle`);
      addClickListener(elem.querySelector(`.token-number-counter.${name} .fa-plus-circle`), `Add`, () => addCallback(count, max));
      addClickListener(elem.querySelector(`.token-number-counter.${name} .fa-minus-circle`), `Subtract`, () => minusCallback(count, max));
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });

      this.updateFunctions.push(() => this.updateMinMax(plusElem, minusElem, count(), max()));
    };
    this.game.resourcesForDisplay.forEach((name) => {
      renderResource(
        name,
        tradeElem,
        () => this.resourceSelected[name],
        () => gameData.game['resources'][name] ?? 0,
        (count, max) => {
          this.resourceSelected[name] = Math.min(max(), count() + 1);
          tradeElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
          this.updateFunctions.forEach((d) => d());
        },
        (count, max) => {
          this.resourceSelected[name] = Math.max(0, count() - 1);
          tradeElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
          this.updateFunctions.forEach((d) => d());
        },
      );
    });
    this.game.resourcesForDisplay
      .filter((d) => !d.includes('-cooked'))
      .forEach((name) => {
        renderResource(
          name,
          tradeForElem,
          () => this.resourceRequested[name],
          () => gameData.resourcesAvailable?.[name] ?? 0,
          (count, max) => {
            this.resourceRequested[name] = Math.min(max(), count() + 1);
            tradeForElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
            this.updateFunctions.forEach((d) => d());
          },
          (count, max) => {
            this.resourceRequested[name] = Math.max(0, count() - 1);
            tradeForElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
            this.updateFunctions.forEach((d) => d());
          },
        );
      });
    this.updateFunctions.forEach((d) => d());
  }
}
