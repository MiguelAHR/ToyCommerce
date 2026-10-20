set -euo pipefail
python3 - <<'PY'
from pathlib import Path
import json
p=Path('/opt/apps/projects/minikids/runtime-private/config.json');d=json.loads(p.read_text());d.pop('WP_HTTP_BLOCK_EXTERNAL',None);d.pop('WP_ACCESSIBLE_HOSTS',None);p.write_text(json.dumps(d))
PY
cd /opt/apps/projects/minikids/current
runuser -u minikids -- wp eval-file /opt/apps/projects/minikids/runtime-private/production-health.php
nginx -t
for url in https://voxelkeys.com/api/health https://appsr001.voxelkeys.com https://appsr001.vitorstream.shop https://minikids.vitorstream.shop; do
 curl -s -o /dev/null --max-time 20 -w '%{http_code} %{time_starttransfer} %{url_effective}\n' "$url"
done
/usr/local/sbin/minikids-backup database
