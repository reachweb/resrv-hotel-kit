# Image manifest

The kit ships **no photography**. Every image renders through a graceful *named slot*
(`partials/image-slot`): when nothing is attached you see a labelled placeholder with
the slot name and aspect ratio; attach an image and the slot renders it via Glide.

**To fill a slot, attach an image to the entry's image field in the Control Panel.**
The slot renders whatever you attach, regardless of filename. Each image field is
pre-scoped to a folder in the `assets` container (e.g. rooms → `public/assets/rooms/`),
so CP uploads land there automatically — the folders are created on upload, nothing is
pre-scaffolded. You can also pre-stage files on disk under `public/assets/<folder>/` and
then attach them, but dropping a file in alone won't fill a slot: the entry field has to
reference it.

The tables below are a **reference**, not a requirement — they list every slot, the
folder/filename convention we suggest (handy for staying organized and matching these
docs), where it appears, and the aspect ratio the layout was designed around. All slots
use `aspect-ratio`, so any sufficiently large image works; aim for ≥1600px on the long
edge (Glide serves `width=1600` and downsizes responsively).

## Heroes & pages

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `hero/home.jpg` | 16:9 | Home full-bleed hero (behind the search card) |
| `hero/<page-slug>.jpg` | 16:9 | Any page using the hero block with an image (dining, spa, …) |
| `hero/404.jpg` | 4:3 | The 404 page illustration |
| `<page-slug>.jpg` | 4:3 | `image_text_split` blocks (About story, taverna split, …) |
| `map-location.jpg` | 21:9 | Contact page map slot |

## Rooms (4 entries)

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `rooms/<slug>.jpg` | 3:2 | Room card (index, showcase, "you may also like") + detail hero |
| `rooms/<slug>-gallery-1.jpg` | 3:2 | Room detail gallery, second image |
| `rooms/<slug>-gallery-2.jpg` | 3:2 | Room detail gallery, third image |

Room slugs: `garden-view-double`, `sea-view-suite`, `terracotta-villa`,
`coastal-family-room`.

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
eight placeholder slots (ratios 3:2 · 3:4 · 4:5 · 16:9 · 3:2 · 3:4 · 4:3 · 1:1). Attach
any number of images in the CP — the mixed ratios are only the placeholder rhythm.

## Brand

`public/assets/brand/` is reserved for a logo / favicon should you replace the text
wordmark in `partials/header`.
