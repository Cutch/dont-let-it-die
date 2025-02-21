class DeckSelectionScreen {
  constructor(game) {
    this.game = game;
    this.error = false;
  }
  getSelectedId() {
    return this.itemSelected;
  }
  hasError() {
    return this.error;
  }
  show(gameData) {
    const deckScaling = {
      'day-event': 2,
      'mental-hindrance': 2,
      'physical-hindrance': 2,
    };
    let deckSelectionElem = document.querySelector(`#deck-selection-screen .decks`);
    if (!deckSelectionElem) {
      this.game.selector.show();
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="deck-selection-screen" class="dlid__container">
            <div class="error"></div>
            <div id="deck-selection-screen" class="dlid__container"><h3>${_('Select a Deck')}</h3><div class="decks"></div></div>
        </div>`,
      );
      deckSelectionElem = document.querySelector(`#deck-selection-screen .decks`);
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
