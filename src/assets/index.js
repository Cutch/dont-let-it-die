const allSprites = [boardsSprites, charactersSprites, decksSprites, expansionSprites, itemsSprites, tokenSprites, upgradesSprites].reduce(
  (acc, { sprites, meta }) => {
    Object.values(sprites).forEach((d) => (d.meta = meta));
    return {
      ...acc,
      ...sprites,
    };
  },
  {},
);
