---
id: dining
blueprint: page
title: Dining
builder:
  -
    id: dining_hero
    type: hero
    heading: 'Sea-to-table, every day'
    lead: 'At La Marea the menu follows the catch and the garden, served on a long terrace above the water. Pick a day, then choose a lunch or dinner sitting.'
  -
    id: dining_reserve
    type: restaurant_booking
  -
    id: dining_la_marea
    type: image_text_split
    heading: 'Grilled fish, garden vegetables, natural wine'
    image_position: right
    text:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Breakfast is slow and generous — warm bread, fruit, eggs and strong coffee as the sea wakes up. Dinner is the catch of the day over coals, sun-ripe vegetables and a cellar of natural local wines.'
      -
        type: paragraph
        content:
          -
            type: text
            text: 'Almost everything travels a few hundred metres to reach your plate: olive oil from our grove, herbs from the garden, fish from the boats that pull in below.'
  -
    id: dining_gallery
    type: gallery_grid
    heading: 'From the kitchen'
    columns: '4'
  -
    id: dining_cta
    type: cta_band
    heading: 'Stay for dinner'
    text: 'Book a room and wake up a few steps from breakfast on the terrace.'
    cta_label: 'Check availability'
    cta_url: '/rooms'
    background: terracotta
---
