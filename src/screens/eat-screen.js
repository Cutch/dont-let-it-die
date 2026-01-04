import { getAllData } from '../assets';
import { addClickListener, addPassiveListener, renderImage, scrollArrow } from '../utils/index';
export class EatScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.foodSelected['id'];
  }
  getSelected() {
    return this.foodSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.eatElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('eat');
  }
  show(gameData, eatableFoods = null) {
    this.foodSelected = null;
    eatableFoods = eatableFoods ?? this.game.gamedatas.eatableFoods;
    if (this.game.gamedatas.characters && !this.game.gamedatas.characters.some((d) => d.name === 'Sig')) {
      eatableFoods = eatableFoods.filter((d) => !d['id'].includes('fish'));
    }
    let eatElem = document.querySelector(`#eat-resource .tokens`);
    if (!eatElem) {
      const resourcesForDisplay = this.game.getResourcesForDisplay(this.game.gamedatas);
      this.resourceSelected = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show('eat');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="eat-screen" class="dlid__container">
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
      const available = this.game.gamedatas.resources[food['id']];
      const requires = food['count'];
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-block ${food['id']}">
            <div class="name">${_(getAllData()[food['id']].options.name)}</div>
            <div class="available line"><span class="label">${_('Available')}: </span><span class="value">${available}</span></div>
            <div class="requires line"><span class="label">${_('Requires')}: </span><span class="value">${requires}</span></div>
            <div class="health line ${food['health'] ? '' : 'hidden'}"><span class="label">${_('Health')}: </span><span class="value">${
              food['health']
            } <i class="fa fa-heart"></i></span></div>
            <div class="stamina line ${food['stamina'] ? '' : 'hidden'}"><span class="label">${_('Stamina')}: </span><span class="value">${
              food['stamina']
            } <i class="fa fa-bolt"></i></span></div>
            <div class="margin"></div>
            <div class="token ${food['id']}"></div>
        <div>`,
      );
      renderImage(food['id'], elem.querySelector(`.token.${food['id']}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token-block.${food['id']}`), _(this.game.data[food['id']].options.name), () =>
        selectCallback(),
      );
    };
    let found = false;
    eatableFoods.forEach((food, i) => {
      const available = this.game.gamedatas.resources[food['id']];
      const requires = food['count'];
      if (available >= requires) {
        renderResource(food, eatElem, () => {
          if (this.foodSelected) {
            document.querySelector(`#eat-screen .token-block.${this.foodSelected['id']}`).style['outline'] = '';
          }
          this.foodSelected = food;
          if (this.foodSelected) {
            document.querySelector(`#eat-screen .token-block.${food['id']}`).style['outline'] = `5px solid #fff`;
          }
        });
        found = true;
      }
    });
    if (!found) eatElem.innerHTML = `<div class="dlid__container"><h3>${_('None')}</h3></div>`;
    this.scroll();
  }
}
