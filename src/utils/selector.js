export class Selector {
  constructor(gamePlayAreaElem) {
    gamePlayAreaElem.insertAdjacentHTML('beforeend', `<div id="selector-overlay"></div>`);
    this.selectorElem = $('selector-overlay');
    this.screenName = null;
    this.hide(null);
  }
  show(name) {
    if (this.screenName) {
      this.selectorElem.innerHTML = '';
    }
    this.screenName = name;
    this.selectorElem.style.display = '';
  }
  hide(name) {
    if (this.screenName === name) {
      this.selectorElem.style.display = 'none';
      this.selectorElem.innerHTML = '';
      this.screenName = null;
    }
  }
  renderByHTML(html) {
    this.selectorElem.innerHTML = html;
  }
  renderByElement() {
    return this.selectorElem;
  }
}
