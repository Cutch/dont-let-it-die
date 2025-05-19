export default {
  getData: () => ({
    sprites: {
      'bow-and-arrow': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Bow And Arrow') }, _('Item Type') + ': ' + _('Weapon'), _('Damage') + ': ' + 3, _('Range') + ': ' + 2],
        },
        frame: { x: 0, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      'medical-hut': {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Medical Hut',
          text: [
            { title: _('Medical Hut') },
            _('Item Type') + ': ' + _('Building'),
            _('Remove 2 Physical Hindrance from a single character, once per Morning phase'),
          ],
        },
        frame: { x: 439, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      'bone-club': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Bone Club') }, _('Item Type') + ': ' + _('Weapon'), _('Damage') + ': ' + 3, _('Range') + ': ' + 1],
        },
        frame: { x: 876, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      'bone-scythe': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Bone Scythe') }, _('Item Type') + ': ' + _('Weapon'), _('Take 1 Fiber when you draw a Fiber Card')],
        },
        frame: { x: 1314, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      bag: {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Bag') }, _('Item Type') + ': ' + _('Tool'), _('Take 1 Berry when you draw a Berry Card')],
        },
        frame: { x: 1752, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      'bone-armor': {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Bone Armor') }, _('Item Type') + ': ' + _('Tool'), _('Ignore all damage from a Danger! Card, twice per day')],
        },
        frame: { x: 2190, y: 0, w: 438, h: 438 },
        rotate: 0,
      },
      'camp-walls': {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Camp Walls',
          text: [
            { title: _('Camp Walls') },
            _('Item Type') + ': ' + _('Building'),
            _('Rival Tribe Night Event cards no longer activate their teal resource action'),
          ],
        },
        frame: { x: 0, y: 439, w: 438, h: 438 },
        rotate: 0,
      },
      fire: { options: { type: 'game-piece', name: 'Fire' }, frame: { x: 439, y: 439, w: 438, h: 438 }, rotate: 0 },
      'hide-armor': {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Hide Armor') }, _('Item Type') + ': ' + _('Tool'), _('Ignore all damage from a Danger! Card, once per day')],
        },
        frame: { x: 876, y: 439, w: 438, h: 438 },
        rotate: 0,
      },
      'knowledge-hut': {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Knowledge Hut',
          text: [
            { title: _('Knowledge Hut') },
            _('Item Type') + ': ' + _('Building'),
            _('When performing the Investigate Fire action, take 1 free FKP Token. Limit once per character each day'),
          ],
        },
        frame: { x: 1314, y: 439, w: 438, h: 438 },
        rotate: 0,
      },
      skull: { options: { type: 'game-piece', name: 'Skull' }, frame: { x: 1756, y: 439, w: 434, h: 438 }, rotate: 0 },
      hatchet: {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Hatchet') }, _('Item Type') + ': ' + _('Tool'), _('Take 1 wood when you draw a Wood Card')],
        },
        frame: { x: 2190, y: 439, w: 438, h: 438 },
        rotate: 0,
      },
      club: {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Club') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 1,
            _('Range') + ': ' + 1,
            _('-1 max stamina'),
          ],
        },
        frame: { x: 0, y: 880, w: 438, h: 438 },
        rotate: 0,
      },
      'cooking-hut': {
        options: {
          type: 'item',
          itemType: 'building',
          text: [{ title: _('Cooking Hut') }, _('Item Type') + ': ' + _('Building'), _('Gain an additional +2 HP when eating food')],
        },
        frame: { x: 439, y: 880, w: 438, h: 438 },
        rotate: 0,
      },
      'carving-knife': {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Carving Knife') }, _('Item Type') + ': ' + _('Tool'), _('Take 1 raw meat when you draw a Meat Card')],
        },
        frame: { x: 876, y: 880, w: 438, h: 438 },
        rotate: 0,
      },
      'item-back': { options: { type: 'back', name: 'Item Back' }, frame: { x: 1314, y: 880, w: 438, h: 438 }, rotate: 0 },
      'sling-shot': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Sling Shot') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 3,
            _('Range') + ': ' + 2,
            _('Must discard 1 stone each use'),
          ],
        },
        frame: { x: 1752, y: 880, w: 438, h: 438 },
        rotate: 0,
      },
      'pick-axe': {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [{ title: _('Pick Axe') }, _('Item Type') + ': ' + _('Tool'), _('Take 1 stone when you draw a Rock Card')],
        },
        frame: { x: 2190, y: 880, w: 438, h: 438 },
        rotate: 0,
      },
      'planning-hut': {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Planning Hut',
          text: [
            { title: _('Planning Hut') },
            _('Item Type') + ': ' + _('Building'),
            _(
              'Twice per day, when drawing a Forage, Gather, Harvest, or Hunt card. Draw two and pick one. The extra card is discarded without being used',
            ),
          ],
        },
        frame: { x: 0, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      spear: {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Spear') }, _('Item Type') + ': ' + _('Weapon'), _('Damage') + ': ' + 2, _('Range') + ': ' + 2],
        },
        frame: { x: 439, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      'sharp-stick': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Sharp Stick') }, _('Item Type') + ': ' + _('Weapon'), _('Damage') + ': ' + 1, _('Range') + ': ' + 1],
        },
        frame: { x: 876, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      shelter: {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Shelter',
          text: [
            { title: _('Shelter') },
            _('Item Type') + ': ' + _('Building'),
            _('You do not take the 1 hp damage during the morning time phase'),
          ],
        },
        frame: { x: 1314, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      'rock-knife': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [{ title: _('Rock Knife') }, _('Item Type') + ': ' + _('Weapon'), _('Damage') + ': ' + 2, _('Range') + ': ' + 1],
        },
        frame: { x: 1752, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      'stone-hammer': {
        options: {
          type: 'item',
          itemType: 'tool',
          text: [
            { title: _('Stone Hammer') },
            _('Item Type') + ': ' + _('Tool'),
            _('Crafting cost reduced by 2 for the equipped character'),
          ],
        },
        frame: { x: 2190, y: 1320, w: 438, h: 438 },
        rotate: 0,
      },
      'mortar-and-pestle': {
        options: {
          type: 'item',
          itemType: 'tool',
          name: 'Mortar And Pestle',
          text: [
            { title: _('Mortar And Pestle') },
            _('Item Type') + ': ' + _('Tool'),
            _('Removes stamina cost from using Medical Herbs for the equipped character'),
            _('Does NOT give access to the Harvest deck'),
          ],
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
            { title: _('Bandage') },
            _('Item Type') + ': ' + _('Tool'),
            _('+1 Maximum Health'),
            _('While equipped, on Death, remove item from game and revive as normal'),
            _('Does NOT give access to the Harvest deck'),
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
          text: [
            { title: _('Skull Shield') },
            _('Item Type') + ': ' + _('Tool'),
            _('+1 Maximum Health while equipped'),
            _('Ignore all damage from a Danger! Card, twice per day'),
          ],
        },
        frame: { x: 876, y: 1760, w: 438, h: 438 },
        rotate: 0,
      },
      'cooking-pot': {
        options: {
          type: 'item',
          itemType: 'tool',
          name: 'Cooking Pot',
          text: [
            { title: _('Cooking Pot') },
            _('Item Type') + ': ' + _('Tool'),
            _('You may cook 1 additional piece of food when performing the Cook Food action'),
          ],
        },
        frame: { x: 1314, y: 1760, w: 438, h: 438 },
        rotate: 0,
      },
      'bone-claws': {
        options: {
          type: 'item',
          itemType: 'tool',
          name: 'Bone Claws',
          text: [
            { title: _('Bone Claws') },
            _('Item Type') + ': ' + _('Tool'),
            _('Explore cost reduced by 2 Stamina for the equipped character'),
          ],
        },
        frame: { x: 1752, y: 1760, w: 438, h: 438 },
        rotate: 0,
      },
      'bone-flute': {
        options: {
          type: 'item',
          itemType: 'tool',
          name: 'Bone Flute',
          text: [
            { title: _('Bone Flute') },
            _('Item Type') + ': ' + _('Tool'),
            _(
              'Once per day, you may Soother a Danger! card. Soothed Danger! cards are ignored, but added back to the bottom of the deck they were drawn from',
            ),
          ],
        },
        frame: { x: 2190, y: 1760, w: 438, h: 438 },
        rotate: 0,
      },
      'stock-hut': {
        options: {
          type: 'item',
          itemType: 'building',
          name: 'Stock Hut',
          text: [
            { title: _('Stock Hut') },
            _('Item Type') + ': ' + _('Building'),
            _('Your resource exchange rate is only 2->1 (instead of 3->1) on Trade with Neighboring Trade actions'),
          ],
        },
        frame: { x: 0, y: 2200, w: 438, h: 438 },
        rotate: 0,
      },
      whip: {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Whip') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 2,
            _('Range') + ': ' + 1,
            _('After killing a Danger! card, pick any tribe member to heal 1 and shuffle the Danger! card back into its deck'),
          ],
        },
        frame: { x: 439, y: 2200, w: 438, h: 438 },
        rotate: 0,
      },
      'fire-stick': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Fire Stick') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage:') + ' X',
            _('Range') + ': ' + 1,
            _("Roll the Fire Die before fighting a Danger!~ card, this roll value is the Fire Stick's damage"),
            _('You may spend up to 3 FKP tokens to increase X by 1 per FXP for 1 combat'),
          ],
        },
        frame: { x: 876, y: 2200, w: 438, h: 438 },
        rotate: 0,
      },
      'rock-weapon': {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Rock') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 1,
            _('Range') + ': ' + 2,
            _('Crafting this item only costs 1 stamina'),
            _('After use, discard item back to the craft-able items area'),
          ],
        },
        frame: { x: 1314, y: 2200, w: 438, h: 438 },
        rotate: 0,
      },
      bola: {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Bola') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 2,
            _('Range') + ': ' + 2,
            _('After use, spend 2 stamina to keep, or discard to craft-able items area'),
          ],
        },
        frame: { x: 1752, y: 2200, w: 438, h: 438 },
        rotate: 0,
      },
      boomerang: {
        options: {
          type: 'item',
          itemType: 'weapon',
          text: [
            { title: _('Boomerang') },
            _('Item Type') + ': ' + _('Weapon'),
            _('Damage') + ': ' + 2,
            _('Range') + ': ' + 2,
            _('Once per day, reduce the life of any attacking Danger! card by 1'),
          ],
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
  }),
};
