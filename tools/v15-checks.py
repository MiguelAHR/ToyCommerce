from pathlib import Path
import urllib.request,urllib.parse,urllib.error,http.cookiejar,json,subprocess,time,secrets,sys
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';BASE='http://localhost:8082/wordpress/'
checks={};details={}
def check(name,value):
    checks[name]=bool(value)
    print(('OK ' if value else 'FAIL ')+name,flush=True)
def session():return urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def get(op,path):
    with op.open(path if path.startswith('http') else BASE+path,timeout=40) as r:return r.read().decode(),r
def doc(op,path):
    s,r=get(op,path);return html.fromstring(s),s,r
def post(op,data):
    try:r=op.open(BASE+'wp-admin/admin-ajax.php',urllib.parse.urlencode(data).encode(),timeout=40)
    except urllib.error.HTTPError as e:r=e
    raw=r.read().decode()
    try:result=json.loads(raw)
    except ValueError:raise RuntimeError('Non-JSON auth response, status '+str(r.code))
    check('no_store_'+str(len(checks)),'no-store' in r.headers.get('Cache-Control',''))
    return r.code,result
def forms(op):
    d,_,_=doc(op,'mi-cuenta/');return {f.get('data-mk12-view'):{i.get('name'):i.get('value','') for i in f.xpath('.//input[@type="hidden"]')} for f in d.xpath('//form[@data-mk12-view]')}
def native(mode):
    p=subprocess.run(['C:/xampp/php/php.exe',str(R/'tools/v15-native.php'),mode],capture_output=True,text=True,encoding='utf8',check=True)
    return json.loads(p.stdout)
def verify_pages(passno):
    op=session();rated=json.loads((O/'datos-V15.json').read_text('utf8'))['ratings'];with_reviews=[p for p in rated if p['count']];empty=next(p for p in rated if not p['count'])
    for path in ['','tienda/','mi-cuenta/','my-account/','?login=1','cart/']:
        d,s,r=doc(op,path);key=path or 'home';check(f'{passno}_{key}_200',r.status==200)
        check(f'{passno}_{key}_three_forms',len(d.xpath('//dialog[@id="mk12-auth"]/form'))==3)
        check(f'{passno}_{key}_v15_css',bool(d.xpath('//link[@id="minikids-commerce-v15-css"]')))
        if path in ['mi-cuenta/','my-account/','?login=1']:
            check(f'{passno}_{key}_auto_flag',any(x in s.replace(' ','') for x in ['"auto":true','"auto":"1"']))
        if path=='tienda/':
            links=d.xpath('//a[contains(@class,"mk15-rating")]');check(f'{passno}_shop_ratings',len(links)==35)
            details['shop_rating_count']=len(links)
    for p in [with_reviews[0],min(with_reviews,key=lambda p:float(p['average'])),max(with_reviews,key=lambda p:float(p['average'])),empty]:
        d,s,_=doc(op,p['url']);pid=str(p['id'])
        check(f'{passno}_{pid}_gate','Solo los clientes que compraron este producto pueden dejar un comentario.' in s and not d.xpath('//form[@id="commentform"]'))
        check(f'{passno}_{pid}_gate_login',bool(d.xpath('//*[contains(@class,"mk15-review-gate")]//a[contains(text(),"Iniciar sesión")]')))
        meters=d.xpath('//*[contains(@class,"mk5-rating-row")]//meter')
        check(f'{passno}_{pid}_histogram',len(meters)==5 and [int(x.get('value')) for x in meters]==[int(p['histogram'].get(str(i),0)) if isinstance(p['histogram'],dict) else 0 for i in range(5,0,-1)])
        summary=d.xpath('//*[contains(@class,"summary")]//a[contains(@class,"mk15-rating")]')
        check(f'{passno}_{pid}_single_rating',bool(summary)==bool(p['count']))
        if p['count']:check(f'{passno}_{pid}_demo_label','Comentario de demostración' in s)
        else:check(f'{passno}_{pid}_empty_text','Sin comentarios todavía' in s)
    for path in ['terminos-y-condiciones/','politica-privacidad/']:
        _,r=get(op,path);check(f'{passno}_{path}_real_link',r.status==200)
    # Native isolated carts: 1 / 2 / 5 different products, long names from real catalog.
    for count in [1,2,5]:
        cart=session()
        selected=sorted(rated,key=lambda p:len(p['name']),reverse=True)[:count]
        for p in selected:
            data=json.loads(cart.open(BASE+'?wc-ajax=add_to_cart',urllib.parse.urlencode({'product_id':p['id'],'quantity':1}).encode(),timeout=40).read())
            check(f'{passno}_cart_{count}_add_{p["id"]}',not data.get('error',False))
        d,s,_=doc(cart,'tienda/');drawer=d.xpath('//*[@id="minikids-cart-drawer"]')[0]
        items=drawer.xpath('.//li[contains(@class,"woocommerce-mini-cart-item")]')
        check(f'{passno}_cart_{count}_rows',len(items)==count)
        check(f'{passno}_cart_{count}_one_remove',all(len(x.xpath('.//a[contains(concat(" ",@class," ")," remove ")]'))==1 for x in items))
        check(f'{passno}_cart_{count}_quantity',all(len(x.xpath('.//*[contains(@class,"mk5-mini-quantity")]'))==1 for x in items))
        check(f'{passno}_cart_{count}_footer_sibling',bool(drawer.xpath('.//*[contains(@class,"widget_shopping_cart_content")]/div[contains(@class,"mk9-bag-footer")]')))
        footer=drawer.xpath('.//div[contains(@class,"mk9-bag-footer")]')[0]
        check(f'{passno}_cart_{count}_footer_complete',all(footer.xpath('.//*[contains(@class,"'+c+'")]') for c in ['mk9-progress','mk9-bag-subtotal','mk9-bag-note','mk9-buy','mk9-view-cart']))
        fragment=json.loads(cart.open(BASE+'?wc-ajax=get_refreshed_fragments',b'',timeout=40).read())['fragments']['div.widget_shopping_cart_content']
        check(f'{passno}_cart_{count}_native_fragments','mk15-mini-list' in fragment and 'mk9-bag-footer' in fragment)
        if count==2:
            before=json.loads(get(cart,'wp-json/wc/store/v1/cart')[0])['totals']['total_price']
            href=items[0].xpath('.//a[contains(@class,"remove")]/@href')[0];get(cart,href)
            after=json.loads(get(cart,'wp-json/wc/store/v1/cart')[0])
            check(f'{passno}_remove_native_nonce',len(after['items'])==1 and int(after['totals']['total_price'])<int(before))
    css=(R/'wordpress/wp-content/mu-plugins/minikids-commerce-v15.css').read_text('utf8')
    check(f'{passno}_css_no_break_all','break-all' not in css)
    check(f'{passno}_css_flex_chain',all(x in css for x in ['flex:1 1 0%','min-height:0','height:100dvh','flex:0 0 auto','minmax(0,1fr)','@media(max-width:419px)']))
    details['width_matrix']=[{'width':w,'height':500,'counts':[1,2,5],'html_css':'checked, no rendering','visual':'pending Claude'} for w in [320,360,390,480,690,768,1024,1440]]
def verify_auth():
    native('reset-qa-limits')
    check('qa_absent_before',native('qa-inspect')['id'] is None)
    op=session();f=forms(op);time.sleep(2)
    check('register_fields',set(['login','register','lost'])==set(f))
    status,data=post(op,{'action':'mk12_auth','mode':'login'});check('nonce_reject',status==403)
    status,data=post(op,f['login']|{'username':'nobody@example.invalid','password':'Wrong123456','website':'spam'});check('honeypot_reject',status==400)
    status,data=post(op,f['login']|{'username':'nobody@example.invalid','password':'Wrong123456','mk15_started':'1'});check('signed_time_reject',status==400)
    status,data=post(op,f['login']|{'username':'nobody@example.invalid','password':'Wrong123456'});check('login_generic_error',status==401 and data['data']['message']=='Los datos no coinciden.')
    password='QaX9!'+secrets.token_hex(14)
    payload=f['register']|{'email':'qa-registro@example.invalid','first_name':'QA','last_name':'Temporal','password':password,'password_confirm':password,'terms':'1'}
    status,data=post(op,payload|{'password':'weak','password_confirm':'weak'});check('weak_password_reject',status==400)
    status,data=post(op,payload|{'password_confirm':'mismatch'});check('confirmation_reject',status==400)
    status,data=post(op,payload|{'terms':'0'});check('terms_reject',status==400)
    op.open(BASE+'?wc-ajax=add_to_cart',b'product_id=39&quantity=2',timeout=40).read()
    status,data=post(op,payload);details['registration_response']={'status':status,'data':data};check('registration_success',status==200 and data.get('success') and data['data'].get('reload'))
    qa=native('qa-inspect');check('customer_role_and_names',qa['roles']==['customer'] and qa['first']=='QA' and qa['last']=='Temporal')
    d,s,_=doc(op,'my-account/');check('native_session_after_registration','logged-in' in d.xpath('//body/@class')[0] and not d.xpath('//dialog[@id="mk12-auth"]'))
    cart=json.loads(get(op,'wp-json/wc/store/v1/cart')[0]);check('cart_preserved_registration',len(cart['items'])==1 and cart['items'][0]['id']==39 and cart['items'][0]['quantity']==2)
    logout=d.xpath('//a[contains(@href,"customer-logout")]/@href');check('logout_native_available',bool(logout));get(op,logout[0])
    d,s,_=doc(op,'my-account/');check('logged_out',bool(d.xpath('//dialog[@id="mk12-auth"]')))
    f=forms(op);time.sleep(2)
    status,known=post(op,f['lost']|{'user_login':'qa-registro@example.invalid'})
    status2,unknown=post(op,f['lost']|{'user_login':'qa-ausente@example.invalid'})
    check('recovery_generic_existing_missing',status==status2==200 and known==unknown and known.get('success'))
    status,data=post(op,f['login']|{'username':'qa-registro@example.invalid','password':password});check('login_success',status==200 and data.get('success') and data['data'].get('reload'))
    d,s,_=doc(op,'my-account/');check('native_session_after_login','logged-in' in d.xpath('//body/@class')[0])
    get(op,d.xpath('//a[contains(@href,"customer-logout")]/@href')[0])
    f=forms(op);time.sleep(2)
    payload=f['register']|{'email':'qa-registro@example.invalid','first_name':'QA','last_name':'Temporal','password':password,'password_confirm':password,'terms':'1'}
    replies=[post(op,payload) for _ in range(6)]
    check('duplicate_registration_generic',all(r[0]==400 and r[1]['data']['message']=='No pudimos crear la cuenta. Revisa los datos o inicia sesión.' for r in replies[:5]))
    check('registration_rate_limit',replies[-1][0]==429)
    # End-to-end HTTP submits the modal's real forms; JS interaction is left for Chrome.
if __name__=='__main__':
    mode=sys.argv[1] if len(sys.argv)>1 else 'pages1'
    try:
        if mode=='auth':verify_auth()
        else:verify_pages(mode)
    finally:
        if mode=='auth':
            cleanup=native('qa-cleanup');check('qa_cleanup_and_inventory',cleanup['qa_absent'] and cleanup['inventory_identical'])
            native('reset-qa-limits')
        (O/('verificacion-V15-'+mode+'.json')).write_text(json.dumps({'checks':checks,'details':details},indent=2,ensure_ascii=False),encoding='utf8')
    if not all(checks.values()):sys.exit(1)
