"""Exportación privada del runtime; nunca incluye configuración ni evidencias."""
from pathlib import Path
import tarfile,hashlib,json
root=Path(__file__).resolve().parents[1]
source=root/'wordpress'; dest=root/'local-private/deploy';dest.mkdir(exist_ok=True)
def allowed(p):
    rel=p.relative_to(source)
    return not (p.name=='wp-config.php' or p.name.startswith('.') or '.bak' in p.name or p.suffix in {'.log','.sql','.zip'} or p.name=='test_home.php' or any(x in rel.parts for x in ['qa-evidencia','cache','upgrade','ai1wm-backups','wc-logs']))
with tarfile.open(dest/'runtime.tar.gz','w:gz',compresslevel=3) as tar:
    for p in source.rglob('*'):
        if p.is_file() and allowed(p):tar.add(p,arcname=p.relative_to(source),recursive=False)
print(json.dumps({'runtime_bytes':(dest/'runtime.tar.gz').stat().st_size,'sha256':hashlib.file_digest(open(dest/'runtime.tar.gz','rb'),'sha256').hexdigest()}))
