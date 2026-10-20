set -euo pipefail
python3 - <<'PY'
import json
from pathlib import Path
p=Path('/opt/apps/projects/minikids/runtime-private/config.json');d=json.loads(p.read_text());d['WP_HTTP_BLOCK_EXTERNAL']=True;d['WP_ACCESSIBLE_HOSTS']='api.brevo.com';p.write_text(json.dumps(d))
PY
