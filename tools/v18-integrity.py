from pathlib import Path
import json,hashlib,runpy,urllib.request,subprocess
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v5-backups/v18'
mu=R/'wordpress/wp-content/mu-plugins'
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
old=json.loads((B/'protected-before.json').read_text('utf8'));current=protected()
previous_mu=json.loads((B/'mu-before.json').read_text('utf8'))
subprocess.run(['C:/xampp/php/php.exe',str(R/'tools/v18-inventory.php'),'after'],check=True)
inventory_before=json.loads((B/'inventory-before.json').read_text('utf8'));inventory_after=json.loads((B/'inventory-after.json').read_text('utf8'))
dates=json.loads((B/'product-date-reconstruction.json').read_text('utf8'))
changed={p for p,h in inventory_before['products'].items() if inventory_after['products'].get(p)!=h}
checks={'protected_unchanged':old==current,'all_preexisting_mu_files_unchanged':all(hashlib.sha256((mu/p).read_bytes()).hexdigest()==h for p,h in previous_mu.items()),'orders_options_users_identical':all(inventory_before[k]==inventory_after[k] for k in ['orders','options','users']),'products_identical_except_verified_native_modified_dates':not dates['errors'] and changed==set(dates['verified']) and all(v['all_other_serialized_fields_identical'] for v in dates['verified'].values())}
assets={};op=urllib.request.build_opener(urllib.request.ProxyHandler({}))
for ext in ['css','js']:
 p=mu/('minikids-commerce-v18.'+ext)
 with op.open('http://localhost:8082/wordpress/wp-content/mu-plugins/'+p.name,timeout=45) as response:
  body=response.read();checks[p.name+'_http_matches_disk']=response.status==200 and body==p.read_bytes()
 assets[p.name]=hashlib.sha256(p.read_bytes()).hexdigest()
for name in ['minikids-commerce-v18.php']:
 subprocess.run(['C:/xampp/php/php.exe','-l',str(mu/name)],check=True)
for name in ['minikids-commerce-v18.js','minikids-commerce-v16.js','minikids-commerce-v12.js','minikids-commerce-v9.js']:
 subprocess.run(['node','--check',str(mu/name)],check=True)
css=(mu/'minikids-commerce-v18.css').read_text('utf8')
checks['css_braces_balanced']=css.count('{')==css.count('}')
result={'checks':checks,'protected_files':len(old),'existing_mu_files':len(previous_mu),'products':len(inventory_before['products']),'orders':len(inventory_before['orders']),'users':len(inventory_before['users']),'asset_sha256':assets,'scope':'Byte hashes, native CRUD read inventory, HTTP assets and syntax. No browser layout validation.'}
result['native_product_date_modified_changes']=len(changed)
result['native_product_date_explanation']='WooCommerce WC_Comments::clear_transients saves the product after native comment count update. Reconstructed SHA-256 of all 35 original serialized products matches with only date_modified substituted; no product write in forensic checker. Rating/count metadata verified separately.'
(O/'integridad-V18.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8');print(json.dumps(result,indent=2));assert all(checks.values())
