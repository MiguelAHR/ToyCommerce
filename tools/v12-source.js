(function($){
 'use strict';
 const one=(s,r=document)=>r.querySelector(s);
 function cardQuantity(input){
  const add=one('.ajax_add_to_cart',input.closest('.mk12-card-buy'));if(!add)return;
  const max=input.max===''?Infinity:Number(input.max),q=Math.max(1,Math.min(max,Math.floor(Number(input.value)||1)));
  input.value=String(q);add.dataset.quantity=String(q);$(add).data('quantity',q);
  const url=new URL(add.href,location.href);url.searchParams.set('quantity',q);add.href=url.href;
 }
 document.addEventListener('click',e=>{const b=e.target.closest('[data-mk12-delta]');if(!b)return;e.preventDefault();const i=one('input',b.closest('.mkr-quantity'));i.value=String((Number(i.value)||1)+Number(b.dataset.mk12Delta));cardQuantity(i);});
 document.addEventListener('change',e=>{if(e.target.matches('.mk12-card-buy input'))cardQuantity(e.target);});
 let active=null,opener=null,overflow='',priority='';
 const visible=()=>[...active.querySelectorAll('button,a[href],input,select,textarea,[tabindex]')].filter(x=>!x.disabled&&x.tabIndex>=0&&x.getClientRects().length);
 function restore(){if(!active)return;active=null;document.body.style.setProperty('overflow',overflow,priority);if(opener?.isConnected)opener.focus({preventScroll:true});opener=null;}
 function close(){if(!active)return;active.close();restore();}
 function switchView(name){
  const view=one('[data-mk12-view="'+name+'"]',active);if(!view)return;
  active.querySelectorAll('[data-mk12-view]').forEach(f=>{f.hidden=f!==view;});
  one('#mk12-auth-title',active).textContent={login:'Iniciar sesión',register:'Crear cuenta',lost:'Recuperar contraseña'}[name];
  one('input:not([type=hidden]):not([name=website])',view)?.focus();
 }
 document.addEventListener('click',e=>{
  const a=e.target.closest('a[href]');
  if(a&&mk12.guest&&!a.closest('#mk12-auth')&&!e.ctrlKey&&!e.metaKey&&!e.shiftKey&&!e.altKey&&e.button===0){
   const u=new URL(a.href,location.href),account=new URL(mk12.account,location.href);
   if(u.origin===account.origin&&[account.pathname,new URL(mk12.alias,location.href).pathname].some(p=>u.pathname.replace(/\/$/,'')===p.replace(/\/$/,''))&&!u.search){
    const dialog=one('#mk12-auth');if(!dialog||typeof dialog.showModal!=='function')return;
    try{dialog.showModal();}catch(_){return;}
    e.preventDefault();active=dialog;opener=a;overflow=document.body.style.getPropertyValue('overflow');priority=document.body.style.getPropertyPriority('overflow');document.body.style.setProperty('overflow','hidden','important');switchView('login');return;
   }
  }
  if(!active)return;
  const sw=e.target.closest('[data-mk12-switch]');if(sw&&active.contains(sw)){e.preventDefault();switchView(sw.dataset.mk12Switch);return;}
  const show=e.target.closest('.mk12-show');if(show){const i=one('input',show.parentElement),reveal=i.type==='password';i.type=reveal?'text':'password';show.setAttribute('aria-pressed',String(reveal));show.setAttribute('aria-label',reveal?'Ocultar contraseña':'Mostrar contraseña');}
  if(e.target.closest('.mk12-auth-close')){close();return;}
  if(e.target===active){const r=active.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)close();}
 });
 document.addEventListener('cancel',e=>{if(e.target===active){e.preventDefault();close();}},true);
 document.addEventListener('close',e=>{if(e.target===active&&!active.open)restore();},true);
 document.addEventListener('keydown',e=>{if(!active||e.key!=='Tab')return;const list=visible(),first=list[0],last=list[list.length-1];if(!first)return;
  if(e.shiftKey&&(document.activeElement===first||!active.contains(document.activeElement))){e.preventDefault();last.focus();}
  else if(!e.shiftKey&&(document.activeElement===last||!active.contains(document.activeElement))){e.preventDefault();first.focus();}
 });
 document.addEventListener('focusin',e=>{if(active&&!active.contains(e.target))visible()[0]?.focus();});
 document.addEventListener('submit',async e=>{
  const form=e.target;if(!active||!form.matches('[data-mk12-view]'))return;e.preventDefault();if(form.dataset.busy)return;
  const button=one('[type=submit]',form),message=one('.mk12-auth-message',form),label=button.textContent;
  form.dataset.busy='1';button.disabled=true;button.textContent='Un momento…';form.setAttribute('aria-busy','true');message.textContent='';
  try{
   const data=new FormData(form);data.set('action','mk12_auth');const response=await fetch(mk12.ajax,{method:'POST',body:data,credentials:'same-origin',cache:'no-store'}),result=await response.json();
   if(result.success&&result.data.reload){close();location.reload();return;}
   message.textContent=result.data?.message||'No pudimos completar la solicitud. Inténtalo de nuevo.';
   if(result.success){form.reset();}else{message.setAttribute('tabindex','-1');message.focus();}
  }catch(_){message.textContent='No pudimos conectar. Inténtalo de nuevo o abre Mi cuenta.';}
  finally{delete form.dataset.busy;button.disabled=false;button.textContent=label;form.removeAttribute('aria-busy');}
 });
 window.addEventListener('pagehide',close);
})(jQuery);
