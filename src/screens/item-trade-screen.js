class ItemTradeScreen {
  constructor(game) {
    this.game = game;
  }
  getTrade() {
    return this.resourceSelected;
  }
  hasError() {
    return false;
  }
  scroll() {
    const { y, height } = this.itemTradeContent.getBoundingClientRect();
    this.arrowElem.style['top'] = `calc(${Math.max(
      0,
      window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
    )}px / var(--bga-game-zoom, 1))`;
    this.arrowElem.style['display'] =
      Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
  }
  hide() {
    this.game.selector.hide('item-trade');
  }
  show(gameData) {
    this.itemSelected = null;
    let itemTradeElem = document.querySelector(`#item-trade-screen .items`);
    if (!itemTradeElem) {
      this.game.selector.show('item-trade');
      this.game.selector.renderByElement().insertAdjacentHTML(
        'beforeend',
        `<div id="item-trade-screen" class="dlid__container">
            <div id="item-trade-screen-content">
              <div id="item-trade-screen__${gameui.player_id}" class="player-item-container"></div>
              <div id="item-trade-screen__camp"><div id="item-trade-screen__camp" class="dlid__container"><h3>${_(
                'Camp Items',
              )}</h3><div class="items"></div></div></div>
            </div>
            <div class="arrow"><i class="fa fa-arrow-up fa-5x" aria-hidden="true"></i></div>
        </div>`,
      );
      this.itemTradeContent = document.querySelector(`#item-trade-screen-content`);

      itemTradeElem = document.querySelector(`#item-trade-screen`);
      this.itemTradeElem = itemTradeElem;
      this.arrowElem = document.querySelector(`#item-trade-screen .arrow`);
      this.arrowElem.style['display'] = 'none';
      this.cleanup = addPassiveListener('scroll', () => this.scroll());

      const playerIds = Array.from(new Set(gameData.characters.map((d) => d.playerId)));
      // Add the other player containers
      playerIds
        .filter((playerId) => playerId != gameui.player_id)
        .forEach((playerId) => {
          this.itemTradeContent.insertAdjacentHTML(
            'beforeend',
            `<div id="item-trade-screen__${playerId}" class="player-item-container"></div>`,
          );
        });

      gameData.characters.forEach((character) => {
        const container = document.querySelector(`#item-trade-screen__${character.playerId}`);
        container.insertAdjacentHTML(
          'beforeend',
          `<div id="item-trade-screen__${character.name}" class="dlid__container"><h3>${character.name}${_(
            ' Items',
          )}</h3><div class="items"></div></div>`,
        );
      });
    }

    gameData.characters.forEach((character) => {
      const itemsElem = document.querySelector(`#item-trade-screen__${character.name} .items`);
      itemsElem.innerHTML = '';
      character.equipment.forEach((equipment) => {
        renderImage(equipment.id, itemsElem, { scale: 1, pos: 'insert' });
        addClickListener(itemsElem.querySelector(`.${equipment.id}`), equipment.name, () => {
          if (character.playerId == gameui.player_id) this.source = equipment.itemId;
          else this.destination = equipment.itemId;
        });
      });
    });
    gameData.characters
      .filter((playerId) => playerId != gameui.player_id)
      .forEach((character) => {
        const itemsElem = document.querySelector(`#item-trade-screen__${character.name} .items`);
        itemsElem.insertAdjacentHTML('beforeend', `<div class="discard"></div>`);
        addClickListener(document.querySelector(`#item-trade-screen__${character.name} .items .discard`), 'Give', () => {
          this.destination = character.name;
        });
      });

    if (true) {
      const itemsElem = document.querySelector(`#item-trade-screen__camp .items`);
      itemsElem.innerHTML = '';
      gameData.campEquipment.forEach((equipment) => {
        renderImage(equipment.name, itemsElem, { scale: 1, pos: 'insert' });
      });
      itemsElem.insertAdjacentHTML('beforeend', `<div class="discard"></div>`);
      addClickListener(document.querySelector(`#item-trade-screen__camp .items .discard`), 'Send to Camp', () => {
        this.destination = 'camp';
      });
    }

    // const renderItem = (name, itemId, elem, selectCallback) => {
    //   elem.insertAdjacentHTML('beforeend', `<div class="token id${itemId}"></div>`);
    //   renderImage(name, document.querySelector(`#item-trade-screen .token.id${itemId}`), { scale: 1, pos: 'insert' });
    //   if (selectCallback) {
    //     addClickListener(
    //       document.querySelector(`#item-trade-screen .token.id${itemId}`),
    //       this.game.data[name].options.name,
    //       selectCallback,
    //     );
    //   }
    // };
    // Object.keys(gameData.campEquipment).forEach(({ name, itemId }) => {
    //   renderItem(name, campElem);
    // });

    // gameData.items.forEach(({ itemId, name }) => {
    //   renderItem(name, itemId, itemTradeElem, () => {
    //     if (this.itemSelected) {
    //       document.querySelector(`#item-trade-screen .token.id${this.itemSelected} .card`).style['outline'] = '';
    //     }
    //     this.itemSelected = itemId;
    //     if (this.itemSelected) {
    //       document.querySelector(`#item-trade-screen .token.id${itemId} .card`).style['outline'] = `5px solid #fff`;
    //     }
    //   });
    // });
  }
}
