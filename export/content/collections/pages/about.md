---
id: about
blueprint: page
title: About
builder:
  -
    id: about_hero
    type: hero
    overline: 'Our story'
    heading: 'A small hotel with the sea for a neighbour'
    lead: 'One family, one stretch of coast, and a quiet idea: that luxury can be barefoot, warm and genuinely kind.'
  -
    id: about_story
    type: rich_text
    overline: 'How it began'
    heading: 'Built slowly, by hand'
    width: narrow
    content:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'We restored an old fishing house stone by stone, then added rooms the way you would extend a home — only where the light and the view asked for them. Nothing here is accidental, and nothing is showy.'
      -
        type: paragraph
        content:
          -
            type: text
            text: 'The materials are honest: limewash, linen, weathered timber and terracotta. The result is a place that feels lived-in and calm, where staff know your name by the second morning.'
  -
    id: about_values
    type: image_text_split
    overline: 'What we care about'
    heading: 'Of this place, not just in it'
    image_position: left
    text:
      -
        type: paragraph
        content:
          -
            type: text
            text: 'We cook with what the boats and the garden bring in, press our own olive oil, and work with neighbours rather than suppliers. Water is heated by the sun where it can be, and the grove is farmed without chemicals.'
    cta_label: 'See our experiences'
    cta_url: '/experiences'
  -
    id: about_gallery
    type: gallery_grid
    heading: 'Around the resort'
    columns: '3'
  -
    id: about_cta
    type: cta_band
    heading: 'Come and stay'
    text: 'Find your room and book direct in under a minute.'
    cta_label: 'Check availability'
    cta_url: '/rooms'
    background: teal
---
