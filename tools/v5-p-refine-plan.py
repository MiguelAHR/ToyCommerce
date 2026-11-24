from pathlib import Path
import json,shutil,datetime
ROOT=Path(__file__).resolve().parents[1];p=ROOT.parent/'diseno_minikids/v5/catalogo_plan.json'
shutil.copy2(p,ROOT.parent/'local-private/v5-backups'/('catalogo_plan-before-refine-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S')+'.json'))
data=json.loads(p.read_text('utf8'))
prices={
'peluches':[29.9,39.9,49.9,39.9,49.9,59.9,34.9,49.9,29.9,39.9,49.9],
'didacticos':[19.9,39.9,49.9,29.9,24.9,24.9,34.9,44.9,29.9],
'vehiculos':[29.9,49.9,39.9,24.9,19.9,34.9,29.9,24.9,19.9,149.9,79.9],
'juegos-de-rol':[39.9,19.9,49.9,34.9,24.9,29.9,39.9,34.9,59.9,29.9,129.9,79.9],
'creatividad':[9.9,14.9,19.9,24.9,39.9,24.9,12.9,19.9,9.9,29.9,14.9,24.9],
'juegos-familiares':[24.9,39.9,29.9,39.9,24.9,29.9,19.9,24.9,29.9,39.9,24.9,14.9,49.9],
'tecnologia':[149.9,99.9,79.9,69.9,29.9,59.9,79.9,39.9,99.9,129.9,199.9,24.9,249.9]}
offers={int(k*81/24)for k in range(24)}
# Keep every effective demo price at or above S/9.90.
offers.remove(43);offers.add(44)
counts={}
for i,s in enumerate(data):
    cat=s['category_slug'];n=counts.get(cat,0);counts[cat]=n+1;r=prices[cat][n]
    s['regular_price']=f'{r:.2f}';s['sale_price']=f'{r*(1-[.1,.15,.2,.25,.3][i%5]):.2f}'if i in offers else ''
p.write_text(json.dumps(data,ensure_ascii=False,indent=2),encoding='utf8')
print('81 demo prices distributed by toy type; 24 offers across all seven categories')
