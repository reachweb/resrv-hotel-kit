---
id: dining
blueprint: page
title: Dining
builder:
  -
    id: dining_hero
    type: hero
    overline: 'Dining'
    heading: 'Sea-to-table, every day'
    lead: 'A menu that follows the catch and the garden, served on a long terrace above the water.'
  -
    id: dining_taverna
    type: image_text_split
    overline: 'The Seaside Taverna'
    heading: 'Grilled fish, garden vegetables, natural wine'
    image_position: right
    text:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Breakfast is slow and generous — warm bread, fruit, eggs and strong coffee as the sea wakes up. Dinner is the catch of the day over coals, sun-ripe vegetables and a cellar of natural local wines.'
    cta_label: 'Reserve a table'
    cta_url: '#reserve'
  -
    id: dining_philosophy
    type: rich_text
    overline: 'In the kitchen'
    heading: 'Short distances, long lunches'
    width: narrow
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Almost everything travels a few hundred metres to reach your plate: olive oil from our grove, herbs from the garden, fish from the boats that pull in below. The menu changes with the season, and sometimes with the morning.'
  -
    id: dining_gallery
    type: gallery_grid
    heading: 'From the kitchen'
    columns: '3'
  -
    id: dining_reserve
    type: restaurant_booking
    overline: 'Reserve a table'
    heading: 'A table by the water'
    intro: 'Tables fill quickly in summer — pick a day and party size, then choose your sitting. Reserve ahead, especially for sunset.'
  -
    id: dining_cta
    type: cta_band
    heading: 'Stay for dinner'
    text: 'Book a room and wake up a few steps from breakfast on the terrace.'
    cta_label: 'Check availability'
    cta_url: '/rooms'
    background: terracotta
---
