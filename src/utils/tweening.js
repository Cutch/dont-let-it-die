class Tweening {
  constructor(game, container) {
    this.game = game;
    this.container = container;
    this.tweenId = 0;
  }
  getBoundingClientRect(elem) {
    const { x, y, left, top, right, bottom, width, height } = elem.getBoundingClientRect();
    return {
      x: x + window.scrollX,
      y: y + window.scrollY,
      left: left + window.scrollX,
      top: top + window.scrollY,
      right: right + window.scrollX,
      bottom: bottom + window.scrollY,
      width,
      height,
    };
  }
  addTween(elem1, elem2, image, scale, count = 1, time = 500) {
    for (let i = 0; i < count; i++) {
      this.game.slideTemporaryObject(renderImage(image, null, { scale, pos: 'return' }), this.container, elem1, elem2, time, i * 300);
    }
  }
  addDestroyTween(elem1, elem2, time = 500) {
    this.game.slideToObjectAndDestroy(elem1, elem2, time);
  }
  addStartTween(elemQueryString1, elemQueryString2, image, scale, count, time) {
    const start = document.querySelector(elemQueryString1).cloneNode(true);
    // End tween
    return () => {
      const end = document.querySelector(elemQueryString2);
      this.addTween(start, end, image, scale, count, time);
    };
  }
}
