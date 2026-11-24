(function(){
 'use strict';
 const root=document.documentElement,started=performance.now();
 if(window.mk7LoaderDebug){root.classList.add('mk7-loader-debug');return;}
 const el=()=>document.getElementById('mk6-loader');
 const MIN=300; // tiempo minimo visible para que no parpadee en paginas muy rapidas
 let done=false,hideTimer,leaving=false,leaveTimer;
 // El velo se ve desde el primer pintado (CSS en la cabecera) en TODA carga, tambien al navegar dentro de la tienda.
 function finish(immediate){
  if(done)return;
  const wait=immediate?0:Math.max(0,MIN-(performance.now()-started));
  if(wait>0){clearTimeout(hideTimer);hideTimer=setTimeout(()=>finish(true),wait);return;}
  done=true;clearTimeout(hideTimer);el()?.classList.add('mk6-ready');
 }
 function stopLeaving(){
  leaving=false;clearTimeout(leaveTimer);
  const l=el();if(l){l.classList.remove('mk7-leaving');l.classList.add('mk6-ready');}
  done=true;
 }
 // Salida: al pulsar un enlace interno (o enviar un formulario) el velo aparece al instante en la pagina actual.
 function leave(){
  if(leaving)return;const l=el();if(!l)return;
  leaving=true;l.classList.remove('mk6-ready');l.classList.add('mk7-leaving');
  clearTimeout(leaveTimer);leaveTimer=setTimeout(stopLeaving,6000); // si la navegacion no ocurre (descarga, cancelada), se retira
 }
 const FILES=/\.(pdf|zip|rar|docx?|xlsx?|pptx?|jpe?g|png|gif|webp|svg|mp4|mp3|csv|txt)(\?|$)/i;
 document.addEventListener('click',e=>{
  if(e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;
  const a=e.target.closest&&e.target.closest('a[href]');if(!a)return;
  if((a.target&&a.target!=='_self')||a.hasAttribute('download'))return;
  let u;try{u=new URL(a.href,location.href);}catch(_){return;}
  if(!/^https?:$/.test(u.protocol)||u.origin!==location.origin)return;
  if(u.pathname===location.pathname&&u.search===location.search)return; // ancla o misma pagina
  if(FILES.test(u.pathname)||/wp-admin|customer-logout|[?&]add-to-cart=/.test(u.href))return;
  // Otros scripts pueden interceptar el clic (modales, AJAX): solo se cubre si la navegacion sigue en pie.
  setTimeout(()=>{if(!e.defaultPrevented)leave();},0);
 },true);
 document.addEventListener('submit',e=>{
  const f=e.target;if(!f||f.tagName!=='FORM'||(f.target&&f.target!=='_self'))return;
  setTimeout(()=>{if(!e.defaultPrevented&&!f.closest('dialog'))leave();},0);
 },true);
 window.addEventListener('load',()=>finish(),{once:true});
 window.addEventListener('pageshow',e=>{if(e.persisted)stopLeaving();else if(document.readyState==='complete')finish();});
 document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible'&&document.readyState==='complete')finish();});
 document.addEventListener('DOMContentLoaded',()=>{if(done)el()?.classList.add('mk6-ready');if(document.readyState==='complete')finish();},{once:true});
 // Empieza a desvanecerse a los 2250 ms y desaparece del todo a los 2500 ms aunque load no llegue.
 setTimeout(()=>finish(true),Math.max(0,2250-(performance.now()-started)));
})();
