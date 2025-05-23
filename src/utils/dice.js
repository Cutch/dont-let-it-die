const sideNames = ['one', 'two', 'three', 'four', 'five', 'six'];
import dojo from 'dojo';
import { renderImage } from './images';
export class Dice {
  constructor(game, div) {
    this.game = game;
    this.div = div;
    this.queue = [];
    this.isRolling = false;
    let html = `<div id="dice-container" class="dice-container"><div id='dice' class="dice">`;
    for (let i = 1; i <= 6; i++) {
      html += `<div class="side-wrapper ${sideNames[i - 1]}"><div class="side">`;
      html += `<div class="image"></div>`;
      html += `</div></div>`;
    }
    html += `</div><div id="dice-container-character"></div></div>`;
    this.div.insertAdjacentHTML('beforeend', html);
    this.container = $('dice-container');
    this.dice = $('dice');
    this.dice.addEventListener('animationEnd', () => {
      this.dice.style.animationPlayState = 'paused';
    });
  }
  _roll({ args, callback }) {
    this.isRolling = true;
    this.container.style['visibility'] = 'unset';
    this.dice.style['transition'] = 'transform 1s, left 1s, top 1s';
    this.dice.style.animationPlayState = 'running';
    this.dice.classList.add('show-' + args.roll);
    this.dice.style['left'] = '33%';
    this.dice.style['top'] = '33%';
    console.log(args);
    //gameui.dice.roll({args:{roll:1, characterId: 'Mabe'}});
    if (args.characterId) {
      renderImage(args.characterId, $('dice-container-character'), {
        scale: 3,
        pos: 'replace',
        overridePos: {
          x: 0.2,
          y: 0.16,
          w: 0.8,
          h: 0.45,
        },
      });
    }

    const animation = new dojo.Animation({
      curve: [0, 1],
      duration: 3000,
      onEnd: () => {
        this.container.style['visibility'] = 'hidden';
        $('dice-container-character').innerHTML = '';
        this.dice.style['transition'] = 'unset';
        this.dice.style['left'] = '66%';
        this.dice.style['top'] = '66%';
        for (let i = 1; i <= 6; i++) {
          this.dice.classList.remove('show-' + i);
        }
        callback && callback();
        if (this.queue.length > 0) {
          setTimeout(() => {
            this._roll(this.queue.shift());
          }, 250);
        } else {
          this.isRolling = false;
        }
      },
    });

    this.game.bgaPlayDojoAnimation(animation);
  }
  async roll(args) {
    return new Promise((resolve) => {
      if (this.queue.length == 0 && !this.isRolling) {
        this._roll({ args, callback: resolve });
      } else {
        this.queue.push({ args, callback: resolve });
      }
    });
  }
}
