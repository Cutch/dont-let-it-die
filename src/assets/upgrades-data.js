export default {
  sprites: {
    '1-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Charcoal Writing') },
          { title: _('Cost:') + ' ' + 6 },
          _('After this is unlocked, future Discoveries cost 1 less to unlock'),
        ],
      },
      frame: {
        x: 0,
        y: 0,
        w: 276,
        h: 123,
      },
    },
    '1-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Tracking') }, { title: _('Cost:') + ' ' + 5 }, 'Killing a Danger! card gives +1 meat'],
      },
      frame: {
        x: 276,
        y: 0,
        w: 276,
        h: 123,
      },
    },
    '10-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Flint') },
          { title: _('Cost:') + ' ' + 6 },
          _(
            'Once per day, spend 1 Rock to roll a second time on the Investigate Fire action, adding both together, but subtract 1 from the result',
          ),
        ],
      },
      frame: {
        x: 552,
        y: 0,
        w: 276,
        h: 123,
      },
    },
    '10-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Fire Stoking') },
          { title: _('Cost:') + ' ' + 8 },
          _('Once per day, if you added at least 1 Wood token to the fire pit, take 2 FKP tokens'),
        ],
      },
      frame: {
        x: 0,
        y: 123,
        w: 276,
        h: 123,
      },
    },
    '11-A': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Haggle') }, { title: _('Cost:') + ' ' + 4 }, 'Once per day, you may trade at 2:1 instead of 3:1'],
      },
      frame: {
        x: 276,
        y: 123,
        w: 276,
        h: 123,
      },
    },
    '11-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Trade Routes') }, { title: _('Cost:') + ' ' + 5 }, 'Trading costs 0 stamina'],
      },
      frame: {
        x: 552,
        y: 123,
        w: 276,
        h: 123,
      },
    },
    '12-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Planning') },
          { title: _('Cost:') + ' ' + 6 },
          _('When using the Investigate Fire action as the last action of a turn, gain 1 FKP'),
        ],
      },
      frame: {
        x: 0,
        y: 246,
        w: 276,
        h: 123,
      },
    },
    '12-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Focus') },
          { title: _('Cost:') + ' ' + 6 },
          _('Once per day, take 2x your Investigate Fire action roll. Use this BEFORE you roll'),
        ],
      },
      frame: {
        x: 276,
        y: 246,
        w: 276,
        h: 123,
      },
    },
    '13-A': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Bone Efficiency') }, { title: _('Cost:') + ' ' + 4 }, 'Take +1 Bone when drawing a Bone card'],
      },
      frame: {
        x: 552,
        y: 246,
        w: 276,
        h: 123,
      },
    },
    '13-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Hide Efficiency') }, { title: _('Cost:') + ' ' + 4 }, 'Take +1 Hide when drawing a Hide card'],
      },
      frame: {
        x: 0,
        y: 369,
        w: 276,
        h: 123,
      },
    },
    '14-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Medicinal Herb Efficiency') },
          { title: _('Cost:') + ' ' + 5 },
          _('Take +1 Medicinal Herb when drawing a Medicinal Herb card'),
        ],
      },
      frame: {
        x: 276,
        y: 369,
        w: 276,
        h: 123,
      },
    },
    '14-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Dino Egg Efficiency') }, { title: _('Cost:') + ' ' + 5 }, 'Take +1 Dino Egg when drawing a Dino Egg card'],
      },
      frame: {
        x: 552,
        y: 369,
        w: 276,
        h: 123,
      },
    },
    '15-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Jewelry') },
          { title: _('Cost:') + ' ' + 5 },
          _('Once per day, use a Craft action and spend 1 Gemstone and 1 Fiber to craft a Necklace. See rulebook for details'),
        ],
      },
      frame: {
        x: 0,
        y: 492,
        w: 276,
        h: 123,
      },
    },
    '15-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Recycling') },
          { title: _('Cost:') + ' ' + 4 },
          _('Once unlocked, you can remove an owned Item from the game during your turn and take up to 2 Resources from the Crafting Cost'),
        ],
      },
      frame: {
        x: 276,
        y: 492,
        w: 276,
        h: 123,
      },
    },
    '16-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Tinder') },
          { title: _('Cost:') + ' ' + 6 },
          _(
            'Once per Morning phase, you may remove 1 Fiber token from the game to reduce the amount of Wood taken out by 1 in the morning',
          ),
        ],
      },
      frame: {
        x: 552,
        y: 492,
        w: 276,
        h: 123,
      },
    },
    '16-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Fire Watch') },
          { title: _('Cost:') + ' ' + 8 },
          _('Once per Morning phase, you may deal 1 damage to all Tribe members to reduce the Wood taken out by 1 for that morning'),
        ],
      },
      frame: {
        x: 0,
        y: 615,
        w: 276,
        h: 123,
      },
    },
    '2-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Smoke Cover') },
          { title: _('Cost:') + ' ' + 4 },
          _('Roll 2 times, pick the lowest value for all Rival Tribe Night Event cards'),
        ],
      },
      frame: {
        x: 276,
        y: 615,
        w: 276,
        h: 123,
      },
    },
    '2-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Revenge') },
          { title: _('Cost:') + ' ' + 8 },
          _('When drawing a Rival Tribe Night Event, gain your roll in resources instead of losing them'),
        ],
      },
      frame: {
        x: 552,
        y: 615,
        w: 276,
        h: 123,
      },
    },
    '3-A': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Hot Rock Sauna') }, { title: _('Cost:') + ' ' + 6 }, _('+3 Max HP'), _('-1 Max Stamina')],
      },
      frame: {
        x: 0,
        y: 738,
        w: 276,
        h: 123,
      },
    },
    '3-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Hot Rock Walking') }, { title: _('Cost:') + ' ' + 7 }, _('+2 Max Stamina'), _('-1 Max HP')],
      },
      frame: {
        x: 276,
        y: 738,
        w: 276,
        h: 123,
      },
    },
    '4-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Smoked Food') },
          { title: _('Cost:') + ' ' + 4 },
          _('Eating Food: Excess healing over max HP may be given to a tribe member'),
        ],
      },
      frame: {
        x: 552,
        y: 738,
        w: 276,
        h: 123,
      },
    },
    '4-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('First Aid') },
          { title: _('Cost:') + ' ' + 6 },
          _('You only need to eat 1 Cooked Meat instead of 3 to revive after death'),
        ],
      },
      frame: {
        x: 0,
        y: 861,
        w: 276,
        h: 123,
      },
    },
    '5-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Controlled Burn') },
          { title: _('Cost:') + ' ' + 6 },
          _('Once per day, spend 2 FKP to take 3 Cooked Meat and remove 1 Fiber from the game'),
        ],
      },
      frame: {
        x: 276,
        y: 861,
        w: 276,
        h: 123,
      },
    },
    '5-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Map Making') },
          { title: _('Cost:') + ' ' + 6 },
          _('Spend 2 Stamina, pick a card from a discard pile, shuffle it back into its resource deck'),
        ],
      },
      frame: {
        x: 552,
        y: 861,
        w: 276,
        h: 123,
      },
    },
    '6-A': {
      options: {
        type: 'upgrade',

        text: [
          { title: _('Berry Farming') },
          { title: _('Cost:') + ' ' + 5 },
          _('Remove 1 Berry token from the game. Each time you get a Berry token at the start of every day phase'),
        ],
      },
      frame: {
        x: 828,
        y: 0,
        w: 276,
        h: 123,
      },
    },
    '6-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Meditation') },
          { title: _('Cost:') + ' ' + 5 },
          _('All tribe members instantly heal back up to their Max HP when this is unlocked'),
        ],
      },
      frame: {
        x: 828,
        y: 123,
        w: 276,
        h: 123,
      },
    },
    '7-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Rest') },
          { title: _('Cost:') + ' ' + 5 },
          _('Once per day, spend 4 stamina to heal yourself for 1 hp and remove a Physical Hindrance card'),
        ],
      },
      frame: {
        x: 828,
        y: 246,
        w: 276,
        h: 123,
      },
    },
    '7-B': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Clarity') },
          { title: _('Cost:') + ' ' + 7 },
          _(
            'Remove all Mental Hindrances in play. You cannot be affected by them anymore, but max Physical Hindrance limit is increased by 1',
          ),
        ],
      },
      frame: {
        x: 828,
        y: 369,
        w: 276,
        h: 123,
      },
    },
    '8-A': {
      options: {
        type: 'upgrade',
        text: [
          { title: _('Cooperation') },
          { title: _('Cost:') + ' ' + 5 },
          _('You may pass the First Player token to any other character instead of to the left'),
        ],
      },
      frame: {
        x: 828,
        y: 492,
        w: 276,
        h: 123,
      },
    },
    '8-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Resourceful') }, { title: _('Cost:') + ' ' + 4 }, 'When you draw a Nothing card, gain 1 Stamina'],
      },
      frame: {
        x: 828,
        y: 615,
        w: 276,
        h: 123,
      },
    },
    '9-A': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Torches') }, { title: _('Cost:') + ' ' + 6 }, 'Danger! cards deal 1 less damage when you fail to kill them'],
      },
      frame: {
        x: 828,
        y: 738,
        w: 276,
        h: 123,
      },
    },
    '9-B': {
      options: {
        type: 'upgrade',
        text: [{ title: _('Tempering') }, { title: _('Cost:') + ' ' + 7 }, 'Weapons have +1 damage against Danger! cards'],
      },
      frame: {
        x: 828,
        y: 861,
        w: 276,
        h: 123,
      },
    },
  },
  meta: {
    version: '1.0',
    css: 'upgrades-card',
    size: {
      w: 1104,
      h: 984,
    },
    scale: '1',
  },
};
