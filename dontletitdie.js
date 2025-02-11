var boardsSprites = {
    sprites: {
        "character-board": {
            frame: {
                x: 1,
                y: 1,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        "knowledge-tree-normal+": {
            frame: {
                x: 1449,
                y: 1,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        "knowledge-tree-hard": {
            frame: {
                x: 2173,
                y: 1,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        "knowledge-tree-normal": {
            frame: {
                x: 2897,
                y: 1,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        "knowledge-tree-easy": {
            frame: {
                x: 725,
                y: 1471,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        "track-normal": {
            frame: {
                x: 725,
                y: 1,
                w: 722,
                h: 1468,
            },
            rotate: 0,
        },
        "track-hard": {
            frame: {
                x: 1,
                y: 1471,
                w: 722,
                h: 1468,
            },
            rotate: 0,
        },
        instructions: {
            frame: {
                x: 1449,
                y: 1471,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        board: {
            frame: {
                x: 2173,
                y: 1471,
                w: 722,
                h: 1468,
            },
            rotate: 90,
        },
        dice: {
            frame: {
                x: 2897,
                y: 1471,
                w: 302,
                h: 467,
            },
            rotate: 90,
        },
    },
    meta: {
        version: "1.0",
        image: "boards-spritesheet.png",
        css: "boards-card",
        size: {
            w: 3620,
            h: 2940,
        },
        scale: "1",
    },
};
var charactersSprites = {
    sprites: {
        Gronk: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Grub: {
            options: {
                type: 'character',
            },
            frame: {
                x: 395,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Kara: {
            options: {
                type: 'character',
            },
            frame: {
                x: 789,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Cron: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1183,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Dub: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1577,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Faye: {
            options: {
                expansion: 'hindrance',
                type: 'character',
            },
            frame: {
                x: 1971,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        Ajax: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        Atouk: {
            options: {
                type: 'character',
            },
            frame: {
                x: 395,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        Ayla: {
            options: {
                type: 'character',
            },
            frame: {
                x: 789,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        River: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1183,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        Sig: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1577,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        Tara: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1971,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        Nirv: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Oof: {
            options: {
                expansion: 'hindrance',
                type: 'character',
            },
            frame: {
                x: 395,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Rex: {
            options: {
                type: 'character',
            },
            frame: {
                x: 789,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Mabe: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1183,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Nanuk: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1577,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Nibna: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1971,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
        Zeebo: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        Thunk: {
            options: {
                type: 'character',
            },
            frame: {
                x: 395,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        Tiku: {
            expansion: 'hindrance',
            options: {
                type: 'character',
            },
            frame: {
                x: 789,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        Vog: {
            options: {
                type: 'character',
            },
            frame: {
                x: 1183,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        DiceThing: {
            options: {
                type: 'instructions',
            },
            frame: {
                x: 1577,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        'back-character': {
            options: {
                type: 'back',
            },
            frame: {
                x: 1971,
                y: 1891,
                w: 392,
                h: 628,
            },
        },
        'back-character-hindrance': {
            options: {
                type: 'back',
            },
            frame: {
                x: 2365,
                y: 1,
                w: 392,
                h: 628,
            },
        },
        'instructions-1': {
            rotate: -90,
            options: {
                type: 'instructions',
            },
            frame: {
                x: 2365,
                y: 631,
                w: 392,
                h: 628,
            },
        },
        'instructions-2': {
            rotate: -90,
            options: {
                type: 'instructions',
            },
            frame: {
                x: 2365,
                y: 1261,
                w: 392,
                h: 628,
            },
        },
    },
    meta: {
        version: '1.0',
        image: 'characters-spritesheet.png',
        css: 'characters-card',
        size: {
            w: 2758,
            h: 2520,
        },
        scale: '1',
    },
};
var decksSprites = {
    sprites: {
        "explore-7_0": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 1,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_1": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 310,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_10": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 619,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_11": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 928,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_12": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 1237,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_13": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 1546,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_14": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 1855,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_15": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 2164,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_4": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 2473,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_5": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 2782,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_6": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 3091,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_7": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 3400,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_8": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 1,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-7_9": {
            options: {
                type: "card",
                deck: "explore",
            },
            frame: {
                x: 310,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "explore-back": {
            options: {
                deck: "explore",
                type: "back",
            },
            frame: {
                x: 619,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_10": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 928,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_11": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 1237,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_12": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 1546,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_13": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 1855,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_14": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 2164,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_15": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 2473,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_4": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 2782,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_8": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 3091,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-7_9": {
            options: {
                type: "card",
                deck: "forage",
            },
            frame: {
                x: 3400,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "forage-back": {
            options: {
                deck: "forage",
                type: "back",
            },
            frame: {
                x: 1,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_10": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 310,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_11": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 619,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_12": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 928,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_13": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 1237,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_14": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 1546,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_15": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 1855,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_4": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 2164,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_8": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 2473,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-7_9": {
            options: {
                type: "card",
                deck: "gather",
            },
            frame: {
                x: 2782,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "gather-back": {
            options: {
                deck: "gather",
                type: "back",
            },
            frame: {
                x: 3091,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_10": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 3400,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_11": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 1,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_12": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 310,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_13": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 619,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_14": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 928,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_15": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 1237,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_4": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 1546,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_5": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 1855,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_8": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 2164,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-7_9": {
            options: {
                type: "card",
                deck: "harvest",
            },
            frame: {
                x: 2473,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "harvest-back": {
            options: {
                deck: "harvest",
                type: "back",
            },
            frame: {
                x: 2782,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_10": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 3091,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_11": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 3400,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_12": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 1,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_13": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 310,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_14": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 619,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_15": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 928,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_4": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 1237,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_5": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 1546,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_6": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 1855,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_7": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 2164,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_8": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 2473,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-7_9": {
            options: {
                type: "card",
                deck: "hunt",
            },
            frame: {
                x: 2782,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "hunt-back": {
            options: {
                deck: "hunt",
                type: "back",
            },
            frame: {
                x: 3091,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_0": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3400,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_1": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_10": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 310,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_11": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 619,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_12": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 928,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_13": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1237,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_14": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1546,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_15": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1855,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_2": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2164,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_3": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2473,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_4": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2782,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_5": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3091,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_6": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3400,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_7": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_8": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 310,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-7_9": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 619,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event": {
            frame: {
                x: 928,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_0": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1237,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_1": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1546,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_10": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1855,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_11": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2164,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_12": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2473,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_13": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2782,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_14": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3091,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_15": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3400,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_2": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_3": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 310,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_4": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 619,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_5": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 928,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_6": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1237,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_7": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1546,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-8_8": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 1855,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_9": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2164,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_10": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2473,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_11": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 2782,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_12": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3091,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_13": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3400,
                y: 3116,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_14": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 1,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_15": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 446,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_4": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 891,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_5": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 1336,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_6": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 1781,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-9_8": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 2226,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
        "night-event-10_9": {
            options: {
                type: "card",
                deck: "night-event",
            },
            frame: {
                x: 3709,
                y: 2671,
                w: 307,
                h: 443,
            },
            rotate: 0,
        },
    },
    meta: {
        version: "1.0",
        image: "decks-spritesheet.png",
        css: "decks-card",
        size: {
            w: 4017,
            h: 3560,
        },
        scale: "1",
    },
};
var expansionSprites = {
    sprites: {
        "day-event-back": {
            options: {
                type: "back",
                deck: "day-event",
            },
            frame: {
                x: 1,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "fish-rule": {
            options: {
                type: "instruction",
            },
            frame: {
                x: 441,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_0": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 881,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_1": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1321,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_10": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1761,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_11": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 2201,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_3": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "daylight-rule": {
            frame: {
                x: 441,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_5": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 881,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_6": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1321,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_7": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1761,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_8": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 2201,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "day-event_1_9": {
            options: {
                type: "card",
                deck: "day-event",
            },
            frame: {
                x: 1,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_0: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 441,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_1: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 881,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_10: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1321,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_11: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1761,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_2: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2201,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_3: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_4: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 441,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_5: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 881,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_6: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1321,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_7: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1761,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_8: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2201,
                y: 1327,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_9: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "physical-hindrance-back": {
            options: {
                type: "back",
                deck: "physical-hindrance",
            },
            frame: {
                x: 441,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        "mental-hindrance-back": {
            options: {
                type: "back",
                deck: "mental-hindrance",
            },
            frame: {
                x: 881,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_0: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1321,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_1: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1761,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_10: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2201,
                y: 1769,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_11: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_2: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 441,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_3: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 881,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_4: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1321,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_5: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 1761,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_6: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2201,
                y: 2211,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_7: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2641,
                y: 1,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_8: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2641,
                y: 443,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hindrance_1_9: {
            options: {
                type: "card",
                deck: "hindrance",
            },
            frame: {
                x: 2641,
                y: 885,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
    },
    meta: {
        version: "1.0",
        image: "expansion-spritesheet.png",
        css: "expansion-card",
        size: {
            w: 3080,
            h: 2652,
        },
        scale: "1",
    },
};
var itemsSprites = {
    sprites: {
        'bow-and-arrow': {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'medical-hut': {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'bone-club': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'bone-scythe': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1314,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        bag: {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'bone-armor': {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'camp-walls': {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        fire: {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'hide-armor': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'knowledge-hut': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1314,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        skull: {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        hatchet: {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 440,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        club: {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'cooking-hut': {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'carving-knife': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'item-back': {
            options: {
                type: 'back',
            },
            frame: {
                x: 1314,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'sling-shot': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'pick-axe': {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 880,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'planning-hut': {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        spear: {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'sharp-stick': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        shelter: {
            options: {
                type: 'item',
            },
            frame: {
                x: 1314,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'rock-knife': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'item-back-hindrance': {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 1320,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'mortar-and-pestle': {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        bandage: {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'skull-shield': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'cooking-pot': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1314,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'bone-claws': {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'bone-flute': {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 1760,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'stock-hut': {
            options: {
                type: 'item',
            },
            frame: {
                x: 0,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        whip: {
            options: {
                type: 'item',
            },
            frame: {
                x: 438,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'fire-stick': {
            options: {
                type: 'item',
            },
            frame: {
                x: 876,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        stone: {
            options: {
                type: 'item',
            },
            frame: {
                x: 1314,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        bola: {
            options: {
                type: 'item',
            },
            frame: {
                x: 1752,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        boomerang: {
            options: {
                type: 'item',
            },
            frame: {
                x: 2190,
                y: 2200,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
        'stone-hammer': {
            options: {
                type: 'item',
            },
            frame: {
                x: 2628,
                y: 0,
                w: 438,
                h: 440,
            },
            rotate: 0,
        },
    },
    meta: {
        version: '1.0',
        image: 'items-spritesheet.png',
        css: 'items-card',
        size: {
            w: 3066,
            h: 2640,
        },
        scale: '1',
    },
};
var tokenSprites = {
    sprites: {
        '1-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 0,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        '1-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 113,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        '2-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 226,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        '2-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 339,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        '3-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 452,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        '3-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 0,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        '4-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 113,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        '4-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 226,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        '5-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 339,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        '5-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 452,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        '6-token': {
            options: {
                type: 'token',
            },
            frame: {
                x: 0,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        '6-unlocked': {
            options: {
                type: 'token',
            },
            frame: {
                x: 113,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        berry: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 226,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        'berry-cooked': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 339,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        bone: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 452,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        'dino-egg': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 0,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        'dino-egg-cooked': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 113,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        fish: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 226,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        'fish-cooked': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 339,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        fkp: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 452,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        'fkp-unlocked': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 0,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        'gem-1': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 113,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        'gem-2': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 226,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        'gem-3': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 339,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        fiber: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 452,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        hide: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 565,
                y: 0,
                w: 113,
                h: 113,
            },
        },
        'meat-cooked': {
            options: {
                type: 'resource',
            },
            frame: {
                x: 565,
                y: 113,
                w: 113,
                h: 113,
            },
        },
        herbs: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 565,
                y: 226,
                w: 113,
                h: 113,
            },
        },
        meat: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 565,
                y: 339,
                w: 113,
                h: 113,
            },
        },
        stone: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 565,
                y: 452,
                w: 113,
                h: 113,
            },
        },
        stew: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 0,
                y: 565,
                w: 113,
                h: 113,
            },
        },
        trap: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 113,
                y: 565,
                w: 113,
                h: 113,
            },
        },
        wood: {
            options: {
                type: 'resource',
            },
            frame: {
                x: 226,
                y: 565,
                w: 113,
                h: 113,
            },
        },
    },
    meta: {
        version: '1.0',
        image: 'token-spritesheet.png',
        css: 'token-card',
        size: {
            w: 678,
            h: 678,
        },
        scale: '1',
    },
};
var upgradesSprites = {
    sprites: {
        upgrades_2_44_0: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_1: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 184,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_10: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 367,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_11: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 550,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_12: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 733,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_13: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 916,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_14: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1099,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_15: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1282,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_2: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_3: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 184,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_4: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 367,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_5: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 550,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_6: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 733,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_7: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 916,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_8: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1099,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_2_44_9: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1282,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_0: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_1: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 184,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_10: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 367,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_11: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 550,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_12: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 733,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_13: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 916,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_14: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1099,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_15: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1282,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_2: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1465,
                y: 1,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_3: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1465,
                y: 370,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_4: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1465,
                y: 739,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_5: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 1,
                y: 1108,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_6: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 184,
                y: 1108,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_7: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 367,
                y: 1108,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_8: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 550,
                y: 1108,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
        upgrades_43_9: {
            options: {
                type: "upgrade",
            },
            frame: {
                x: 733,
                y: 1108,
                w: 181,
                h: 367,
            },
            rotate: 0,
        },
    },
    meta: {
        version: "1.0",
        image: "upgrades-spritesheet.png",
        css: "upgrades-card",
        size: {
            w: 1647,
            h: 1476,
        },
        scale: "1",
    },
};
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var allSprites = [boardsSprites, charactersSprites, decksSprites, expansionSprites, itemsSprites, tokenSprites, upgradesSprites].reduce(function (acc, _a) {
    var sprites = _a.sprites, meta = _a.meta;
    Object.values(sprites).forEach(function (d) { return (d.meta = meta); });
    return __assign(__assign({}, acc), sprites);
}, {});
var renderImage = function (name, div, scale, pos) {
    if (scale === void 0) { scale = 2; }
    if (pos === void 0) { pos = 'append'; }
    // example of adding a div for each player
    if (!allSprites[name])
        throw new Error("Missing image ".concat(name));
    var _a = allSprites[name], _b = _a.meta, css = _b.css, _c = _b.size, spriteWidth = _c.w, spriteHeight = _c.h, _d = _a.frame, x = _d.x, y = _d.y, w = _d.w, h = _d.h, rotate = _a.rotate;
    var html;
    if (rotate)
        html = "<div class=\"card-rotator\" style=\"transform: rotate(".concat(rotate, "deg) translate(25%, -50%);height: ").concat(w / scale, "px;width: ").concat(h / scale, "px;\">\n    <div name=\"").concat(name, "-").concat(rotate, "\" class=\"card ").concat(css, " ").concat(name, "\" style=\"background-size: ").concat(spriteWidth / scale, "px ").concat(spriteHeight / scale, "px;background-position: -").concat(x / scale, "px -").concat(y / scale, "px;width: ").concat(w / scale, "px;height: ").concat(h / scale, "px;\"></div>\n    </div>");
    else
        html = "<div name=\"".concat(name, "\" class=\"card ").concat(css, " ").concat(name, "\" style=\"background-size: ").concat(spriteWidth / scale, "px ").concat(spriteHeight / scale, "px;background-position: -").concat(x / scale, "px -").concat(y / scale, "px;width: ").concat(w / scale, "px;height: ").concat(h / scale, "px;\"></div>");
    if (pos === 'replace')
        div.innerHTML = html;
    else if (pos === 'insert')
        div.insertAdjacentHTML('afterbegin', html);
    else
        div.insertAdjacentHTML('beforeend', html);
};
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * DontLetItDie implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dontletitdie.js
 *
 * DontLetItDie user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */
var actionMappings = {
    actInvestigateFire: 'Investigate Fire',
    actCraft: 'Craft',
    actDrawGather: 'Gather',
    actDrawForage: 'Forage',
    actDrawHarvest: 'Harvest',
    actDrawHunt: 'Hunt',
    actSpendFKP: 'Spend FKP',
    actAddWood: 'Add Wood',
    actEat: 'Eat',
    actCook: 'Cook',
    actTrade: 'Trade',
};
define(['dojo', 'dojo/_base/declare', 'ebg/core/gamegui', 'ebg/counter'], function (dojo, declare) {
    return declare('bgagame.dontletitdie', ebg.core.gamegui, {
        constructor: function () {
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;/
            this.selectedCharacters = [];
            this.mySelectedCharacters = [];
        },
        /*
              setup:
              
              This method must set up the game user interface according to current game situation specified
              in parameters.
              
              The method is called each time the game interface is displayed to a player, ie:
              _ when the game starts
              _ when a player refreshes the game page (F5)
              
              "gameData" argument contains all datas retrieved by your "getAllDatas" PHP method.
          */
        renderPlayer: function (player) {
            document.querySelector("player-side-".concat(player.id, " > .health > .value")).innerHTML = 0;
            document.querySelector("player-side-".concat(player.id, " > .stamina > .value")).innerHTML = 0;
        },
        updatePlayer: function (player, gameData) {
            var _a, _b, _c, _d, _e, _f;
            // Player side board
            var playerSideContainer = document.getElementById("player-side-".concat(player.id));
            if (!playerSideContainer) {
                this.getPlayerPanelElement(player.id).insertAdjacentHTML('beforeend', "<div id=\"player-side-".concat(player.id, "\">\n          <div class=\"health\"><span class=\"label\">Health: </span><span class=\"value\"></span></div>\n          <div class=\"stamina\"><span class=\"label\">Stamina: </span><span class=\"value\"></span></div>\n          <div class=\"equipment\"><span class=\"label\">Equipment: </span><span class=\"value\">None</span></div>\n        </div>"));
            }
            else {
                // playerSideContainer.querySelector(`#player-${player.id} .health .value`).innerHTML = gameData.characters[0].health;
                // playerSideContainer.querySelector(`#player-${player.id} .stamina .value`).innerHTML = gameData.characters[0].stamina;
                // playerSideContainer.querySelector(`#player-${player.id} .equipment .value`).innerHTML =
                //   gameData.characters[0].equipment?.join(', ') ?? 'None';
            }
            // Player main board
            if (!document.getElementById("player-".concat(player.id))) {
                document.getElementById('players-container').insertAdjacentHTML('beforeend', "<div id=\"player-".concat(player.id, "\" class=\"player-card\">\n            <div class=\"card\"></div>\n            <div class=\"color-marker\" style=\"background-color: #").concat(player.color, "\"></div>\n            <div class=\"character\"></div>\n            <div class=\"health\" style=\"background-color: #").concat(player.color, ";left: ").concat(((_c = (_b = (_a = gameData.characters) === null || _a === void 0 ? void 0 : _a[0]) === null || _b === void 0 ? void 0 : _b.health) !== null && _c !== void 0 ? _c : 0) * 21 + 127, "px\"></div>\n            <div class=\"stamina\" style=\"background-color: #").concat(player.color, ";left: ").concat(((_f = (_e = (_d = gameData.characters) === null || _d === void 0 ? void 0 : _d[0]) === null || _e === void 0 ? void 0 : _e.stamina) !== null && _f !== void 0 ? _f : 0) * 21 + 127, "px\"></div>\n            <div class=\"weapon\"></div>\n            <div class=\"tool\"></div>\n            </div>"));
                renderImage("character-board", document.querySelector("#player-".concat(player.id, " > .card")), 4);
            }
            renderImage("Gronk", document.querySelector("#player-".concat(player.id, " > .character")), 4, 'replace');
            renderImage("club", document.querySelector("#player-".concat(player.id, " > .weapon")), 4, 'replace');
            renderImage("club", document.querySelector("#player-".concat(player.id, " > .tool")), 4, 'replace');
        },
        addClickListener: function (elem, name, callback) {
            elem.tabIndex = '0';
            elem.addEventListener('click', callback);
            elem.addEventListener('onKeyDown', function (e) {
                if (e.key === 'Enter')
                    callback();
            });
            elem.style.cursor = 'pointer';
            elem.role = 'button';
            elem['aria-label'] = name;
        },
        updateResources: function (gameData) {
            var elem = document.querySelector("#discoverable-container .tokens");
            if (!elem) {
                document
                    .getElementById('game_play_area')
                    .insertAdjacentHTML('beforeend', "<div id=\"discoverable-container\" class=\"dlid-container\"><h3>Discoverable Resources</h3><div class=\"tokens\"></div></div>");
                elem = document.querySelector("#discoverable-container .tokens");
            }
            this.updateResource('wood', elem, gameData);
            this.updateResource('stone', elem, gameData);
            this.updateResource('fiber', elem, gameData);
            this.updateResource('bone', elem, gameData);
            this.updateResource('meat', elem, gameData);
            this.updateResource('berry', elem, gameData);
            this.updateResource('hide', elem, gameData);
        },
        updateResource: function (name, elem, gameData) {
            var _a, _b;
            elem.insertAdjacentHTML('beforeend', "<div class=\"token ".concat(name, "\"><div class=\"counter\">").concat((_b = (_a = gameData.resourcesAvailable) === null || _a === void 0 ? void 0 : _a[name]) !== null && _b !== void 0 ? _b : 0, "</div></div>"));
            renderImage(name, elem.querySelector("#discoverable-container .token.".concat(name)), 2, 'insert');
        },
        setupBoard: function (gameData) {
            var _this = this;
            this.firstPlayer = gameData.playerorder[0];
            // Main board
            document
                .getElementById('game_play_area')
                .insertAdjacentHTML('beforeend', "<div id=\"board-container\" class=\"dlid-container\"><div class=\"board\"><div class=\"tokens\"></div><div class=\"gather\"></div><div class=\"forage\"></div><div class=\"harvest\"></div><div class=\"hunt\"></div></div></div>");
            renderImage("board", document.querySelector("#board-container > .board"), 2, 'insert');
            renderImage("gather-back", document.querySelector(".board > .gather"), 4, 'replace');
            renderImage("forage-back", document.querySelector(".board > .forage"), 4, 'replace');
            renderImage("harvest-back", document.querySelector(".board > .harvest"), 4, 'replace');
            renderImage("hunt-back", document.querySelector(".board > .hunt"), 4, 'replace');
            this.addClickListener(document.querySelector(".board > .gather"), 'Gather Deck', function () {
                _this.bgaPerformAction('actDrawGather');
            });
            this.addClickListener(document.querySelector(".board > .forage"), 'Forage Deck', function () {
                _this.bgaPerformAction('actDrawForage');
            });
            this.addClickListener(document.querySelector(".board > .harvest"), 'Harvest Deck', function () {
                _this.bgaPerformAction('actDrawHarvest');
            });
            this.addClickListener(document.querySelector(".board > .hunt"), 'Hunt Deck', function () {
                _this.bgaPerformAction('actDrawHunt');
            });
            this.updateResources(gameData);
        },
        setupCharacterSelections: function (elem, gameData) {
            var _this = this;
            Object.keys(charactersSprites.sprites)
                .filter(function (d) { return charactersSprites.sprites[d].options.type === 'character'; })
                .sort()
                .forEach(function (characterName) {
                renderImage(characterName, elem, 2, 'append');
                _this.addClickListener(elem.querySelector(".".concat(characterName)), characterName, function () {
                    var _a, _b;
                    var i = _this.mySelectedCharacters.indexOf(characterName);
                    if (i >= 0) {
                        // Remove selection
                        _this.mySelectedCharacters.splice(i, 1);
                    }
                    else {
                        _this.mySelectedCharacters.push(characterName);
                        _this.bgaPerformAction('actCharacterClicked', {
                            character1: (_a = _this.mySelectedCharacters) === null || _a === void 0 ? void 0 : _a[0],
                            character2: (_b = _this.mySelectedCharacters) === null || _b === void 0 ? void 0 : _b[1],
                        });
                    }
                });
            });
        },
        setup: function (gameData) {
            var _this = this;
            var knowledgeTree = 'normal';
            var mode = 'normal';
            this.dontPreloadImage('upgrades-spritesheet.png');
            console.log(gameData);
            var playArea = document.getElementById('game_play_area');
            playArea.style.display = 'none';
            playArea.parentElement.insertAdjacentHTML('beforeend', "<div id=\"character-container\" class=\"dlid-container\"></div>");
            this.setupCharacterSelections(document.getElementById('character-container'), gameData);
            playArea.insertAdjacentHTML('beforeend', "<div id=\"players-container\" class=\"dlid-container\"></div>");
            Object.values(gameData.players).forEach(function (player) {
                _this.updatePlayer(player, gameData);
            });
            this.setupBoard(gameData);
            // renderImage(`board`, playArea);
            playArea.insertAdjacentHTML('beforeend', "<div id=\"track-container\" class=\"dlid-container\"></div>");
            renderImage("track-".concat(mode), document.getElementById('track-container'));
            // renderImage(`dice`, document.getElementById('track-container'));
            // renderImage("bow-and-arrow", playArea);
            // Setting up player boards
            playArea.insertAdjacentHTML('beforeend', "<div id=\"knowledge-container\" class=\"dlid-container\"></div>");
            renderImage("knowledge-tree-".concat(knowledgeTree), document.getElementById('knowledge-container'));
            playArea.insertAdjacentHTML('beforeend', "<div id=\"instructions-container\" class=\"dlid-container\"></div>");
            renderImage("instructions", document.getElementById('instructions-container'));
            // TODO: Set up your game interface here, according to "gameData"
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },
        ///////////////////////////////////////////////////
        //// Game & client states
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function (stateName, args) {
            console.log('Entering state: ' + stateName, args);
            switch (stateName) {
                case 'dummy':
                    break;
            }
        },
        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function (stateName) {
            console.log('Leaving state: ' + stateName);
            switch (stateName) {
                case 'characterSelect':
                    dojo.style('character-select', 'display', 'none');
                    break;
                // case 'dummy':
                //   break;
            }
        },
        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function (stateName, args) {
            var _this = this;
            console.log('onUpdateActionButtons: ' + stateName, args);
            if (this.isCurrentPlayerActive()) {
                switch (stateName) {
                    case 'playerTurn':
                        var actions_1 = args.actions; // returned by the argPlayableActions
                        // Add test action buttons in the action status bar, simulating a card click:
                        if (actions_1)
                            Object.keys(actions_1).forEach(function (action) {
                                return _this.statusBar.addActionButton("".concat(_(actionMappings[action]), " <i class=\"fa fa-bolt stamina\"></i> ").concat(actions_1[action]), function () {
                                    return _this.bgaPerformAction(action);
                                });
                            });
                        this.statusBar.addActionButton(_('Pass'), function () { return _this.bgaPerformAction('actPass'); }, { color: 'secondary' });
                        break;
                    case 'characterSelect':
                        var playerCount = Object.keys(args.players).length;
                        if (playerCount === 3) {
                            this.selectCharacterCount = gamegui.player_id == this.firstPlayer ? 2 : 1;
                        }
                        else if (playerCount === 1) {
                            this.selectCharacterCount = 4;
                        }
                        else if (playerCount === 2) {
                            this.selectCharacterCount = 2;
                        }
                        else if (playerCount === 4) {
                            this.selectCharacterCount = 1;
                        }
                        if (this.selectCharacterCount == 1)
                            this.statusBar.addActionButton(_('Confirm 1 character'), function () { return _this.bgaPerformAction('actChooseCharacters'); });
                        else
                            this.statusBar.addActionButton(_('Confirm ${x} characters').replace('${x}', this.selectCharacterCount), function () {
                                return _this.bgaPerformAction('actChooseCharacters');
                            });
                        break;
                }
            }
        },
        ///////////////////////////////////////////////////
        //// Utility methods
        /*
          
              Here, you can defines some utility methods that you can use everywhere in your javascript
              script.
          
          */
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications
        /*
              setupNotifications:
              
              In this method, you associate each of your game notifications with your local method to handle it.
              
              Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                    your dontletitdie.game.php file.
          
          */
        setupNotifications: function () {
            console.log('notifications subscriptions setup');
            // TODO: here, associate your game notifications with local methods
            dojo.subscribe('characterClicked', this, 'notif_characterClicked');
            // Example 1: standard notification handling
            // dojo.subscribe( 'tokenUsed', this, "notif_tokenUsed" );
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            dojo.subscribe('tokenUsed', this, 'notif_tokenUsed');
            this.notifqueue.setSynchronous('tokenUsed', 1000);
            //
        },
        // TODO: from this point and below, you can write your game notifications handling methods
        notif_characterClicked: function (notif) {
            console.log('notif_characterClicked');
            console.log(notif);
        },
        // TODO: from this point and below, you can write your game notifications handling methods
        notif_tokenUsed: function (notif) {
            var _this = this;
            console.log('notif_tokenUsed');
            console.log(notif);
            Object.values(notif.args.gameData.players).forEach(function (player) {
                _this.updatePlayer(player, notif.args.gameData);
            });
            this.updateResources(notif.args.gameData);
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            // TODO: play the card in the user interface.
        },
    });
});
