from pathlib import Path
import urllib.request,json,hashlib,sys
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v10-backups'
op=urllib.request.build_opener(urllib.request.ProxyHandler({}));base='http://127.0.0.1:8082/wordpress/'
checks={}
def check(name,value):
    checks[name]=bool(value);print(('PASS ' if value else 'FAIL ')+name)
products=json.loads((B/'native-after.json').read_text('utf8'))['products']
sale=products['470']['data']['slug'];normal=next(x['data']['slug'] for x in products.values() if x['data']['status']=='publish' and not x['data']['sale_price'])
for label,slug in [('abaco','abaco-pedagogico-y-laberinto-de-madera'),('oferta',sale),('normal',normal)]:
    with op.open(base+'product/'+slug+'/') as res:s=res.read().decode();check(label+'_http200',res.status==200)
    (O/f'V10-{label}.html').write_text(s,encoding='utf8');d=html.fromstring(s)
    check(label+'_una_miga',len(d.xpath('//nav[contains(@class,"breadcrumb")]'))==1 and len(d.xpath('//div[contains(@class,"summary")]//nav[contains(@class,"mk8-breadcrumb")]'))==1)
    check(label+'_un_titulo_comentarios',len(d.xpath('//h2[normalize-space(.)="Comentarios"]'))==1 and not d.xpath('//h2[@class="woocommerce-Reviews-title"]'))
    check(label+'_resenas_nativas',len(d.xpath('//*[@id="review_form"]'))==1 and len(d.xpath('//*[@id="commentform"]'))==1 and len(d.xpath('//section[contains(@class,"mk5-review-overview")]'))==1)
    check(label+'_4_dialogos',len(d.xpath('//dialog[@class="mk8-dialog"]'))==4)
    buttons=d.xpath('//button[@data-mk8-dialog]');check(label+'_4_controles',len(buttons)==4 and all(len(d.xpath('//*[@id="'+b.get('aria-controls')+'"]'))==1 for b in buttons))
    check(label+'_3_svg',len(d.xpath('//div[@class="mk5-help-links"]/button/svg'))==3)
    check(label+'_sin_edad_malformada','a + años' not in d.text_content())
    if label=='oferta':check('oferta_3_filas',len(d.xpath('//dl[@class="mk8-price"]/dt'))==3 and not d.xpath('//div[@class="mk10-price-row"]'))
    if label=='normal':check('normal_precio_etiqueta_misma_fila',len(d.xpath('//div[@class="mk10-price-row"]/p'))==2 and not d.xpath('//dl[@class="mk8-price"]'))
before=json.loads((B/'native-before.json').read_text('utf8'));after=json.loads((B/'native-after.json').read_text('utf8'))
check('pedidos_hash_intacto',before['orders']==after['orders'])
check('hpos_correo_pasarelas_intactos',before['options']==after['options'])
fields=['sku','regular_price','sale_price','price','stock_quantity','stock_status','manage_stock','image_id','gallery_image_ids']
check('datos_comerciales_intactos',set(before['products'])==set(after['products']) and all(all(v['data'][f]==after['products'][i]['data'][f] for f in fields) for i,v in before['products'].items()))
protected=json.loads((B/'protected-before.json').read_text('utf8'))
check('protegidos_intactos',all((R/p).is_file() and hashlib.sha256((R/p).read_bytes()).hexdigest()==v for p,v in protected.items()))
for name in ['minikids-commerce-v8.js','minikids-commerce-v8.css']:
    with op.open(base+'wp-content/mu-plugins/'+name) as res:check(name+'_200',res.status==200)
out={'checks':checks,'passed':sum(checks.values()),'total':len(checks),'protected_files':len(protected),'no_browser':True,'normal_slug':normal,'sale_slug':sale}
(O/('verificacion-V10-'+(sys.argv[1] if len(sys.argv)>1 else '1')+'.json')).write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({'passed':out['passed'],'total':out['total']}));sys.exit(0 if all(checks.values()) else 1)
