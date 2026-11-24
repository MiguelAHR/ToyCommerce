// Execute actual V9 controller functions in a synthetic VM, without a browser.
const fs=require('fs'),path=require('path'),vm=require('vm'),assert=require('assert/strict');
const source=fs.readFileSync(path.join(__dirname,'../wordpress/wp-content/mu-plugins/minikids-commerce-v9.js'),'utf8');
const code=source.slice(source.indexOf('  let expired=false'),source.indexOf('  form.inert=true;summary.append(review);'));
const checks=[];
function fixture(remaining=600){
 let mono=0,wall=1000000,ready=false,mode='ok',serverLeft=remaining,navigations=[];
 const timer={textContent:''},paragraph={textContent:''},fail={setAttribute(){}};
 const form={inert:true};
 const context={URL,Math,String,Date:{now:()=>wall},performance:{now:()=>mono},mk9:{cart:'http://localhost:8082/wordpress/cart/',status:'/status',state:{remaining,token:'server-token'}},form,URLSearchParams,
  one:(s,r)=>s==='#mk6-timer'?timer:s==='#mk9-failsafe'?fail:paragraph,
  document:{body:{classList:{add(){ready=true},remove(){ready=false}}}},
  location:{replace:u=>navigations.push(u)},setTimeout:fn=>context.pending=fn,
  fetch:async()=>{if(mode==='network')throw Error();return {ok:mode==='ok',json:async()=>({success:mode==='ok',data:{remaining:serverLeft}})};}
 };
 vm.runInNewContext(code+';globalThis.api={tick,verify,expired:()=>expired,verified:()=>verified};',context);
 return {c:context,timer,form,paragraph,get ready(){return ready},set mode(v){mode=v},set serverLeft(v){serverLeft=v},advance(ms){mono+=ms;wall+=ms},shiftWall(ms){wall+=ms},navigations};
}
(async()=>{
 let f=fixture();assert.equal(f.c.api.tick(),600);assert.equal(f.timer.textContent,'10:00');await f.c.api.verify();assert(f.ready&&!f.form.inert);checks.push('Server window 600 s; ready only after successful server verification');
 f.advance(2000);assert.equal(f.c.api.tick(),598);f.serverLeft=600;await f.c.api.verify();assert.equal(f.c.api.tick(),598);checks.push('Server response cannot increase an existing deadline');
 f.shiftWall(-3600000);f.advance(598000);assert.equal(f.c.api.tick(),0);assert(f.form.inert&&!f.ready&&f.c.api.expired());checks.push('Moving wall clock backwards cannot defeat monotonic expiry');
 assert(f.paragraph.textContent.includes('venció'));f.c.pending();assert(f.navigations[0].includes('/cart/?mk-session-expired=1'));checks.push('Expiry hides/inerts form, announces and returns to preserved cart');
 f=fixture(15);f.advance(15000);f.c.api.tick();assert(f.c.api.expired());checks.push('Local 15 s expiry uses same controller');
 f=fixture();f.mode='rejected';await f.c.api.verify();assert(f.c.api.expired()&&!f.ready);checks.push('Server rejection closes a stale tab even before its visual deadline');
 f=fixture();f.mode='network';await f.c.api.verify();assert(!f.ready&&f.form.inert&&!f.c.api.verified());checks.push('Network or JSON failure leaves form blocked and static fallback visible');
 f=fixture();await f.c.api.verify();f.serverLeft=5;await f.c.api.verify();assert.equal(f.c.api.tick(),5);checks.push('Server status can shorten the remaining window');
 f=fixture();f.shiftWall(601000);f.c.api.tick();assert(f.c.api.expired());checks.push('Forward wall clock or elapsed background time also expires');
 const result={passed:checks.length,checks,scope:'Unchanged production timer/status functions in Node VM, synthetic DOM/fetch/clocks. No layout, navigation, browser painting or real BFCache.'};
 fs.writeFileSync(path.join(__dirname,'../../diseno_minikids/v5/timer-checks-V9.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
})().catch(e=>{console.error(e);process.exitCode=1});
