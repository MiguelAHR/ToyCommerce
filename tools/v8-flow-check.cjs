// Run unchanged production timer in the established deterministic VM harness.
const fs=require('fs'),vm=require('vm'),assert=require('assert/strict'),path=require('path');
const root=path.resolve(__dirname,'../wordpress/wp-content/mu-plugins');
let harness=fs.readFileSync(path.join(__dirname,'v7-timer-check.cjs'),'utf8').replace('timer-checks-V7.json','timer-checks-V8.json');
vm.runInNewContext(harness,{require,__dirname,console,URL});
const source=fs.readFileSync(path.join(root,'minikids-commerce-v6.js'),'utf8');
const flow=source.slice(source.indexOf('  let current=0;'),source.indexOf('  // V7 fixed absolute deadline.'));
function node(){return {hidden:true,disabled:false,textContent:'',events:{},setAttribute(k,v){this[k]=v;},addEventListener(k,f){this.events[k]=f;},focus(){this.focused=true;},classList:{add(){}}};}
const form=node(),nav=node(),controls=node(),error=node(),back=node(),next=node(),buttons=[node(),node(),node()],panels=[node(),node(),node()],headings=panels.map(node),handlers={},checks=[];
buttons.forEach((b,i)=>b.dataset={step:String(i)});
const fields=panels.map((p,i)=>({disabled:false,type:i===0?'email':'text',required:true,value:'',willValidate:false,validity:{valid:true},getClientRects:()=>p.hidden?[]:[{}],closest:()=>null,setAttribute(){},focus(){this.focused=true;}}));
const context={form,nav,controls,panels,document:{body:{}},one:(s,p)=>s==='h2'?headings[panels.indexOf(p)]:({'.mk6-step-error':error,'.mk6-back':back,'.mk6-next':next}[s]||null),all:(s,p)=>p===nav?buttons:s==='input,select,textarea'?[fields[panels.indexOf(p)]]:[],$:target=>({on:(event,fn)=>handlers[event]=fn,trigger(){}})};
vm.runInNewContext(flow,context);
assert(!panels[0].hidden&&panels[1].hidden&&panels[2].hidden);assert.equal(handlers.checkout_place_order(),false);checks.push('Datos only; order submission blocked');
next.events.click();assert(!panels[0].hidden);assert(error.textContent);checks.push('Required contact data prevents advancing');
fields[0].value='owner@example.test';next.events.click();assert(panels[0].hidden&&!panels[1].hidden&&panels[2].hidden);assert.equal(handlers.checkout_place_order(),false);checks.push('Entrega only; payment panel hidden and order submission blocked');
next.events.click();assert(panels[2].hidden);checks.push('Required delivery data prevents advancing');
fields[1].value='QA local';next.events.click();assert(!panels[2].hidden&&panels[0].hidden&&panels[1].hidden&&next.hidden);assert.equal(handlers.checkout_place_order(),true);checks.push('Pago only; native order event permitted, native server validates terms');
back.events.click();assert(panels[2].hidden&&!panels[1].hidden);assert.equal(handlers.checkout_place_order(),false);checks.push('Going back hides payment and blocks order event again');
const result={passed:checks.length,checks,scope:'Production step functions in Node VM with synthetic DOM nodes. HTTP separately checks native button ancestry; native checkout rejected missing terms and incomplete data before creating its only QA order. No real browser.'};
fs.writeFileSync(path.resolve(__dirname,'../../diseno_minikids/v5/pasos-checks-V8.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
