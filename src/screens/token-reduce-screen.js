import { addClickListener, addPassiveListener, renderImage, scrollArrow } from '../utils/index';
export class TokenReduceScreen {
  constructor(game) {
    this.game = game;
    this.updateFunctions = [];
    this.error = false;
  }
  setError() {
    const resourceCount = Object.values(this.resourceCosts).reduce((a, b) => a + b);
    const initialCost = Object.values(this.game.gamedatas.selectionState.item.cost).reduce((a, b) => a + b);

    document.querySelector(`#token-reduce-resource .cost`).innerHTML = resourceCount;
    const error = document.querySelector(`#token-reduce-screen .error`);
    if (initialCost - resourceCount > this.game.gamedatas.selectionState.reduceBy) {
      error.innerHTML = _('Resources can only be reduced by ${count}').replace('${count}', this.game.gamedatas.selectionState.reduceBy);
      error.style.visibility = '';
      this.error = true;
    } else if (resourceCount < this.game.gamedatas.selectionState.totalCost) {
      error.innerHTML = _('Total resource cost must be at least ${count}').replace(
        '${count}',
        this.game.gamedatas.selectionState.totalCost,
      );
      error.style.visibility = '';
      this.error = true;
    } else if (
      initialCost - resourceCount < this.game.gamedatas.selectionState.reduceBy &&
      resourceCount >= this.game.gamedatas.selectionState.totalCost
    ) {
      error.innerHTML = _('Reduce resources by ${count}').replace('${count}', this.game.gamedatas.selectionState.reduceBy);
      error.style.visibility = '';
      this.error = true;
    } else {
      error.style.visibility = 'hidden';
      error.innerHTML = '';
      this.error = false;
    }
  }
  getSelection() {
    return this.resourceCosts;
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
  scroll() {
    scrollArrow(this.tokenReduceContent, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('token-reduce');
  }
  show(gameData) {
    this.updateFunctions = [];
    let tokenReduceElem = document.querySelector(`#token-reduce-resource .tokens`);
    if (!tokenReduceElem) {
      this.resourceCosts = { ...gameData.selectionState.item.cost };
      this.game.selector.show('token-reduce');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="token-reduce-screen" class="dlid__container">
            <div class="error"></div>
            <div id="token-reduce-body">
              <div id="token-reduce-resource" class="dlid__container"><div><h3>${_(
                gameData.selectionState?.title ?? 'Required Resources',
              )}</h3><div class="cost"></div></div><div class="tokens"></div></div>
            </div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      tokenReduceElem = document.querySelector(`#token-reduce-resource .tokens`);
      this.tokenReduceContent = document.querySelector(`#token-reduce-body`);
      this.arrowElem = document.querySelector(`#token-reduce-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    tokenReduceElem.innerHTML = '';
    const renderResource = (name, elem, count, max, addCallback, minusCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <i class="fa fa-plus-circle fa-4x"></i>
            <div class="token ${name}"><div class="counter dot dot--number">${count()}/${max()}</div></div>
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

    Object.keys(gameData.selectionState.item.cost).forEach((name) => {
      renderResource(
        name,
        tokenReduceElem,
        () => this.resourceCosts[name],
        () => gameData.selectionState.item.cost[name] ?? 0,
        (count, max) => {
          this.resourceCosts[name] = Math.min(max(), count() + 1);
          tokenReduceElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
          this.updateFunctions.forEach((d) => d());
        },
        (count, max) => {
          this.resourceCosts[name] = Math.max(0, count() - 1);
          tokenReduceElem.querySelector(`.token-number-counter.${name} .counter`).innerHTML = `${count()}/${max()}`;
          this.updateFunctions.forEach((d) => d());
        },
      );
    });
    this.updateFunctions.forEach((d) => d());
    this.scroll();
  }
}
