import { addClickListener, addPassiveListener, renderImage, scrollArrow } from '../utils/index';
export class WeaponScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.weaponSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.weaponElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('which-weapon');
  }
  show(gameData) {
    this.weaponSelected = null;
    let weaponElem = document.querySelector(`#weapon-item .tokens`);
    if (!weaponElem) {
      const resourcesForDisplay = this.game.getResourcesForDisplay(gameData);
      this.resourceSelected = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show('which-weapon');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="weapon-screen" class="dlid__container">
              <div id="weapon-item" class="dlid__container"><h3>${_('Weapons')}</h3><div class="tokens"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
          </div>`,
      );
      weaponElem = document.querySelector(`#weapon-item .tokens`);
      this.weaponElem = weaponElem;
      this.arrowElem = document.querySelector(`#weapon-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    weaponElem.innerHTML = '';
    const renderResource = (weapon, elem, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-block id${weapon.itemId}">
            <div class="name">${weapon.name}</div>
            <div class="line"><span class="label">${_('Damage')}: </span><span class="value">${weapon.damage}</span></div>
            <div class="line"><span class="label">${_('Range')}: </span><span class="value">${weapon.range}</span></div>
            <div style="display: ${weapon.useCostString ? '' : 'none'}" class="line"><span class="label">${_(
              'Cost',
            )}: </span><span class="value">${weapon.useCostString}</span></div>
            <div class="margin"></div>
        <div>`,
      );
      addClickListener(elem.querySelector(`.token-block.id${weapon.itemId}`), weapon.name, () => selectCallback());
    };
    gameData.chooseWeapons.forEach((weapon) => {
      renderResource(weapon, weaponElem, () => {
        if (this.weaponSelected) {
          document.querySelector(`#weapon-screen .token-block.id${this.weaponSelected}`).style['outline'] = '';
        }
        this.weaponSelected = weapon['itemId'];
        if (this.weaponSelected) {
          document.querySelector(`#weapon-screen .token-block.id${weapon['itemId']}`).style['outline'] = `5px solid #fff`;
        }
      });
    });
    this.scroll();
  }
}
