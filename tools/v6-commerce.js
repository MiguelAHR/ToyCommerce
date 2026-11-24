(function($){
 'use strict';
 const one=(s,r=document)=>r.querySelector(s),all=(s,r=document)=>[...r.querySelectorAll(s)];
 const make=(tag,cls,text)=>{const el=document.createElement(tag);el.className=cls;if(text)el.textContent=text;return el;};
 const btn=(text,label,fn)=>{const el=make('button','',text);el.type='button';el.setAttribute('aria-label',label);el.addEventListener('click',fn);return el;};
 let lastCouponError='';
 function quantities(){
  all('.quantity').forEach(w=>{
   const i=one('input.qty',w);if(!i||w.dataset.mk6||i.type==='hidden')return;
   w.dataset.mk6='1';all('.ct-increase,.ct-decrease,button',w).forEach(b=>b.remove());
   const change=d=>{const min=i.min===''?1:Number(i.min),max=i.max===''?Infinity:Number(i.max);i.value=String(Math.max(min,Math.min(max,(Number(i.value)||min)+d*(Number(i.step)||1))));$(i).trigger('input').trigger('change');};
   w.prepend(btn('−','Reducir cantidad',()=>change(-1)));w.append(btn('+','Aumentar cantidad',()=>change(1)));
  });
 }
 function cart(){
  const form=one('.woocommerce-cart-form');if(!form)return;
  form.id='mk6-cart-form';
  // The summary's coupon input is externally associated with the native form.
  all('.actions .coupon',form).forEach(e=>e.remove());
  all('[name="update_cart"]',form).forEach(e=>{e.hidden=true;e.setAttribute('aria-hidden','true');e.tabIndex=-1;});
  if(!one('input[type=hidden][name=update_cart]',form)){const marker=make('input','');marker.type='hidden';marker.name='update_cart';marker.value='1';form.append(marker);}
  const coupon=one('.mk6-coupon-row #coupon_code');if(coupon)coupon.setAttribute('form',form.id);
  const apply=one('.mk6-coupon-controls button');
  if(apply&&!apply.dataset.mk6){apply.dataset.mk6='1';apply.addEventListener('click',e=>{
   e.preventDefault();if(!coupon.value.trim())return;lastCouponError='';
   // Let wc-cart handle its own apply_coupon request, notices and recalculation.
   const submit=make('button','');submit.type='submit';submit.name='apply_coupon';submit.value='Aplicar cupón';submit.hidden=true;form.append(submit);submit.click();submit.remove();
  });}
  coupon?.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();apply?.click();}});
  const wrapper=one('.mk6-coupon-row td');if(wrapper&&lastCouponError){const message=make('p','mk6-coupon-message',lastCouponError);message.setAttribute('role','alert');wrapper.append(message);}
 }
 function recommendations(){all('.mk6-recommendations').forEach(section=>{
  if(section.dataset.mk6)return;const track=one('ul.products',section);if(!track)return;section.dataset.mk6='1';
  const nav=make('div','mk6-carousel-nav'),dots=make('div','mk6-carousel-dots');
  const move=d=>track.scrollBy({left:d*track.clientWidth,behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});
  nav.append(btn('','Artículos anteriores',()=>move(-1)),btn('','Más artículos',()=>move(1)));section.append(nav,dots);
  let pages=0;function sync(){const n=Math.max(1,Math.ceil((track.scrollWidth-2)/track.clientWidth));if(n!==pages){pages=n;dots.replaceChildren();for(let p=0;p<n;p++){const b=btn('','Ver grupo '+(p+1),()=>track.scrollTo({left:p*track.clientWidth,behavior:'smooth'}));dots.append(b);}}const active=Math.round(track.scrollLeft/track.clientWidth);all('button',dots).forEach((b,i)=>b.setAttribute('aria-current',String(i===active)));nav.hidden=n<=1;}
  track.addEventListener('scroll',sync,{passive:true});if(window.ResizeObserver)new ResizeObserver(sync).observe(track);else window.addEventListener('resize',sync);sync();
 });}
 $(function(){
  quantities();cart();recommendations();
  $(document.body).on('errored_coupon',function(e,code,html){lastCouponError=$('<div>').html(html).text().trim();});
  $(document.body).on('updated_wc_div updated_cart_totals wc_fragments_loaded wc_fragments_refreshed added_to_cart removed_from_cart',()=>{quantities();cart();});
  let updateTimer;$(document).on('input change','.woocommerce-cart-form input.qty',()=>{clearTimeout(updateTimer);updateTimer=setTimeout(()=>{$('.woocommerce-cart-form input[type=hidden][name=update_cart]').prop('disabled',false);$(document.body).trigger('wc_update_cart');},600);});
  $(document).on('click','.mk5-native-cart .checkout-button',function(e){const consent=one('#mk6-cart-consent');if(consent&&!consent.checked){e.preventDefault();one('.mk6-consent-status').textContent='Acepta los términos y la política de privacidad para continuar.';consent.focus();}});
  const sidebar=one('.mkr-catalog-sidebar');if(sidebar){const b=btn('Filtrar productos','Mostrar u ocultar filtros',()=>{const open=sidebar.classList.toggle('mk6-filters-open');b.setAttribute('aria-expanded',String(open));});b.className='mk6-filter-toggle';b.setAttribute('aria-expanded','false');sidebar.prepend(b);}
  if(!mk6.checkout)return;
  const form=one('form.checkout'),details=one('#customer_details'),payment=one('#payment'),review=one('#order_review'),nav=one('.mk6-steps'),controls=one('.mk6-checkout-navigation');
  if(!form||!details||!payment||!review||!nav||!controls)return;
  const panels=['Datos','Entrega','Pago'].map((name,i)=>{const p=make('section','mk6-step-panel');p.id='mk6-step-'+i;p.dataset.step=i;p.setAttribute('aria-label',name);const h=make('h2','',name);h.tabIndex=-1;p.append(h);details.append(p);return p;});
  const address=make('div','woocommerce-billing-fields'),addressFields=make('div','woocommerce-billing-fields__field-wrapper');address.append(addressFields);panels[1].append(address);
  all('.woocommerce-billing-fields__field-wrapper>.form-row').forEach(row=>(/^billing_(first_name|last_name|company|email|phone)_field$/.test(row.id)?panels[0]:addressFields).append(row));
  all('.woocommerce-account-fields,.woocommerce-shipping-fields,.woocommerce-additional-fields',details).forEach(el=>panels[el.matches('.woocommerce-account-fields')?0:1].append(el));
  panels[2].append(payment);all('#customer_details>.col-1,#customer_details>.col-2').forEach(el=>el.hidden=true);
  // Native coupon endpoint + nonce, with a summary control (no nested forms).
  const coupon=one('form.checkout_coupon');if(coupon){
   coupon.hidden=true;const box=make('div','mk6-checkout-coupon'),label=make('label','','Código de cupón'),input=make('input',''),status=make('div','mk6-coupon-status');input.id='mk6-checkout-coupon';label.htmlFor=input.id;status.setAttribute('role','status');
   const apply=btn('Aplicar cupón','Aplicar cupón',async()=>{if(!input.value.trim()||apply.disabled)return;apply.disabled=true;try{
    const params=window.wc_checkout_params,res=await fetch(params.wc_ajax_url.replace('%%endpoint%%','apply_coupon'),{method:'POST',credentials:'same-origin',body:new URLSearchParams({security:params.apply_coupon_nonce,coupon_code:input.value.trim()})});if(!res.ok)throw new Error();
    const html=await res.text();$(status).html(html);$(document.body).trigger('update_checkout',{update_shipping_method:false});
   }catch(_){status.textContent='No pudimos aplicar el cupón. Vuelve a intentarlo.';}finally{apply.disabled=false;}});
   box.append(label,input,apply,status);review.append(box);input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();apply.click();}});
  }
  const couponToggle=one('.woocommerce-form-coupon-toggle');if(couponToggle)couponToggle.hidden=true;
  let current=0;nav.hidden=false;controls.hidden=false;form.classList.add('mk6-steps-ready');
  const error=one('.mk6-step-error'),back=one('.mk6-back'),next=one('.mk6-next');
  function show(n,focus=false){current=n;panels.forEach((p,i)=>p.hidden=i!==n);all('button[data-step]',nav).forEach((b,i)=>{b.disabled=i>n;b.setAttribute('aria-current',i===n?'step':'false');b.setAttribute('aria-controls','mk6-step-'+i);});back.hidden=n===0;next.hidden=n===2;error.textContent='';if(focus)one('h2',panels[n]).focus();}
  function invalid(panel){
   return all('input,select,textarea',panel).find(i=>{
    if(i.disabled||i.type==='hidden'||!i.getClientRects().length)return false;
    const required=i.required||!!i.closest('.validate-required');
    return (required&&(i.type==='checkbox'?!i.checked:!i.value.trim()))||(i.value&&i.type==='email'&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(i.value))||(i.willValidate&&!i.validity.valid);
   });
  }
  function validate(n){const i=invalid(panels[n]);if(!i)return true;error.textContent=i.type==='email'?'Escribe un correo electrónico válido.':'Completa correctamente los campos obligatorios para continuar.';$(i).trigger('validate');i.setAttribute('aria-invalid','true');i.focus();return false;}
  next.addEventListener('click',()=>{if(validate(current))show(current+1,true);});back.addEventListener('click',()=>show(current-1,true));
  all('button[data-step]',nav).forEach(b=>b.addEventListener('click',()=>{const n=Number(b.dataset.step);if(n<current)show(n,true);}));
  form.addEventListener('keydown',e=>{if(e.key==='Enter'&&e.target.matches('input:not([type=checkbox]):not([type=radio])')&&current<2){e.preventDefault();next.click();}});
  $(form).on('checkout_place_order',()=>{for(let n=0;n<2;n++)if(invalid(panels[n])){show(n);validate(n);return false;}return current===2;});
  $(document.body).on('updated_checkout',()=>{const p=one('#payment');if(p&&p.parentElement!==panels[2])panels[2].append(p);show(current);});
  $(document.body).on('checkout_error',()=>{for(let n=0;n<3;n++)if(one('.woocommerce-invalid,[aria-invalid="true"]',panels[n])){show(n,true);break;}});
  show(0);
  // Reload extends the session only after a trusted field edit; never revive expired time.
  const key='mk6-secure-window';let state;
  const read=()=>{try{return JSON.parse(sessionStorage.getItem(key));}catch(_){return null;}};
  const save=()=>{try{sessionStorage.setItem(key,JSON.stringify(state));}catch(_){/* Timer still works in memory. */}};
  state=read();const now=Date.now();const reload=performance.getEntriesByType('navigation')[0]?.type==='reload';
  if(!state||!Number.isFinite(state.end))state={end:now+600000,activity:false};
  else if(state.end>now&&reload&&state.activity)state={end:now+600000,activity:false};
  state.activity=false;save();
  form.addEventListener('input',e=>{if(e.isTrusted&&Date.now()<state.end){state.activity=true;save();}},true);
  form.addEventListener('change',e=>{if(e.isTrusted&&Date.now()<state.end){state.activity=true;save();}},true);
  let expired=false;function tick(){const seconds=Math.max(0,Math.ceil((state.end-Date.now())/1000)),timer=one('#mk6-timer');if(timer)timer.textContent=String(Math.floor(seconds/60)).padStart(2,'0')+':'+String(seconds%60).padStart(2,'0');if(!seconds&&!expired){expired=true;form.inert=true;const msg=make('p','mk6-expired','Tu sesión de compra segura venció');msg.setAttribute('role','alert');form.before(msg);try{sessionStorage.removeItem(key);}catch(_){}const url=new URL(mk6.cart);url.searchParams.set('mk-session-expired','1');location.replace(url.href);}}
  tick();setInterval(tick,1000);document.addEventListener('visibilitychange',tick);
 });
})(window.jQuery);
