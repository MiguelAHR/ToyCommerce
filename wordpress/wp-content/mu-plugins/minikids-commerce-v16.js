(function($){
 'use strict';
 const one=(s,r=document)=>r.querySelector(s),all=(s,r=document)=>[...r.querySelectorAll(s)];

 // Reviews modal (<dialog>) with mk8 mechanics
 let active=null,opener=null,overlay=null,savedOverflow='',savedPriority='';
 const focusable=d=>all('button,a[href],input,select,textarea,[tabindex]',d).filter(el=>!el.disabled&&el.tabIndex>=0&&el.getClientRects().length);

 function restore(){
  const btn=opener;
  if(active){active.removeAttribute('open');active.classList.remove('is-open');}
  if(overlay){overlay.remove();overlay=null;}
  document.body.style.setProperty('overflow',savedOverflow,savedPriority);
  document.body.classList.remove('mk10-modal-open');
  active=opener=null;
  if(btn&&btn.isConnected)btn.focus({preventScroll:true});
 }
 function closeReviews(){
  if(!active)return;
  const d=active;
  if(typeof d.close==='function'){try{d.close();}catch(_){}}
  restore();
 }
 function openReviews(trigger=null){
  const dialog=one('#mk16-reviews-dialog');
  if(!dialog)return false;
  if(active===dialog)return true;
  if(active)closeReviews();
  active=dialog;opener=trigger;
  savedOverflow=document.body.style.getPropertyValue('overflow');
  savedPriority=document.body.style.getPropertyPriority('overflow');
  document.body.style.setProperty('overflow','hidden','important');
  document.body.classList.add('mk10-modal-open');
  try{
   if(typeof dialog.showModal!=='function')throw new Error('Dialog API unavailable');
   dialog.showModal();
  }catch(_){
   dialog.setAttribute('open','');dialog.classList.add('is-open');
   overlay=document.createElement('div');overlay.className='mk10-modal-overlay';
   overlay.setAttribute('aria-hidden','true');document.body.append(overlay);
  }
  (one('.mk16-reviews-close',dialog)||dialog).focus({preventScroll:true});
  return true;
 }

 // Filtering & "Cargar mas" inside the reviews modal
 let currentFilter='all',visibleLimit=8;
 function applyReviewFilter(){
  const dialog=one('#mk16-reviews-dialog');if(!dialog)return;
  const items=all('.mk16-review-item',dialog);
  let matched=0;
  items.forEach(item=>{
   const match=currentFilter==='all'
    ||(currentFilter==='photos'&&item.dataset.hasPhoto==='1')
    ||(item.dataset.rating===currentFilter);
   if(!match){item.hidden=true;return;}
   matched++;
   item.hidden=matched>visibleLimit;
  });
  const empty=one('.mk16-filter-empty',dialog);if(empty)empty.hidden=matched>0;
  const more=one('[data-mk16-load-more]',dialog);if(more)more.hidden=matched<=visibleLimit;
  all('[data-mk16-filter]',dialog).forEach(el=>{
   const on=el.dataset.mk16Filter===currentFilter;
   el.classList.toggle('is-active',on);
   el.setAttribute('aria-pressed',String(on));
  });
 }

 document.addEventListener('click',e=>{
  const t=e.target instanceof Element?e.target:null;if(!t)return;
  const openBtn=t.closest('[data-mk16-reviews-open]');
  if(openBtn){e.preventDefault();openReviews(openBtn);return;}
  const hashLink=t.closest('a[href$="#reviews"],a[href$="#comments"]');
  if(hashLink&&one('#mk16-reviews-dialog')){
   const u=new URL(hashLink.href,location.href);
   if(u.pathname.replace(/\/$/,'')===location.pathname.replace(/\/$/,'')){
    e.preventDefault();openReviews(hashLink);return;
   }
  }
  if(!active)return;
  if(t.closest('.mk16-reviews-close')&&active.contains(t)){e.preventDefault();closeReviews();return;}
  const filterBtn=t.closest('[data-mk16-filter]');
  if(filterBtn&&active.contains(filterBtn)){
   e.preventDefault();
   const next=filterBtn.dataset.mk16Filter;
   currentFilter=(filterBtn.classList.contains('mk16-bar-btn')&&currentFilter===next)?'all':next;
   visibleLimit=8;
   applyReviewFilter();
   const listWrap=one('.mk16-reviews-list-wrap',active);if(listWrap)listWrap.scrollTop=0;
   return;
  }
  if(t.closest('[data-mk16-load-more]')&&active.contains(t)){
   e.preventDefault();visibleLimit+=8;applyReviewFilter();return;
  }
  if(t===overlay){closeReviews();return;}
  if(t===active){
   const r=active.getBoundingClientRect();
   if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)closeReviews();
  }
 });
 document.addEventListener('cancel',e=>{if(e.target===active){e.preventDefault();closeReviews();}},true);
 document.addEventListener('close',e=>{if(e.target===active&&!active.hasAttribute('open'))restore();},true);
 document.addEventListener('keydown',e=>{
  if(!active)return;
  if(e.key==='Escape'){e.preventDefault();closeReviews();return;}
  if(e.key!=='Tab')return;
  const list=focusable(active),first=list[0],last=list[list.length-1];
  if(!first){e.preventDefault();active.focus();return;}
  if(e.shiftKey&&(document.activeElement===first||!active.contains(document.activeElement))){e.preventDefault();last.focus();}
  else if(!e.shiftKey&&(document.activeElement===last||!active.contains(document.activeElement))){e.preventDefault();first.focus();}
 });
 document.addEventListener('focusin',e=>{
  if(active&&!active.contains(e.target))(focusable(active)[0]||active).focus({preventScroll:true});
 });
 window.addEventListener('pagehide',closeReviews);
 window.addEventListener('pageshow',()=>{if(active)closeReviews();});

 function checkReviewsHash(){
  const h=location.hash;
  if((h==='#reviews'||h==='#comments'||/^#comment-\d+$/.test(h))&&one('#mk16-reviews-dialog')){
   openReviews();
   if(/^#comment-\d+$/.test(h)){
    const target=one(h);
    if(target){target.hidden=false;target.scrollIntoView({block:'nearest'});}
   }
  }
 }
 window.addEventListener('hashchange',checkReviewsHash);

 // Gallery smooth hover zoom (2.2x) + high-res + touch/Enter PhotoSwipe
 function initGalleryZoom(){
  const gallery=one('.woocommerce-product-gallery');
  if(!gallery)return;
  const canHover=window.matchMedia('(hover: hover) and (pointer: fine)');
  const slides=all('.ct-media-container',gallery).filter(el=>!el.closest('.flexy-pills'));
  let rafId=0;

  function upgradeHiRes(container,img){
   const hi=img.dataset.large_image||container.dataset.src;
   if(!hi||img.dataset.mk16Hires==='1')return;
   img.dataset.mk16Hires='1';
   const pre=new Image();
   pre.onload=()=>{img.src=hi;img.srcset=hi+' 1200w';};
   pre.src=hi;
  }

  function resetSlide(container){
   container.classList.remove('is-zooming');
   const img=one('img',container);
   if(img){img.style.transform='';img.style.transformOrigin='';}
  }
  function resetAll(){
   if(rafId){cancelAnimationFrame(rafId);rafId=0;}
   slides.forEach(resetSlide);
  }

  function openLightbox(){
   const trigger=one('.woocommerce-product-gallery__trigger',gallery);
   if(trigger){trigger.click();}
  }

  slides.forEach(container=>{
   const img=one('img',container);if(!img)return;
   if(!container.hasAttribute('tabindex')){
    container.setAttribute('tabindex','0');
    container.setAttribute('role','button');
    container.setAttribute('aria-label','Ampliar imagen del producto');
   }
   container.addEventListener('keydown',e=>{
    if(e.key==='Enter'){e.preventDefault();openLightbox();}
   });
   container.addEventListener('click',e=>{
    if(e.target.closest('.woocommerce-product-gallery__trigger,[class*="flexy-arrow"]'))return;
    if(gallery.querySelector('[data-flexy-dragging]'))return;
    if(!canHover.matches){e.preventDefault();openLightbox();}
   });
   container.addEventListener('mouseenter',()=>{
    if(!canHover.matches||gallery.querySelector('[data-flexy-dragging]'))return;
    upgradeHiRes(container,img);
    container.classList.add('is-zooming');
    img.style.transform='scale(2.2)';
   });
   container.addEventListener('mousemove',e=>{
    if(!canHover.matches||gallery.querySelector('[data-flexy-dragging]'))return;
    if(!container.classList.contains('is-zooming')){
     upgradeHiRes(container,img);
     container.classList.add('is-zooming');
    }
    const cx=e.clientX,cy=e.clientY;
    if(rafId)cancelAnimationFrame(rafId);
    rafId=requestAnimationFrame(()=>{
     rafId=0;
     const rect=container.getBoundingClientRect();
     if(!rect.width||!rect.height)return;
     const x=Math.max(0,Math.min(100,(cx-rect.left)/rect.width*100));
     const y=Math.max(0,Math.min(100,(cy-rect.top)/rect.height*100));
     img.style.transformOrigin=x.toFixed(2)+'% '+y.toFixed(2)+'%';
     img.style.transform='scale(2.2)';
    });
   });
   container.addEventListener('mouseleave',()=>{
    if(rafId){cancelAnimationFrame(rafId);rafId=0;}
    resetSlide(container);
   });
  });

  if($){
   $(document).on('blocksy:frontend:flexy:slide-change',resetAll);
  }
  gallery.addEventListener('click',e=>{
   if(e.target.closest('.flexy-pills,.flexy-arrow-prev,.flexy-arrow-next,.mk5-gallery-arrows'))resetAll();
  });
 }

 function syncAuthNonces(){
  const tok=one('.mk16-auth-nonces'),auth=one('#mk12-auth');
  if(!tok||!auth)return;
  [['login','woocommerce-login-nonce'],['register','woocommerce-register-nonce'],['lost','woocommerce-lost-password-nonce']].forEach(([k,name])=>{
   const val=tok.dataset[k],input=one('form[data-mk12-view="'+k+'"] input[name="'+name+'"]',auth);
   if(val&&input)input.value=val;
  });
 }

 $(function(){
  applyReviewFilter();
  checkReviewsHash();
  initGalleryZoom();
  syncAuthNonces();
  if($){
   $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart',syncAuthNonces);
  }
 });
})(window.jQuery);
