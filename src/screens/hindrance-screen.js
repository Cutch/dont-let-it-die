class HindranceSelectionScreen {
  constructor(game) {
    this.game = game;
  }
  getSelected() {
    return this.cardSelections;
  }
  hasError() {
    return false;
  }
  scroll() {
    scrollArrow(this.hindranceElem, this.arrowElem);
  }
  hide() {
    this.game.selector.hide('hindranceScreen');
  }
  show(gameData) {
    this.cardSelections = [];
    let hindranceElem = document.querySelector(`#hindrance-items .items`);
    if (!hindranceElem) {
      this.game.selector.show('hindranceScreen');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="hindrance-screen" class="dlid__container">
            <div class="selections"></div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      hindranceElem = document.querySelector(`#hindrance-screen .selections`);
      this.hindranceElem = hindranceElem;
      this.arrowElem = document.querySelector(`#hindrance-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());
    }
    hindranceElem.innerHTML = '';
    const renderItem = (id, elem, selectCallback) => {
      elem.insertAdjacentHTML('beforeend', `<div class="token id${id}"></div>`);
      renderImage(id, document.querySelector(`#hindrance-screen .token.id${id}`), { scale: 2, pos: 'insert' });
      addClickListener(document.querySelector(`#hindrance-screen .token.id${id}`), this.game.data[id].options.name, selectCallback);
    };
    gameData.selectionState.characters.reverse().forEach((d) => {
      document
        .querySelector(`#hindrance-screen .selections`)
        .insertAdjacentHTML(
          'afterbegin',
          `<div id="hindrance-items-${d.characterId}" class="dlid__container"><h3>${d.characterId}</h3><div class="items"></div></div>`,
        );
      const div = document.querySelector(`#hindrance-items-${d.characterId}`);
      d.physicalHindrance.forEach(({ id }) => {
        renderItem(id, div, () => {
          const i = this.cardSelections.findIndex((d) => d.cardId === id);
          if (i >= 0) {
            this.cardSelections.splice(i, 1);
            document.querySelector(`#hindrance-screen .token.id${id} .card`).style['outline'] = '';
          } else {
            this.cardSelections.push({ cardId: id, characterId: d.characterId });
            document.querySelector(`#hindrance-screen .token.id${id} .card`).style['outline'] = `5px solid #fff`;
          }
        });
      });
    });
    this.scroll();
  }
}
