const itemsSprites = {
  sprites: {
    'bow-and-arrow': {
      options: { type: 'item', itemType: 'weapon', name: 'Bow And Arrow', damage: 3, range: 2, text: '' },
      frame: { x: 0, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    'medical-hut': {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Medical Hut',
        text: 'Remove 2 Physical Hindrance from a single character, once per Morning phase',
      },
      frame: { x: 439, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    'bone-club': {
      options: { type: 'item', itemType: 'weapon', name: 'Bone Club', damage: 3, range: 1, text: '' },
      frame: { x: 876, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    'bone-scythe': {
      options: { type: 'item', itemType: 'weapon', name: 'Bone Scythe', text: 'Take 1 Fiber when you draw a Fiber Card' },
      frame: { x: 1314, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    bag: {
      options: { type: 'item', itemType: 'tool', name: 'Bag', text: 'Take 1 Berry when you draw a Berry Card' },
      frame: { x: 1752, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    'bone-armor': {
      options: { type: 'item', itemType: 'tool', name: 'Bone Armor', text: 'Ignore all damage from a Danger! Card, twice per day' },
      frame: { x: 2190, y: 0, w: 438, h: 438 },
      rotate: 0,
    },
    'camp-walls': {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Camp Walls',
        text: 'Rival Tribe Night Event cards no longer activate their teal resource action',
      },
      frame: { x: 0, y: 439, w: 438, h: 438 },
      rotate: 0,
    },
    fire: { options: { type: 'game-piece', name: 'Fire' }, frame: { x: 439, y: 439, w: 438, h: 438 }, rotate: 0 },
    'hide-armor': {
      options: { type: 'item', itemType: 'tool', name: 'Hide Armor', text: 'Ignore all damage from a Danger! Card, once per day' },
      frame: { x: 876, y: 439, w: 438, h: 438 },
      rotate: 0,
    },
    'knowledge-hut': {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Knowledge Hut',
        text: 'When performing the Investigate Fire action, take 1 free FKP Token. Limit once per character each day',
      },
      frame: { x: 1314, y: 439, w: 438, h: 438 },
      rotate: 0,
    },
    skull: { options: { type: 'game-piece', name: 'Skull' }, frame: { x: 1756, y: 439, w: 434, h: 438 }, rotate: 0 },
    hatchet: {
      options: { type: 'item', itemType: 'tool', name: 'Hatchet', text: 'Take 1 wood when you draw a Wood Card' },
      frame: { x: 2190, y: 439, w: 438, h: 438 },
      rotate: 0,
    },
    club: {
      options: { type: 'item', itemType: 'weapon', name: 'Club', damage: 1, range: 1, text: '-1 max stamina' },
      frame: { x: 0, y: 880, w: 438, h: 438 },
      rotate: 0,
    },
    'cooking-hut': {
      options: { type: 'item', itemType: 'building', name: 'Cooking Hut', text: 'Gain an additional +2 HP when eating food' },
      frame: { x: 439, y: 880, w: 438, h: 438 },
      rotate: 0,
    },
    'carving-knife': {
      options: { type: 'item', itemType: 'tool', name: 'Carving Knife', text: 'Take 1 raw meat when you draw a Meat Card' },
      frame: { x: 876, y: 880, w: 438, h: 438 },
      rotate: 0,
    },
    'item-back': { options: { type: 'back', name: 'Item Back' }, frame: { x: 1314, y: 880, w: 438, h: 438 }, rotate: 0 },
    'sling-shot': {
      options: { type: 'item', itemType: 'weapon', name: 'Sling Shot', damage: 3, range: 2, text: 'Must discard 1 stone each use' },
      frame: { x: 1752, y: 880, w: 438, h: 438 },
      rotate: 0,
    },
    'pick-axe': {
      options: { type: 'item', itemType: 'tool', name: 'Pick Axe', text: 'Take 1 stone when you draw a Rock Card' },
      frame: { x: 2190, y: 880, w: 438, h: 438 },
      rotate: 0,
    },
    'planning-hut': {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Planning Hut',
        text: 'Twice per day, when drawing a Forage, Gather, Harvest, or Hunt card. Draw two and pick one. The extra card is discarded without being used',
      },
      frame: { x: 0, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    spear: {
      options: { type: 'item', itemType: 'weapon', name: 'Spear', damage: 2, range: 2, text: '' },
      frame: { x: 439, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    'sharp-stick': {
      options: { type: 'item', itemType: 'weapon', name: 'Sharp Stick', damage: 1, range: 1, text: '' },
      frame: { x: 876, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    shelter: {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Shelter',
        text: 'You do not take the 1 hp damage during the morning time phase',
      },
      frame: { x: 1314, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    'rock-knife': {
      options: { type: 'item', itemType: 'weapon', name: 'Rock Knife', damage: 2, range: 1, text: '' },
      frame: { x: 1752, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    'stone-hammer': {
      options: { type: 'item', itemType: 'tool', name: 'Stone Hammer', text: 'Crafting cost reduced by 2 for the equipped character' },
      frame: { x: 2190, y: 1320, w: 438, h: 438 },
      rotate: 0,
    },
    'mortar-and-pestle': {
      options: {
        type: 'item',
        itemType: 'tool',
        name: 'Mortar And Pestle',
        text: ['Removes stamina cost from using Medical Herbs for the equipped character', 'Does NOT give access to the Harvest deck'],
      },
      frame: { x: 0, y: 1760, w: 438, h: 438 },
      rotate: 0,
    },
    bandage: {
      options: {
        type: 'item',
        itemType: 'tool',
        name: 'Bandage',
        text: [
          '+1 Maximum Health',
          'While equipped, on Death, remove item from game and revive as normal',
          'Does NOT give access to the Harvest deck',
        ],
      },
      frame: { x: 439, y: 1760, w: 438, h: 438 },
      rotate: 0,
    },
    'skull-shield': {
      options: {
        type: 'item',
        itemType: 'tool',
        name: 'Skull Shield',
        text: ['+1 Maximum Health while equipped', 'Ignore all damage from a Danger! Card, twice per day'],
      },
      frame: { x: 876, y: 1760, w: 438, h: 438 },
      rotate: 0,
    },
    'cooking-pot': {
      options: {
        type: 'item',
        itemType: 'tool',
        name: 'Cooking Pot',
        text: 'You may cook 1 additional piece of food when performing the Cook Food action',
      },
      frame: { x: 1314, y: 1760, w: 438, h: 438 },
      rotate: 0,
    },
    'bone-claws': {
      options: { type: 'item', itemType: 'tool', name: 'Bone Claws' },
      frame: { x: 1752, y: 1760, w: 438, h: 438 },
      rotate: 0,
      text: 'Explore cost reduced by 2 Stamina for the equipped character',
    },
    'bone-flute': {
      options: { type: 'item', itemType: 'tool', name: 'Bone Flute' },
      frame: { x: 2190, y: 1760, w: 438, h: 438 },
      rotate: 0,
      text: 'Once per day, you may Soother a Danger! card. Soothed Danger! cards are ignored, but added back to the bottom of the deck they were drawn from',
    },
    'stock-hut': {
      options: {
        type: 'item',
        itemType: 'building',
        name: 'Stock Hut',
        text: 'Your resource exchange rate is only 2->1 (instead of 3->1) on Trade with Neighboring Trade actions',
      },
      frame: { x: 0, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
    whip: {
      options: {
        type: 'item',
        itemType: 'weapon',
        name: 'Whip',
        damage: 2,
        range: 1,
        text: 'After killing a Danger! card, pick any tribe member to heal 1 and shuffle the Danger! card back into its deck',
      },
      frame: { x: 439, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
    'fire-stick': {
      options: {
        type: 'item',
        itemType: 'weapon',
        name: 'Fire Stick',
        damage: 'X',
        range: 1,
        text: [
          "Roll the Fire Die before fighting a Danger!~ card, this roll value is the Fire Stick's damage",
          'You may spend up to 3 FKP tokens to increase X by 1 per FXP for 1 combat',
        ],
      },
      frame: { x: 876, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
    'rock-weapon': {
      options: {
        type: 'item',
        itemType: 'weapon',
        name: 'Rock',
        damage: 1,
        range: 2,
        text: ['Crafting this item only costs 1 stamina', 'After use, discard item back to the craft-able items area'],
      },
      frame: { x: 1314, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
    bola: {
      options: {
        type: 'item',
        itemType: 'weapon',
        name: 'Bola',
        damage: 2,
        range: 2,
        text: 'After use, spend 2 stamina to keep, or discard to craft-able items area',
      },
      frame: { x: 1752, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
    boomerang: {
      options: {
        type: 'item',
        itemType: 'weapon',
        name: 'Boomerang',
        damage: 2,
        range: 2,
        text: 'Once per day, reduce the life of any attacking Danger! card by 1',
      },
      frame: { x: 2190, y: 2200, w: 438, h: 438 },
      rotate: 0,
    },
  },
  meta: {
    version: '1.0',
    image: 'items-spritesheet.png',
    css: 'items-card',
    size: {
      w: 2628,
      h: 2640,
    },
    scale: '1',
  },
};
