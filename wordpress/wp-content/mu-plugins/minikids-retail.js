(function($){
 'use strict';
 const slider=document.querySelector('.mkr-hero-slider');
 if(slider){
  const slides=[...slider.querySelectorAll(':scope > .mkr-campaign')];
  if(slides.length>1){
   const track=document.createElement('div');track.className='mkr-hero-track';track.tabIndex=0;
   track.setAttribute('aria-label','Promociones de MiniKids');track.setAttribute('role','group');
   slider.setAttribute('role','region');slider.setAttribute('aria-roledescription','carrusel');slider.setAttribute('aria-label','Campañas destacadas');
   const arrows=document.createElement('div');arrows.className='mkr-carousel-arrows';
   const markers=document.createElement('div');markers.className='mkr-carousel-dots';markers.setAttribute('role','group');markers.setAttribute('aria-label','Elegir campaña');
   const status=document.createElement('span');status.className='screen-reader-text';status.setAttribute('role','status');
   slides.forEach((slide,i)=>{slide.setAttribute('role','group');slide.setAttribute('aria-roledescription','diapositiva');slide.setAttribute('aria-label',`${i+1} de ${slides.length}`);track.append(slide);});
   let current=0,lastAnnounced=0,frame=0,timer;
   const nearest=()=>slides.reduce((best,slide,i)=>Math.abs(slide.getBoundingClientRect().left-track.getBoundingClientRect().left)<Math.abs(slides[best].getBoundingClientRect().left-track.getBoundingClientRect().left)?i:best,0);
   function sync(announce){current=nearest();[...markers.children].forEach((dot,i)=>dot.setAttribute('aria-current',String(i===current)));if(announce&&current!==lastAnnounced){status.textContent=`Campaña ${current+1} de ${slides.length}`;lastAnnounced=current;}}
   function go(index){index=(index+slides.length)%slides.length;const slide=slides[index];track.scrollTo({left:track.scrollLeft+slide.getBoundingClientRect().left-track.getBoundingClientRect().left,behavior:'instant'});sync(true);}
   for(const [label,symbol,step]of [['Campaña anterior','‹',-1],['Siguiente campaña','›',1]]){const btn=document.createElement('button');btn.type='button';btn.setAttribute('aria-label',label);btn.textContent=symbol;btn.addEventListener('click',()=>go(nearest()+step));arrows.append(btn);}
   slides.forEach((slide,i)=>{const dot=document.createElement('button');dot.type='button';dot.setAttribute('aria-label',`Ver campaña ${i+1}: ${slide.querySelector('img')?.alt.split('.')[0]||''}`);dot.addEventListener('click',()=>go(i));markers.append(dot);});
   slider.append(arrows,track,markers,status);slider.classList.add('is-ready');sync(false);
   track.addEventListener('keydown',event=>{if(event.target!==track)return;if(event.key==='ArrowRight'||event.key==='ArrowLeft'){event.preventDefault();go(nearest()+(event.key==='ArrowRight'?1:-1));}});
   track.addEventListener('scroll',()=>{if(!frame)frame=requestAnimationFrame(()=>{frame=0;sync(false)});if(!('onscrollend'in track)){clearTimeout(timer);timer=setTimeout(()=>sync(true),120);}});
   if('onscrollend'in track)track.addEventListener('scrollend',()=>sync(true));
  }
 }
 const button=document.querySelector('.mkr-category-toggle');
 const panel=document.querySelector('#mkr-category-panel');
 function closeCategories(){if(!button||!panel)return;panel.hidden=true;button.setAttribute('aria-expanded','false');}
 if(button&&panel){
  button.addEventListener('click',()=>{const open=button.getAttribute('aria-expanded')==='true';panel.hidden=open;button.setAttribute('aria-expanded',String(!open));});
  document.addEventListener('click',e=>{if(!e.target.closest('.mkr-category-control'))closeCategories();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!panel.hidden){closeCategories();button.focus();}});
  panel.addEventListener('focusout',e=>{if(e.relatedTarget&&!e.relatedTarget.closest('.mkr-category-control'))closeCategories();});
 }
 if(!$)return;
 $(function(){
  const grid=document.querySelector('.minikids-tienda-grid');
  if(!grid)return;
  const list=grid.querySelector('ul.products'),pills=document.querySelector('.minikids-cat-pills-wrap'),search=document.querySelector('.minikids-catalog-search-container');
  if(!list||!pills||grid.closest('.mkr-catalog-layout'))return;
  const items=[...list.querySelectorAll('li.product')],order=new Map(items.map((e,i)=>[e,i]));
  const price=item=>{const el=item.querySelector('.price ins .amount')||item.querySelector('.price .amount');return Number((el?.textContent||'0').replace(/[^\d.]/g,''));};
  const layout=document.createElement('div');layout.className='mkr-catalog-layout';
  const sidebar=document.createElement('aside');sidebar.className='mkr-catalog-sidebar';sidebar.setAttribute('aria-label','Filtros del catálogo');
  const priceDetails=document.createElement('details');priceDetails.open=!matchMedia('(max-width:600px)').matches;priceDetails.innerHTML='<summary>Precio</summary>';
  const options=document.createElement('fieldset');options.className='mkr-price-options';
  const legend=document.createElement('legend');legend.className='screen-reader-text';legend.textContent='Filtrar por precio';options.append(legend);
  for(const[value,text]of [['all','Todos los precios'],['low','Hasta S/ 50'],['high','Más de S/ 50']]){const label=document.createElement('label'),radio=document.createElement('input');radio.type='radio';radio.name='mkr-price';radio.value=value;radio.checked=value==='all';label.append(radio,document.createTextNode(text));options.append(label);}
  priceDetails.append(options);
  const categoryDetails=document.createElement('details');categoryDetails.open=!matchMedia('(max-width:600px)').matches;categoryDetails.innerHTML='<summary>Categorías</summary>';categoryDetails.append(pills);sidebar.append(priceDetails,categoryDetails);
  const main=document.createElement('div');main.className='mkr-catalog-main';
  const toolbar=document.createElement('div');toolbar.className='mkr-catalog-toolbar';
  const status=document.createElement('span');status.className='mkr-catalog-status';status.setAttribute('role','status');
  const sortLabel=document.createElement('label');sortLabel.className='mkr-sort';sortLabel.append(document.createTextNode('Ordenar por'));
  const sort=document.createElement('select');sort.setAttribute('aria-label','Ordenar productos');
  for(const[value,text]of [['featured','Destacados'],['price-asc','Menor precio'],['price-desc','Mayor precio'],['name','Nombre']]){const option=document.createElement('option');option.value=value;option.textContent=text;sort.append(option);}
  sortLabel.append(sort);toolbar.append(status,sortLabel);
  grid.before(layout);layout.append(sidebar,main);main.append(toolbar);if(search)main.append(search);main.append(grid);
  const empty=document.createElement('p');empty.className='mkr-extra-empty';empty.hidden=true;empty.textContent='No hay productos en este rango de precios. Prueba con todos los precios.';main.append(empty);
  const title=document.querySelector('.minikids-tienda-header h1'),breadcrumb=document.createElement('p');breadcrumb.className='mkr-breadcrumb';
  const home=document.createElement('a');home.href=document.querySelector('.mkr-brand').href;home.textContent='Inicio';const trail=document.createElement('span');breadcrumb.append(home,trail);document.querySelector('.minikids-tienda-header')?.before(breadcrumb);
  function update(){
   const selected=options.querySelector('input:checked')?.value||'all';let visible=0,baseVisible=0;
   for(const item of items){const amount=price(item),hide=selected==='low'?amount>50:selected==='high'?amount<=50:false;
    if(item.classList.contains('mkr-price-hidden')!==hide)item.classList.toggle('mkr-price-hidden',hide);
    if(!item.classList.contains('minikids-product-hidden')){baseVisible++;if(!hide)visible++;}
   }
   const label=visible===1?'1 producto':`${visible} productos`;if(status.textContent!==label)status.textContent=label;
   empty.hidden=visible>0||baseVisible===0;
   const active=pills.querySelector('.minikids-pill-active'),category=active?.getAttribute('data-cat')||'all';
   const name=category==='all'?'Tienda de juguetes':active.textContent.trim();if(title&&title.textContent!==name)title.textContent=name;
   trail.textContent=category==='all'?' / Juguetes':` / Juguetes / ${name}`;
  }
  options.addEventListener('change',update);
  sort.addEventListener('change',()=>{const sorted=[...items];sorted.sort((a,b)=>sort.value==='price-asc'?price(a)-price(b):sort.value==='price-desc'?price(b)-price(a):sort.value==='name'?(a.querySelector('h2')?.textContent||'').localeCompare(b.querySelector('h2')?.textContent||'','es'):order.get(a)-order.get(b));list.append(...sorted);update();});
  // V5 catalog observer lives in minikids-commerce-v5.js.

  for(const item of items){const add=item.querySelector('.ajax_add_to_cart[data-product_id]');if(!add||item.querySelector('.mk12-card-buy'))continue;
   const name=item.querySelector('h2')?.textContent.trim()||'producto';const quantity=document.createElement('div');quantity.className='mkr-quantity';
   const input=document.createElement('input');input.type='number';input.min='1';input.max='99';input.step='1';input.value='1';input.setAttribute('aria-label','Cantidad de '+name);input.inputMode='numeric';
   function set(n){n=Math.max(1,Math.min(Number(input.max)||99,Math.floor(Number(n)||1)));input.value=String(n);add.setAttribute('data-quantity',String(n));$(add).data('quantity',n);}
   const minus=document.createElement('button');minus.type='button';minus.textContent='−';minus.setAttribute('aria-label','Reducir cantidad de '+name);minus.addEventListener('click',()=>set(Number(input.value)-1));
   const plus=document.createElement('button');plus.type='button';plus.textContent='+';plus.setAttribute('aria-label','Aumentar cantidad de '+name);plus.addEventListener('click',()=>set(Number(input.value)+1));
   input.addEventListener('input',()=>set(input.value));quantity.append(minus,input,plus);add.before(quantity);
   add.textContent='AGREGAR';
  }
  update();
 });
 let previousFocus=null;
 $('.mkr-cart').on('click',function(e){
  if(!document.querySelector('#minikids-cart-drawer'))return;
  e.preventDefault();previousFocus=this;
  $('#minikids-cart-drawer').addClass('active').attr({'aria-hidden':'false','role':'dialog','aria-modal':'true','aria-label':'Mi carrito'});
  $('body').addClass('minikids-cart-open');
  const close=document.querySelector('.minikids-cart-close');if(close)close.focus();
 });
 $(document).on('click','.minikids-cart-close,.minikids-cart-drawer-backdrop,.drawer-continue-btn',()=>{if(previousFocus){previousFocus.focus();previousFocus=null;}});
 $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart',function(){
  const count=document.querySelector('.mkr-cart-count');if(count)$('.cart-count-badge').text('('+count.textContent.trim()+')');
 });
 document.addEventListener('keydown',e=>{
  if(e.key==='Escape'&&previousFocus){previousFocus.focus();previousFocus=null;}
  const drawer=document.querySelector('#minikids-cart-drawer.active');if(!drawer||e.key!=='Tab')return;
  const controls=[...drawer.querySelectorAll('a[href],button,input')].filter(x=>!x.disabled&&x.getClientRects().length);
  if(!controls.length)return;
  if(e.shiftKey&&document.activeElement===controls[0]){e.preventDefault();controls.at(-1).focus();}
  else if(!e.shiftKey&&document.activeElement===controls.at(-1)){e.preventDefault();controls[0].focus();}
 });
})(window.jQuery);

;
