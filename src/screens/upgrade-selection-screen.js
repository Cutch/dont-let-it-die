class UpgradeSelectionScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.upgradeSelected;
  }
  sendSelection() {
    const { from, replace } = this.getSelectedId();
    if (from && replace) {
      this.game.bgaPerformAction('actMoveDiscovery', {
        upgradeId: from,
        upgradeReplaceId: replace,
      });
      [
        !replace.match(/^[0-9]/) && this.upgradeElem.querySelector(`.selections .fkp-spot.${replace}`),
        !from.match(/^[0-9]/) && this.upgradeElem.querySelector(`.selections .fkp-spot.${from}`),
        document.querySelector(`#upgrades .items *[name="${from}"]`),
      ]
        .filter(Boolean)
        .forEach((d) => (d.style['outline'] = ''));

      this.upgradeSelected = {};
    }
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
  update(gameData) {
    const selections = this.upgradeElem.querySelector(`.selections`);
    selections.innerHTML = '';
    const filled = Object.entries(gameData.upgrades).reduce((acc, [k, v]) => (v.replace ? { ...acc, [v.replace]: k } : acc), {});

    Object.keys(gameData.upgrades).forEach((unlockId) => {
      const discovery = document.querySelector(`#upgrades .items *[name="${unlockId}"] .upgrade-placed`);
      if (discovery) discovery.style.display = gameData.upgrades[unlockId]?.replace ? '' : 'none';
    });
    // List all open slots & render replaced spots
    gameData.selectableUpgrades.forEach((unlockId) => {
      const { x, y } = allSprites[`knowledge-tree-${gameData.difficulty}`].upgrades[unlockId];
      selections.insertAdjacentHTML(
        'beforeend',
        `<div class="fkp-spot ${unlockId}" style="top: ${(y - 7) * 1.2}px; left: ${(x - 103) * 1.2}px;"></div>`,
      );
      const elem = selections.querySelector(`.fkp-spot.${unlockId}`);

      if (filled[unlockId]) {
        renderImage(filled[unlockId], elem, { scale: 1.7 / 1.2 });
      }

      if (this.upgradeSelected.replace == unlockId) elem.style['outline'] = `5px solid #fff`;
      addClickListener(elem, 'Select', () => {
        const filled = Object.entries(gameData.upgrades).reduce((acc, [k, v]) => (v.replace ? { ...acc, [v.replace]: k } : acc), {});
        // Swap condition
        if (
          this.upgradeSelected.from == null &&
          this.upgradeSelected.replace &&
          this.upgradeSelected.replace != unlockId &&
          (filled[this.upgradeSelected.replace] || filled[unlockId])
        ) {
          this.upgradeSelected.from = unlockId;
          elem.style['outline'] = `5px solid #fff`;
        } else {
          if (this.upgradeSelected.replace) {
            selections.querySelector(`.fkp-spot.${this.upgradeSelected.replace}`).style['outline'] = '';
          }
          if (this.upgradeSelected.replace == unlockId) {
            this.upgradeSelected.replace = null;
          } else {
            this.upgradeSelected.replace = unlockId;
            elem.style['outline'] = `5px solid #fff`;
          }
        }
        this.sendSelection();
      });
    });
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
        scale: 1.25,
      });
      this.upgradeElem = upgradeElem;

      this.arrowElem = document.querySelector(`#upgrade-selection-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    // List all open slots
    const itemsElem = document.querySelector(`#upgrades .items`);
    itemsElem.innerHTML = '';
    Object.keys(gameData.upgrades).forEach((unlockId) => {
      // Render the new discovery
      renderImage(unlockId, itemsElem, { scale: 1 });
      const discovery = itemsElem.querySelector(`*[name="${unlockId}"]`);
      discovery.insertAdjacentHTML('beforeend', `<i class="fa fa-check-circle-o fa-2x upgrade-placed" aria-hidden="true"></i>`);
      addClickListener(discovery, 'Select', () => {
        if (this.upgradeSelected.from) {
          itemsElem.querySelector(`*[name="${this.upgradeSelected.from}"]`).style['outline'] = '';
        }
        if (this.upgradeSelected.from == unlockId) {
          this.upgradeSelected.from = null;
        } else {
          this.upgradeSelected.from = unlockId;
          discovery.style['outline'] = `5px solid #fff`;
        }
        this.sendSelection();
      });
    });

    this.update(gameData);

    this.scroll();
  }
}
