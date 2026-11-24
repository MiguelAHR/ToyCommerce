// Unit checks of the actual checkout transitions, in Node without a browser.
const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('wordpress/wp-content/mu-plugins/minikids-commerce-v9.js','utf8');
const extract=(name,end)=>source.slice(source.indexOf('  function '+name+'('),source.indexOf(end,source.indexOf('  function '+name+'(')));
const element=()=>({hidden:false,disabled:false,textContent:'',innerHTML:'',attrs:{},setAttribute(k,v){this.attrs[k]=v;},classList:{toggle(){}},scrollIntoView(){},focus(){}});
const cards=Array.from({length:3},()=>({card:element(),body:element(),edit:element(),waiting:element(),short:element(),error:element(),num:element(),icon:element()}));
cards.forEach(c=>c.body.contains=x=>x===c.body);
const context={cards,completed:-1,current:0,emailDone:false,personal:element(),nextData:element(),mk9:{icons:{check:'CHECK',lock:'LOCK',user:'USER',truck:'TRUCK'}},document:{},matchMedia:()=>({matches:true})};
context.one=(selector,root)=>{
 const card=cards.find(c=>c.card===root);
 if(selector==='.mk9-number')return card.num;
 if(selector==='.mk9-card-icon')return card.icon;
 return {value:'value',focus(){}};
};
vm.createContext(context);
vm.runInContext(extract('show','  function refresh'),context);
vm.runInContext(extract('invalidate','  function validateNode'),context);
vm.runInContext(extract('revealPersonal','  // Invalidate'),context);
context.show(0);
assert.equal(context.personal.hidden,true);assert.equal(context.nextData.textContent,'Continuar');
assert.equal(cards[1].card.attrs['aria-disabled'],'true');assert.equal(cards[2].card.attrs['aria-disabled'],'true');
context.show(2);assert.equal(context.current,0);
context.emailDone=true;context.revealPersonal();assert.equal(context.personal.hidden,false);assert.equal(context.nextData.textContent,'Continuar a envío');
context.completed=0;context.show(1);assert.equal(context.current,1);assert.equal(cards[0].num.innerHTML,'CHECK');assert.equal(cards[0].edit.hidden,false);assert.equal(cards[1].card.attrs['aria-disabled'],'false');assert.equal(cards[2].edit.disabled,true);
context.completed=1;context.show(2);assert.equal(context.current,2);assert.equal(cards[1].num.innerHTML,'CHECK');
context.invalidate({target:cards[0].body});assert.equal(context.completed,-1);assert.equal(context.current,0);assert.equal(cards[2].card.attrs['aria-disabled'],'true');
console.log('PASS actual transition functions: email reveal, locks, completed checks, edit and dependent invalidation (16 assertions).');
