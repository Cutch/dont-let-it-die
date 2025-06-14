export default {
  getData: () => ({
    sprites: {
      'day-event-back': {
        options: {
          type: 'back',
          deck: 'day-event',
        },
        frame: {
          x: 1,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'fish-rule': {
        options: {
          type: 'instruction',
        },
        frame: {
          x: 441,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_0': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _("Something's not right... The hair on the back of your neck stands straight up, a great beast is near..."),
            {
              title: _('Tame the beast'),
            },
            _(
              "Out of nowhere a beast emerges from a bush and pounces on you! You do your best to fight it off, but it's far too quick and you are easily overwhelmed. Defeated and covered in slobber, you hold up your new wolf pup friend. Give him a name! He doesn't seem to want to leave your side. Set this card next to your character. Spend 2 Stamina, roll the Fire Die. On a blank, take 1 Wood, otherwise take 1 Rock, once per day.",
            ),
          ],
        },
        frame: {
          x: 881,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_1': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _(
              "You've been tasked with going to the river to get the Tribe some more water. While wading in the river, you feel pressure and a sharp pain in your ankle!",
            ),
            {
              title: _('Make a snappy comeback'),
            },
            _(
              'Take 1 Damage. You manage to shake it loose! It looks pretty angry though... Fight the Snapping Turtle in normal combat. 1 Damage | 1 Life. If you win, take this card and flip it upside down.',
            ),
          ],
        },
        frame: {
          x: 1321,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_10': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _('You found a berry bush full of plump, ripe berries. The problem is, that Boar over there thinks he found it first...'),
            { title: _('Yoink!') },
            _('Spend 1 Stamina, you grab a handful of berries and high tail it back to camp. Take 2 Berries.'),
            { title: _("We'll see about that") },
            _("You show that Boar who's boss, only taking minor wounds. It'll think twice next time. Take 5 Berries and 1 Damage."),
          ],
        },
        frame: {
          x: 1761,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_11': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _('After searching the area, you stumble upon a huge tree! This would surely help keep the fire alive...'),
            { title: _('Oops...') },
            _('You forgot your tool to cut this tree down back at camp... Spend 3 Stamina to go and get it. Take 3 Wood.'),
            { title: _('Who needs tools?') },
            _('In a display of dominance, you punch the tree, knocking loose a decent sized branch. Take 1 Wood and 1 Damage.'),
          ],
        },
        frame: {
          x: 2201,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_3': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _("It's a glorious day, not a cloud in sight. Now to figure out why there's a huge shadow being cast on you..."),
            { title: _('AaaaaaAAaaaAAAaaAAAAAaAAAA') },
            _(
              'You try to run away, but the flying beast is much too fast! It grabs you in its talons and soars high into the sky. After struggling, you manage to break free, plummeting down to the ground. Take 2 Damage.',
            ),
            { title: _('When in doubt, throw a rock') },
            _(
              'You may discard 1 Rock to roll the Fire Die. On a blank roll add the Rock token to this card. After 2 Rock tokens have been added, remove and discard them along with this card. Take 3 Meat tokens.',
            ),
            _("If you can't get 2 Rocks on this card, take 2 Damage."),
          ],
        },
        frame: {
          x: 1,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'daylight-rule': {
        frame: {
          x: 441,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_5': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _('While walking through some tall grass, you hear a rattling sound from under foot. To your dismay, you see a snake...'),
            { title: _('The only good snake...') },
            _(
              'If you have a weapon equipped, you quickly attack the snake before he has time to react. The adrenaline of the situation has given you more energy. Gain 2 Stamina.',
            ),
            { title: _('Maybe if I just move my foot...') },
            _(
              'As soon as you move your foot the snake strikes! As it slithers into the distance you can feel yourself starting to get woozy. Reduce your Max Stamina by 1.',
            ),
          ],
        },
        frame: {
          x: 881,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_6': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _('You hear rustling in the bushes... You raise your fist in anticipation of the lurking dangers it could contain...'),
            { title: _('Sorry about that!') },
            _(
              'A shadowy blob bursts from the bushes! Your fist flies through the air before you can even think. Pick a character other than yourself. They take 1 Damage.',
            ),
            { title: _("Let's think about this...") },
            _(
              "Spend 2 Stamina, you take a step back and grunt. 'Ugh!' one of your tribe members peeks their head up from the bushes. Both of you laugh and gorge yourselves with berries. You and another character of your choice heal 1 Damage each.",
            ),
          ],
        },
        frame: {
          x: 1321,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_7': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _("You notice you're being followed, out of the corner of your eye you see a weird looking lizard..."),
            { title: _('Nope') },
            _('Roll the Fire Die, if you roll a blank, you escape without taking damage, otherwise, take 1 Damage.'),
            { title: _("It doesn't look that tough...") },
            _(
              'You charge at the lizard! Roll the Fire Die 3 times, adding each roll to the total. If the total is 5 or greater, take 2 Meat and 1 Bone. Otherwise, take 2 Damage.',
            ),
          ],
        },
        frame: {
          x: 1761,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_8': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _("After getting distracted by a shiny beetle, you find yourself lost in a part of the valley you've never seen before."),
            { title: _('Climb a tree') },
            _('Spend 2 Stamina to climb the tallest tree you can find, you see the camp off in the distance.'),
            { title: _('Backtrack') },
            _('You follow your tracks back to the path, but all that extra walking took a toll on your empty stomach. Take 1 Damage.'),
          ],
        },
        frame: {
          x: 2201,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'day-event_1_9': {
        options: {
          type: 'card',
          deck: 'day-event',
          text: [
            _("You spot a herd of Mammoths being chased by a Sabertooth. With all this commotion in the area, it's not safe to stay."),
            {
              title: _('Sneak around'),
            },
            _('You decide to chance it. Roll the Fire Die, if you get a blank, you lose 2 Stamina. Otherwise take 2 Meat.'),
            {
              title: _('Head back to camp'),
            },
            _(
              'On the way back to the camp, you trip and fail, losing 1 Stamina. Luckily, it seems you tripped on a Berry bush. Take 2 Berries.',
            ),
          ],
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
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Blind') }, _('Can only equip range 1 Weapons')],
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
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Broken Arm') }, _('Cannot have any Weapons equipped')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Berserk') }, _('When you take any damage, deal 1 damage to another tribe member other than yourself')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [
            { title: _('Cowardly') },
            _(
              'When drawing a Danger! card, take 1 damage (must be applied to you, cannot be blocked, escaped, ignored or soothed) and discard. You may not fight Danger! cards in combat.',
            ),
          ],
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
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Broken Leg') }, _('Cannot Forage, Gather, Hunt, Harvest or Explore')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Obsessive') }, _('When you end your turn with an odd amount of Health (1, 3, 5 etc) take 1 damage')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [
            { title: _('Paranoid') },
            _(
              'When you have less than Max Health and you have enough food tokens to eat, you must always eat it immediately and before other tribe members.',
            ),
          ],
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
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Bad Back') }, _('Cannot have any Tools equipped')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Depressed') }, _('Increase the cost of all actions that cost Stamina by 1')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Dumb') }, _('Investigate Fire actions cost you +1 Stamina and roll results are reduced by 1')],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          title: _('Forgetful'),
          text: [
            { title: _('Forgetful') },
            _(
              "Before Gathering, Foraging, Hunting or Harvesting, roll the fire die. If you get a blank, don't draw a card, but still spend the Stamina.",
            ),
          ],
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
          type: 'card',
          deck: 'mental-hindrance',
          expansion: 'hindrance',
          title: _('Anti-Social'),
          text: [
            { title: _('Anti-Social') },
            _("Cannot trade or be given items from other characters. Cannot be healed by any other tribe member's skills."),
          ],
        },
        frame: {
          x: 1,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'physical-hindrance-back': {
        options: {
          type: 'back',
          deck: 'physical-hindrance',
        },
        frame: {
          x: 441,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      'mental-hindrance-back': {
        options: {
          type: 'back',
          deck: 'mental-hindrance',
        },
        frame: {
          x: 881,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_0: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Sun Burnt') }, _('Cannot perform the Investigate Fire action')],
        },
        frame: {
          x: 1321,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_1: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Swollen Eyes') }, _('Reduce resources gained from resource cards by 1')],
        },
        frame: {
          x: 1761,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_10: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Deep Wound') }, _('Maximum Health reduced by 1')],
        },
        frame: {
          x: 2201,
          y: 1769,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_11: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Dehydrated') }, _('Take 1 extra damage when taking any damage from Danger! cards')],
        },
        frame: {
          x: 1,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_2: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Twisted Ankle') }, _('Maximum Stamina reduced by 1')],
        },
        frame: {
          x: 441,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_3: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Nauseous') }, _('Can only eat food once per day phase')],
        },
        frame: {
          x: 881,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_4: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Parasites') }, _('All Healing received reduced by 1')],
        },
        frame: {
          x: 1321,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_5: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Sick') }, _('Physical Hindrances can only be removed with Medicinal Herbs')],
        },
        frame: {
          x: 1761,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_6: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Diseased') }, _('Take 1 extra damage from the Morning Phase')],
        },
        frame: {
          x: 2201,
          y: 2211,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_7: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Exhausted') }, _('Start the Morning Phase with -2 Stamina')],
        },
        frame: {
          x: 2641,
          y: 1,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_8: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Malnourished') }, _('Your damage has to exceed, not just meet the Danger! cards life in order to defeat it')],
        },
        frame: {
          x: 2641,
          y: 443,
          w: 438,
          h: 440,
        },
        rotate: 0,
      },
      hindrance_2_9: {
        options: {
          type: 'card',
          deck: 'physical-hindrance',
          expansion: 'hindrance',
          text: [{ title: _('Concussion') }, _('Reduce Investigate Fire action rolls by 1')],
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
      version: '1.0',
      image: 'expansion-spritesheet.png',
      css: 'expansion-card',
      size: {
        w: 3080,
        h: 2652,
      },
      scale: '1',
    },
  }),
};
