class Tweening {
  constructor(container) {
    this.container = container;
  }
  addTween(elem1, elem2, image, time = 1000) {
    const containerRect = this.container.getBoundingClientRect();

    const id = uuidv4();
    this.container.insertAdjacentHTML('beforeend', `<div id="${id}" class="tween"></div>`);
    const tweenElem = document.getElementById(id);
    renderImage(image, tweenElem, { scale: 2 });
    const elementRect1 = elem1.getBoundingClientRect();
    const elementRect2 = elem2.getBoundingClientRect();

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
      }, 2000);
    }, 0);
  }
}
