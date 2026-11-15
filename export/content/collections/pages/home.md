---
id: home
blueprint: page
title: Home
template: home
transparent_nav: true
builder:
  - id: home_hero
    type: hero
    heading: 'Wake to the sound of the sea'
    lead: 'An intimate coastal-luxury resort where the days are long, the sea is close, and booking direct takes under a minute.'
    show_search: true
    enabled: true
  -
    id: home_rooms
    type: rooms_showcase
    heading: 'Rooms that open to the sea'
    intro: 'From a quiet garden double to a private-pool villa — every room is dressed in linen and natural light.'
    rooms:
      - garden-view-double
      - sea-view-suite
      - terracotta-villa
    cta_label: 'View all rooms'
    cta_url: /rooms
    enabled: true
  -
    id: home_story
    type: image_text_split
    heading: 'Barefoot luxury, unhurried'
    image_position: right
    text:
      -
        type: paragraph
        content:
          - type: text
            text: 'Limewashed walls, weathered timber and terracotta, a private beach below and an adults-only spa cut into the cliff. We kept things simple so the sea, the light and the long lunches could take the lead.'
    cta_label: 'Our story'
    cta_url: /about
    enabled: true
  - id: home_offers
    type: offers_strip
    heading: 'Reasons to book direct'
    intro: 'Better rates, free cancellation on most stays, and a welcome that an OTA can never send.'
    enabled: true
  - id: home_reviews
    type: reviews_carousel
    heading: 'Loved by the people who stay'
    show_aggregate: true
    enabled: true
  -
    id: home_tiles
    type: feature_tiles
    heading: 'Dining, spa and sea'
    intro: 'The best of the coast, a few steps from your room.'
    tiles:
      -
        id: tile_dining
        title: 'La Marea'
        text: 'A sea-to-table menu that follows the morning catch.'
        link: /dining
      -
        id: tile_spa
        title: 'Spa & Wellness'
        text: 'An adults-only spa with sea-facing treatment rooms.'
        link: /spa
      -
        id: tile_beach
        title: 'The Private Beach'
        text: 'A sheltered cove with loungers, parasols and beach service.'
    enabled: true
---
