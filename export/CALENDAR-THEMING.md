# Calendar theming

The booking calendar (date popup, range selection, per-day price labels) is Resrv's
bundled alpine-calendar — **never fork the component**. It reads CSS custom properties,
all defined in one `:root` block in `resources/css/site.css` and mapped to the kit's
brand tokens. Change the values there and rebuild (`npm run build`).

| Variable | Kit value | Drives |
| --- | --- | --- |
| `--color-calendar-bg` | `#FFFFFF` | Popup card background |
| `--color-calendar-text` | `#1B2B28` (Ink) | Day numbers |
| `--color-calendar-border` | `#DCD5C7` (Border) | Card / separator lines |
| `--color-calendar-hover` | `#C5D3CF` (Sage Light) | Day hover fill |
| `--color-calendar-overlay` | `rgba(27,43,40,.30)` | Mobile overlay scrim |
| `--color-calendar-primary` | `#C98A5E` (Terracotta) | Selected / active day |
| `--color-calendar-primary-text` | `#FFFFFF` | Text on selected day |
| `--color-calendar-muted` | `#5A6B67` (Muted) | Secondary text |
| `--color-calendar-disabled` | `#B7B1A4` | Past / unavailable days |
| `--color-calendar-other-month` | `#B7B1A4` | Leading/trailing month days |
| `--color-calendar-weekday` | `#5A6B67` | MON–SUN header row |
| `--color-calendar-range` | `#E8DFD2` (Sand) | In-range fill between check-in/out |
| `--color-calendar-today-ring` | `#9CB4B0` (Sage) | Today indicator ring |
| `--color-calendar-focus-ring` | `#C98A5E` | Keyboard focus ring |
| `--color-calendar-available` | `#4F7A5B` (Success) | Availability label/dot |
| `--color-calendar-available-bg` | `#EAF1EA` | Availability label fill |
| `--color-calendar-unavailable` | `#B7B1A4` | Unavailable label |
| `--color-calendar-day-meta` | `#5A6B67` | **Per-day price labels** (spa/dining single-date calendars) |
| `--radius-calendar` | `16px` | Popup card radius |
| `--radius-calendar-day` | `9999px` | Day cell radius (pill) |
| `--radius-calendar-day-range-edge` | `9999px` | Range end-caps (defaults derive from day radius) |
| `--radius-calendar-day-range-middle` | `0` | Mid-range cells |
| `--shadow-calendar` | `0 12px 40px rgba(27,43,40,.12)` | Popup shadow |
| `--font-calendar` | `"Inter", ui-sans-serif, …` | Calendar typeface |

Notes:

- The variables live in `:root` (not Tailwind's `@theme`) because Resrv's bundled
  `resrv-frontend.css` consumes them directly at runtime.
- Single-date calendars (spa, dining) show per-day prices and availability when the
  search widget passes `showAvailabilityOnCalendar="true"` — themed by the
  `*-available*` and `*-day-meta` variables.
- Closed weekdays (spa Tuesdays, La Marea Mondays) render as disabled because the
  seeder writes **no availability rows** for them — that's data, not CSS.
