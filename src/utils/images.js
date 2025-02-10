const renderImage = (name, div, scale = 2) => {
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

  if (rotate)
    div.insertAdjacentHTML(
      "beforeend",
      `<div class="card-rotator" style="transform: rotate(${rotate}deg) translate(0, -50%);height: ${
        w / scale
      }px;width: ${h / scale}px;">
    <div name="${name}-${rotate}" class="card ${css}" style="background-size: ${
        spriteWidth / scale
      }px ${spriteHeight / scale}px;background-position: -${x / scale}px -${
        y / scale
      }px;width: ${w / scale}px;height: ${h / scale}px;"></div>
    </div>`
    );
  else
    div.insertAdjacentHTML(
      "beforeend",
      `<div name="${name}-${rotate}" class="card ${css}" style="background-size: ${
        spriteWidth / scale
      }px ${spriteHeight / scale}px;background-position: -${x / scale}px -${
        y / scale
      }px;width: ${w / scale}px;height: ${h / scale}px;"></div>`
    );
};
