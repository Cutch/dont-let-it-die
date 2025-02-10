const renderImage = (name, div, scale = 2, replace = false) => {
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
  if (rotate)
    html = `<div class="card-rotator" style="transform: rotate(${rotate}deg) translate(25%, -50%);height: ${
      w / scale
    }px;width: ${h / scale}px;">
    <div name="${name}-${rotate}" class="card ${css}" style="background-size: ${
      spriteWidth / scale
    }px ${spriteHeight / scale}px;background-position: -${x / scale}px -${
      y / scale
    }px;width: ${w / scale}px;height: ${h / scale}px;"></div>
    </div>`;
  else
    html = `<div name="${name}-${rotate}" class="card ${css}" style="background-size: ${
      spriteWidth / scale
    }px ${spriteHeight / scale}px;background-position: -${x / scale}px -${
      y / scale
    }px;width: ${w / scale}px;height: ${h / scale}px;"></div>`;

  if (replace) div.innerHTML = html;
  else div.insertAdjacentHTML("beforeend", html);
};
