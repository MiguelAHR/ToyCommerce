(function($){
 'use strict';
 const qs=(s,r=document)=>r.querySelector(s),all=(s,r=document)=>[...r.querySelectorAll(s)];
 if(window.wc?.blocksCheckout?.registerCheckoutFilters)window.wc.blocksCheckout.registerCheckoutFilters('minikids-v5',{proceedToCheckoutButtonLabel:()=>'IR A PAGAR',placeOrderButtonLabel:()=>'Realizar pedido'});
 function button(text,label,fn){const b=document.createElement('button');b.type='button';b.textContent=text;b.setAttribute('aria-label',label);b.addEventListener('click',fn);return b;}
 function quantity(root){
  all('.quantity',root).forEach(w=>{if(w.dataset.mk5)return;const i=qs('input.qty',w);if(!i)return;w.dataset.mk5='1';
   // Blocksy sometimes already supplies its own steppers.
   if(qs('.ct-increase,.ct-decrease',w)){all('.ct-increase,.ct-decrease',w).forEach(el=>{el.tabIndex=0;el.setAttribute('role','button');el.setAttribute('aria-label',el.matches('.ct-increase')?'Aumentar cantidad':'Reducir cantidad');el.addEventListener('keydown',e=>{if(e.key===' '||e.key==='Enter'){e.preventDefault();el.click();}});});return;}
   if(qs('button',w))return;
   const change=delta=>{const min=Number(i.min)||1,max=i.max===''?Infinity:Number(i.max),step=Number(i.step)||1;i.value=String(Math.max(min,Math.min(max,(Number(i.value)||min)+delta*step)));i.dispatchEvent(new Event('change',{bubbles:true}));};
   w.prepend(button('−','Reducir cantidad',()=>change(-1)));w.append(button('+','Aumentar cantidad',()=>change(1)));
  });
 }
 $(function(){
  const grid=qs('.minikids-tienda-grid ul.products');
  if(grid){
   const items=all('li.product',grid),layout=qs('.mkr-catalog-layout'),sidebar=qs('.mkr-catalog-sidebar'),pills=qs('.minikids-category-pills'),main=qs('.mkr-catalog-main');
   if(!layout||!sidebar)return;
   const records=new Map(items.map((el,i)=>{const id=qs('[data-product_id]',el)?.dataset.product_id||[...el.classList].find(x=>/^post-\d+$/.test(x))?.slice(5);return [el,{...mk5.products[id],order:i}];}));
   const priceDetails=qs('details',sidebar);priceDetails.replaceChildren();const summary=document.createElement('summary');summary.textContent='PRECIO';priceDetails.append(summary);
   const ranges=[['all','Todos los precios',0,Infinity],['under25','Menos de S/ 25',0,25],['25to50','S/ 25 a S/ 49.99',25,50],['50to100','S/ 50 a S/ 99.99',50,100],['100up','S/ 100 a más',100,Infinity]];
   const field=document.createElement('fieldset');field.className='mkr-price-options';const legend=document.createElement('legend');legend.className='screen-reader-text';legend.textContent='Rango de precio';field.append(legend);priceDetails.append(field);
   for(const [value,label]of ranges){const l=document.createElement('label'),i=document.createElement('input');i.type='radio';i.name='mk5-price';i.value=value;i.checked=value==='all';l.append(i,document.createTextNode(label+' '));const count=document.createElement('span');count.className='mk5-count';l.append(count);field.append(l);}
   const brands=[...new Set([...records.values()].map(r=>r.brand).filter(Boolean))].sort();
   const brandDetails=document.createElement('details');brandDetails.open=true;brandDetails.innerHTML='<summary>MARCA</summary>';const select=document.createElement('select');select.setAttribute('aria-label','Filtrar por marca');select.add(new Option('Todas las marcas',''));brands.forEach(b=>select.add(new Option(b,b)));brandDetails.append(select);sidebar.append(brandDetails);
   all('details summary',sidebar).forEach(s=>{if(s.textContent==='Categorías')s.textContent='CATEGORÍA';});
   all('.minikids-pill:not(.minikids-pill-reset)',pills).forEach(p=>{const c=document.createElement('span');c.className='mk5-count';p.append(c);});
   let frame=0;
   function update(){
    const selected=ranges.find(r=>r[0]===(qs('input:checked',field)?.value||'all'));let visible=0;
    const base=el=>!el.classList.contains('minikids-product-hidden');
    for(const el of items){const r=records.get(el),price=r.price??0,brand=select.value===''||r.brand===select.value,hide=price<selected[2]||price>=selected[3]||!brand;
     el.classList.toggle('mk5-filter-hidden',hide);if(base(el)&&!hide)visible++;
    }
    all('label',field).forEach((l,j)=>{const r=ranges[j];qs('.mk5-count',l).textContent='('+items.filter(el=>{const d=records.get(el);return base(el)&&(!select.value||d.brand===select.value)&&d.price>=r[2]&&d.price<r[3];}).length+')';});
    all('option',select).forEach((op,j)=>{if(j)op.textContent=op.value+' ('+items.filter(el=>base(el)&&records.get(el).brand===op.value).length+')';});
    all('.minikids-pill:not(.minikids-pill-reset)',pills).forEach(p=>{const cat=p.dataset.cat;qs('.mk5-count',p).textContent=' ('+items.filter(el=>cat==='all'||el.classList.contains('product_cat-'+cat)).length+')';});
    qs('.mkr-catalog-status').textContent=visible+' productos';const empty=qs('.mkr-extra-empty');if(empty){empty.hidden=visible>0;empty.textContent='No hay productos con estos filtros. Prueba otra categoría, precio o marca.';}
    const active=qs('.minikids-pill-active',pills),name=active?active.childNodes[0].textContent.trim():'Todos los juguetes';const title=qs('.minikids-tienda-header h1');if(title)title.textContent=name==='Todos'?'Tienda de juguetes':name;
    const trail=qs('.mkr-breadcrumb span');if(trail)trail.textContent=' | Tienda'+(active?.dataset.cat!=='all'?' | '+name:'');
   }
   field.addEventListener('change',update);select.addEventListener('change',update);qs('.mkr-sort select')?.addEventListener('change',()=>requestAnimationFrame(update));
   new MutationObserver(rs=>{if(rs.some(r=>r.target.matches('li.product')&&r.oldValue?.includes('minikids-product-hidden')!==r.target.className.includes('minikids-product-hidden'))&&!frame)frame=requestAnimationFrame(()=>{frame=0;update();});}).observe(grid,{subtree:true,attributes:true,attributeOldValue:true,attributeFilter:['class']});
   items.forEach(el=>{
    const d=records.get(el),add=qs('.ajax_add_to_cart',el),i=qs('.mkr-quantity input',el);if(i&&d.max>0)i.max=d.max;
    if(add&&!add.closest('.mk12-card-buy')){add.textContent='AGREGAR';add.insertAdjacentHTML('afterbegin','<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 3h3l2 12h12l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>');}
    const brand=d.brand||qs('.entry-meta',el)?.textContent.trim();if(brand&&!qs('.mk5-card-brand',el)){const label=document.createElement('span');label.className='mk5-card-brand';label.textContent=brand;const title=qs('.woocommerce-loop-product__title',el);title?.before(label);}
   });
   // Existing category/search handlers remain the source of category state.
   document.addEventListener('click',e=>{if(e.target.closest('.minikids-pill-reset,.btn-reset-catalog')){qs('input[value="all"]',field).checked=true;select.value='';requestAnimationFrame(update);}});
   update();
  }
  quantity(document);
  function drawer(){
   const root=qs('#minikids-cart-drawer');if(!root)return;quantity(root);
   const heading=qs('.minikids-cart-drawer-header h2,.minikids-cart-drawer-header h3',root);if(heading&&heading.firstChild?.nodeType===3)heading.firstChild.textContent='Mi carrito ';
   const a=qs('.drawer-viewcart-btn',root);if(a)a.textContent='Ver todo el carrito';
   let status=qs('.mk5-mini-status',root);if(!status){status=document.createElement('p');status.className='mk5-mini-status';status.setAttribute('role','status');qs('.minikids-cart-drawer-body',root).append(status);}
  }
  drawer();$(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart',drawer);
  let miniBusy=false;
  $(document).on('change','.mk5-mini-quantity input',async function(){
   if(miniBusy)return;const wrap=this.closest('.mk5-mini-quantity'),root=qs('#minikids-cart-drawer'),status=qs('.mk5-mini-status',root);miniBusy=true;all('.mk5-mini-quantity input,.mk5-mini-quantity button',root).forEach(x=>x.disabled=true);status.textContent='Actualizando carrito…';
   try{const res=await fetch(mk5.ajax,{method:'POST',credentials:'same-origin',body:new URLSearchParams({nonce:mk5.nonce,key:wrap.dataset.key,quantity:this.value})});const data=await res.json();if(!res.ok||!data.fragments)throw new Error(data.data?.message||'No pudimos actualizar la cantidad.');
    Object.entries(data.fragments).forEach(([selector,html])=>$(selector).replaceWith(html));$(document.body).trigger('wc_fragments_refreshed');status.textContent='Carrito actualizado.';
   }catch(e){status.textContent=e.message;$(document.body).trigger('wc_fragment_refresh');}finally{miniBusy=false;all('.mk5-mini-quantity input,.mk5-mini-quantity button',root).forEach(x=>x.disabled=false);}
  });
  // Supplement native galleries without replacing the slider, lightbox or zoom.
  const gallery=qs('.woocommerce-product-gallery');
  if(gallery){
   const thumb=qs('.flex-control-thumbs',gallery)||qs('.flexy-pills',gallery);const slides=all('.woocommerce-product-gallery__image,.flexy-items>.flexy-item',gallery);
   if(thumb)all('.ct-media-container',thumb).forEach((el,i)=>{el.tabIndex=0;el.setAttribute('role','button');el.setAttribute('aria-label','Ver imagen '+(i+1));el.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();el.click();}});});
   if(thumb&&slides.length>1){
    const nav=document.createElement('div');nav.className='mk5-gallery-arrows';
    function move(delta){const controls=all('img,button',thumb).filter(e=>e.matches('img')||!qs('img',e));let n=controls.findIndex(e=>e.classList.contains('flex-active')||e.closest('[aria-selected="true"],.active'));n=(n+delta+controls.length)%controls.length;controls[n]?.click();controls[n]?.scrollIntoView({block:'nearest',inline:'nearest'});}
    nav.append(button('‹','Imagen anterior',()=>move(-1)),button('›','Imagen siguiente',()=>move(1)));gallery.append(nav);
    const up=button('↑','Miniatura anterior',()=>move(-1)),down=button('↓','Miniatura siguiente',()=>move(1));up.className=down.className='mk5-thumb-arrow';thumb.before(up);thumb.after(down);
   }
   // Hover zoom handled by minikids-commerce-v16.js.
  }
  $(document).on('click','.mk5-empty-cart',async function(){
   const empty=this;if(empty.disabled)return;
   const status=qs('.mk5-cart-status');empty.disabled=true;status.textContent='Vaciando carrito…';
   try{
    const res=await fetch(mk5.store,{credentials:'same-origin'});const data=await res.json();let nonce=res.headers.get('Nonce');if(!res.ok||!nonce)throw new Error();
    for(const item of data.items){const r=await fetch(mk5.store+'/remove-item',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Nonce':nonce},body:JSON.stringify({key:item.key})});if(!r.ok)throw new Error();nonce=r.headers.get('Nonce')||nonce;}
    location.reload();
   }catch(e){status.textContent='No pudimos vaciar el carrito. Puedes retirar los artículos con el botón de cada uno.';empty.disabled=false;}
  });

 });
})(window.jQuery);
