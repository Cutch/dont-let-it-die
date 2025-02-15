const allSprites = [boardsSprites, charactersSprites, decksSprites, expansionSprites, itemsSprites, tokenSprites, upgradesSprites].reduce(
  (acc, { sprites, meta }) => {
    Object.keys(sprites).forEach((k) => ((sprites[k].meta = meta), (sprites[k].id = k)));
    return {
      ...acc,
      ...sprites,
    };
  },
  {},
);
