import dojo from 'dojo';
export default {
  getData: () => ({
    sprites: {
      'character-board': {
        frame: {
          x: 1,
          y: 1,
          w: 722,
          h: 1468,
        },
        rotate: 90,
      },
      'track-normal': {
        options: {
          text: [
            { title: _('Morning Phase') },
            _('Remove (Wood Count) from fire pit'),
            _('Increase Day Count +1'),
            dojo.string.substitute(_('Each character takes ${count} Damage'), {
              count: 1,
            }),
            _('Refresh up to max stamina'),
            _('Trade items as desired'),
            _('Pass the first player token'),
          ],
        },
        frame: {
          x: 725,
          y: 1,
          w: 722,
          h: 1468,
        },
        rotate: 0,
      },
      'track-hard': {
        options: {
          text: [
            { title: _('Morning Phase') },
            _('Remove (Wood Count) from fire pit'),
            _('Increase Day Count +1'),
            dojo.string.substitute(_('Each character takes ${count} Damage'), {
              count: 2,
            }),
            _('Refresh up to max stamina'),
            _('Trade items as desired'),
            _('Pass the first player token'),
          ],
        },
        frame: {
          x: 1449,
          y: 1,
          w: 722,
          h: 1468,
        },
        rotate: 0,
      },
      board: {
        frame: {
          x: 2173,
          y: 1,
          w: 722,
          h: 1468,
        },
        rotate: 90,
      },
    },
    meta: {
      version: '1.0',
      image: 'boards-spritesheet.png',
      css: 'boards-card',
      size: {
        w: 2897,
        h: 1470,
      },
      scale: '1',
    },
  }),
};
