---
id: home
blueprint: page
title: Home
template: home
transparent_nav: true
builder:
  - id: home_hero
    type: hero
    overline: 'Mediterranean coast'
    heading: 'Wake to the sound of the sea'
    lead: 'An intimate coastal-luxury resort where the days are long, the sea is close, and booking direct takes under a minute.'
    show_search: true
    enabled: true
  -
    id: home_rooms
    type: rooms_showcase
    overline: 'Rooms & Suites'
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
    overline: 'The resort'
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
    overline: Offers
    heading: 'Reasons to book direct'
    intro: 'Better rates, free cancellation on most stays, and a welcome that an OTA can never send.'
    enabled: true
  -
    id: home_experiences
    type: experiences_grid
    overline: Experiences
    heading: 'Dining, spa and sea'
    intro: 'The best of the coast, a few steps from your room.'
    experiences:
      - exp-seaside-taverna
      - exp-cliffside-spa
      - exp-private-beach
    cta_label: 'All experiences'
    cta_url: /experiences
    enabled: true
  - id: home_reviews
    type: reviews_carousel
    overline: 'Guest stories'
    heading: 'Loved by the people who stay'
    show_aggregate: true
    enabled: true
  - id: home_location
    type: map_contact
    overline: Location
    heading: '20 minutes from the airport'
    intro: 'A private beach, an adults-only spa and the old town a short drive along the coast. Check-in 15:00 · check-out 11:00.'
    show_map: true
    show_form: false
    enabled: true
---
