"""Checkout presentation only. MCP read, private backup, lint, write, exact readback."""
from pathlib import Path
import runpy, json, hashlib, subprocess, urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1]
O=R.parent/'diseno_minikids/v5'
B=R.parent/'local-private/v17-backups'; B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply']
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']
before=protected()
(B/'protected-before.json').write_text(json.dumps(before))
v16={p.name:hashlib.sha256(p.read_bytes()).hexdigest() for p in (R/'wordpress/wp-content/mu-plugins').glob('minikids-commerce-v16.*')}
receipts=[]
def replace(s,a,b):
    assert s.count(a)==1, a[:100]
    return s.replace(a,b,1)

s=read('minikids-commerce-v9.php')
s=replace(s,'<details class="mk9-summary" open><summary>Resumen de tu bolsa</summary><div class="mk9-products-slot">', '<details class="mk9-summary" open><summary><span>Resumen de tu bolsa</span><span class="mk17-summary-total">\'.wp_kses_post(WC()->cart->get_total()).\'</span></summary><section class="mk17-products-card" aria-labelledby="mk17-summary-title"><h2 id="mk17-summary-title">Resumen de tu bolsa</h2><div class="mk9-products-slot">')
s=replace(s,'<div class="mk9-summary-status" role="status"></div></details>', '<div class="mk9-summary-status" role="status"></div></section></details>')
s=replace(s,"$fragments['.mk9-summary > .mk9-progress']", "$fragments['.mk9-summary .mk9-progress']")
receipts.append(apply('minikids-commerce-v9.php',s))

s=read('minikids-commerce-v6.php')
s=replace(s,'<p class="mk11-secure-label">\'.mk9_icon(\'lock\').\' Compra segura</p><p>Ventana segura ', '<p class="mk17-secure-clock">\'.mk9_icon(\'lock\').\'<span>Ventana segura</span><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> ')
receipts.append(apply('minikids-commerce-v6.php',s))

s=read('minikids-commerce-v12.js')
s=replace(s,'if(result.success&&result.data.reload){close(false);location.reload();return;}', "if(result.success&&result.data.reload){document.dispatchEvent(new CustomEvent('mk17:auth-success'));close(false);location.reload();return;}")
receipts.append(apply('minikids-commerce-v12.js',s))

s=read('minikids-commerce-v9.js')
s=replace(s,"  let expired=false,checking=false,verified=false,current=0,emailDone=false,completed=-1;", """  let expired=false,checking=false,verified=false,current=0,emailDone=false,completed=-1;
  // One-use draft saved only after successful modal authentication; no payment/nonces/passwords.
  const draftKey='mk17-checkout-auth-draft';
  let restored=false;
  try{
   const raw=sessionStorage.getItem(draftKey);sessionStorage.removeItem(draftKey);
   const draft=raw?JSON.parse(raw):null;
   if(draft&&draft.token===mk9.state.token&&Date.now()-draft.at<120000){
    all('input,select,textarea',form).filter(i=>/^(billing_|shipping_|order_comments$)/.test(i.name)&&!['password','hidden','file'].includes(i.type)).forEach(i=>{
     const value=draft.fields[i.name];if(value===undefined)return;
     if(['radio','checkbox'].includes(i.type))i.checked=value.includes(i.value);else i.value=value;
    });emailDone=!!draft.emailDone;restored=true;
   }
  }catch(_){}
  document.addEventListener('mk17:auth-success',()=>{
   const fields={};all('input,select,textarea',form).filter(i=>/^(billing_|shipping_|order_comments$)/.test(i.name)&&!['password','hidden','file'].includes(i.type)).forEach(i=>{
    if(['radio','checkbox'].includes(i.type)){if(!fields[i.name])fields[i.name]=[];if(i.checked)fields[i.name].push(i.value);}else fields[i.name]=i.value;
   });
   try{sessionStorage.setItem(draftKey,JSON.stringify({token:mk9.state.token,at:Date.now(),emailDone,fields}));}catch(_){}
  });""")
s=replace(s,"const mobile=matchMedia('(max-width:899px)')", "const mobile=matchMedia('(max-width:768px)')")
s=replace(s,"if(matchMedia('(max-width:899px)').matches)", "if(matchMedia('(max-width:768px)').matches)")
s=replace(s,"personal.hidden=false;return;}", "revealPersonal();return;}")
s=replace(s,"  function validateNode(i,card){", """  function revealPersonal(){
   personal.hidden=false;nextData.textContent='Continuar a envío';
   if(!matchMedia('(prefers-reduced-motion: reduce)').matches&&personal.animate){
    personal.animate([{height:'0px',opacity:0,overflow:'hidden'},{height:personal.scrollHeight+'px',opacity:1,overflow:'hidden'}],{duration:240,easing:'ease-out'});
   }
   one('input:not([type=hidden]),select',personal)?.focus();
  }
  // Invalidate dependent completions when a previously completed panel is changed.
  details.addEventListener('input',invalidate);details.addEventListener('change',invalidate);
  function invalidate(e){
   const n=cards.findIndex(c=>c.body.contains(e.target));
   if(n>=0&&n<2&&completed>=n){completed=n-1;show(Math.min(current,n));}
  }
  function validateNode(i,card){""")
s=replace(s,"   current=n;cards.forEach", "   if(n>completed+1)return;\n   current=n;cards.forEach")
s=replace(s,"   personal.hidden=!emailDone;cards.forEach((c,i)=>c.edit.setAttribute('aria-expanded',String(i===n)));", """   personal.hidden=!emailDone;nextData.textContent=emailDone?'Continuar a envío':'Continuar';
   cards.forEach((c,i)=>{
    const locked=i>completed+1,done=i<=completed;
    c.card.classList.toggle('mk17-locked',locked);c.card.classList.toggle('mk17-done',done);
    c.card.setAttribute('aria-disabled',String(locked));c.edit.disabled=locked;
    c.edit.setAttribute('aria-disabled',String(locked));c.edit.setAttribute('aria-expanded',String(i===n));
    const num=one('.mk9-number',c.card);num.innerHTML=done?mk9.icons.check:String(i+1);num.setAttribute('aria-label',done?'Paso '+(i+1)+' completado':'Paso '+(i+1));
    one('.mk9-card-icon',c.card).innerHTML=mk9.icons[locked?'lock':['user','truck','lock'][i]];
   });""")
s=replace(s,"   const payment=one('#payment');if(payment)cards[2].body.append(payment);", """   const payment=one('#payment');if(payment)cards[2].body.append(payment);
   const total=one('#order_review .order-total .woocommerce-Price-amount'),caption=one('.mk17-summary-total');
   if(total&&caption)caption.replaceChildren(total.cloneNode(true));""")
s=replace(s,"  refresh();show(0);tick();setInterval(tick,1000);verify();", "  refresh();show(0);tick();setInterval(tick,1000);verify();if(restored)$(document.body).trigger('update_checkout');")
receipts.append(apply('minikids-commerce-v9.js',s))

receipts.append(apply('minikids-commerce-v17.php', '''<?php
/** Plugin Name: MiniKids · Diseño del checkout V17 */
defined('ABSPATH') || exit;
add_action('wp',function(){
 if(!mk6_checkout())return;
 remove_action('woocommerce_before_checkout_form','woocommerce_checkout_login_form',10);
 add_action('woocommerce_before_checkout_form',function(){
  if(is_user_logged_in())return;
  echo '<aside class="mk17-account-notice" aria-label="Acceso a tu cuenta">'.mk9_icon('user').'<span>¿Ya tienes cuenta?</span><a class="showlogin" href="#mk12-auth" aria-haspopup="dialog" aria-controls="mk12-auth">Iniciar sesión</a></aside>';
 },10);
},210);
add_action('wp_enqueue_scripts',function(){
 if(!mk6_checkout())return;
 wp_enqueue_style('minikids-commerce-v17',plugins_url('minikids-commerce-v17.css',__FILE__),['minikids-commerce-v9','minikids-commerce-v12','minikids-commerce-v15'],filemtime(__DIR__.'/minikids-commerce-v17.css'));
},200);
'''))
receipts.append(apply('minikids-commerce-v17.css',(R/'tools/v17-checkout.css').read_text('utf8')))

after=protected()
assert before==after,'Protected file changed'
assert v16=={p.name:hashlib.sha256(p.read_bytes()).hexdigest() for p in (R/'wordpress/wp-content/mu-plugins').glob('minikids-commerce-v16.*')},'V16 changed'
for name in ['minikids-commerce-v9.js','minikids-commerce-v12.js']:
 subprocess.run(['node','--check',str(R/'wordpress/wp-content/mu-plugins'/name)],check=True)
(O/'aplicacion-V17.json').write_text(json.dumps({'writes':receipts,'protected_files_unchanged':len(before),'v16_sha256_unchanged':v16},ensure_ascii=False,indent=2),encoding='utf8')
