class DeckSelectionScreen {
  constructor(game) {
    this.game = game;
  }
  getSelectedId() {
    return this.itemSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    const { y, height } = this.deckSelectionElem.getBoundingClientRect();
    this.arrowElem.style['top'] = `calc(${Math.max(
      0,
      window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
    )}px / var(--bga-game-zoom, 1))`;
    this.arrowElem.style['display'] =
      Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
  }
  hide() {
    this.game.selector.hide('deckSelection');
  }
  show(gameData) {
    this.itemSelected = null;
    const deckScaling = {
      'day-event': 2,
      'mental-hindrance': 2,
      'physical-hindrance': 2,
    };
    let deckSelectionElem = document.querySelector(`#deck-selection-screen .decks`);
    if (!deckSelectionElem) {
      this.game.selector.show('deckSelection');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="deck-selection-screen" class="dlid__container">
            <div id="deck-selection-screen" class="dlid__container"><h3>${_('Select a Deck')}</h3><div class="decks"></div></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      deckSelectionElem = document.querySelector(`#deck-selection-screen .decks`);
      this.deckSelectionElem = deckSelectionElem;
      this.arrowElem = document.querySelector(`#deck-selection-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    deckSelectionElem.innerHTML = '';
    const renderItem = (name, elem, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <div class="token ${name}">
              <div class="data">
                <div>${_('Deck')}: ${gameData.decks[name].count}</div>
                <div>${_('Discard')}: ${gameData.decks[name].discardCount}</div>
              </div>
            </div>
          <div>`,
      );
      renderImage(name + '-back', elem.querySelector(`.token.${name}`), { scale: deckScaling[name] ?? 1, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), allSprites[name + '-back'].options.name, () => selectCallback());
    };
    Object.keys(gameData.decks).forEach((deckName) => {
      renderItem(deckName, deckSelectionElem, () => {
        if (this.itemSelected) {
          document.querySelector(`#deck-selection-screen .token.${this.itemSelected} .card`).style['outline'] = '';
        }
        this.itemSelected = deckName;
        if (this.itemSelected) {
          document.querySelector(`#deck-selection-screen .token.${deckName} .card`).style['outline'] = `5px solid #fff`;
        }
      });
    });
  }
}
