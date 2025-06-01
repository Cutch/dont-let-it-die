import dojo from 'dojo';
export default {
  getData: () => ({
    sprites: {
      'warmth-1': {
        options: {
          text: [
            { title: _('Warmth') + ' 1' },
            dojo.string.substitute(_('${number} Max Stamina'), {
              number: '+1',
            }),
          ],
        },
      },
      'warmth-2': {
        options: {
          text: [
            { title: _('Warmth') + ' 2' },
            dojo.string.substitute(_('${number} Max Stamina'), {
              number: '+1',
            }),
          ],
        },
      },
      'warmth-3': {
        options: {
          text: [
            { title: _('Warmth') + ' 3' },
            dojo.string.substitute(_('${number} Max Stamina'), {
              number: '+1',
            }),
          ],
        },
      },
      'cooking-1': {
        options: {
          text: [
            { title: _('Cooking') + ' 1' },
            dojo.string.substitute(_('Unlocks Cooked ${resource}'), {
              resource: _('Berries'),
            }),
          ],
        },
      },
      'cooking-2': {
        options: {
          text: [
            { title: _('Cooking') + ' 2' },
            dojo.string.substitute(_('Unlocks Cooked ${resource}'), {
              resource: _('Meat'),
            }),
          ],
        },
      },
      'crafting-1': {
        options: {
          text: [
            { title: _('Crafting') + ' 1' },
            dojo.string.substitute(_('Allows crafting all items marked with a ${color} circle'), {
              color: _('yellow'),
            }),
          ],
        },
      },
      'crafting-2': {
        options: {
          text: [
            { title: _('Crafting') + ' 2' },
            dojo.string.substitute(_('Allows crafting all items marked with a ${color} circle'), {
              color: _('blue'),
            }),
          ],
        },
      },
      'crafting-3': {
        options: {
          text: [
            { title: _('Crafting') + ' 3' },
            dojo.string.substitute(_('Allows crafting all items marked with a ${color} circle'), {
              color: _('red'),
            }),
          ],
        },
      },
      spices: { options: { text: [{ title: _('Spices') }, _('Eating food grants +1 Health')] } },
      relaxation: {
        options: {
          text: [
            { title: _('Relaxation') },
            dojo.string.substitute(_('${number} Max Health'), {
              number: '+2',
            }),
            _('instantly heal +2 Health when unlocked'),
          ],
        },
      },
      'forage-1': {
        options: {
          text: [
            { title: _('Forage') + ' 1' },
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Berry'),
              card: _('Berry'),
            }),
          ],
        },
      },
      'forage-2': {
        options: {
          text: [
            { title: _('Forage') + ' 2' },
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Fiber'),
              card: _('Fiber'),
            }),
          ],
        },
      },
      'resource-1': {
        options: {
          text: [
            { title: _('Resource') + ' 1' },
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Rock'),
              card: _('Rock'),
            }),
          ],
        },
      },
      'resource-2': {
        options: {
          text: [
            { title: _('Resource') + ' 2' },
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Wood'),
              card: _('Wood'),
            }),
          ],
        },
      },
      'hunt-1': {
        options: {
          text: [
            { title: _('Hunt') + ' 1' },
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Meat'),
              card: _('Meat'),
            }),
          ],
        },
      },
      'fire-starter': { options: { text: [{ title: _('Fire Starter') }, _('WIN!')] } },
      'knowledge-tree-easy': {
        upgrades: {
          'warmth-1': {
            x: 140,
            y: 125,
          },
          'warmth-2': {
            x: 338,
            y: 125,
          },
          'warmth-3': {
            x: 538,
            y: 125,
          },

          'cooking-1': {
            x: 140,
            y: 219,
          },
          'crafting-1': {
            x: 338,
            y: 219,
          },
          spices: {
            x: 538,
            y: 219,
          },

          'cooking-2': {
            x: 140,
            y: 313,
          },
          'crafting-2': {
            x: 338,
            y: 313,
          },
          'fire-starter': {
            x: 538,
            y: 313,
          },
        },
        frame: {
          y: 1,
          x: 1,
          w: 948,
          h: 722,
        },
      },
      'knowledge-tree-normal': {
        upgrades: {
          'warmth-1': {
            x: 140,
            y: 33,
          },
          'warmth-2': {
            x: 338,
            y: 33,
          },
          'warmth-3': {
            x: 538,
            y: 33,
          },

          'cooking-1': {
            x: 140,
            y: 125,
          },
          'cooking-2': {
            x: 338,
            y: 125,
          },
          relaxation: {
            x: 538,
            y: 125,
          },

          'crafting-1': {
            x: 140,
            y: 224,
          },
          'crafting-2': {
            x: 338,
            y: 222,
          },
          'crafting-3': {
            x: 538,
            y: 222,
          },

          'forage-1': {
            x: 140,
            y: 316,
          },
          'resource-1': {
            x: 338,
            y: 316,
          },
          'resource-2': {
            x: 538,
            y: 316,
          },

          'forage-2': {
            x: 140,
            y: 408,
          },
          'hunt-1': {
            x: 338,
            y: 408,
          },
          'fire-starter': {
            x: 538,
            y: 408,
          },
        },
        frame: {
          y: 2173,
          x: 1,
          w: 948,
          h: 722,
        },
      },
      'knowledge-tree-normal+': {
        upgrades: {
          'crafting-1': {
            x: 140,
            y: 33,
          },
          'resource-1': {
            x: 338,
            y: 33,
          },
          'crafting-2': {
            x: 538,
            y: 33,
          },

          'cooking-1': {
            x: 140,
            y: 125,
          },
          'warmth-1': {
            x: 338,
            y: 125,
          },
          'crafting-3': {
            x: 538,
            y: 125,
          },

          'warmth-2': {
            x: 140,
            y: 224,
          },
          'forage-1': {
            x: 338,
            y: 222,
          },
          relaxation: {
            x: 538,
            y: 222,
          },

          'forage-2': {
            x: 140,
            y: 316,
          },
          'warmth-3': {
            x: 338,
            y: 316,
          },
          'resource-2': {
            x: 538,
            y: 316,
          },

          'cooking-2': {
            x: 140,
            y: 408,
          },
          'hunt-1': {
            x: 338,
            y: 408,
          },
          'fire-starter': {
            x: 538,
            y: 408,
          },
        },
        frame: {
          y: 725,
          x: 1,
          w: 948,
          h: 722,
        },
      },
      'knowledge-tree-hard': {
        upgrades: {
          'warmth-1': {
            x: 140,
            y: 33,
          },
          'cooking-1': {
            x: 338,
            y: 33,
          },
          'warmth-3': {
            x: 538,
            y: 33,
          },

          'crafting-1': {
            x: 140,
            y: 125,
          },
          'warmth-2': {
            x: 338,
            y: 125,
          },
          'cooking-2': {
            x: 538,
            y: 125,
          },

          'resource-1': {
            x: 140,
            y: 224,
          },
          'crafting-2': {
            x: 338,
            y: 222,
          },
          'forage-1': {
            x: 538,
            y: 222,
          },

          'crafting-3': {
            x: 140,
            y: 316,
          },
          relaxation: {
            x: 338,
            y: 316,
          },
          'forage-2': {
            x: 538,
            y: 316,
          },

          'resource-2': {
            x: 140,
            y: 408,
          },
          'hunt-1': {
            x: 338,
            y: 408,
          },
          'fire-starter': {
            x: 538,
            y: 408,
          },
        },
        frame: {
          y: 1449,
          x: 1,
          w: 948,
          h: 722,
        },
      },
    },
    meta: {
      version: '1.0',
      image: 'tech-spritesheet.png',
      css: 'tech-card',
      size: {
        w: 950,
        h: 2896,
      },
      scale: '1',
    },
  }),
};
