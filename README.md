# Elia — Beyond the Interface

A bilingual portfolio site (English and Persian) for Elia, a PHP & Laravel developer.
Laravel 12 views over a config-driven content layer: no database, no admin, no third-party
requests. Persian is a first-class direction, not a mirrored stylesheet.

## Stack

| Layer | Choice |
| --- | --- |
| Runtime | PHP `^8.2` (developed on 8.5.6), Laravel `^12.0` |
| Frontend | Blade anonymous components, Vite 7, Tailwind CSS 4 (CSS-first `@theme` tokens) |
| Typography | Self-hosted WOFF2: Space Grotesk (Latin), Estedad arabic+latin subsets (Persian, variable `wght 100–900`); system mono for micro labels |
| Data | `config/content.php` + `lang/{en,fa}/*.php` — `DB_CONNECTION=none`, no migrations |
| Tests | PHPUnit feature tests that parse the rendered HTML with DOMXPath |

## Local development

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
npm run dev &                      # vite
php artisan serve --port=8931      # http://127.0.0.1:8931/en and /fa
```

Verification, run from the repo root:

```bash
php artisan test                                   # feature + design guards
npm run build                                      # production assets into public/build
php artisan route:list                             # 6 named routes × 2 locales
```

## Layout of the interesting parts

- `resources/css/app.css` — the whole design system: tokens, type scale (separate optical
  scale for Persian), `.frame`, `.spec-row`, `.btn`, `.switcher`, the 3D skill hub.
- `resources/views/components/section.blade.php` — the editorial rail (`section-rail` +
  `section-body`) every section is built from.
- `resources/views/components/skill-hub.blade.php` + `resources/js/app.js` — the ring;
  ARIA tab semantics are added by JS only, so no-JS markup stays valid.
- `resources/views/components/site-header.blade.php` + `resources/js/nav-underline.js` —
  the desktop bar. The Projects preview is a CSS disclosure (`:hover`/`:focus-within`, gated
  to `(hover: hover)`), and one shared `.nav-rule` element is placed by JS under the current
  link so it can travel; each link keeps its own `::after` rule as the no-JS marking.
- `resources/js/cursor-glow.js` + `.cursor-glow` / `.glow` in `resources/css/app.css` —
  the pointer field. One fixed `0×0` anchor behind all content (`z-index: -1` inside the
  stacking context `body { isolation: isolate }` creates) carries two radial layers, `::before`
  as the room-sized ambient field and `::after` as the tight core, and the JS chases a single
  pointer target with two different time constants (`tau` 0.16 s and 0.045 s, as
  `1 - e^(-dt/tau)` so the lag is the same on any refresh rate). The gap that opens between them
  while the cursor moves is the effect; a hard 1:1 follow reads as a decal, not as light. The
  core layer is `mix-blend-mode: plus-lighter`, so the two fields add up where they overlap
  instead of the top one hiding the bottom. Both positions are written as `--ax/--ay` and
  `--cx/--cy` and read by a `transform`; `translate: -50% -50%` stays static in the rule so only
  the transform is touched per frame. The first move also seeds both layers at the pointer, or
  the field would fly in from the document's top-left corner. Once the layers arrive the loop
  parks itself and, after 2.6 s without input, the field fades out (0.24 s in, 0.75 s out) rather
  than sitting over text indefinitely. Any box carrying `.glow` lights a 1px masked ring whose
  centre is the cursor, using `--gx`/`--gy` written on that box — and those are fed the *eased
  core*, not the raw pointer, so the box and the field it sits in agree. The ring is a
  `@media (hover: hover)` rule; the field is a JS module that only runs when
  `(hover: hover) and (pointer: fine)` matches and `prefers-reduced-motion` is off. Both are also
  `display: none` under `forced-colors`, so a touch or high-contrast load gets neither.
- `── Glass ──` in `resources/css/app.css` — `.project-card`, `.panel`, `.nav-preview`, `.frame`
  and `.hero-code` hold a translucent two-stop fill instead of a solid one and sit over a fixed
  `backdrop-filter: blur(14px) saturate(1.3)`, so the cursor field is literally what the box is
  made of. Two of the three pointer reactions are registered custom properties (`@property
  --sheen`, `@property --ring`), which is what lets a radial highlight inside the glass and the
  ring's radius interpolate; the blur number itself never animates, because animating
  `backdrop-filter` re-renders the whole backdrop of the box every frame. `--gx`/`--gy` are
  cleared when the pointer leaves a box, so a pane lit by keyboard focus reflects its own centre
  rather than wherever the cursor last was. Under `prefers-reduced-motion` the panes stay (frost
  is not motion) but nothing inside them follows the cursor; under `forced-colors` the blur and
  the sheen are both turned off.
- `app/Http/Middleware/SetLocale.php` — locale comes from the URL prefix and is applied
  globally, which is what lets an unmatched path render a Persian 404.

## Measured budget (2026-11-05, `npm run build`)

| Asset | Raw | Gzip |
| --- | --- | --- |
| `app-*.css` | 51.19 kB | 10.56 kB |
| `app-*.js` | 6.36 kB | 2.41 kB |
| HTML per page | 11.4 – 31.8 kB | — |
| Fonts, English | 1 file, 22.3 kB | preloaded |
| Fonts, Persian | 2 files, 46.5 + 36.4 kB | both preloaded |

Subresource requests on a page load: 4 (stylesheet, script, two fonts on `/fa`, one on `/en`).
No CDN, no analytics, no external origin.

Pointer field cost, sampled while the field was fully lit and both layers were still moving:
240 `pointermove` frames, median 16.7 ms, p95 16.9 ms, max 17.0 ms, 0 frames over 20 ms on a
60 Hz panel. Each frame writes six custom properties — four transforms for the two layers and
two ring coordinates for the box under the cursor — and reads box geometry only when the pointer
changes box, the page scrolls, or the window resizes. Scrolling with the field lit and a
`.project-card`'s blur active costs exactly the same: 57 frames, median 16.7 ms, p95 16.8 ms,
max 17.0 ms, 0 over 20 ms. The `backdrop-filter` is not the expensive part, because it is a
fixed value and the backdrop it samples is repainted by the scroll itself rather than by a
transition.

The trail, measured on a 1100 px pointer jump: the distance between the core and the ambient
field opens to 532 px about 50 ms in, then closes to 43 px by 500 ms. No CSS transition is
involved, so the lag is a property of the two easing constants and comes out the same at 60,
120 or 144 Hz.

Contrast, probed from screenshots of the composited page instead of from the token values,
because the field, the glass and the blur all land on the same pixel. Over the bare canvas the
field lifts the background from `#0b0d12` to `#231d3e` at its centre — a luminance gain of
0.0118 — and the worst text pairs there are ink 13.23:1, muted 7.84:1, brand-violet 5.86:1
against 16.13 / 9.56 / 7.14 with the field switched off. Inside a fully lit pane — the pointer
parked at the centre of a `.project-card`, `--sheen` interpolated up to 13%, the ring open and
the field blurred through the frost — the brightest pane fill is `#231e41` and the same three
pairs are 13.07:1, 7.74:1, 5.79:1. The field's own share of that last figure is 6.18:1 → 5.79:1;
the rest of the loss is the glass, which is the trade the panes were approved for. Every pair
stays above the WCAG AA floor of 4.5:1, and the violet link text in a fully lit pane is the
number to raise if the sheen or the field is ever made stronger.

Accessibility floors verified in the browser: 16 distinct text/background pairs, all passing
WCAG AA (lowest 4.59:1); `:focus-visible` = 2px solid brand violet with 2px offset; the brand
mark, nav links, language switcher, menu toggle, links and buttons all reach 44px on touch
widths while desktop keeps its 40px scale (the inline project-title link stays text-sized and
is paired with a 44px button); no horizontal overflow from 320px to 1440px in either direction
on any page except `/projects`, whose card grid keeps a 20rem minimum track and scrolls
horizontally by 20px at exactly 320px (see Outstanding decisions and known issues);
`prefers-reduced-motion` collapses animation and transition durations globally.

## Deployment

The web server document root must be `public/`. `public/build/` is gitignored, so assets
have to be built as part of the release (or copied as a build artifact).

```bash
git clone <repository> && cd portfolio
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

npm ci
npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
```

Environment values that must change before a release:

| Key | Required value | Why |
| --- | --- | --- |
| `APP_ENV` | `production` | enables production error handling |
| `APP_DEBUG` | `false` | `true` renders stack traces and configuration to visitors |
| `APP_URL` | the public origin | `canonical`, `og:url` and `hreflang` are generated from it |
| `APP_KEY` | from `key:generate` | session/cookie encryption |

There are no migrations — never run `php artisan migrate` here; the connection is `none`.

Apache serves this app through the committed `public/.htaccess`. For nginx, the equivalent
is `try_files $uri $uri/ /index.php?$query_string;`.

Smoke test after release:

```bash
for p in en fa en/projects fa/projects en/lab en/about en/contact en/projects/planino; do
  printf '%-24s %s\n' "$p" "$(curl -s -o /dev/null -w '%{http_code}' "https://example.com/$p")"
done
curl -sI https://example.com/ | grep -i '^location'   # expect /en
```

Cache the three artifacts again after every deploy (`config:clear`, `route:clear`,
`view:clear` first if the previous release left stale files behind); stale caches are the
usual cause of a release that appears not to have taken effect.

## Outstanding decisions and known issues

Content waiting on confirmed facts (nothing here is filled in with guesses):

- Four contact channels are configured and render in the footer and on `/contact`, each with
  `rel="me noopener"`: GitHub, LinkedIn, Instagram and Telegram. The last two arrived as
  `@handles` and are stored as their canonical profile URLs. Their labels live in
  `contact.channels` in each lang file, so the Persian page reads گیت‌هاب / لینکدین /
  اینستاگرام / تلگرام rather than four Latin brand names on an otherwise translated page;
  a channel added to config without both label entries fails. `contact.email` is the only
  channel still null — the address exists in local git config and publishing it is the
  owner's spam tradeoff to make, not ours.
- Of the four, only Telegram could be verified from this machine: `t.me/RangerELM` answers
  with a non-empty bio where a made-up handle answers with an empty one. LinkedIn returns
  `999` to every client and Instagram login-walls every probe including `/embed/`, so a typo
  in either URL would only surface when a visitor clicked it.
- Planino links `https://github.com/ranger-elm/Planino_v1` — the repository holding the newest
  code, despite the older-sounding name — and has no `demo`, because nothing is deployed. Four
  case-study sections (§6.2) are written from the owner's own answers plus the repository;
  `challenges` and `lessons` are deliberately absent, and the renderer drops a heading whose
  body is missing, so the page never shows an empty block.
- The stack says MySQL because that is what the working `.env` of the current repository runs;
  its shipped `.env.example` says sqlite. Worth confirming which one the portfolio should claim.
- The four added About paragraphs and the skills heading wording need approval.
- No `og:image` exists, so social previews are text-only. Adding one needs an approved
  visual asset — a raster card cannot be produced from the identity without a decision.

Known layout defect, measured and not yet fixed:

- The projects card grid is `grid-cols-[repeat(auto-fit,minmax(20rem,1fr))]`
  (`resources/views/pages/projects.blade.php:10`). At a 320px viewport the content column is
  265px wide but the `minmax()` floor forces the track to 320px, so the card runs past the edge
  and `documentElement.scrollWidth` is 340 against `innerWidth` 320 — a 20px horizontal scroll
  on `/en/projects`, and 35px past the left edge on `/fa/projects`. It predates the glass stage
  and that stage did not touch the file. The one-token fix is `minmax(min(20rem,100%),1fr)`;
  it needs approval because it changes a rule the approved design pass already shipped.

Housekeeping:

- `composer validate` reports a content-hash mismatch in `composer.lock` that predates the
  metadata edits in this file. Fixing it needs network access: `composer update --lock`.
- `package.json` still lists `axios`, which no code imports.
- `.env.example` carries unused `REDIS_*`, `AWS_*` and `MAIL_*` keys from the Laravel
  skeleton; the local `.env` still has `APP_NAME=Laravel`, which is not rendered anywhere.
- `public/favicon.ico` is the skeleton's 0-byte placeholder; browsers use `/favicon.svg`.
- Locale URLs are case-sensitive: `/EN` is a 404, and a trailing slash canonicalises to the
  unprefixed form (`/en/` publishes `canonical` for `/en`).
- No `sitemap.xml`: 13 URLs, reachable from the header and footer on every page.
