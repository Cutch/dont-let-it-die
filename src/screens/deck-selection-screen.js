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
    const renderItem = (name, elem, count, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${name}">
            <div class="token ${name}"><div class="counter dot">${count()}</div></div>
            <div>`,
      );
      renderImage(name, elem.querySelector(`.token.${name}`), { scale: 2, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${name}`), this.game.data[name].options.name, () => selectCallback(count));
    };
    Object.keys(gameData.decks).forEach((deckName) => {
      renderItem(
        deckName + '-back',
        deckSelectionElem,
        () => gameData.availableEquipment[deckName],
        (count) => {
          deckSelectionElem.querySelector(`.token-number-counter.${deckName} .counter`).innerHTML = count();
          if (this.itemSelected) {
            document.querySelector(`#deck-selection-screen .token.${this.itemSelected} .card`).style['outline'] = '';
          }
          this.itemSelected = deckName;
          if (this.itemSelected) {
            document.querySelector(`#deck-selection-screen .token.${deckName} .card`).style['outline'] = `5px solid #fff`;
          }
        },
      );
    });
  }
}
