from pathlib import Path
R=Path(__file__).resolve().parents[1]
s=(R/'wordpress/wp-content/mu-plugins/minikids-commerce-v12.js').read_text('utf8')
s=s.replace("function close(){if(!active)return;active.close();restore();}","function close(navigate=true){if(!active)return;active.close();restore();if(navigate&&window.mk15?.auto)location.assign(mk15.home);}")
start=s.index(" document.addEventListener('click',e=>{\n  const a=")
end=s.index('  if(!active)return;',start)
s=s[:start]+''' function openAuth(view='login',trigger=null){
  const dialog=one('#mk12-auth');if(!dialog||typeof dialog.showModal!=='function')return false;
  if(!active){
   const bag=one('#minikids-cart-drawer.active .minikids-cart-close');bag?.click();
   try{dialog.showModal();}catch(_){return false;}
   active=dialog;opener=trigger;overflow=document.body.style.getPropertyValue('overflow');priority=document.body.style.getPropertyPriority('overflow');document.body.style.setProperty('overflow','hidden','important');
  }
  switchView(view);return true;
 }
 function authView(a){
  if(a.matches('.showlogin'))return 'login';
  const u=new URL(a.href,location.href),account=new URL(mk12.account,location.href),alias=new URL(mk12.alias,location.href),lost=new URL(mk15.lost,location.href);
  if(u.origin!==location.origin||u.searchParams.has('action')||/customer-logout|logout|reset-link-sent/.test(u.pathname)||u.searchParams.has('key'))return null;
  const path=u.pathname.replace(/\\/$/,'');
  if(path===lost.pathname.replace(/\\/$/,'')&&!u.searchParams.has('show-reset-form'))return 'lost';
  return [account.pathname,alias.pathname].some(p=>path===p.replace(/\\/$/,''))||u.searchParams.has('login')?'login':null;
 }
 document.addEventListener('click',e=>{
  const a=e.target.closest('a[href]');
  if(a&&mk12.guest&&!a.closest('#mk12-auth')&&!e.ctrlKey&&!e.metaKey&&!e.shiftKey&&!e.altKey&&e.button===0){
   const view=authView(a);if(view&&openAuth(view,a)){e.preventDefault();e.stopImmediatePropagation();return;}
  }
'''+s[end:]
# Capture account entries before Woo's checkout showlogin handler.
s=s.replace(" });\n document.addEventListener('cancel'", " },true);\n document.addEventListener('cancel'",1)
s=s.replace("  const button=one('[type=submit]',form)","""  const password=one('[name=password]',form),confirm=one('[name=password_confirm]',form);
  if(confirm){
   confirm.setCustomValidity(password.value===confirm.value?'':'Las contraseñas no coinciden.');
   const valid=password.value.length>=10&&password.value.length<=128&&/[a-z]/.test(password.value)&&/[A-Z]/.test(password.value)&&/[0-9]/.test(password.value);
   password.setCustomValidity(valid?'':'Usa al menos 10 caracteres, mayúsculas, minúsculas y números.');
   if(!form.reportValidity())return;
  }
  const button=one('[type=submit]',form)""")
s=s.replace("{close();location.reload();return;}","{close(false);location.reload();return;}")
s=s.replace("if(result.success){form.reset();}","if(result.success){form.reset();form.querySelectorAll('input:not([type=hidden]),button[type=submit]').forEach(x=>x.disabled=true);}")
s=s.replace("button.disabled=false;button.textContent=label", "button.disabled=message.textContent==='Si el correo existe, recibirás un enlace.';button.textContent=label")
s=s.replace(" window.addEventListener('pagehide',close);",''' document.addEventListener('input',e=>{
  if(!e.target.closest('[data-mk12-view="register"]'))return;
  e.target.setCustomValidity?.('');
  const form=e.target.form,p=one('[name=password]',form),confirm=one('[name=password_confirm]',form);confirm.setCustomValidity('');p.setCustomValidity('');
  const score=[p.value.length>=10,/[a-z]/.test(p.value),/[A-Z]/.test(p.value),/[0-9]/.test(p.value)].filter(Boolean).length;
  one('.mk15-strength',form).textContent='Fuerza: '+(score===4?'adecuada':score>=2?'media':'baja')+'. Usa al menos 10 caracteres, mayúsculas, minúsculas y números.';
 });
 function autoOpen(){if(window.mk15?.auto&&!openAuth())document.documentElement.classList.remove('mk15-auto-auth');}
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',autoOpen);else autoOpen();
 window.addEventListener('pagehide',()=>close(false));
 window.addEventListener('pageshow',e=>{if(e.persisted)autoOpen();});''')
(R/'tools/v15-stage/minikids-commerce-v12.js').write_text(s,encoding='utf8')
