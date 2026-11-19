# Image manifest

The kit ships **no photography**. Every image renders through a graceful *named slot*
(`partials/image-slot`): when nothing is attached you see a labelled placeholder with
the slot name and aspect ratio; attach an image and the slot renders it via Glide.

Empty slots are drawn as surfaces from the hotel, so a fresh install looks finished:
sun on a limewashed wall (rooms, sections), clay (offers), sage (spa, map), and dark
terracotta, cypress and sea where white type sits on top (feature tiles, offer heroes).
The home hero draws first light over the bay. Choose a surface with the partial's `tone`
param (`limewash` · `clay` · `sage` · `sea` · `terracotta` · `cypress`); the textures
and colours live under `.img-slot` in `resources/css/site.css`.

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
| `<page-slug>.jpg` | 4:3 | `image_text_split` blocks (About story, Dining split, …) |
| `home/<tile>.jpg` | 3:2 | Home "Dining, spa and sea" tiles (`feature_tiles` block — attach per tile on the Home entry) |
| `map-location.jpg` | 21:9 | Contact page map slot |

The 404 page is not a slot: it ships a drawn illustration (an arched window onto the
sea, `partials/sea-window`), so it needs no photo.

## Rooms (4 entries)

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `rooms/<slug>.jpg` | 3:2 | Room card (index, showcase, "you may also like") + detail hero |
| `rooms/<slug>.jpg` | 16:9 | Checkout order summary (same file, cropped; hidden when empty) |
| `rooms/<slug>-gallery-1.jpg` | 3:2 | Room detail gallery, second image |
| `rooms/<slug>-gallery-2.jpg` | 3:2 | Room detail gallery, third image |

Room slugs: `garden-view-double`, `sea-view-suite`, `terracotta-villa`,
`coastal-family-room`.

## Spa & dining

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `spa/<treatment-slug>.jpg` | 3:2 | Treatment page editorial image |
| `spa/<treatment-slug>.jpg` | 16:9 | Checkout order summary (same file, cropped; hidden when empty) |
| `dining/la-marea.jpg` | 3:2 | Dining booking band, beside the table booking (cropped to 4:5 on desktop; the venue entry's Image field) |
| `dining/la-marea.jpg` | 16:9 | Checkout summary for table reservations (same file, cropped; hidden when empty) |

Treatment slugs: `mediterranean-massage`, `sea-salt-body-polish`,
`garden-botanicals-facial`, `couples-ritual`, `morning-stretch-steam`,
`in-room-treatment`.

## Offers & journal

| Slot | Ratio | Where it appears |
| --- | --- | --- |
| `offers/<slug>.jpg` | 3:2 | Offer cards |
| `offers/<slug>.jpg` | 21:9 | Offer detail hero band |
| `journal/<slug>.jpg` | 3:2 / 16:9 | Journal cards / article hero (journal module) |

## Dining gallery

The gallery block (used on Dining) renders attached assets as an even grid of 4:5
tiles (photos crop to fill the tile; the lightbox shows them whole); until populated
it shows four 4:5 placeholder slots. Attach any number of images in the CP. Give
each one an **Alt Text** in the asset editor: it names the tile and captions the
lightbox. Without one, a photo is announced as "Photo 2 of 4".

## Brand

The "resrv." wordmark is inline SVG in `resources/views/partials/logo.antlers.html`, and
the favicons are `public/favicon.ico`, `public/favicon.svg` and `public/apple-touch-icon.png`.
To rebrand, replace that partial and those three files.
