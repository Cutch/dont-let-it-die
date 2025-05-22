import { addClickListener } from './clickable';

function uuidv4() {
  if (window.crypto && window.crypto.getRandomValues) {
    return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
      (c ^ (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))).toString(16),
    );
  }

  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c == 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}
// try to handle passive events
let passiveEventSupported = false;
try {
  const opts = Object.defineProperty({}, 'passive', {
    get() {
      passiveEventSupported = true;
    },
  });
  window.addEventListener('test', null, opts);
} catch (e) {}
// if they are supported, setup the optional params
// FALSE doubles as the default CAPTURE value

const passiveEvent = passiveEventSupported ? { capture: false, passive: true } : false;

export const addPassiveListener = (type, callback) => {
  window.addEventListener(type, callback, passiveEvent);
  return () => {
    window.removeEventListener(type, callback, passiveEvent);
  };
};

export const scrollArrow = (content, arrowElem) => {
  const { y, height } = content.getBoundingClientRect();
  arrowElem.style['top'] = `calc(${Math.max(
    0,
    window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2,
  )}px / var(--bga-game-zoom, 1))`;
  arrowElem.style['display'] = Math.max(0, window.scrollY - (window.scrollY + y) - height + window.innerHeight / 2) == 0 ? 'none' : '';
};

export const isStudio = () => {
  return window.location.hostname === 'studio.boardgamearena.com';
};
