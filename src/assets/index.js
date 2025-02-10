const allSprites = [
  boardsSprites,
  charactersSprites,
  decksSprites,
  expansionSprites,
  itemsSprites,
  upgradesSprites,
].reduce((acc, { sprites, meta }) => {
  Object.values(sprites).forEach((d) => (d.meta = meta));
  return {
    ...acc,
    ...sprites,
  };
}, {});
