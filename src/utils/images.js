const getSpriteSize = (name, scale = 2) => {
  const {
    frame: { w, h },
    rotate,
  } = allSprites[name];
  if (rotate) return { width: h / scale, height: w / scale };
  else return { width: w / scale, height: h / scale };
};
const renderImage = (name, div, scale = 2, pos = 'append') => {
  // example of adding a div for each player
  if (!allSprites[name]) throw new Error(`Missing image ${name}`);
  const {
    meta: {
      css,
      size: { w: spriteWidth, h: spriteHeight },
    },
    frame: { x, y, w, h },
    rotate,
  } = allSprites[name];
  let html;
  const scaledX = Math.round(x / scale);
  const scaledY = Math.round(y / scale);
  const scaledWidth = Math.round(w / scale);
  const scaledHeight = Math.round(h / scale);
  const scaledSpriteWidth = Math.round(spriteWidth / scale);
  const scaledSpriteHeight = Math.round(spriteHeight / scale);
  if (rotate)
    html = `<div class="card-rotator" style="transform: rotate(${rotate}deg) translate(${scaledWidth + 3}px, ${
      -scaledHeight / 2
    }px);height: ${scaledWidth}px;transform-origin:top;width: ${scaledHeight}px;">
    <div name="${name}-${rotate}" class="card ${css} ${name}" style="background-size: ${scaledSpriteWidth}px ${scaledSpriteHeight}px;background-position: -${scaledX}px -${scaledY}px;width: ${scaledWidth}px;height: ${scaledHeight}px;"></div>
    </div>`;
  else
    html = `<div name="${name}" class="card ${css} ${name}" style="background-size: ${scaledSpriteWidth}px ${scaledSpriteHeight}px;background-position: -${scaledX}px -${scaledY}px;width: ${scaledWidth}px;height: ${scaledHeight}px;"></div>`;

  if (pos === 'replace') div.innerHTML = html;
  else if (pos === 'insert') div.insertAdjacentHTML('afterbegin', html);
  else div.insertAdjacentHTML('beforeend', html);
};
