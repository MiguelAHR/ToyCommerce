from pathlib import Path
import json,hashlib,subprocess,shutil
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v13-backups';O=R.parent/'diseno_minikids/v5';M=R/'wordpress/wp-content/mu-plugins'
subprocess.run(['C:/xampp/php/php.exe','-l',str(B/'native-snapshot.php')],check=True)
subprocess.run(['C:/xampp/php/php.exe',str(B/'native-snapshot.php'),'after'],check=True)
a=json.loads((B/'native-before.json').read_text('utf8'));b=json.loads((B/'native-after.json').read_text('utf8'))
protected=json.loads((B/'protected-before.json').read_text('utf8'))
diff=[p for p,v in protected.items() if not (R/p).is_file() or hashlib.sha256((R/p).read_bytes()).hexdigest()!=v]
names=['minikids-commerce-v12.php','minikids-commerce-v12.css','minikids-commerce-v12.js','minikids-commerce-v9.php','minikids-commerce-v5.css','minikids-estetica.css']
lint={}
for name in names:
 if name.endswith(('.php','.js')):
  args=['C:/xampp/php/php.exe','-l'] if name.endswith('.php') else ['node','--check']
  result=subprocess.run(args+[str(M/name)],capture_output=True,text=True);lint[name]=result.returncode==0
  assert lint[name],result.stderr
 for p in M.glob(name+'.bak-*'):
  dest=B/p.name
  assert p.resolve().parent==M.resolve() and dest.resolve().parent==B.resolve()
  if dest.exists():dest=B/(p.name+'.'+hashlib.sha256(p.read_bytes()).hexdigest()[:12])
  if not dest.exists():shutil.move(str(p),str(dest))
evidence={'protected_count':len(protected),'protected_differences':diff,'orders_before':len(a['orders']),'orders_unchanged':a['orders']==b['orders'],'options_unchanged':a['options']==b['options'],'product_changes':[pid for pid in a['products'] if a['products'][pid]!=b['products'].get(pid)],'lint':lint,'files':names}
(O/'integridad-V13.json').write_text(json.dumps(evidence,indent=2),encoding='utf8');print(json.dumps(evidence),flush=True)
assert not diff and evidence['orders_unchanged'] and evidence['options_unchanged']
