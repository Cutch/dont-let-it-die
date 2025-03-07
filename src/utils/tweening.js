class Tweening {
  constructor(container) {
    this.container = container;
    this.tweenId = 0;
  }
  getBoundingClientRect(elem) {
    const { x, y, left, top, right, bottom, width, height } = elem.getBoundingClientRect();
    return { x, y, left, top, right, bottom, width, height };
  }
  addTween(elem1, elem2, image, scale, count = 1, time = 500) {
    const elementRect1 = elem1 instanceof Element ? this.getBoundingClientRect(elem1) : elem1;
    const elementRect2 = elem2 instanceof Element ? this.getBoundingClientRect(elem2) : elem2;
    const containerRect = this.getBoundingClientRect(this.container);

    const id = `tween_${++this.tweenId}`;
    this.container.insertAdjacentHTML(
      'beforeend',
      `<div id="${id}" class="tween" style="transition: top ${time}ms linear, left ${time}ms linear;"></div>`,
    );
    const tweenElem = document.getElementById(id);
    renderImage(image, tweenElem, { scale });

    const relativeX1 = elementRect1.left - containerRect.left;
    const relativeY1 = elementRect1.top - containerRect.top;
    const relativeX2 = elementRect2.left - containerRect.left;
    const relativeY2 = elementRect2.top - containerRect.top;

    tweenElem.style.left = `${relativeX1}px`;
    tweenElem.style.top = `${relativeY1}px`;
    setTimeout(() => {
      tweenElem.style.left = `${relativeX2}px`;
      tweenElem.style.top = `${relativeY2}px`;
      setTimeout(() => {
        tweenElem.remove();
      }, time);
    }, 100);
    if (count > 1) {
      setTimeout(() => {
        this.addTween(elementRect1, elementRect2, image, scale, count - 1);
      }, 300);
    }
  }
  addStartTween(elemQueryString1, elemQueryString2, image, scale, count, time) {
    const start = this.getBoundingClientRect(document.querySelector(elemQueryString1));
    // End tween
    return () => {
      const end = this.getBoundingClientRect(document.querySelector(elemQueryString2));
      this.addTween(start, end, image, scale, count, time);
    };
  }
}
