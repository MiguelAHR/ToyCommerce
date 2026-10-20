import fs from 'node:fs/promises';
import {visit,evaluate,send,close} from 'file:///C:/xampp/htdocs/minkids_dev/tools/browser-review.mjs';
const base='http://localhost:8082/wordpress';
const interactionsOnly=process.env.MINIKIDS_QA_INTERACTIONS_ONLY==='1';
const checks=interactionsOnly?JSON.parse(await fs.readFile('brain/attention-browser-checks.json','utf8')):[];
const evidence='wordpress/wp-content/qa-evidencia';await fs.mkdir(evidence,{recursive:true});
async function check(name,expression){const passed=!!await evaluate(expression);checks.push({name,passed});console.log(`${passed?'OK':'FAIL'} ${name}`);if(!passed)throw Error(name);}
async function wait(expression){for(let n=0;n<120;n++){try{if(await evaluate(expression))return;}catch{}await new Promise(r=>setTimeout(r,250));}throw Error('No cargó el estado esperado');}
async function capture(name){const r=await send('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});await fs.writeFile(`${evidence}/${name}.png`,Buffer.from(r.data,'base64'));}
try{
 if(!interactionsOnly)for(const pass of [1,2])for(const width of [360,390,768,1024,1280,1440]){
  for(const [slug,label] of [['nosotros','nosotros'],['libro-de-reclamaciones','libro']]){
   await visit(base+'/'+slug+'/',width,900);
   await check(`P${pass} ${label} ${width}: sin desborde`,'document.documentElement.scrollWidth<=innerWidth');
   await check(`P${pass} ${label} ${width}: encabezado y contenido`,'!!document.querySelector("h1")&&!!document.querySelector("main")');
   if(label==='libro')await check(`P${pass} formulario ${width}: etiquetas y texto16px`, `[...document.querySelectorAll('.mkpr-form input:not([type=hidden]):not([type=radio]):not([type=checkbox]),.mkpr-form textarea')].filter(e=>!e.closest('[aria-hidden=true]')).every(e=>!!document.querySelector('label[for="'+e.id+'"]')&&parseFloat(getComputedStyle(e).fontSize)>=16)`);
   if(pass===2)await capture(`${label}-${width}`);
  }
 }
 await visit(base+'/tienda/',1440,1000);
 await evaluate('document.querySelector("#minikids-catalog-search").value="zzsinresultadoszz";document.querySelector("#minikids-catalog-search").dispatchEvent(new Event("input",{bubbles:true}))');
 await check('Búsqueda sin resultados y reset',`getComputedStyle(document.querySelector('.minikids-no-results')).display!=='none'&&getComputedStyle(document.querySelector('.minikids-pill-reset')).display!=='none'`);
 await evaluate('document.querySelector(".minikids-pill-reset").click()');
 await check('Limpiar restaura catálogo','document.querySelectorAll("li.product:not(.minikids-product-hidden)").length>0');
 await evaluate('document.querySelector("a.ajax_add_to_cart").click()');
 await wait('document.querySelector("#minikids-cart-drawer")?.classList.contains("active")');
 await check('Drawer Ajax conserva fragmentos','!!document.querySelector("#minikids-cart-drawer .widget_shopping_cart_content .mini_cart_item")');
 await check('Drawer con enlaces de carrito y checkout','!!document.querySelector(".drawer-viewcart-btn")&&!!document.querySelector(".drawer-checkout-btn")');
 await evaluate('document.dispatchEvent(new KeyboardEvent("keydown",{key:"Escape",bubbles:true}))');
 await check('Escape cierra drawer','!document.querySelector("#minikids-cart-drawer").classList.contains("active")');
 await visit(base+'/libro-de-reclamaciones/',390,844);
 await evaluate('document.querySelector("[name=mkpr_minor]").click()');
 await check('Representante obligatorio para menor','document.querySelector("[name=mkpr_guardian]").required');
 await visit(base+'/wp-login.php',1440,1000);
 const password=(await fs.readFile('local-private/access-local.txt','utf8')).match(/Contrasena: (.+)/)[1];
 await evaluate(`document.querySelector('#user_login').value='admin';document.querySelector('#user_pass').value=${JSON.stringify(password)};document.querySelector('#wp-submit').click()`);
 await wait('!!document.querySelector("#adminmenu")');
 for(const id of [10,143]){
  await visit(base+`/wp-admin/post.php?post=${id}&action=edit`);
  await wait(`!!window.wp?.data?.select('core/editor')?.getCurrentPostId()`);
  await check(`Editor ${id}: bloques nativos válidos`,`(()=>{const content=wp.data.select('core/editor').getEditedPostContent();const blocks=wp.blocks.parse(content);const all=b=>b.isValid!==false&&(b.innerBlocks||[]).every(all);return blocks.length>0&&blocks.every(all)})()`);
  await capture(`editor-${id}`);
 }
 await visit(base+'/wp-admin/options-general.php?page=mkpn-business');
 await check('Negocio y correo editables desde el panel',`!!document.querySelector('input[name="mkp_business[email]"]')&&!!document.querySelector('input[name=mkp_mail_enabled]')`);
 await check('La clave no se muestra en el panel','document.querySelector("input[name=brevo_key]").value===""');
 await capture('administracion-negocio');
 const ids=JSON.parse(await fs.readFile('local-private/attention-qa.json','utf8'));
 await visit(base+`/wp-admin/post.php?post=${ids.claims[0]}&action=edit`);
 await check('Atención humana y respuesta disponibles','!!document.querySelector("#mkpr-response")&&!!document.querySelector("[name=mkpr_send_reply]")');
 await visit(base+'/');
 const logout=await evaluate('document.querySelector("#wp-admin-bar-logout a")?.href');if(logout)await visit(logout);
}finally{await fs.writeFile('brain/attention-browser-checks.json',JSON.stringify(checks,null,2));close();}
