// Exercise the production card validation/show functions using synthetic DOM nodes.
const fs=require('fs'),vm=require('vm'),assert=require('assert/strict'),path=require('path');
const source=fs.readFileSync(path.join(__dirname,'../wordpress/wp-content/mu-plugins/minikids-commerce-v9.js'),'utf8');
const code=source.slice(source.indexOf('  function validateNode('),source.indexOf('  function refresh('));
function node(){return {hidden:false,textContent:'',setAttribute(){},removeAttribute(){},focus(){this.focused=true},scrollIntoView(){},classList:{toggle(){}}};}
const cards=[0,1,2].map(()=>({card:node(),body:node(),error:node(),edit:node(),waiting:node(),short:node()})),personal=node();
const email=Object.assign(node(),{type:'email',required:true,value:'',willValidate:false,disabled:false,getClientRects:()=>[{}],closest:()=>null});
const address=Object.assign(node(),{type:'text',required:true,value:'',willValidate:false,disabled:false,getClientRects:()=>[{}],closest:()=>null});
const context={cards,personal,mk9:{needsShipping:false},emailDone:false,current:0,completed:-1,one:(s,r)=>s==='#billing_email'?email:s==='#billing_address_1'?address:null,all:(s,r)=>r===cards[0].body?[email]:r===cards[1].body?[address]:[]};
vm.runInNewContext(code+';globalThis.api={show,validate,validateNode,complete:n=>completed=n};',context);
const {api}=context,checks=[];
api.show(0);assert(!cards[0].body.hidden&&cards[1].body.hidden&&cards[2].body.hidden);checks.push('Only Tus datos starts open');
assert(!api.validateNode(email,cards[0]));assert.equal(cards[0].error.textContent,'Este campo es obligatorio.');checks.push('Missing email has required Spanish error');
email.value='invalid';assert(!api.validateNode(email,cards[0]));checks.push('Malformed email cannot continue');
email.value='qa@example.test';assert(api.validate(0));api.complete(0);api.show(1);assert(cards[0].body.hidden&&!cards[1].body.hidden&&cards[2].body.hidden&&!cards[0].edit.hidden);checks.push('Completed data becomes summary with Editar; Envío alone open');
assert(!api.validate(1));assert(!cards[1].body.hidden&&cards[2].body.hidden);checks.push('Missing address cannot advance');
address.value='QA without order';assert(api.validate(1));api.complete(1);api.show(2);assert(cards[0].body.hidden&&cards[1].body.hidden&&!cards[2].body.hidden);checks.push('Pago opens after contact and shipping validation');
api.show(0);assert(!cards[0].body.hidden&&cards[1].body.hidden&&cards[2].body.hidden);checks.push('Editar reopens one card and hides Pago');
const result={passed:checks.length,checks,scope:'Production validation/show functions in Node VM with synthetic nodes. No browser field layout, real click, focus navigation or payment/Ajax interaction.'};
fs.writeFileSync(path.join(__dirname,'../../diseno_minikids/v5/pasos-checks-V9.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
