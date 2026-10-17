#!/usr/bin/env bash
# End-to-end: the plugin in the dev WordPress against the API in the local kind
# cluster (ltc repo: dev/kind/up.sh). Prereqs: kind cluster up, then
#   docker compose -f dev/docker-compose.yml -f dev/docker-compose.kind.yml -p ltcwp up -d
# Exits non-zero on the first failed check.
set -uo pipefail
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
html=$(curl -s "$WP/?page_id=$page_id")
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
before=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "/public/embed/")
curl -s "$WP/?page_id=$page_id" >/dev/null
after=$($K logs deploy/ltc-api --since=10m 2>/dev/null | grep -c "/public/embed/")
[ "$before" = "$after" ] && pass "no new embed requests ($after total)" || bad "cache miss: $before -> $after requests"

echo "== 3. Spanish"
$WPCLI language core install es_ES >/dev/null 2>&1; $WPCLI option update WPLANG es_ES >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -s "$WP/?page_id=$page_id" | grep -q "Resultados de Powerball" && pass "rendered in Spanish" || bad "not in Spanish"
curl -s "$API/public/embed/powerball?n=1&locale=es" | grep -q '"locale": *"es"' && pass "API served es" || bad "API locale"
$WPCLI option update WPLANG "" >/dev/null 2>&1

echo "== 4. API down: last good copy, Site Health warns"
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -s "$WP/?page_id=$page_id" >/dev/null                 # warm the stale copy
$K scale deploy/ltc-api --replicas=0 >/dev/null; $K wait --for=delete pod -l app=ltc-api --timeout=120s >/dev/null 2>&1
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
down=$(curl -s "$WP/?page_id=$page_id")
echo "$down" | grep -q 'class="ltc-ball' && pass "results still shown while the API is down" || bad "page empty while the API is down"
echo "$down" | grep -qE "(Fatal error|Warning|Notice)</b>:" && bad "PHP error on the page" || pass "no PHP errors"
st=$($WPCLI eval '$r = Lotalytics\Results\Site_Health::test(); echo $r["status"];' 2>/dev/null | tail -1)
[ "$st" = "recommended" ] && pass "Site Health flags the outage" || bad "Site Health status '$st'"
$K scale deploy/ltc-api --replicas=1 >/dev/null; $K rollout status deploy/ltc-api --timeout=300s >/dev/null
$WPCLI eval 'Lotalytics\Results\Api_Client::clear_cache();' >/dev/null 2>&1
curl -s "$WP/?page_id=$page_id" | grep -q 'class="ltc-ball' && pass "recovers when the API is back" || bad "no recovery"
st=$($WPCLI eval 'delete_option("lotalytics_last_error"); $r = Lotalytics\Results\Site_Health::test(); echo $r["status"];' 2>/dev/null | tail -1)
[ "$st" = "good" ] && pass "Site Health good again" || bad "Site Health status '$st'"

echo "== 5. the API records where the widget is used"
POD=$($K get pod -l app=ltc-api -o jsonpath='{.items[0].metadata.name}')
seen=$($K exec -i "$POD" -- sh -c 'cd /app && python -' 2>/dev/null <<'EOF' | tail -1
import sys
sys.path.insert(0, "/app")
from models.game_data.game_source import GameSource  # noqa
from models.game.drawing import Drawing  # noqa
from config.db.database import SessionLocal
from service.embeds import embed_service
from models.embeds.embed_site import EmbedSite
db = SessionLocal()
embed_service.fold(db)
print(sorted((s.domain, sorted(s.games)) for s in db.query(EmbedSite).all()))
EOF
)
echo "$seen" | grep -q "localhost" && pass "embed_sites: $seen" || bad "no embed_sites row: $seen"

[ $fail = 0 ] && echo "ALL PASSED" || { echo "FAILURES"; exit 1; }
