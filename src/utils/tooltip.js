export class Tooltip {
  constructor(gamePlayAreaElem) {
    gamePlayAreaElem.insertAdjacentHTML(
      'beforeend',
      `<div id="tooltip-overlay"><div class="inner">
        <div class="close"><i class="fa fa-times fa-2x" aria-hidden="true"></i></div>
        <div class="body"></div>
      </div></div>`,
    );
    this.tooltipElem = $('tooltip-overlay');
    this.tooltipBody = document.querySelector('#tooltip-overlay .body');
    this.hide();

    addClickListener(document.querySelector('#tooltip-overlay .close'), 'Close', this.handleClick);
  }
  handleEscapeKey = (e) => {
    if (e.key === 'Escape') {
      this.hide();
    }
  };
  handleClick = (e) => {
    this.hide();
  };
  show() {
    this.tooltipElem.style.display = '';
    setTimeout(() => {
      document.addEventListener('click', this.handleClick);
      document.addEventListener('keydown', this.handleEscapeKey);
    }, 0);
  }
  hide() {
    document.removeEventListener('click', this.handleClick);
    document.removeEventListener('keydown', this.handleEscapeKey);
    this.tooltipElem.style.display = 'none';
    this.tooltipBody.innerHTML = '';
  }
  renderByHTML(html) {
    this.tooltipBody.innerHTML = html;
  }
  renderByElement() {
    return this.tooltipBody;
  }
}
