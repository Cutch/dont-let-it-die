const boardsSprites = {
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
      text: [
        'Remove (Wood Count) from fire pit',
        'Increase Day Count +1',
        'Each character takes 1 Damage',
        'Refresh up to max stamina',
        'Trade items as desired',
        'Pass first player token to the left',
      ],
      frame: {
        x: 725,
        y: 1,
        w: 722,
        h: 1468,
      },
      rotate: 0,
    },
    'track-hard': {
      text: [
        'Remove (Wood Count) from fire pit',
        'Increase Day Count +1',
        'Each character takes 1 Damage',
        'Refresh up to max stamina',
        'Trade items as desired',
        'Pass first player token to the left',
      ],
      frame: {
        x: 1449,
        y: 1,
        w: 722,
        h: 1468,
      },
      rotate: 0,
    },
    instructions: {
      frame: {
        x: 2173,
        y: 1,
        w: 722,
        h: 1468,
      },
      rotate: 90,
    },
    board: {
      frame: {
        x: 2897,
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
      w: 3621,
      h: 1470,
    },
    scale: '1',
  },
};
