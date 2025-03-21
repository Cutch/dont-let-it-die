class Deck {
  constructor(game, deck, countData, div, scale = 4, style = 'vertical') {
    this.game = game;
    this.countData = countData;
    this.div = div;
    this.scale = scale;
    this.style = style;
    this.deck = deck;
    this.div.classList.add('deck');
    this.div.classList.add(this.style === 'vertical' ? 'vertical' : 'horizontal');
    renderImage(`${this.deck}-back`, this.div, { scale: this.scale, pos: 'replace' });
    this.div.insertAdjacentHTML(
      'beforeend',
      `<div class="flipped-card"></div><div class="shuffle shuffle-1"></div><div class="shuffle shuffle-2"></div>`,
    );
    renderImage(`${this.deck}-back`, this.div.querySelector(`.shuffle-1`), { scale: this.scale, pos: 'replace' });
    renderImage(`${this.deck}-back`, this.div.querySelector(`.shuffle-2`), { scale: this.scale, pos: 'replace' });
    this.div
      .querySelector(`.${this.deck}-back`)
      .insertAdjacentHTML('beforeend', `<div class="deck-counter dot counter">${this.countData.count}</div>`);
    this.drawing = [];
    this.topDiscard = null;
    this.setDiscard(this.topDiscard);
  }
  async shuffle() {
    return new Promise((resolve) => {
      this.topDiscard = null;
      this.setDiscard();
      this.div.querySelector(`.shuffle-1`).classList.add('enable');
      this.div.querySelector(`.shuffle-2`).classList.add('enable');
      setTimeout(() => {
        this.div.querySelector(`.shuffle-1`).classList.remove('enable');
        this.div.querySelector(`.shuffle-2`).classList.remove('enable');
        resolve();
      }, 1500);
    });
  }
  updateDeckCounts(countData) {
    this.countData = countData;
    this.div.querySelector(`.deck-counter`).innerHTML = this.countData.count;
    this.div.querySelector(`.discard-counter`).innerHTML = this.countData.discardCount;
  }
  setDiscard(cardId) {
    if (this.cleanup) this.cleanup();
    if (!cardId) {
      const { width, height } = getSpriteSize(`${this.deck}-back`, this.scale);
      this.div.querySelector(`.flipped-card`).innerHTML = `<div class="empty-discard" style="width: ${width}px;height: ${height}px;">${_(
        'Discard',
      )}</div>`;
    } else {
      renderImage(cardId, this.div.querySelector(`.flipped-card`), { scale: this.scale, pos: 'replace' });
      this.cleanup = addClickListener(this.div.querySelector(`.flipped-card`), cardId, () => {
        this.game.tooltip.show();
        const {
          frame: { w, h },
          rotate,
        } = allSprites[cardId];

        renderImage(cardId, this.game.tooltip.renderByElement(), { scale: (rotate ? h : w) < 300 ? 0.5 : 1, pos: 'replace' });
      });
    }
    this.div
      .querySelector(`.flipped-card`)
      .insertAdjacentHTML('beforeend', `<div class="discard-counter dot counter">${this.countData.discardCount}</div>`);
    this.topDiscard = cardId;
  }
  async drawCard(cardId) {
    return new Promise((resolve) => {
      this.drawing.push(cardId);
      if (this.drawing.length === 1) this._drawCard(this.drawing[0], (id) => id === cardId && resolve());
    });
  }
  _drawCard(cardId, callback) {
    this.div.insertAdjacentHTML(
      'beforeend',
      `<div class="flip-card">
  <div class="flip-card-inner">
  <div class="flip-card-front"></div>
  <div class="flip-card-back"></div>
  </div>
  </div>`,
    );
    renderImage(`${this.deck}-back`, this.div.querySelector(`.flip-card-front`), { scale: this.scale, pos: 'replace' });
    renderImage(cardId, this.div.querySelector(`.flip-card-back`), { scale: this.scale, pos: 'replace' });
    setTimeout(() => {
      this.div.querySelector(`.flip-card`).classList.add('flip');
      setTimeout(() => {
        this.div.querySelector(`.flip-card`).classList.add('discard');
        setTimeout(() => {
          this.setDiscard(cardId);
          this.div.querySelector('.flip-card').remove();
          this.drawing.splice(this.drawing.indexOf(cardId), 1);
          callback(cardId);
          if (this.drawing.length > 0) this._drawCard(this.drawing[0]);
        }, 1000);
      }, 1000);
    }, 100);
  }
}
