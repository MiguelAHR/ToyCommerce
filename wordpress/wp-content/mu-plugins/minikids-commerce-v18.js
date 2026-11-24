/* Presentation only. Do not handle checkout submit, authentication or cart mutations. */
(()=>{
 'use strict';
 function init(){
  const viewport=document.querySelector('meta[name="viewport"]');
  if(viewport)viewport.content=/viewport-fit\s*=[^,]+/.test(viewport.content)?viewport.content.replace(/viewport-fit\s*=[^,]+/,'viewport-fit=cover'):viewport.content+', viewport-fit=cover';
  const auth=document.getElementById('mk12-auth');
  if(!auth)return;
  // Account for keyboards that resize only the visual viewport. Native dialog focus stays in V12.
  function fit(){
   const visual=window.visualViewport;
   if(!visual||window.innerWidth>1024)return;
   auth.style.setProperty('max-height',Math.max(0,visual.height-24)+'px','important');
   // Native dialog centering can use the layout viewport behind an open keyboard.
   auth.style.setProperty('top',((Number(visual.offsetTop)||0)+12)+'px','important');
   auth.style.setProperty('bottom','auto','important');
   auth.style.setProperty('margin-top','0','important');
   auth.style.setProperty('margin-bottom','0','important');
   if(auth.open&&auth.contains(document.activeElement))document.activeElement.scrollIntoView({block:'nearest',inline:'nearest'});
  }
  if(window.visualViewport){window.visualViewport.addEventListener('resize',fit);window.visualViewport.addEventListener('scroll',fit);}
  window.addEventListener('resize',()=>{
   if(window.innerWidth>1024)['max-height','top','bottom','margin-top','margin-bottom'].forEach(p=>auth.style.removeProperty(p));else fit();
  });
  auth.addEventListener('focusin',fit);fit();
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
