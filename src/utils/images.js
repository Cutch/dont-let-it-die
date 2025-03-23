const getSpriteSize = (name, scale = 2) => {
  const {
    frame: { w, h },
    rotate,
  } = allSprites[name];
  if (rotate) return { width: h / scale, height: w / scale };
  else return { width: w / scale, height: h / scale };
};
const renderImage = (
  name,
  div,
  { scale = 2, pos = 'append', card = true, css: extraCss = '', overridePos = null, rotate: rotateAPI = 0, centered = false } = {},
) => {
  // example of adding a div for each player
  if (!allSprites[name]) throw new Error(`Missing image ${name}`);
  const {
    meta: {
      css = '',
      size: { w: spriteWidth, h: spriteHeight },
    },
    frame: { x, y, w, h },
    rotate,
  } = allSprites[name];
  let html;
  let scaledX = Math.round(x / scale);
  let scaledY = Math.round(y / scale);
  let scaledWidth = Math.round(w / scale);
  let scaledHeight = Math.round(h / scale);
  const scaledSpriteWidth = Math.ceil(spriteWidth / scale);
  const scaledSpriteHeight = Math.ceil(spriteHeight / scale);
  if (overridePos) {
    scaledX = scaledX + scaledWidth * overridePos.x;
    scaledY = scaledY + scaledHeight * overridePos.y;
    scaledWidth = scaledWidth * Math.abs(overridePos.w - overridePos.x);
    scaledHeight = scaledHeight * Math.abs(overridePos.h - overridePos.y);
  }
  if (rotate || rotateAPI)
    html = `<div class="card-rotator" style="transform: rotate(${rotate || rotateAPI}deg) ${
      centered ? ';transform-origin: center;' : `translate(${scaledWidth + 3}px, ${-scaledHeight / 2}px);transform-origin:top;`
    }height: ${scaledWidth}px;width: ${scaledHeight}px;">
    <div name="${name}-${rotate || rotateAPI}" class="image card ${css} ${extraCss} ${
      card ? 'card' : ''
    } ${name}" style="background-size: ${scaledSpriteWidth}px ${scaledSpriteHeight}px;background-position: -${scaledX}px -${scaledY}px;width: ${scaledWidth}px;height: ${
      scaledHeight - 1
    }px;"></div>
    </div>`;
  else
    html = `<div name="${name}" class="image ${css} ${extraCss} ${
      card ? 'card' : ''
    } ${name}" style="background-size: ${scaledSpriteWidth}px ${scaledSpriteHeight}px;background-position: -${scaledX}px -${scaledY}px;width: ${scaledWidth}px;height: ${scaledHeight}px;"></div>`;

  if (pos === 'replace') div.innerHTML = html;
  else if (pos === 'insert') div.insertAdjacentHTML('afterbegin', html);
  else div.insertAdjacentHTML('beforeend', html);
};
