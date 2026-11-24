/* BEGIN MINIKIDS V4 */
/* Runs alongside the backed-up retail behavior; no Woo/cart listeners changed. */
(()=>{
 'use strict';
 function controls(root,track,hero){
  const mobile=matchMedia('(max-width:767px)');
  const items=()=>[...track.querySelectorAll(hero?(mobile.matches?'.mk4-image':'.mk4-slide'):'.mk4-image')];
  const dots=document.createElement('div');dots.className='mk4-dots';dots.setAttribute('role','group');dots.setAttribute('aria-label',hero?'Elegir campaña':'Recorrer selección');
  const status=document.createElement('span');status.className='screen-reader-text';status.setAttribute('role','status');
  let active=0,frame=0;
  function targets(){return hero?items().map(x=>x.getBoundingClientRect().left-track.getBoundingClientRect().left+track.scrollLeft):Array.from({length:6},(_,i)=>(track.scrollWidth-track.clientWidth)*i/5);}
  function nearest(){const values=targets();return values.reduce((best,x,i)=>Math.abs(x-track.scrollLeft)<Math.abs(values[best]-track.scrollLeft)?i:best,0);}
  function sync(){active=nearest();[...dots.children].forEach((x,i)=>x.setAttribute('aria-current',String(i===active)));}
  function go(i){const values=targets();i=(i+values.length)%values.length;track.scrollTo({left:values[i],behavior:'instant'});sync();status.textContent=`${hero?'Campaña':'Posición'} ${i+1} de ${values.length}`;}
  function rebuild(){dots.replaceChildren();targets().forEach((_,i)=>{const b=document.createElement('button');b.type='button';b.className='mk4-dot';b.setAttribute('aria-label',`${hero?'Ver campaña':'Ver posición'} ${i+1}`);b.addEventListener('click',()=>go(i));dots.append(b);});sync();}
  for(const [cls,label,char,step]of [['mk4-prev','Anterior','‹',-1],['mk4-next','Siguiente','›',1]]){const b=document.createElement('button');b.type='button';b.className='mk4-arrow '+cls;b.setAttribute('aria-label',(hero?'Campaña: ':'Selección: ')+label);b.textContent=char;b.addEventListener('click',()=>go(nearest()+step));root.append(b);}
  root.setAttribute('role','region');root.setAttribute('aria-roledescription','carrusel');root.setAttribute('aria-label',hero?'Campañas MiniKids':root.closest('.mk4-section')?.querySelector('h2')?.textContent||'Selección de juguetes');track.tabIndex=0;
  track.addEventListener('keydown',e=>{if(e.target!==track)return;if(e.key==='ArrowRight'||e.key==='ArrowLeft'){e.preventDefault();go(nearest()+(e.key==='ArrowRight'?1:-1));}});
  track.addEventListener('scroll',()=>{if(!frame)frame=requestAnimationFrame(()=>{frame=0;sync();});});
  mobile.addEventListener('change',()=>{track.scrollLeft=0;rebuild();});new ResizeObserver(sync).observe(track);
  root.append(dots,status);rebuild();
 }
 document.querySelectorAll('.mk4-hero').forEach(root=>controls(root,root.querySelector('.mk4-hero-track'),true));
 document.querySelectorAll('.mk4-carousel').forEach(root=>controls(root,root.querySelector('.mk12-home-track ul.products')||root.querySelector('.mk4-card-track'),false));
})();
