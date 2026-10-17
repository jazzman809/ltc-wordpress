#!/usr/bin/env bash
# End-to-end: the plugin in the dev WordPress against the API in the local kind
# cluster (ltc repo: dev/kind/up.sh). Prereqs: kind cluster up, then
#   docker compose -f dev/docker-compose.yml -f dev/docker-compose.kind.yml -p ltcwp up -d
# Exits non-zero on the first failed check.
# No pipefail: `curl | grep -q` exits early on a match, curl gets SIGPIPE, and
# pipefail would count that as a failure.
set -u
cd "$(dirname "$0")"
API=http://localhost:18080
WP=http://localhost:8089
K="kubectl --context kind-ltc-test -n ltc"
WPCLI=./wp-kind
fail=0
pass() { echo "  PASS  $*"; }
bad()  { echo "  FAIL  $*"; fail=1; }

page_id=$($WPCLI post list --post_type=page --name=kind-test --field=ID 2>/dev/null | tail -1)
if [ -z "$page_id" ]; then
  page_id=$($WPCLI post create --post_type=page --post_status=publish --post_title="Kind test" --post_name=kind-test \
    --post_content='<!-- wp:lotalytics/latest-results {"game":"powerball","draws":3} /--><!-- wp:lotalytics/next-draw {"game":"mega-sena"} /-->[lotalytics_results game="bonoloto" draws="2"][lotalytics_results game="no-such-game"]' --porcelain 2>/dev/null | tail -1)
fi
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache(); echo Lotalytics\Results\Api_Client::base();' 2>/dev/null | tail -1 | grep -q 18080 \
  && pass "plugin points at the kind API" || bad "LOTALYTICS_API_BASE not in effect"

echo "== 1. the page shows what the API serves"
want=$(curl -s "$API/public/embed/powerball?n=3&locale=en" | python3 -c "import sys,json; d=json.load(sys.stdin); print(' '.join('-'.join(map(str,x['numbers']+x['bonus_numbers'])) for x in d['drawings']))")
html=$(curl -sL "$WP/?page_id=$page_id")
got=$(python3 - "$html" <<'EOF'
import re, sys
h = sys.argv[1]
i = h.find("wp-block-lotalytics-latest-results")
block = h[i:h.find("</div>", i)] if i >= 0 else ""
draws = block.split('<li class="ltc-draw">')[1:4]
print(" ".join("-".join(re.findall(r'class="ltc-ball[^"]*">(\d+)<', d)) for d in draws))
EOF
)
[ -n "$want" ] && [ "$want" = "$got" ] && pass "Powerball block = API ($got)" || bad "Powerball: API '$want' vs page '$got'"
echo "$html" | grep -q "ltc-next-draw" && pass "next-draw block rendered" || bad "next-draw block missing"
echo "$html" | grep -q "Bonoloto" && pass "Bonoloto shortcode rendered" || bad "Bonoloto shortcode missing"
echo "$html" | grep -q "ltc-unavailable" && bad "visitor sees the unavailable hint" || pass "unknown game is invisible to visitors"

echo "== 2. cached: a second load makes no API call"
before=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "GET /public/embed/")
curl -sL "$WP/?page_id=$page_id" >/dev/null
after=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "GET /public/embed/")
[ "$before" = "$after" ] && pass "no new embed requests ($after total)" || bad "cache miss: $before -> $after requests"

echo "== 3. Spanish"
$WPCLI language core install es_ES >/dev/null 2>&1; $WPCLI option update WPLANG es_ES >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -sL "$WP/?page_id=$page_id" | grep -q "Resultados de Powerball" && pass "rendered in Spanish" || bad "not in Spanish"
curl -s "$API/public/embed/powerball?n=1&locale=es" | grep -q '"locale": *"es"' && pass "API served es" || bad "API locale"
$WPCLI option update WPLANG "" >/dev/null 2>&1

echo "== 4. API down: last good copy, Site Health warns"
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -sL "$WP/?page_id=$page_id" >/dev/null                 # warm the stale copy
$K scale deploy/ltc-api --replicas=0 >/dev/null; $K wait --for=delete pod -l app=ltc-api --timeout=120s >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
down=$(curl -sL "$WP/?page_id=$page_id")
echo "$down" | grep -q 'class="ltc-ball' && pass "results still shown while the API is down" || bad "page empty while the API is down"
echo "$down" | grep -qE "(Fatal error|Warning|Notice)</b>:" && bad "PHP error on the page" || pass "no PHP errors"
st=$($WPCLI eval '$r = Lotalytics\Results\Site_Health::test(); echo $r["status"];' 2>/dev/null | tail -1)
[ "$st" = "recommended" ] && pass "Site Health flags the outage" || bad "Site Health status '$st'"
$K scale deploy/ltc-api --replicas=1 >/dev/null; $K rollout status deploy/ltc-api --timeout=300s >/dev/null
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -sL "$WP/?page_id=$page_id" | grep -q 'class="ltc-ball' && pass "recovers when the API is back" || bad "no recovery"
st=$($WPCLI eval 'delete_option("lotalytics_last_error"); $r = Lotalytics\Results\Site_Health::test(); echo $r["status"];' 2>/dev/null | tail -1)
[ "$st" = "good" ] && pass "Site Health good again" || bad "Site Health status '$st'"

echo "== 5. the API records where the widget is used"
# The API never counts localhost (its own hosts), so give the site a real-looking
# name for one load: home_url() is what the plugin reports as the page.
$WPCLI option update home http://fan-site.test:8089 >/dev/null 2>&1
$WPCLI option update siteurl http://fan-site.test:8089 >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -sL --resolve fan-site.test:8089:127.0.0.1 "http://fan-site.test:8089/?page_id=$page_id" >/dev/null
$WPCLI option update home http://localhost:8089 >/dev/null 2>&1
$WPCLI option update siteurl http://localhost:8089 >/dev/null 2>&1
POD=$($K get pod -l app=ltc-api --field-selector=status.phase=Running --sort-by=.metadata.creationTimestamp -o jsonpath='{.items[-1:].metadata.name}')
seen=$($K exec -i "$POD" -- sh -c 'cd /app && python -' 2>/dev/null <<'EOF' | tail -1
import sys
sys.path.insert(0, "/app")
import main  # noqa: F401 — every model registered
from config.db.database import SessionLocal
from service.embeds import embed_service
from models.embeds.embed_site import EmbedSite
db = SessionLocal()
embed_service.fold(db)
print(sorted((s.domain, sorted(s.games)) for s in db.query(EmbedSite).all()))
EOF
)
echo "$seen" | grep -q "fan-site.test" && pass "embed_sites: $seen" || bad "no embed_sites row: $seen"

echo "== 6. API keys (phase 2): needs dev/kind/make_test_keys.py output in /tmp/kind_keys.json"
if [ ! -f /tmp/kind_keys.json ]; then
  bad "no /tmp/kind_keys.json (run ltc dev/kind/make_test_keys.py)"
else
PAID=$(python3 -c "import json; print(json.load(open('/tmp/kind_keys.json'))['paid'])")
FREE=$(python3 -c "import json; print(json.load(open('/tmp/kind_keys.json'))['free'])")
$WPCLI rewrite structure '/%postname%/' --hard >/dev/null 2>&1
$WPCLI option update lotalytics_api_key "$PAID" >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -s -o /dev/null "$WP/"                                        # flushes the section's rules
panel=$($WPCLI eval 'wp_set_current_user(1); Lotalytics\Results\Settings::field_key();' 2>/dev/null)
echo "$panel" | grep -q "API Growth" && pass "settings show the paid plan" || bad "plan not shown"

v1_before=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "GET /v1/games")
curl -sL "$WP/?page_id=$page_id" >/dev/null
v1_after=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "GET /v1/games")
[ "$v1_before" = "$v1_after" ] && pass "latest/next blocks never spend the key's quota" || bad "free blocks called /v1 ($v1_before -> $v1_after)"

arch_id=$($WPCLI post list --post_type=page --name=kind-archive --field=ID 2>/dev/null | tail -1)
[ -z "$arch_id" ] && arch_id=$($WPCLI post create --post_type=page --post_status=publish --post_title="Archive" --post_name=kind-archive \
  --post_content='<!-- wp:lotalytics/results-archive {"game":"powerball","perPage":10} /-->' --porcelain 2>/dev/null | tail -1)
a1=$(curl -sL "$WP/?page_id=$arch_id"); a2=$(curl -sL "$WP/?page_id=$arch_id&ltc_page=2")
n1=$(echo "$a1" | grep -o '<li class="ltc-draw">' | wc -l | tr -d ' ')
first_date() { python3 -c "import re,sys; m=re.search(r'ltc-draw__date\">\s*<a[^>]*>([^<]+)<', sys.stdin.read()); print(m.group(1) if m else '')"; }
d1=$(echo "$a1" | first_date); d2=$(echo "$a2" | first_date)
[ "$n1" = "10" ] && pass "archive block: 10 draws" || bad "archive block shows $n1 draws"
[ -n "$d1" ] && [ "$d1" != "$d2" ] && pass "archive page 2 starts later ($d1 / $d2)" || bad "pagination: '$d1' vs '$d2'"
echo "$a1" | grep -q 'ltc-pager__next' && pass "archive has a pager" || bad "no pager"

code() { curl -s -o /dev/null -w "%{http_code}" "$WP$1"; }
[ "$(code /lottery/)" = 200 ] && pass "/lottery/ 200" || bad "/lottery/ $(code /lottery/)"
[ "$(code /lottery/powerball/)" = 200 ] && [ "$(code /lottery/powerball/page/2/)" = 200 ] && pass "game archive + page 2: 200" || bad "game archive"
sleep 1   # the paid plan allows 5 requests a second, and the pages above just used some
last=$(curl -s "$API/v1/games/powerball/drawings?page_size=1" -H "X-API-Key: $PAID" | python3 -c "import sys,json; print(json.load(sys.stdin)['drawings'][0]['date'])")
sleep 1
want=$(curl -s "$API/v1/games/powerball/drawings/$last" -H "X-API-Key: $PAID" | python3 -c "import sys,json; d=json.load(sys.stdin)['drawing']; print('-'.join(map(str,d['numbers']+d['bonus_numbers'])))")
page=$(curl -s "$WP/lottery/powerball/$last/")
got=$(echo "$page" | grep -o 'class="ltc-ball[^"]*">[0-9]*<' | grep -o '[0-9]*<' | tr -d '<' | paste -sd- -)
[ "$want" = "$got" ] && pass "draw page $last = API ($got)" || bad "draw page: API '$want' vs page '$got'"
echo "$page" | grep -q '<link rel="canonical" href="[^"]*/lottery/powerball/'"$last"'/"' && pass "draw page canonical" || bad "no canonical"
echo "$page" | grep -q '<meta name="description" content="Powerball winning numbers' && pass "draw page description" || bad "no description"
[ "$(code /lottery/eurojackpot/)" = 404 ] && pass "a game outside the plan: 404" || bad "eurojackpot $(code /lottery/eurojackpot/)"
[ "$(code /lottery/powerball/1999-01-01/)" = 404 ] && pass "a date with no draw: 404" || bad "no-draw date $(code /lottery/powerball/1999-01-01/)"
locs=$(curl -s "$WP/wp-sitemap-lotalytics-1.xml" | grep -o "<loc>" | wc -l | tr -d ' ')
[ "$locs" -ge 90 ] && pass "sitemap: $locs URLs" || bad "sitemap: $locs URLs"
q=$($WPCLI option get lotalytics_quota --format=json 2>/dev/null | tail -1)
echo "$q" | grep -q monthly_limit && pass "quota headers recorded" || bad "no quota snapshot: $q"
pf=$($WPCLI eval '
$i = (array) get_option("lotalytics_cache_index"); $n = 0;
foreach ($i as $k => $e) { if (empty($e["url"])) continue; $o = str_replace("lotalytics_", "lotalytics_stale_", $k); $s = get_option($o);
  if (is_array($s) && !empty($s["next_draw"])) { $s["next_draw"] = "2000-01-01"; update_option($o, $s, false); $n++; } }
echo Lotalytics\Results\Api_Client::prefetch();' 2>/dev/null | tail -1)
[ "${pf:-0}" -ge 1 ] && pass "prefetch refreshed $pf entries after their draw" || bad "prefetch refreshed '${pf}'"

$WPCLI option update lotalytics_api_key "$FREE" >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
[ -z "$(curl -sL "$WP/?page_id=$arch_id" | grep -o 'ltc-draw')" ] && pass "free key: archive hidden from visitors" || bad "free key shows the archive"
[ "$(code /lottery/powerball/)" = 404 ] && pass "free key: no results section" || bad "free key section $(code /lottery/powerball/)"
# The API checks the page right away (ports 80/443, public hosts), so use a
# real, resolvable site: an unresolvable name only fails after DNS timeouts.
$WPCLI option update home https://example.com >/dev/null 2>&1
reg=$($WPCLI eval '$r = Lotalytics\Results\Api_Client::register_site(home_url("/")); echo is_wp_error($r) ? "ERR ".$r->get_error_message() : json_encode(array_column($r["attribution"]["sites"], "domain"));' 2>/dev/null | tail -1)
$WPCLI option update home http://localhost:8089 >/dev/null 2>&1
echo "$reg" | grep -q "example.com" && pass "free key: site registered from the plugin ($reg)" || bad "register: $reg"
$WPCLI option update lotalytics_api_key "$PAID" >/dev/null 2>&1
fi

[ $fail = 0 ] && echo "ALL PASSED" || { echo "FAILURES"; exit 1; }
