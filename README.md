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
- `app/Http/Middleware/SetLocale.php` — locale comes from the URL prefix and is applied
  globally, which is what lets an unmatched path render a Persian 404.

## Measured budget (2026-11-05, `npm run build`)

| Asset | Raw | Gzip |
| --- | --- | --- |
| `app-*.css` | 41.75 kB | 9.17 kB |
| `app-*.js` | 4.87 kB | 1.95 kB |
| HTML per page | 11.4 – 31.7 kB | — |
| Fonts, English | 1 file, 22.3 kB | preloaded |
| Fonts, Persian | 2 files, 46.5 + 36.4 kB | both preloaded |

Subresource requests on a page load: 4 (stylesheet, script, two fonts on `/fa`, one on `/en`).
No CDN, no analytics, no external origin.

Accessibility floors verified in the browser: 16 distinct text/background pairs, all passing
WCAG AA (lowest 4.59:1); `:focus-visible` = 2px solid brand violet with 2px offset; the brand
mark, nav links, language switcher, menu toggle, links and buttons all reach 44px on touch
widths while desktop keeps its 40px scale (the inline project-title link stays text-sized and
is paired with a 44px button); zero horizontal overflow from 320px to 1440px in both
directions; `prefers-reduced-motion` collapses animation and transition durations globally.

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
