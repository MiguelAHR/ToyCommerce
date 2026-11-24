"""Local MCP writes with backup=true, private prior copies, syntax checks/readback."""
from pathlib import Path
import runpy,json,hashlib,subprocess,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v5-backups/v18'
if (O/'aplicacion-V18.json').exists():raise RuntimeError('Already applied; use explicit patch/reapply')
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
before=protected();(B/'protected-before.json').write_text(json.dumps(before),encoding='utf8')
mu=R/'wordpress/wp-content/mu-plugins';existing={p.name:hashlib.sha256(p.read_bytes()).hexdigest() for p in mu.iterdir() if p.is_file()}
(B/'mu-before.json').write_text(json.dumps(existing),encoding='utf8')
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
receipts=[]
for ext in ['css','js','php']:
 name='minikids-commerce-v18.'+ext
 source=R/'tools'/('v18-presentation.'+ext+('.txt' if ext=='php' else ''))
 receipts.append({'file':name,'receipt':d['apply'](name,source.read_text('utf8'))})
assert before==protected(),'Protected files changed'
assert all(hashlib.sha256((mu/name).read_bytes()).hexdigest()==sha for name,sha in existing.items()),'Existing MU changed'
subprocess.run(['node','--check',str(mu/'minikids-commerce-v18.js')],check=True)
(O/'aplicacion-V18.json').write_text(json.dumps({'writes':receipts,'protected_files_unchanged':len(before),'existing_mu_files_unchanged':len(existing)},ensure_ascii=False,indent=2),encoding='utf8')
