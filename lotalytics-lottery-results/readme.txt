=== Lotalytics – Lottery Results ===
Contributors: lotalytics
Tags: lottery, lotto, results, powerball, euromillions
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The latest lottery results and the next draw date for 50+ lotteries, as blocks, a shortcode or a widget. Free; no account needed.

== Description ==

Show the winning numbers of Powerball, Mega Millions, EuroMillions, Eurojackpot, Lotto 6/49, SuperEnalotto, Mega-Sena, Bonoloto and 40+ other lotteries on your site. Results update on their own after every draw.

* **Two blocks**: "Lottery results" (the latest 1–5 draws) and "Next lottery draw".
* **Shortcodes** for classic themes and page builders: `[lotalytics_results game="powerball" draws="5"]` and `[lotalytics_next_draw game="powerball"]`.
* **A classic widget** for widget areas.
* **Fits your theme**: the blocks take your theme's colours, fonts and spacing; the stylesheet is tiny, only loads where results are shown, and can be turned off. Templates can be overridden from your theme (`yourtheme/lotalytics/latest-results.php`).
* **Fast**: results are fetched by your server and cached; your pages never wait on us, and if the service is briefly unavailable the last results are shown.
* **In your language**: English, Spanish, Portuguese, French, German and Italian. Dates follow your site's date format.
* **No tracking**: nothing about your visitors is collected or sent.

Results are facts only: winning numbers, bonus balls and draw dates. No predictions.

= External service =

This plugin is a front end for the **Lotalytics** results service (https://lotalytics.com). It requests data from `https://api.lotalytics.com` to display lottery results:

* **What is sent, and when**: when a page with results is rendered and the cached copy has expired, your server requests `https://api.lotalytics.com/public/embed/<game>` with the number of draws, the page language and your site's home address; the editor's game list comes from `https://api.lotalytics.com/public/games`. The requests are made by your server, never by your visitors' browsers, and contain no information about your visitors.
* **Terms of service**: https://lotalytics.com/terms
* **Privacy policy**: https://lotalytics.com/privacy

= Credit link =

The plugin never adds a link to your site unless you ask it to. Under **Settings → Lotalytics** you can turn on a small "Results by Lotalytics" credit under the results. The free service asks sites that use it to show this credit.

= For developers =

Filters: `lotalytics_results_data` (the payload before rendering), `lotalytics_ball_html` (one ball), `lotalytics_credit_html`, `lotalytics_cache_ttl` (seconds), `lotalytics_api_base` (point the plugin at a staging API; or define `LOTALYTICS_API_BASE` in wp-config.php). Every CSS colour and size is a custom property (`--ltc-ball-bg`, `--ltc-bonus-bg`, `--ltc-ball-size`…).

== Installation ==

1. Install and activate the plugin.
2. Add the "Lottery results" block to a page (or the shortcode, or the widget) and pick a game.
3. Optional: set a default game, and the credit link, under Settings → Lotalytics.

== Frequently Asked Questions ==

= Is it free? =

Yes. The plugin is free, and the results it shows come from Lotalytics' free service. Paid Lotalytics API plans (full history, statistics) will be supported in a later version.

= How fast do results appear after a draw? =

Usually within minutes of the official results being published. The plugin checks more often around a game's draw date.

= Which games are supported? =

Every game listed at https://lotalytics.com/games: the US multi-state and state games, the UK and European lotteries, Canada, Australia, Brazil, Mexico and more. The block's game picker always shows the current list.

= The results look different from my theme =

The blocks follow your theme's colours and fonts. To restyle further, override the CSS custom properties in your theme, turn the plugin stylesheet off in Settings → Lotalytics, or copy a template into `yourtheme/lotalytics/`.

== Screenshots ==

1. The "Lottery results" block in a block theme.
2. The block settings: game, number of draws, compact mode.
3. Settings → Lotalytics.

== Changelog ==

= 0.1.0 =
* First release: "Lottery results" and "Next lottery draw" blocks, shortcodes, classic widget, opt-in credit link, six languages.
