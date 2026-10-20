import fs from 'node:fs/promises';
import {visit,evaluate,send,close} from 'file:///C:/xampp/htdocs/minkids_dev/tools/browser-review.mjs';
const base='https://minikids.vitorstream.shop';const checks=[];
await fs.mkdir('local-private/deploy/evidence',{recursive:true});
async function check(name,expression){const passed=!!await evaluate(expression);checks.push({name,passed});console.log(`${passed?'OK':'FAIL'} ${name}`);}
try{
 for(const pass of [1,2])for(const width of [360,390,768,1024,1280,1440])for(const [path,label] of [['/','inicio'],['/tienda/','tienda'],['/cart/','carrito'],['/nosotros/','nosotros'],['/libro-de-reclamaciones/','libro']]){
  await visit(base+path,width,900);
  for(let i=0;i<30;i++){if(await evaluate('document.readyState==="complete"'))break;await new Promise(r=>setTimeout(r,300));}
  await check(`P${pass} ${label} ${width}`,`!!document.querySelector('main') && document.documentElement.scrollWidth<=innerWidth && !document.body.innerText.includes('critical error')`);
  if(pass===2){const r=await send('Page.captureScreenshot',{format:'png'});await fs.writeFile(`local-private/deploy/evidence/${label}-${width}.png`,Buffer.from(r.data,'base64'));}
 }
 await visit(base+'/tienda/',1280,900);
 await check('Catálogo y buscador',`!!document.querySelector('#minikids-catalog-search')&&document.querySelectorAll('li.product').length>=17`);
 await evaluate(`document.querySelector('#minikids-catalog-search').value='zzzz-no-existe';document.querySelector('#minikids-catalog-search').dispatchEvent(new Event('input',{bubbles:true}))`);
 await check('Búsqueda en vivo vacía',`getComputedStyle(document.querySelector('.minikids-no-results')).display!=='none'`);
 await evaluate(`document.querySelector('#minikids-catalog-search').value='';document.querySelector('#minikids-catalog-search').dispatchEvent(new Event('input',{bubbles:true}))`);
 await evaluate(`document.querySelector('li.product a.ajax_add_to_cart')?.click()`);
 await new Promise(r=>setTimeout(r,4000));
 await check('Drawer Ajax',`!!document.querySelector('.widget_shopping_cart_content .woocommerce-mini-cart-item')`);
 const product=await evaluate(`document.querySelector('li.product a.woocommerce-LoopProduct-link')?.href||document.querySelector('li.product h2 a')?.href`);
 if(product){await visit(product,390,900);await check('Ficha móvil',`!!document.querySelector('.single-product')&&document.documentElement.scrollWidth<=innerWidth`);}
 await visit(base+'/checkout/',390,900);await check('Checkout móvil',`!!document.querySelector('main')&&document.documentElement.scrollWidth<=innerWidth`);
 await visit(base+'/wp-login.php',1280,900);
 const password=(await fs.readFile('local-private/access-production.txt','utf8')).match(/Contrasena: (.+)/)[1];
 await evaluate(`document.querySelector('#user_login').value='admin';document.querySelector('#user_pass').value=${JSON.stringify(password)};document.querySelector('#wp-submit').click()`);
 await new Promise(r=>setTimeout(r,3000));
 await visit(base+'/wp-admin/options-general.php?page=mkpn-business',1280,900);
 await check('Administración de negocio',`!!document.querySelector('#wpbody-content')&&document.body.innerText.includes('MiniKids')&&!document.querySelector('input[type=password]')?.value`);
 for(const id of [10,143]){
  await visit(base+'/wp-admin/post.php?post='+id+'&action=edit',1280,900);
  for(let i=0;i<60;i++){if(await evaluate('!!window.wp?.data?.select("core/editor")?.getEditedPostContent()'))break;await new Promise(r=>setTimeout(r,250));}
  await check('Gutenberg '+id,`wp.blocks.parse(wp.data.select('core/editor').getEditedPostContent()).every(b=>b.isValid)`);
 }
}finally{await fs.writeFile('brain/production-browser-checks.json',JSON.stringify(checks,null,2));close();}
if(checks.some(c=>!c.passed))process.exitCode=1;
