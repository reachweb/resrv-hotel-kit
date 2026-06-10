# Image manifest

The kit ships **no photography**. Every image renders through a graceful *named slot*
(`partials/image-slot`): when the asset is missing you see a labelled placeholder with
the expected path and aspect ratio; when you add the file (or attach an asset in the CP)
the slot renders it via Glide.

Two ways to fill a slot:

1. **CP (preferred):** upload to the `assets` container and attach it to the entry's
   image field — the slot uses the attached asset regardless of filename.
2. **By convention:** drop files into `public/assets/<folder>/` using the names below —
   the placeholder labels show exactly which file each slot expects.

All slots are rendered with `aspect-ratio`, so any sufficiently large image works;
the ratios below are what the layout was designed around. Aim for ≥1600px on the
long edge (Glide serves `width=1600` and downsizes responsively).

## Heroes & pages

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `hero/home.jpg` | 16:9 | Home full-bleed hero (behind the search card) |
| `hero/<page-slug>.jpg` | 16:9 | Any page using the hero block with an image (dining, spa, …) |
| `hero/404.jpg` | 4:3 | The 404 page illustration |
| `<page-slug>.jpg` | 4:3 | `image_text_split` blocks (About story, taverna split, …) |
| `map-location.jpg` | 21:9 | Contact page map slot |

## Rooms (5 entries)

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `rooms/<slug>.jpg` | 3:2 | Room card (index, showcase, "you may also like") + detail hero |
| `rooms/<slug>-gallery-1.jpg` | 3:2 | Room detail gallery, second image |
| `rooms/<slug>-gallery-2.jpg` | 3:2 | Room detail gallery, third image |

Room slugs: `garden-view-double`, `sea-view-suite`, `terracotta-villa`,
`coastal-family-room`, `cliffside-honeymoon-suite`.

## Spa & dining

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `spa/<treatment-slug>.jpg` | 3:2 | Treatment page editorial image |
| `spa/<treatment-slug>.jpg` | 16:9 | Checkout order summary (same file, cropped) |
| `dining/la-marea.jpg` | 16:9 | Checkout summary for table reservations |

Treatment slugs: `mediterranean-massage`, `sea-salt-body-polish`,
`garden-botanicals-facial`, `couples-ritual`, `morning-stretch-steam`,
`in-room-treatment`.

## Offers, experiences, journal

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `offers/<slug>.jpg` | 3:2 | Offer cards |
| `offers/<slug>.jpg` | 21:9 | Offer detail hero band |
| `experiences/<slug>.jpg` | 3:2 | Experience tiles (bento grid) |
| `experiences/<slug>.jpg` | 21:9 | Experience detail hero |
| `journal/<slug>.jpg` | 3:2 / 16:9 | Journal cards / article hero (journal module) |

## Gallery page

The gallery block renders attached assets as a masonry grid; until populated it shows
eight placeholder slots (`gallery/01.jpg` 3:2 · `02` 3:4 · `03` 4:5 · `04` 16:9 ·
`05` 3:2 · `06` 3:4 · `07` 4:3 · `08` 1:1). Attach any number of images in the CP —
the mixed ratios are only the placeholder rhythm.

## Brand

`public/assets/brand/` is reserved for a logo / favicon should you replace the text
wordmark in `partials/header`.
