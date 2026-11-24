"""Apply owned V7 files through local MCP, with backups, lint and readback."""
from pathlib import Path
import sys,socket,json,subprocess,hashlib
ROOT=Path(__file__).resolve().parents[1];sys.path.insert(0,str(ROOT.parent))
resolve=socket.getaddrinfo;socket.getaddrinfo=lambda host,*a,**kw:resolve('127.0.0.1' if host=='localhost' else host,*a,**kw)
from diseno_mcp import client
OUT=ROOT.parent/'diseno_minikids/v5';BACK=ROOT.parent/'local-private/v7-backups';BACK.mkdir(exist_ok=True)
c=client();assert c.url.startswith('http://localhost:8082/wordpress/')
def read(name):
    data=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
    if not (BACK/name).exists():(BACK/name).write_text(data,encoding='utf8')
    return data
def write(name,data):
    stage=OUT/name;stage.write_text(data,encoding='utf8')
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(stage)],check=True)
    if name.endswith('.js'):subprocess.run(['node','--check',str(stage)],check=True)
    result=c.ability('minikids/write-file',{'path':'mu-plugins/'+name,'content':data,'backup':True})
    if name.endswith('.php'):subprocess.run(['C:/xampp/php/php.exe','-l',str(ROOT/'wordpress/wp-content/mu-plugins'/name)],check=True)
    assert c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']==data
    print('Applied/read back '+name,flush=True);return result
php=read('minikids-commerce-v6.php')
php=php.replace("'checkout'=>mk6_checkout()", "'checkout'=>mk6_checkout(),'ttl'=>(mk7_local_debug() && isset($_GET['mk6_ttl']) && (string)$_GET['mk6_ttl']==='15')?15:600")
start=php.index('// 14: critical CSS');end=php.index('// 4: native es_ES',start)
php=php[:start]+'''// V7: debug hooks are limited to a loopback site and local/debug environment.
function mk7_local_debug(){
    $host=wp_parse_url(home_url('/'),PHP_URL_HOST);
    return in_array($host,['localhost','127.0.0.1','::1'],true) && (wp_get_environment_type()==='local' || (defined('WP_DEBUG') && WP_DEBUG));
}
function mk7_loader_enabled(){return !is_admin()&&!is_customize_preview()&&!isset($_GET['elementor-preview']);}
add_action('wp_head',function(){
    if(!mk7_loader_enabled())return;
    $debug=mk7_local_debug()&&isset($_GET['mk6_loader_debug'])&&(string)$_GET['mk6_loader_debug']==='1';
    echo '<style id="mk6-loader-critical">'.file_get_contents(__DIR__.'/minikids-loader-v7.css').'</style>';
    echo '<script>window.mk7LoaderDebug='.($debug?'true':'false').';'.file_get_contents(__DIR__.'/minikids-loader-v7.js').'</script>';
},1);
add_action('wp_body_open',function(){
    if(!mk7_loader_enabled())return;
    echo '<div id="mk6-loader" aria-hidden="true"><img src="'.esc_url(mk6_logo()).'" alt="" width="200" height="90" fetchpriority="high"><div class="mk6-dots"><i></i><i></i><i></i></div></div>';
},0);

''' +php[end:]
loadercss='''/* Independent veil failsafe and dot animations; never disable dot opacity motion. */
#mk6-loader{position:fixed;inset:0;z-index:2147483000;background:#ffd60a;display:grid;place-content:center;justify-items:center;gap:28px;pointer-events:none;opacity:1;visibility:visible;transition:opacity .25s,visibility 0s .25s;animation-name:mk7-loader-out;animation-duration:.25s;animation-delay:2.25s;animation-fill-mode:forwards}
#mk6-loader img{width:200px;height:auto}#mk6-loader .mk6-dots{display:flex;gap:12px}
#mk6-loader i{width:12px;height:12px;border-radius:50%;background:#0756ad;animation-name:mk7-dot;animation-duration:.8s;animation-iteration-count:infinite;animation-direction:alternate}
#mk6-loader i:nth-child(2){background:#fff;animation-delay:.2s}#mk6-loader i:nth-child(3){animation-delay:.4s}
#mk6-loader.mk6-ready{opacity:0;visibility:hidden;animation-name:none}
.mk7-fast-nav #mk6-loader:not(.mk7-show){opacity:0;visibility:hidden;transition:none}
.mk7-loader-debug #mk6-loader{animation-name:none;opacity:1;visibility:visible}
@keyframes mk7-dot{to{transform:translateY(-8px);opacity:.45}}
@keyframes mk7-pulse{from{opacity:1}to{opacity:.4}}
@keyframes mk7-loader-out{to{opacity:0;visibility:hidden}}
@media(prefers-reduced-motion:reduce){#mk6-loader i{animation-name:mk7-pulse;transform:none}}
'''
loaderjs='''(function(){
 'use strict';
 const root=document.documentElement,started=performance.now();
 if(window.mk7LoaderDebug){root.classList.add('mk7-loader-debug');return;}
 const nav=performance.getEntriesByType('navigation')[0];
 let internal=false;try{internal=nav?.type==='navigate'&&new URL(document.referrer).origin===location.origin;}catch(_){}
 if(internal)root.classList.add('mk7-fast-nav');
 let shown=internal?null:started,done=false,hideTimer;
 const el=()=>document.getElementById('mk6-loader');
 function finish(immediate=false){
  if(done)return;
  if(!immediate&&shown!==null&&performance.now()-shown<400){clearTimeout(hideTimer);hideTimer=setTimeout(()=>finish(),400-(performance.now()-shown));return;}
  done=true;clearTimeout(hideTimer);el()?.classList.add('mk6-ready');
 }
 if(internal)setTimeout(()=>{if(!done&&document.readyState!=='complete'){shown=performance.now();el()?.classList.add('mk7-show');}else finish(true);},400);
 window.addEventListener('load',()=>finish(),{once:true});
 window.addEventListener('pageshow',e=>{if(e.persisted)finish(true);else if(document.readyState==='complete')finish();});
 document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')finish();});
 document.addEventListener('DOMContentLoaded',()=>{if(done)el()?.classList.add('mk6-ready');if(document.readyState==='complete')finish();},{once:true});
 // Start fading at 2250ms; entirely hidden by 2500ms, even when load never fires.
 setTimeout(()=>finish(true),Math.max(0,2250-(performance.now()-started)));
})();
'''
js=read('minikids-commerce-v6.js');start=js.index('  // Reload extends');end=js.index('\n });',start)
js=js[:start]+'''  // V7 fixed absolute deadline. Trusted activity never extends the total window.
  const key='mk6-secure-window';let state;
  const read=()=>{try{return JSON.parse(sessionStorage.getItem(key));}catch(_){return null;}};
  const save=()=>{try{sessionStorage.setItem(key,JSON.stringify(state));}catch(_){/* In-memory deadline remains effective. */}};
  const now=Date.now(),ttl=Number(mk6.ttl)===15?15000:600000;
  state=read();
  if(!state||!Number.isFinite(state.end))state={version:7,start:now,end:now+ttl,activity:false};
  else{
   // Legacy V6 stores end in epoch milliseconds; derive its original 10min start.
   const start=Number.isFinite(state.start)?state.start:state.end-600000;
   state={version:7,start,end:Math.min(state.end,start+ttl,now+ttl),activity:false};
  }
  save();
  form.addEventListener('input',e=>{if(e.isTrusted&&Date.now()<state.end){state.activity=true;save();}},true);
  form.addEventListener('change',e=>{if(e.isTrusted&&Date.now()<state.end){state.activity=true;save();}},true);
  let expired=false;
  function tick(){
   const seconds=Math.max(0,Math.ceil((state.end-Date.now())/1000)),timer=one('#mk6-timer');
   if(timer)timer.textContent=String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');
   if(!seconds&&!expired){
    expired=true;form.inert=true;save();
    const msg=make('p','mk6-expired','Tu sesión de compra segura venció');msg.setAttribute('role','alert');form.before(msg);
    // Keep the expired record until reaching the cart, including reload/BFCache.
    const url=new URL(mk6.cart);url.searchParams.set('mk-session-expired','1');
    setTimeout(()=>location.replace(url.href),1200);
   }
  }
  $(form).on('checkout_place_order',()=>{tick();if(expired)return false;});
  form.addEventListener('submit',e=>{tick();if(expired){e.preventDefault();e.stopImmediatePropagation();}},true);
  tick();setInterval(tick,1000);document.addEventListener('visibilitychange',tick);window.addEventListener('pageshow',tick);
''' +js[end:]
js=js.replace("  if(!mk6.checkout)return;",'''  if(!mk6.checkout){
   // A new window may start after the expired checkout has actually returned to cart.
   if(one('.woocommerce-cart-form')||one('.cart-empty'))try{const s=JSON.parse(sessionStorage.getItem('mk6-secure-window'));if(s&&s.end<=Date.now())sessionStorage.removeItem('mk6-secure-window');}catch(_){}
   return;
  }''')
css=read('minikids-commerce-v6.css')+'''
/* V7: gallery follows the purchase column; keep only the image viewport clipped. */
@media(min-width:1024px){
 .minikids-retail.single-product :is(#main,.site-main,div.product,.product-entry-wrapper){overflow:visible!important}
 .minikids-retail.single-product .product-entry-wrapper>.woocommerce-product-gallery{position:sticky!important;top:24px;align-self:start}
}
@media(max-width:1023px){
 .minikids-retail.single-product .product-entry-wrapper{grid-template-columns:minmax(0,1fr)!important}
 .minikids-retail.single-product .product-entry-wrapper>.woocommerce-product-gallery{position:relative!important;top:auto}
}
/* Native actions and nonce stay in the form, without a rendered empty row. */
body.minikids-retail .mk5-native-cart .woocommerce-cart-form tr:has(>td.actions){display:none!important}
body.minikids-retail .mk5-native-cart .woocommerce-cart-form table.shop_table{margin-bottom:0!important}
/* Already inside the page container: no second viewport-based inset. */
body.minikids-retail .mk6-recommendations.mk5-cart-related{width:100%!important;max-width:1216px!important;margin-inline:auto!important}
.mk6-expired{padding:24px;border:2px solid var(--brand-blue);background:var(--brand-yellow);color:var(--brand-ink);font-weight:700}
'''
results=[]
for name,data in [('minikids-loader-v7.css',loadercss),('minikids-loader-v7.js',loaderjs)]:
    try:read(name)
    except RuntimeError as e:
        if 'mkp_no_file' not in str(e) and 'No existe el archivo indicado.' not in str(e):raise
    results.append(write(name,data))
for name,data in [('minikids-commerce-v6.php',php),('minikids-commerce-v6.js',js),('minikids-commerce-v6.css',css)]:results.append(write(name,data))
(OUT/'aplicacion-V7.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf8')
