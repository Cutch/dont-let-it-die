class Deck {
  constructor(deck, div, scale = 4) {
    this.div = div;
    this.scale = scale;
    this.deck = deck;
    renderImage(`${deck}-back`, this.div, this.scale, 'replace');
    this.div.insertAdjacentHTML('beforeend', `<div class="flipped-card"></div>`);
    this.drawing = [];
    this.topDiscard = null;
  }
  setDiscard(cardId) {
    renderImage(cardId, this.div.querySelector(`.flipped-card`), this.scale, 'replace');
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
    renderImage(`${this.deck}-back`, this.div.querySelector(`.flip-card-front`), this.scale, 'replace');
    renderImage(cardId, this.div.querySelector(`.flip-card-back`), this.scale, 'replace');
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
    }, 750);
  }
}
