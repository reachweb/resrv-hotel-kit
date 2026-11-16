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
    heading: 'Offers when you book direct'
    intro: 'Savings you only get by booking with us. Most rates also come with free cancellation.'
    enabled: true
  - id: home_reviews
    type: reviews_carousel
    heading: 'Loved by the people who stay'
    show_aggregate: true
    enabled: true
  -
    id: home_tiles
    type: feature_tiles
    heading: 'La Marea and the spa'
    intro: 'A few steps from your room, and booked the same way as your stay.'
    tiles:
      -
        id: tile_dining
        title: 'La Marea'
        text: 'A sea-to-table menu that follows the morning catch.'
        link: '/dining#reserve'
        bookable:
          - venue-la-marea
        action_label: 'Reserve a table'
      -
        id: tile_spa
        title: 'Spa & Wellness'
        text: 'An adults-only spa with sea-facing treatment rooms.'
        link: '/spa#treatments'
        bookable:
          - spa-couples-ritual
          - spa-garden-botanicals-facial
          - spa-in-room-treatment
          - spa-mediterranean-massage
          - spa-morning-stretch-steam
          - spa-sea-salt-body-polish
        action_label: 'Book a treatment'
    enabled: true
---
