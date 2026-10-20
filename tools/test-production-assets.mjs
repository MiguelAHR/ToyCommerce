import fs from 'node:fs/promises';
import {visit,evaluate,send,close} from 'file:///C:/xampp/htdocs/minkids_dev/tools/browser-review.mjs';
const base='https://minikids.vitorstream.shop';const checks=[];
async function check(name,expression){const passed=!!await evaluate(expression);checks.push({name,passed});console.log(`${passed?'OK':'FAIL'} ${name}`);}
try{
 for(const pass of [1,2])for(const width of [360,390,768,1024,1280,1440]){
  await visit(base+'/',width,900);
  await check(`P${pass} logo y fotos ${width}`,`(async()=>{const logo=[...document.querySelectorAll('.site-branding .site-title a')].find(e=>e.getBoundingClientRect().width>0);const bg=getComputedStyle(logo).backgroundImage;const url=bg.slice(5,-2);const img=new Image();img.src=url;await img.decode();return img.naturalWidth>0&&url.startsWith('${base}/wp-content/uploads/')&&[...document.images].filter(i=>i.complete).every(i=>i.naturalWidth>0)&&document.documentElement.scrollWidth<=innerWidth;})()`);
  if(pass===2){const r=await send('Page.captureScreenshot',{format:'png'});await fs.writeFile(`local-private/deploy/evidence/inicio-${width}.png`,Buffer.from(r.data,'base64'));}
 }
 const products=await (await fetch(base+'/wp-json/wc/store/v1/products?per_page=1')).json();
 for(const pass of [1,2]){
  await visit(products[0].permalink,390,900);
  await check(`P${pass} ficha producto móvil`,`!!document.querySelector('.single-product')&&document.documentElement.scrollWidth<=innerWidth`);
  const r=await send('Page.captureScreenshot',{format:'png'});await fs.writeFile(`local-private/deploy/evidence/producto-${pass}-390.png`,Buffer.from(r.data,'base64'));
 }
}finally{await fs.writeFile('brain/production-assets-checks.json',JSON.stringify(checks,null,2));close();}
if(checks.some(c=>!c.passed))process.exitCode=1;
