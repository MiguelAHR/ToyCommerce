(function($){
 'use strict';
 const one=(s,r=document)=>r.querySelector(s),all=(s,r=document)=>[...r.querySelectorAll(s)];
 const make=(tag,cls,text)=>{const el=document.createElement(tag);el.className=cls;if(text)el.textContent=text;return el;};
 const button=(text,fn)=>{const b=make('button','',text);b.type='button';b.addEventListener('click',fn);return b;};
 function drawer(){
  const root=one('#minikids-cart-drawer');if(!root)return;
  const h=one('.drawer-title',root);if(h)h.textContent='Mi bolsa';
  const panel=one('[role=dialog]',root);panel?.setAttribute('aria-label','Mi bolsa');
 }
 $(function(){
  drawer();$(document.body).on('wc_fragments_loaded wc_fragments_refreshed added_to_cart removed_from_cart',drawer);
  if(!mk9.checkout)return;
  // The CSS animation starts with the parsed body, before this footer module.
  function failIfUnready(){if(!document.body.classList.contains('mk-secure-ready'))document.body.classList.add('mk-secure-failed');}
  const watch=one('#mk11-failure-watch');
  watch?.addEventListener('animationend',e=>{if(e.animationName==='mk11-watch')failIfUnready();});
  setTimeout(failIfUnready,Math.max(0,4000-(performance.now()-(performance.getEntriesByType('navigation')[0]?.responseStart||0))));
  if(mk9.state?.expired){
   document.body.classList.add('mk-secure-failed');
   const fail=one('#mk9-failsafe');if(fail){fail.setAttribute('role','alert');one('p',fail).textContent='Tu sesión de compra segura venció. Tu carrito se conserva.';}
   const url=new URL(mk9.cart);url.searchParams.set('mk-session-expired','1');setTimeout(()=>location.replace(url.href),1200);return;
  }
  const form=one('form.checkout'),details=one('#customer_details'),summary=one('.mk9-summary'),review=one('#order_review');
  if(!form||!details||!summary||!review||!one('#payment')||!mk9.state?.token)return;
  let expired=false,checking=false,verified=false,current=0,emailDone=false,completed=-1;
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
  });
  let deadline=performance.now()+Math.max(0,mk9.state.remaining)*1000;
  let wallDeadline=Date.now()+Math.max(0,mk9.state.remaining)*1000;
  const cart=new URL(mk9.cart);cart.searchParams.set('mk-session-expired','1');
  function expire(){
   if(expired)return;document.body.classList.add('mk-secure-failed');expired=true;verified=false;form.inert=true;document.body.classList.remove('mk-secure-ready');
   const fail=one('#mk9-failsafe');if(fail){fail.setAttribute('role','alert');one('p',fail).textContent='Tu sesión de compra segura venció. Tu carrito se conserva.';}
   setTimeout(()=>location.replace(cart.href),1200);
  }
  function tick(){
   const seconds=Math.max(0,Math.ceil(Math.min(deadline-performance.now(),wallDeadline-Date.now())/1000));
   const timer=one('#mk6-timer');if(timer)timer.textContent=String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');
   if(seconds===0)expire();return seconds;
  }
  async function verify(){
   if(checking||expired)return;checking=true;verified=false;form.inert=true;document.body.classList.remove('mk-secure-ready');
   try{
    const sent=performance.now(),r=await fetch(mk9.status,{method:'POST',credentials:'same-origin',cache:'no-store',body:new URLSearchParams({token:mk9.state.token})});
    const result=await r.json();if(!r.ok||!result.success){expire();return;}
    const left=Math.max(0,result.data.remaining*1000-(performance.now()-sent));
    deadline=Math.min(deadline,performance.now()+left);wallDeadline=Math.min(wallDeadline,Date.now()+left);
    if(tick()&&!expired){verified=true;form.inert=false;document.body.classList.remove('mk-secure-failed');document.body.classList.add('mk-secure-ready');}
   }catch(_){const fail=one('#mk9-failsafe p');if(fail)fail.textContent='No se pudo comprobar la ventana de compra segura. Vuelve al carrito para continuar.';}
   finally{checking=false;}
  }
  form.inert=true;summary.append(review);
  const mobile=matchMedia('(max-width:768px)');summary.open=!mobile.matches;
  summary.addEventListener('toggle',()=>{if(!mobile.matches&&!summary.open)summary.open=true;});
  mobile.addEventListener('change',()=>{summary.open=!mobile.matches;});
  const cards=['Tus datos','Envío','Pago'].map((title,i)=>{
   const card=make('section','mk9-card'),h=make('h2',''),num=make('span','mk9-number',String(i+1)),body=make('div','mk9-card-body'),waiting=make('p','mk9-waiting','Esperando que se complete la información'),short=make('p','mk9-completed');
   h.id='mk9-title-'+i;card.setAttribute('aria-labelledby',h.id);h.append(num,document.createTextNode(title));
   const icon=make('span','mk9-card-icon');icon.innerHTML=mk9.icons[['user','truck','lock'][i]];h.append(icon);
   const edit=button('Editar',()=>show(i,true));edit.className='mk9-edit';edit.setAttribute('aria-label','Editar '+title);h.append(edit);
   const error=make('p','mk9-error');error.setAttribute('role','alert');error.tabIndex=-1;body.append(error);
   card.append(h,waiting,short,body);details.append(card);return {card,body,error,edit,waiting,short};
  });
  const email=one('#billing_email_field'),personal=make('div','mk9-personal'),address=make('div','woocommerce-billing-fields'),fields=make('div','woocommerce-billing-fields__field-wrapper');address.append(fields);
  cards[0].body.append(make('p','mk9-intro','Rápido, fácil, seguro'));if(!email)return;cards[0].body.append(email);
  const info=make('div','mk9-email-info');info.append(make('p','','Guardamos tu correo de manera 100 % segura para:'));
  ['Identificar tu perfil','Notificar los estados de tu compra','Guardar el historial de compras','Facilitar tus próximas compras'].forEach(text=>{const p=make('p','');p.innerHTML=mk9.icons.check;p.append(document.createTextNode(text));info.append(p);});
  cards[0].body.append(info,personal);cards[1].body.append(address);
  all('.woocommerce-billing-fields__field-wrapper>.form-row').forEach(row=>(/^billing_(first_name|last_name|company|phone|document)_field$/.test(row.id)||/document|dni|ruc/.test(row.id)?personal:fields).append(row));
  all('.woocommerce-account-fields,.woocommerce-shipping-fields,.woocommerce-additional-fields',details).forEach(el=>(el.matches('.woocommerce-account-fields')?personal:cards[1].body).append(el));
  all('#customer_details>.col-1,#customer_details>.col-2').forEach(el=>el.hidden=true);
  const shipTable=make('table','mk9-shipping-methods'),shipBody=make('tbody','');shipTable.append(shipBody);cards[1].body.append(shipTable);
  const nextData=button('Continuar',()=>{
   if(!emailDone){if(!validateNode(one('input',email),cards[0]))return;emailDone=true;revealPersonal();return;}
   if(validate(0)){completed=Math.max(completed,0);show(1,true);}
  });cards[0].body.append(nextData);
  cards[1].body.append(button('Continuar',()=>{if(validate(0)&&validate(1)){completed=1;show(2,true);}}));
  function revealPersonal(){
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
  function validateNode(i,card){
   const required=i.required||!!i.closest('.validate-required');
   const invalid=(required&&(i.type==='checkbox'?!i.checked:!i.value.trim()))||(i.value&&i.type==='email'&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(i.value))||(i.willValidate&&!i.validity.valid);
   if(invalid){card.error.textContent=!i.value.trim()?'Este campo es obligatorio.':'Revisa este campo e ingresa un valor válido.';i.setAttribute('aria-invalid','true');i.focus();return false;}
   i.removeAttribute('aria-invalid');card.error.textContent='';return true;
  }
  function validate(n){
   const c=cards[n];c.body.hidden=false;if(n===0)personal.hidden=false;
   const bad=all('input,select,textarea',c.body).find(i=>!i.disabled&&i.type!=='hidden'&&i.getClientRects().length&&!validateNode(i,c));
   if(bad){show(n);if(n===0){emailDone=true;personal.hidden=false;}bad.focus();return false;}
   if(n===1&&mk9.needsShipping){const shipping=one('input[name^="shipping_method"]:checked,input[type="hidden"][name^="shipping_method"]',c.body);if(!shipping){c.error.textContent='Selecciona un método de envío disponible para tu dirección.';show(1);return false;}}
   if(n!==current)c.body.hidden=true;return true;
  }
  function show(n,focus=false){
   if(n>completed+1)return;
   current=n;cards.forEach((c,i)=>{c.body.hidden=i!==n;c.edit.hidden=i>completed||i===n;c.waiting.hidden=i<=completed||i===n;c.short.hidden=i>completed||i===n;c.short.textContent=i===0?((one('#billing_email')?.value||'')+' · '+(one('#billing_first_name')?.value||'')):(one('#billing_address_1')?.value||'');c.card.classList.toggle('mk9-open',i===n);});
   personal.hidden=!emailDone;nextData.textContent=emailDone?'Continuar a envío':'Continuar';
   cards.forEach((c,i)=>{
    const locked=i>completed+1,done=i<=completed;
    c.card.classList.toggle('mk17-locked',locked);c.card.classList.toggle('mk17-done',done);
    c.card.setAttribute('aria-disabled',String(locked));c.edit.disabled=locked;
    c.edit.setAttribute('aria-disabled',String(locked));c.edit.setAttribute('aria-expanded',String(i===n));
    const num=one('.mk9-number',c.card);num.innerHTML=done?mk9.icons.check:String(i+1);num.setAttribute('aria-label',done?'Paso '+(i+1)+' completado':'Paso '+(i+1));
    one('.mk9-card-icon',c.card).innerHTML=mk9.icons[locked?'lock':['user','truck','lock'][i]];
   });
   if(focus){cards[n].error.tabIndex=-1;cards[n].card.scrollIntoView({block:'nearest'});const input=one('input:not([type=hidden]),select,button',cards[n].body);input?.focus();}
  }
  function refresh(){
   const payment=one('#payment');if(payment)cards[2].body.append(payment);
   const total=one('#order_review .order-total .woocommerce-Price-amount'),caption=one('.mk17-summary-total');
   if(total&&caption)caption.replaceChildren(total.cloneNode(true));
   const shipping=all('#order_review tr.woocommerce-shipping-totals');if(shipping.length){shipBody.replaceChildren();shipping.forEach(row=>{
    const selected=one('input:checked,input[type="hidden"]',row),label=selected?all('label',row).find(el=>el.htmlFor===selected.id):null;
    const copy=make('tr','mk9-shipping-total'),heading=make('th','','Envío'),value=make('td','',label?.textContent||one('td',row)?.textContent.trim()||'Se calcula con tu dirección');
    copy.append(heading,value);row.parentNode.replaceChild(copy,row);shipBody.append(row);
   });}
  }
  const coupon=one('form.checkout_coupon');if(coupon){
   coupon.hidden=true;const slot=one('.mk9-coupon-slot'),label=make('label','','Cupón de descuento'),input=make('input',''),status=make('p','mk9-coupon-status');input.id='mk9-coupon-code';label.htmlFor=input.id;status.setAttribute('role','status');
   const apply=button('Aplicar',async()=>{if(!input.value.trim()||apply.disabled)return;apply.disabled=true;try{const p=window.wc_checkout_params,r=await fetch(p.wc_ajax_url.replace('%%endpoint%%','apply_coupon'),{method:'POST',credentials:'same-origin',body:new URLSearchParams({security:p.apply_coupon_nonce,coupon_code:input.value.trim()})});if(!r.ok)throw new Error();$(status).html(await r.text());$(document.body).trigger('update_checkout');}catch(_){status.textContent='No pudimos aplicar el cupón. Vuelve a intentarlo.';}finally{apply.disabled=false;}});
   input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();apply.click();}});slot.append(label,input,apply,status);
  }
  summary.append(review);one('#order_review_heading')?.remove();all('.mk6-steps,.mk6-checkout-navigation,.woocommerce-form-coupon-toggle').forEach(e=>e.hidden=true);
  if(matchMedia('(max-width:768px)').matches)summary.open=false;
  let cartBusy=false;
  async function changeItem(wrap,remove=false){
   if(cartBusy||expired)return;cartBusy=true;const status=one('.mk9-summary-status');status.textContent='Actualizando bolsa…';
   const input=one('input',wrap),qty=Number(input.value);all('.mk9-product-actions button,.mk9-product-actions input').forEach(e=>e.disabled=true);
   try{
    if(remove){const get=await fetch(mk5.store,{credentials:'same-origin',cache:'no-store'});const nonce=get.headers.get('Nonce');if(!get.ok||!nonce)throw new Error();const r=await fetch(mk5.store+'/remove-item',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Nonce':nonce},body:JSON.stringify({key:wrap.dataset.key})});if(!r.ok)throw new Error();const data=await r.json();if(!data.items.length){location.assign(mk9.cart);return;}}
    else{const r=await fetch(mk5.ajax,{method:'POST',credentials:'same-origin',body:new URLSearchParams({nonce:mk5.nonce,key:wrap.dataset.key,quantity:String(qty)})});const data=await r.json();if(!r.ok||!data.fragments)throw new Error(data.data?.message);}
    status.textContent='Bolsa actualizada.';$(document.body).trigger('wc_fragment_refresh');$(document.body).trigger('update_checkout');
   }catch(e){status.textContent=e.message||'No pudimos actualizar la bolsa. Revisa la cantidad y el stock.';}finally{cartBusy=false;all('.mk9-product-actions button,.mk9-product-actions input').forEach(e=>e.disabled=false);}
  }
  $(document).on('click','.mk9-product-actions button',function(){const w=this.closest('.mk9-product-actions'),i=one('input',w);if(this.hasAttribute('data-remove')){changeItem(w,true);return;}i.value=String(Math.max(1,Math.min(i.max?Number(i.max):Infinity,Number(i.value)+Number(this.dataset.delta))));changeItem(w);});
  $(document).on('change','.mk9-product-actions input',function(){changeItem(this.closest('.mk9-product-actions'));});
  $(form).on('checkout_place_order',()=>{if(!verified||expired||!tick()||cartBusy||current!==2)return false;for(let n=0;n<2;n++)if(!validate(n))return false;return true;});
  form.addEventListener('submit',e=>{if(!verified||expired||!tick()||current!==2){e.preventDefault();e.stopImmediatePropagation();}},true);
  form.addEventListener('keydown',e=>{if(e.key==='Enter'&&e.target.matches('input:not([type=checkbox]):not([type=radio])')&&current<2){e.preventDefault();one('button:last-child',cards[current].body)?.click();}});
  $(document.body).on('updated_checkout',()=>{refresh();show(current);});
  $(document.body).on('checkout_error',()=>{for(let i=0;i<3;i++)if(one('.woocommerce-invalid,[aria-invalid="true"]',cards[i].body)){show(i,true);break;}});
  window.addEventListener('pagehide',()=>{verified=false;form.inert=true;document.body.classList.remove('mk-secure-ready');});
  window.addEventListener('pageshow',()=>{if(tick())verify();});
  document.addEventListener('visibilitychange',()=>{if(!document.hidden&&tick())verify();});
  refresh();show(0);tick();setInterval(tick,1000);verify();if(restored)$(document.body).trigger('update_checkout');
 });
})(window.jQuery);
