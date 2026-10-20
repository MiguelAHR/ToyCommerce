"""Compara los terceros publicados con la línea base privada previa a los cambios."""
import sys,json,hashlib
from pathlib import Path
root=Path(sys.argv[1]);baseline=json.loads(Path(sys.argv[2]).read_text());failures=[];count=0;excluded=0
for name,expected in baseline.items():
    if not any(name.startswith('wordpress/'+prefix) for prefix in ['wp-admin/','wp-includes/','wp-content/themes/','wp-content/plugins/']):continue
    if '.bak' in Path(name).name or Path(name).name.startswith('.'):excluded+=1;continue
    path=root/Path(name).relative_to('wordpress')
    count+=1
    if not path.is_file() or hashlib.sha256(path.read_bytes()).hexdigest()!=expected:failures.append(name)
print(json.dumps({'protected_checked':count,'excluded_backups_and_git_metadata':excluded,'mismatches':failures}))
if failures:raise SystemExit(1)
