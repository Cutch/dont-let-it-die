class CardSelectionScreen {
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
    scrollArrow(this.cardSelectionElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('cardSelection');
  }
  show(gameData) {
    this.itemSelected = null;
    const deckScaling = {
      'day-event': 2,
      'mental-hindrance': 2,
      'physical-hindrance': 2,
    };
    let cardSelectionElem = document.querySelector(`#card-selection-screen .decks`);
    const distinctDecks = Array.from(new Set(gameData.cardSelection.cards.map((d) => d.deck)));
    if (!cardSelectionElem) {
      this.game.selector.show('cardSelection');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="card-selection-screen" class="dlid__container">
            <div class="decks"></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      cardSelectionElem = document.querySelector(`#card-selection-screen .decks`);
      if (distinctDecks.length === 1) {
        cardSelectionElem.insertAdjacentHTML(
          'beforeend',
          `<div class="dlid__container ${distinctDecks[0]}"><h3>${_('Select a Card')}</h3><div class="cards"></div></div>`,
        );
      } else {
        distinctDecks.forEach((deck) => {
          cardSelectionElem.insertAdjacentHTML(
            'beforeend',
            `<div class="dlid__container ${deck}"><h3>${this.game.deckMapping[deck]}</h3><div class="cards"></div></div>`,
          );
        });
      }
      this.cardSelectionElem = cardSelectionElem;
      this.arrowElem = document.querySelector(`#card-selection-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    const renderItem = (cardName, deckName, elem, selectCallback) => {
      elem.insertAdjacentHTML(
        'beforeend',
        `<div class="token-number-counter ${cardName}">
            <div class="token ${cardName}"></div>
          <div>`,
      );
      renderImage(cardName, elem.querySelector(`.token.${cardName}`), { scale: deckScaling[deckName] ?? 1, pos: 'insert' });
      addClickListener(elem.querySelector(`.token.${cardName}`), _('Select a Card'), () => selectCallback());
    };
    distinctDecks.forEach((deck) => {
      const deckElem = this.cardSelectionElem.querySelector(`.${deck} .cards`);
      deckElem.innerHTML = '';
      const cards = gameData.cardSelection.cards.filter((d) => d.deck === deck);
      cards.forEach(({ deck, id: card }) => {
        renderItem(card, deck, deckElem, () => {
          if (this.itemSelected) {
            document.querySelector(`#card-selection-screen .token.${this.itemSelected} .card`).style['outline'] = '';
          }
          this.itemSelected = card;
          if (this.itemSelected) {
            document.querySelector(`#card-selection-screen .token.${card} .card`).style['outline'] = `5px solid #fff`;
          }
        });
      });
    });
    // gameData.cardSelection.cards
    this.scroll();
  }
}
