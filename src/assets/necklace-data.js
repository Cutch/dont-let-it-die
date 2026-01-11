import dojo from 'dojo';
export default {
  getData: () => ({
    sprites: {
      'gem-b-necklace': {
        options: {
          type: 'item',
          itemType: 'necklace',
          text: [
            { title: _('Blue Necklace') },
            dojo.string.substitute(_('${number} Max Health'), {
              number: '+1',
            }),
            _('Requires the Jewelry Discovery to be Unlocked'),
          ],
        },
        frame: {
          x: 0,
          y: 0,
          w: 440,
          h: 440,
        },
        rotate: 0,
      },
      'gem-y-necklace': {
        options: {
          type: 'item',
          itemType: 'necklace',
          text: [
            { title: _('Yellow Necklace') },
            dojo.string.substitute(_('${number} Max Stamina'), {
              number: '+1',
            }),
            _('Requires the Jewelry Discovery to be Unlocked'),
          ],
        },
        frame: {
          x: 440,
          y: 0,
          w: 440,
          h: 440,
        },
        rotate: 0,
      },
      'gem-p-necklace': {
        options: {
          type: 'item',
          itemType: 'necklace',
          text: [
            { title: _('Purple Necklace') },
            _('Once per day re-roll any Fire Die roll'),
            _('Requires the Jewelry Discovery to be Unlocked'),
          ],
        },
        frame: {
          x: 880,
          y: 0,
          w: 440,
          h: 440,
        },
        rotate: 0,
      },
    },
    meta: {
      version: '1.0',
      image: 'necklace-spritesheet.png',
      css: 'necklace-card',
      size: {
        w: 1314,
        h: 440,
      },
      scale: '1',
    },
  }),
};
