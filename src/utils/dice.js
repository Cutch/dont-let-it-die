const sideNames = ['one', 'two', 'three', 'four', 'five', 'six'];
class Dice {
  constructor(div) {
    this.div = div;
    this.queue = [];
    this.rolling = false;
    let html = `<div id="dice-container" class="dice-container"><div id='dice' class="dice">`;
    for (let i = 1; i <= 6; i++) {
      html += `<div class="side-wrapper ${sideNames[i - 1]}"><div class="side">`;
      html += `<div class="image"></div>`;
      html += `</div></div>`;
    }
    html += `</div></div>`;
    this.div.insertAdjacentHTML('beforeend', html);
    this.container = $('dice-container');
    this.dice = $('dice');
    this.dice.addEventListener('animationEnd', () => {
      this.dice.style.animationPlayState = 'paused';
    });
  }
  _roll({ roll, callback }) {
    this.rolling = true;
    this.container.style['visibility'] = 'unset';
    this.dice.style['transition'] = 'transform 1s, left 1s, top 1s';
    this.dice.style.animationPlayState = 'running';
    this.dice.classList.add('show-' + roll);
    this.dice.style['left'] = '33%';
    this.dice.style['top'] = '33%';

    setTimeout(() => {
      this.container.style['visibility'] = 'hidden';
      this.dice.style['transition'] = 'unset';
      this.dice.style['left'] = '66%';
      this.dice.style['top'] = '66%';
      for (let i = 1; i <= 6; i++) {
        this.dice.classList.remove('show-' + i);
      }
      callback();
      if (this.queue.length > 0) {
        setTimeout(() => {
          this._roll(this.queue.shift());
        }, 250);
      } else {
        this.rolling = false;
      }
    }, 3000);
  }
  roll(roll) {
    new Promise((resolve) => {
      if (this.queue.length == 0 && !this.rolling) {
        this._roll({ roll, callback: resolve });
      } else {
        this.queue.push({ roll, callback: resolve });
      }
    });
  }
}
