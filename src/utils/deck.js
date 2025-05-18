import allSprites from '../assets';
export class Deck {
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
      `<div class="flipped-card"></div><div class="deck-marker"></div><div class="shuffle shuffle-1"></div><div class="shuffle shuffle-2"></div>`,
    );
    renderImage(`${this.deck}-back`, this.div.querySelector(`.shuffle-1`), { scale: this.scale, pos: 'replace' });
    renderImage(`${this.deck}-back`, this.div.querySelector(`.shuffle-2`), { scale: this.scale, pos: 'replace' });
    this.div
      .querySelector(`.${this.deck}-back`)
      .insertAdjacentHTML('beforeend', `<div class="deck-counter dot dot--number counter">${this.countData.count}</div>`);
    this.drawing = [];
    this.partialDrawCard = null;
    this.topDiscard = null;
    this.setDiscard(this.topDiscard);
  }
  async shuffle(gameData) {
    return new Promise((resolve) => {
      this.topDiscard = null;
      this.setDiscard(gameData.decksDiscards?.[gameData.deck]?.name);
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
      .insertAdjacentHTML('beforeend', `<div class="discard-counter dot dot--number counter">${this.countData.discardCount}</div>`);
    this.topDiscard = cardId;
  }
  updateMarker({ tokens }) {
    const marker = this.div.querySelector(`.deck-marker`);
    marker.innerHTML = '';
    tokens?.forEach((token) => {
      renderImage(token, marker, { scale: 2, pos: 'replace' });
      if (token === 'trap') {
        this.game.addHelpTooltip(
          marker.querySelector(`.${token}`),
          _('If rolling equal or greater than a Danger! cards life, trap it to remove it and this token from the game.'),
        );
      }
    });
  }
  async drawCard(cardId, partial = false) {
    this.drawing.push([cardId, partial]);
    if (this.drawing.length === 1) {
      await this._drawCard(...this.drawing[0]);
    }
  }
  async _drawCard(cardId, partial = false) {
    if (!this.partialDrawCard) {
      await this.partialDraw(cardId);
    }
    if (!partial) {
      await this.finishPartialDraw(cardId);
    } else {
      this.drawing.splice(0, 1);
      if (this.drawing.length > 0) {
        await this._drawCard(...this.drawing[0]);
      }
    }
  }
  async partialDraw(cardId) {
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
    await this.game.wait(100);
    this.div.querySelector(`.flip-card`).classList.add('flip');
    await this.game.wait(1000);
    this.partialDrawCard = cardId;
  }
  async finishPartialDraw(cardId) {
    this.partialDrawCard = null;
    this.div.querySelector(`.flip-card`).classList.add('discard');
    await this.game.wait(1000);
    this.setDiscard(cardId);
    this.div.querySelector('.flip-card').remove();
    this.drawing.splice(0, 1);
    if (this.drawing.length > 0) {
      await this._drawCard(...this.drawing[0]);
    }
  }
}
