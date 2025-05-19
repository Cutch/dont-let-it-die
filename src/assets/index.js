import boards from './boards-data';
import characters from './characters-data';
import decks from './decks-data';
import expansion from './expansion-data';
import items from './items-data';
import necklace from './necklace-data';
import tech from './tech-data';
import token from './token-data';
import upgrades from './upgrades-data';
let dataCache = null;
export const getAllData = () => {
  if (dataCache) return dataCache;
  dataCache = [boards, characters, decks, expansion, items, necklace, tech, token, upgrades].reduce((acc, data) => {
    const { sprites, meta } = data.getData();
    Object.keys(sprites).forEach((k) => ((sprites[k].meta = meta), (sprites[k].id = k)));
    return {
      ...acc,
      ...sprites,
    };
  }, {});
  return dataCache;
};
