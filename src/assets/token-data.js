export default {
  getData: () => ({
    sprites: {
      hide: {
        options: {
          type: 'resource',
          name: _('Hide'),
        },
        frame: {
          x: 0,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      'meat-cooked': {
        options: {
          type: 'resource',
          name: _('Cooked Meat'),
        },
        frame: {
          x: 113,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      berry: {
        options: {
          type: 'resource',
          name: _('Berry'),
        },
        frame: {
          x: 226,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      'berry-cooked': {
        options: {
          type: 'resource',
          name: _('Cooked Berry'),
        },
        frame: {
          x: 339,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      bone: {
        options: {
          type: 'resource',
          name: _('Bone'),
        },
        frame: {
          x: 452,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      'dino-egg': {
        options: {
          type: 'resource',
          name: _('Dino Egg'),
          text: [
            { title: _('Dino Egg') },
            _('Eat 2 Raw Dino Eggs to gain 1 HP and 1 Stamina'),
            _('Eat 1 Cooked Dino Egg to gain 3 HP and 1 Stamina'),
          ],
        },
        frame: {
          x: 0,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      'dino-egg-cooked': {
        options: {
          type: 'resource',
          name: _('Cooked Dino Egg'),
        },
        frame: {
          x: 113,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      fish: {
        options: {
          type: 'resource',
          name: _('Fish'),
          text: [
            { title: _('Fish') },
            _('Fish are treated like Meat for cooking and eating'),
            _('A character may only eat fish once per day'),
            _('Eating 2 Raw Fish gives you 1 Stamina'),
            _('Eating 1 Cooked Fish gives you 2 Stamina'),
          ],
        },
        frame: {
          x: 226,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      'fish-cooked': {
        options: {
          type: 'resource',
          name: _('Cooked Fish'),
        },
        frame: {
          x: 339,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      fkp: {
        options: {
          type: 'resource',
          name: _('Fire Knowledge Point'),
        },
        frame: {
          x: 452,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      'fkp-unlocked': {
        options: {
          type: 'resource',
          name: _('Unlocked Fire Knowledge Point'),
        },
        frame: {
          x: 0,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      'gem-b': {
        options: {
          type: 'resource',
          name: _('Gem'),
          text: [{ title: _('Gem') }, _('Trade value is 1:2 instead of 3:1')],
        },
        frame: {
          x: 113,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      'gem-p': {
        options: {
          type: 'resource',
          name: _('Gem'),
          text: [{ title: _('Gem') }, _('Trade value is 1:2 instead of 3:1')],
        },
        frame: {
          x: 226,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      'gem-y': {
        options: {
          type: 'resource',
          name: _('Gem'),
          text: [{ title: _('Gem') }, _('Trade value is 1:2 instead of 3:1')],
        },
        frame: {
          x: 339,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      fiber: {
        options: {
          type: 'resource',
          name: _('Fiber'),
        },
        frame: {
          x: 452,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      herb: {
        options: {
          type: 'resource',
          name: _('Herb'),
          text: [{ title: _('Medicinal Herb') }, _('Use a Medicinal Herb and 1 Stamina to remove a Physical Hindrance')],
        },
        frame: {
          x: 565,
          y: 0,
          w: 111,
          h: 111,
        },
      },
      meat: {
        options: {
          type: 'resource',
          name: _('Meat'),
        },
        frame: {
          x: 565,
          y: 113,
          w: 111,
          h: 111,
        },
      },
      rock: {
        options: {
          type: 'resource',
          name: _('Rock'),
        },
        frame: {
          x: 565,
          y: 226,
          w: 111,
          h: 111,
        },
      },
      stew: {
        options: {
          type: 'resource',
          name: _('Stew'),
          text: [{ title: _('Stew') }, _('Can be eaten once per day per character to reuse a once-per day character skill')],
        },
        frame: {
          x: 0,
          y: 339,
          w: 111,
          h: 111,
        },
      },
      trap: {
        options: {
          type: 'resource',
          name: _('Trap'),
          text: [
            { title: _('Trap') },
            _('When a Danger Card is drawn, roll the Fire Die. If the roll is equal to or greater than its life you may Trap It'),
          ],
        },
        frame: {
          x: 113,
          y: 339,
          w: 111,
          h: 111,
        },
      },
      wood: {
        options: {
          type: 'resource',
          name: _('Wood'),
        },
        frame: {
          x: 226,
          y: 339,
          w: 111,
          h: 111,
        },
      },
    },
    meta: {
      version: '1.0',
      image: 'token-spritesheet.png',
      css: 'token-card',
      size: {
        w: 678,
        h: 452,
      },
      scale: '1',
    },
  }),
};
