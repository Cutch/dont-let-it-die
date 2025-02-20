class Deck {
  constructor(game, deck, div, scale = 4, style = 'vertical') {
    this.game = game;
    this.div = div;
    this.scale = scale;
    this.style = style;
    this.deck = deck;
    this.div.classList.add('deck');
    this.div.classList.add(this.style === 'vertical' ? 'vertical' : 'horizontal');
    renderImage(`${this.deck}-back`, this.div, { scale: this.scale, pos: 'replace' });
    this.div.insertAdjacentHTML('beforeend', `<div class="flipped-card"></div>`);
    this.drawing = [];
    this.topDiscard = null;
    this.setDiscard(this.topDiscard);
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
        renderImage(cardId, this.game.tooltip.renderByElement(), { scale: 1, pos: 'replace' });
      });
    }
    this.topDiscard = cardId;
  }
  drawCard(cardId) {
    this.drawing.push(cardId);
    if (this.drawing.length === 1) this._drawCard(this.drawing[0]);
  }
  _drawCard(cardId) {
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
          if (this.drawing.length > 0) this._drawCard(this.drawing[0]);
        }, 1000);
      }, 1000);
    }, 100);
  }
}
