import dojo from 'dojo';
export default {
  getData: () => ({
    sprites: {
      Gronk: {
        options: {
          type: 'character',
          text: [
            { title: 'Gronk' },
            _('Can take 2 Damage to gain 2 stamina once per day'),
            _('Gains 2 stamina when filling a Danger! card'),
            _('Can have two Weapon items equipped'),
          ],
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
          text: [
            { title: 'Grub' },
            _('Grub can NOT perform the Hunt action'),
            _('Immediately escapes all Danger! cards'),
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Fiber'),
              card: _('Gather'),
            }),
          ],
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
          text: [
            { title: 'Kara' },
            _('Eating food grants double Health'),
            _("Re-roll anybody's Investigate Fire action, once per day"),
            _('Add 2 stamina to anybody (Except Kara), once per day'),
          ],
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
          startsWith: 'hide-armor',
          text: [
            { title: 'Cron' },
            _('When a tribe member kills a Danger! card, they gain 1 Stamina'),
            _('Spend 2 Stamina to shuffle a discard pile back into its deck'),
            _('Starts with Hide Armor'),
          ],
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
          text: [
            { title: 'Dub' },
            _('Bones may be used as FKP'),
            _('Can spend 1 Bone to discard a Night Event card and redraw, once per night'),
            _('When Dub rolls a 1 the fire die, take 1 Berry'),
          ],
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
          startsWith: 'skull-shield',
          text: [
            { title: 'Faye' },
            _('When Faye uses a Medicinal Herb on herself to remove a Physical Hindrance card, heal 1'),
            _('Spend 2 Stamina, trade or take 1 Physical Hindrance from another Tribe Member'),
            dojo.string.substitute(_('Starts with a ${item}!'), {
              item: _('Skull Shield'),
            }),
          ],
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
          text: [
            { title: 'Ajax' },
            _('Spend 2 Stamina, heal for 2 Health, once per day'),
            _('When Ajax draws a 1/1 Beast Danger! card, he instantly kills it and and takes 1 meat and 0 damage meat'),
            _('Can have two different Tool items equipped'),
          ],
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
          text: [
            { title: 'Atouk' },
            _('Atouk can NOT perform Forage or Hunt actions'),
            _(
              'Remove 2 of any resource cost when crafting an item. Only when Atouk takes the craft action. Must use at least 2 total resources',
            ),
            _('Can spend 2 stamina to take 1 wood, once per day wood'),
          ],
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
          text: [
            { title: 'Ayla' },
            _('Hunting costs Ayla 2 stamina'),
            _('Spend 1 Stamina to turn, 1 raw berry into 1 fiber'),
            _('Heal 2 Health when killing a Danger! card, once per day'),
          ],
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
          text: [
            { title: 'River' },
            _('River can only eat Berries'),
            _('When River draws a Nothing card, take 2 FKP'),
            _('River has a free Investigate Fire action, once per day'),
          ],
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
          text: [
            { title: 'Sig' },
            _('Sig can NOT obtain items from trading with another character'),
            _('The Investigate Fire action costs Sig 5 stamina, but you roll the die twice and add the rolls together'),
            _('If Sig has a Sharp Stick or Spear equipped, he may spend 2 stamina to roll the Fire die and take that many Fish tokens'),
            _('Fish are treated like Meat for cooking and eating'),
          ],
          expansion: 'mini-expansion',
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
          text: [
            { title: 'Tara' },
            _('Gain 2 stamina when gaining Health from eating berries, once per day'),
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Berry'),
              card: _('Forage'),
            }),
            _('Spend 2 stamina to heal any character for 1 Health'),
          ],
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
          text: [
            { title: 'Nirv' },
            _('When any tribe member kills a Danger! card, Nirv heals 1 Health'),
            _('When drawing a Rival Tribe Night Event card, all tribe members heal 1 Health'),
            _('Nirv has a free Gather action, once per day'),
          ],
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
          startsWith: 'mortar-and-pestle',
          text: [
            { title: 'Oof' },
            _('When Oof is part of the tribe, you may also revive characters with 6 Cooked Berries'),
            _('Spend 3 Stamina, remove 1 Physical Hindrance from any tribe member'),
            dojo.string.substitute(_('Starts with a ${item}!'), {
              item: _('Mortar and Pestle'),
            }),
          ],
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
          expansion: 'hindrance',
          type: 'character',
          startsWith: 'fire-stick',
          text: [
            { title: 'Rex' },
            _('Spend 2 Stamina to look at the top card from a Resource deck, place it back on top of the deck'),
            _('Spend 1 Stamina to move or place 1 Trap token by a Resource deck'),
            dojo.string.substitute(_('Starts with a ${item}!'), {
              item: _('Fire Stick'),
            }),
          ],
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
          text: [
            { title: 'Mabe' },
            _(
              'When Mabe falls below 3 Health, roll the Fire die. A blank means Mabe gains 2 stamina, otherwise take 1 FKP. Once per day phase',
            ),
            _("If you have at least one resource, spend 3 stamina to 'copy' it and take another of the chosen resource type"),
            _('Can NOT perform the Investigate Fire action'),
          ],
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
          text: [
            { title: 'Nanuk' },
            _('Nanuk can only eat Meat, but gains double Health from it'),
            _('Tribe members gain 1 FKP for every Danger! card they kill'),
            _('After killing a Danger! card, Nanuk may choose Hide or Bone in place of Meat'),
          ],
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
          startsWith: 'bag',
          text: [
            { title: 'Nibna' },
            _('When Nibna eats food and her Health is 1, the food heals for double, once per day'),
            _('Take 2 Health damage to heal the rest of the group 1 Health'),
            dojo.string.substitute(_('Starts with a ${item}!'), {
              item: _('Bag'),
            }),
          ],
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
          text: [
            { title: 'Zeebo' },
            _('Zeebo can NOT equip Tool items'),
            _('Zeebo heals 1 Health every time he draws a berry card'),
            _('Spend 3 Stamina after drawing a resource card to take double the listed amount'),
          ],
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
          startsWith: 'sharp-stick',
          text: [
            { title: 'Thunk' },
            _('Thunk can NOT perform Forage or Gather actions'),
            dojo.string.substitute(_('Take 1 ${resource} when drawing a ${card} card'), {
              resource: _('Meat'),
              card: _('Hunt'),
            }),
            dojo.string.substitute(_('Starts with a ${item}!'), {
              item: _('Sharp Stick'),
            }),
          ],
        },
        frame: {
          x: 395,
          y: 1891,
          w: 392,
          h: 628,
        },
      },
      Tiku: {
        options: {
          expansion: 'hindrance',
          type: 'character',
          text: [
            { title: 'Tiku' },
            _('Spend 1 Stamina, 1 Raw Berry, 1 Raw Meat and 1 Medicinal Herb to take 1 Stew token'),
            _('Tiku is not affected by Mental Hindrances, Physical Hindrance limit is 4 instead of 3'),
            _("When Tiku draws a card from the Explore deck and it's NOT a Danger! card, take 1 Dino Egg"),
          ],
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
          text: [
            { title: 'Vog' },
            _('Vog skips taking damage from the morning phase'),
            _('Eating food costs Vog 1 Stamina'),
            _('When anyone is about to take damage from a Danger! card, you may give all incoming damage to Vog instead'),
          ],
        },
        frame: {
          x: 1183,
          y: 1891,
          w: 392,
          h: 628,
        },
      },
      AlternateUpgradeTrack: {
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
      Blarg: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Blarg' },
            _('The Tribe can construct 2 Buildings per game'),
            _('Crafting Discoveries cost -2 FKP to unlock'),
            _('Spend 1 Stamina, give 1 owned Item to another Tribe member, they may immediately equip it'),
          ],
        },
        frame: {
          x: 1971,
          y: 1891,
          w: 392,
          h: 628,
        },
      },
      Cali: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          startsWithHindrance: 'hindrance_1_4',
          text: [
            { title: 'Cali' },
            _(
              'Investigate Fire actions cost Cali 2 Stamina. Call out what the roll will be before rolling. If correct, take double, otherwise take nothing',
            ),
            _('When Cali eats Cooked food, pick between Healing double OR gaining 1 Max Health'),
            _("Starts with the 'Paranoid' Mental Hindrance"),
          ],
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
      Loka: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Loka' },
            _('Gain 1 Wood when the Tribe unlocks a discovery'),
            _(
              'Spend 2 Stamina, place a Hide token here from the supply. Loka can discard a Hide on this card back to the supply to reduce damage of a Danger! card by 1 OR take +1 Resource from a drawn Resource card',
            ),
          ],
        },
        frame: {
          x: 2365,
          y: 1891,
          w: 392,
          h: 628,
        },
      },
      Tooth: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Tooth' },
            _('Cannot use the Investigate Fire, Cook, Craft or Trade actions. Cannot equip Items'),
            _('When facing a Danger! card, resolve combat as if Tooth had a 2 Damage, 1 Range Weapon'),
            _("On a Tribe member's turn, they may spend 2 Stamina to pet Tooth and gain 1 Health"),
          ],
        },
        frame: {
          x: 2759,
          y: 1,
          w: 392,
          h: 628,
        },
      },
      Sooha: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Sooha' },
            _('When rolling a 0 on an Investigate Fire action, lose 1 Health and gain 2 FKP'),
            _('Can spend Health as if it were Stamina'),
            _('The Relaxation discovery gives Sooha a total of +4 Max Health and Heals him fully'),
          ],
        },
        frame: {
          x: 2759,
          y: 631,
          w: 392,
          h: 628,
        },
      },
      Samp: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Samp' },
            _('Once per Investigate Fire action, Samp may lose 1 Health to add 1 to the roll'),
            _('When Samp takes any damage from a Danger! card, he gains 1 Stamina'),
            _('When a Tribe member rolls a 3 while Investigating the Fire, Samp gains 1 Max Stamina'),
          ],
        },
        frame: {
          x: 2759,
          y: 1261,
          w: 392,
          h: 628,
        },
      },
      Yurt: {
        options: {
          expansion: 'death-valley',
          type: 'character',
          text: [
            { title: 'Yurt' },
            _('When a Tribe member crafts an Item, Yurt picks anyone to gain 1 Health or 1 Stamina'),
            _('The craft action for the Tribe costs -2 Stamina'),
            _('Cannot be in the same tribe as Atouk'),
          ],
        },
        frame: {
          x: 2759,
          y: 1891,
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
        w: 3152,
        h: 2520,
      },
      scale: '1',
    },
  }),
};
