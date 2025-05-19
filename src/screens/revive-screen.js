import { getAllData } from '../assets';
import { addClickListener, addPassiveListener, renderImage, scrollArrow } from '../utils/index';
export class ReviveScreen {
  constructor(game) {
    this.game = game;
  }
  getSelected() {
    return { characterSelected: this.characterSelected, foodSelected: this.foodSelected };
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.eatElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('revive');
  }
  show(gameData) {
    this.characterSelected = null;
    this.foodSelected = null;
    const canUseFish = gameData.characters && !gameData.characters.some((d) => d.name === 'Sig');
    let eatElem = document.querySelector(`#eat-resource .tokens`);
    let characterElem = document.querySelector(`#revive-character .tokens`);
    if (!eatElem) {
      const resourcesForDisplay = this.game.getResourcesForDisplay(gameData);
      this.resourceSelected = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show('revive');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="revive-screen" class="dlid__container">
            <div id="eat-resource" class="dlid__container"><h3>${_('Food')}</h3><div class="tokens"></div></div>
            <div id="revive-character" class="dlid__container"><h3>${_('Characters')}</h3><div class="tokens"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
          </div>`,
      );
      eatElem = document.querySelector(`#eat-resource .tokens`);
      this.eatElem = eatElem;
      characterElem = document.querySelector(`#revive-character .tokens`);
      this.arrowElem = document.querySelector(`#revive-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    eatElem.innerHTML = '';
    characterElem.innerHTML = '';
    const renderResource = (food, elem, selectCallback) => {
      const available = (gameData.resources[food.id] ?? 0) + (food.id == 'meat-cooked' ? (gameData.resources['fish-cooked'] ?? 0) : 0);
      const requires = food['count'];
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-block ${food['id']}">
            <div class="name">${_(getAllData()[food.id].options.name)}</div>
            <div class="available line"><span class="label">${_('Available')}: </span><span class="value">${available}</span></div>
            <div class="requires line"><span class="label">${_('Requires')}: </span><span class="value">${requires}</span></div>
            <div class="health line"><span class="label">${_(
              'Health',
            )}: </span><span class="value">3 <i class="fa fa-heart"></i></span></div>
            <div class="margin"></div>
            <div class="token ${food['id']}"></div>
        <div>`,
      );
      if (food.id == 'meat-cooked')
        if (!canUseFish) renderImage('fish-cooked', elem.querySelector(`.token.${food['id']}`), { scale: 2, pos: 'insert' });
      renderImage(food.id, elem.querySelector(`.token.${food['id']}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token-block.${food['id']}`), _(this.game.data[food['id']].options.name), () =>
        selectCallback(),
      );
    };
    gameData.revivableFoods.forEach((food, i) => {
      renderResource(food, eatElem, () => {
        if (this.foodSelected) {
          document.querySelector(`#revive-screen .token-block.${this.foodSelected}`).style['outline'] = '';
        }
        this.foodSelected = food['id'];
        if (this.foodSelected) {
          document.querySelector(`#revive-screen .token-block.${food['id']}`).style['outline'] = `5px solid #fff`;
        }
      });
    });

    const renderItem = (name, elem, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <div class="token ${name}"></div>
        <div>`,
      );
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), name, () => selectCallback());
    };
    gameData.characters
      .filter((d) => d.incapacitated && (d.health ?? 0) == 0)
      .forEach((character) => {
        renderItem(character.name, characterElem, () => {
          if (this.characterSelected) {
            document.querySelector(`#revive-screen .token.${this.characterSelected} .card`).style['outline'] = '';
          }
          this.characterSelected = character.name;
          if (this.characterSelected) {
            document.querySelector(`#revive-screen .token.${character.name} .card`).style['outline'] = `5px solid #fff`;
          }
        });
      });
    this.scroll();
  }
}
