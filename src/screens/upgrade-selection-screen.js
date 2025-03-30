class UpgradeSelectionScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.upgradeSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.upgradeElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('which-upgrade');
  }
  show(gameData) {
    this.upgradeSelected = {};
    let upgradeElem = document.querySelector(`#upgrade-selection-screen .content`);
    if (!upgradeElem) {
      const resourcesForDisplay = this.game.getResourcesForDisplay(gameData);
      this.resourceSelected = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.resourceRequested = resourcesForDisplay.reduce((acc, name) => ({ ...acc, [name]: 0 }), {});
      this.game.selector.show('which-upgrade');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="upgrade-selection-screen" class="dlid__container">
            <div class="content">
              <div class="board"><div class="selections"></div></div>
              <div id="upgrades" class="dlid__container"><h3>${_('New Discoveries')}</h3><div class="items"></div></div>
            </div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
          </div>`,
      );
      upgradeElem = document.querySelector(`#upgrade-selection-screen .content`);
      renderImage(`knowledge-tree-${gameData.difficulty}`, document.querySelector(`#upgrade-selection-screen .board`), {
        pos: 'insert',
        scale: 1.5,
      });
      this.upgradeElem = upgradeElem;

      this.arrowElem = document.querySelector(`#upgrade-selection-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    const selections = this.upgradeElem.querySelector(`.selections`);
    selections.innerHTML = '';
    const filled = Object.values(gameData.upgrades).reduce((acc, x) => [...acc, x], []);
    gameData.selectableUpgrades.forEach((unlockId) => {
      if (!filled.includes(unlockId)) {
        const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[unlockId];
        selections.insertAdjacentHTML('beforeend', `<div class="fkp-spot ${unlockId}" style="top: ${y - 7}px; left: ${x - 103}px;"></div>`);
        const elem = selections.querySelector(`.fkp-spot.${unlockId}`);
        addClickListener(elem, 'Select', () => {
          this.upgradeSelected.replace = unlockId;
        });
      }
    });
    const elem = document.querySelector(`#upgrades .items`);
    Object.keys(gameData.upgrades).forEach((unlockId) => {
      const replaceUnlockId = gameData.upgrades[unlockId].replaces;
      if (replaceUnlockId) {
        const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[replaceUnlockId];
        selections.insertAdjacentHTML(
          'beforeend',
          `<div class="fkp-spot ${replaceUnlockId}" style="top: ${y - 7}px; left: ${x - 103}px;"></div>`,
        );
        const elem = selections.querySelector(`.fkp-spot.${replaceUnlockId}`);
        addClickListener(elem, 'Select', () => {});
        if (elem) {
          renderImage(replaceUnlockId, elem, { scale: 1.7 });
        }
      }
      renderImage(unlockId, elem, { scale: 1 });
      addClickListener(elem.querySelector(`*[name="${unlockId}"]`), 'Select', () => {
        this.upgradeSelected.replace = unlockId;
      });
    });

    this.scroll();
  }
}
