import { getAllData } from '../assets';
import { addClickListener, addPassiveListener, renderImage, scrollArrow } from '../utils/index';
export class UpgradeSelectionScreen {
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
        !replace.match(/^[0-9]/) && this.upgradeElem.querySelector(`.upgrade-selections .fkp-spot.${replace}`),
        !from.match(/^[0-9]/) && this.upgradeElem.querySelector(`.upgrade-selections .fkp-spot.${from}`),
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
    const selections = this.upgradeElem.querySelector(`.upgrade-selections`);
    selections.innerHTML = '';
    const filled = Object.entries(gameData.upgrades).reduce((acc, [k, v]) => (v.replace ? { ...acc, [v.replace]: k } : acc), {});

    Object.keys(gameData.upgrades).forEach((unlockId) => {
      const discovery = document.querySelector(`#upgrades .items *[name="${unlockId}"] .upgrade-placed`);
      if (discovery) discovery.style.display = gameData.upgrades[unlockId]?.replace ? '' : 'none';
    });
    const nonSelectableUpgrades = gameData.allUnlocks.filter(
      (unlockId) => !gameData.selectableUpgrades.includes(unlockId) && !gameData.upgrades[unlockId],
    );
    // List all open slots & render replaced spots
    [...nonSelectableUpgrades, ...gameData.selectableUpgrades].forEach((unlockId) => {
      const { x, y } = getAllData()[`knowledge-tree-${this.game.difficulty}`].upgrades[unlockId];
      selections.insertAdjacentHTML(
        'beforeend',
        `<div class="fkp-spot ${gameData.selectableUpgrades.includes(unlockId) ? 'fkp-spot-outline' : ''} ${unlockId}" style="top: ${(y - 7) * 1.2}px; left: ${(x - 103) * 1.2}px;"></div>`,
      );
      const elem = selections.querySelector(`.fkp-spot.${unlockId}`);
      if (!gameData.selectableUpgrades.includes(unlockId)) {
        this.game.addHelpTooltip({
          node: elem,
          tooltipText: unlockId,
        });
      } else {
        if (filled[unlockId]) {
          renderImage(filled[unlockId], elem, { scale: 1.7 / 1.2 });
        } else {
          this.game.addHelpTooltip({
            node: elem,
            tooltipText: unlockId,
          });
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
      }
    });
  }
  show(gameData) {
    this.upgradeSelected = {};
    let upgradeElem = document.querySelector(`#upgrade-selection-screen .content`);
    if (!upgradeElem) {
      this.game.selector.show('which-upgrade');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="upgrade-selection-screen" class="dlid__container">
            <div class="content">
              <div class="board"><div class="upgrade-selections"></div></div>
              <div id="upgrades" class="dlid__container"><h3>${_('New Discoveries')}</h3><div class="items"></div></div>
            </div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
          </div>`,
      );
      upgradeElem = document.querySelector(`#upgrade-selection-screen .content`);
      renderImage(`knowledge-tree-${this.game.difficulty}`, document.querySelector(`#upgrade-selection-screen .board`), {
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
      this.game.addHelpTooltip({
        node: discovery,
        tooltipText: unlockId,
      });
    });

    this.update(gameData);

    this.scroll();
  }
}
