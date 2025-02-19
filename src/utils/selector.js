class Selector {
  constructor(gamePlayAreaElem) {
    gamePlayAreaElem.insertAdjacentHTML('beforeend', `<div id="selector-overlay"></div>`);
    this.selectorElem = document.getElementById('selector-overlay');
    this.hide();
  }
  show() {
    this.selectorElem.style.display = '';
  }
  hide() {
    this.selectorElem.style.display = 'none';
    this.selectorElem.innerHTML = '';
  }
  renderByHTML(html) {
    this.selectorElem.innerHTML = html;
  }
  renderByElement() {
    return this.selectorElem;
  }
}
