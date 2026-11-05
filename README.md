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
  the pointer field. One fixed radial disc follows the cursor behind all content (`z-index: -1`
  inside the stacking context `body { isolation: isolate }` creates), and any box carrying
  `.glow` lights a 1px masked ring whose centre is the cursor, using `--gx`/`--gy` written on
  that box. The ring is a `@media (hover: hover)` rule; the disc is a JS module that only runs
  when `(hover: hover) and (pointer: fine)` matches and `prefers-reduced-motion` is off. Both
  are also `display: none` under `forced-colors`, so a touch or high-contrast load gets neither.
- `── Glass ──` in `resources/css/app.css` — `.project-card`, `.panel`, `.nav-preview`, `.frame`
  and `.hero-code` hold a translucent two-stop fill instead of a solid one and sit over a fixed
  `backdrop-filter: blur(14px) saturate(1.3)`, so the cursor disc is literally what the box is
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
| `app-*.css` | 48.74 kB | 10.24 kB |
| `app-*.js` | 5.85 kB | 2.20 kB |
| HTML per page | 11.4 – 31.8 kB | — |
| Fonts, English | 1 file, 22.3 kB | preloaded |
| Fonts, Persian | 2 files, 46.5 + 36.4 kB | both preloaded |

Subresource requests on a page load: 4 (stylesheet, script, two fonts on `/fa`, one on `/en`).
No CDN, no analytics, no external origin.

Pointer field cost, sampled over 120 `pointermove` frames at 1280×800: median 16.7 ms, p95
16.9 ms, max 17.5 ms, 0 frames over 20 ms. Each frame writes four custom properties — two on
the disc, which a `transform` reads, and two on the box under the cursor. Box geometry is read
only when the pointer changes box, the page scrolls, or the window resizes.

With the glass panes lit (a pointer parked inside a `.project-card`, `--sheen` interpolating on
every frame): 119 `pointermove` frames, median 16.7 ms, p95 17.3 ms, max 18.7 ms, 0 frames over
20 ms. The `backdrop-filter` costs nothing measurable while scrolling — 83 scroll frames at a
median of 16.7 ms both with the blur active and with it forced to `none` — because the blur is a
fixed value and the backdrop it samples is repainted by the scroll itself, not by a transition.

Contrast in the lit states, probed at the worst-case pixel — the cursor disc's centre and the
pane's own sheen landing on the same spot, which is the darkest the glass ever gets under the
brightest light: body ink 12.60:1, muted 7.46:1, brand-violet link text 5.58:1 on a
`.project-card`, and 5.20:1 for the same violet at the corner of a `.frame`. Every pair stays
above the WCAG AA floor of 4.5:1; the violet link text in a fully lit pane is below AAA and is
the cost of the glass, so it is the number to raise if the sheen is ever made stronger.

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

- `config/portfolio.php` `contact.*` (email, GitHub, LinkedIn) are empty, so those links
  render on no page; the contact page shows nothing until they are supplied.
- `config/content.php` has one project (Planino) with `repository: null`, `demo: null`, and
  no year or role, and all six §6.2 case-study sections are empty.
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
