// Deterministic logic tests; no browser or CSS animation execution.
const fs=require('fs'),vm=require('vm'),assert=require('assert/strict'),path=require('path');
const root=path.resolve(__dirname,'../wordpress/wp-content/mu-plugins'),checks=[];
function runtime(){
 let time=0,id=0;const jobs=new Map(),events={},classes=new Set(),rootClasses=new Set();
 const add=(target,type,fn)=>{(events[target+':'+type]??=[]).push(fn);};
 const context={performance:{now:()=>time,getEntriesByType:()=>[{type:'navigate'}]},URL,location:{origin:'http://localhost:8082'},document:{referrer:'',readyState:'loading',visibilityState:'visible',documentElement:{classList:{add:c=>rootClasses.add(c)}},getElementById:()=>({classList:{add:c=>classes.add(c)}}),addEventListener:(t,f)=>add('doc',t,f)},window:{addEventListener:(t,f)=>add('win',t,f)},setTimeout:(f,delay)=>{jobs.set(++id,{f,at:time+delay});return id;},clearTimeout:i=>jobs.delete(i)};
 function advance(to){while(true){let next=[...jobs.entries()].filter(([,j])=>j.at<=to).sort((a,b)=>a[1].at-b[1].at)[0];if(!next)break;time=next[1].at;jobs.delete(next[0]);next[1].f();}time=to;}
 const fire=(target,type,e={})=>(events[target+':'+type]||[]).forEach(f=>f(e));
 return{context,advance,fire,classes,rootClasses,jobs};
}
const loader=fs.readFileSync(path.join(root,'minikids-loader-v7.js'),'utf8');
function loaderCase(name,fn){const r=runtime();fn(r);checks.push(name);}
loaderCase('first load waits minimum 400ms',r=>{vm.runInNewContext(loader,r.context);r.advance(100);r.fire('win','load');r.advance(399);assert(!r.classes.has('mk6-ready'));r.advance(400);assert(r.classes.has('mk6-ready'));});
loaderCase('internal fast load stays hidden without show class',r=>{r.context.document.referrer='http://localhost:8082/wordpress/';vm.runInNewContext(loader,r.context);r.advance(110);r.fire('win','load');r.advance(500);assert(r.rootClasses.has('mk7-fast-nav'));assert(r.classes.has('mk6-ready'));assert(!r.classes.has('mk7-show'));});
loaderCase('internal slow load shows at400, remains minimum400',r=>{r.context.document.referrer='http://localhost:8082/wordpress/';vm.runInNewContext(loader,r.context);r.advance(400);assert(r.classes.has('mk7-show'));r.advance(500);r.fire('win','load');r.advance(799);assert(!r.classes.has('mk6-ready'));r.advance(800);assert(r.classes.has('mk6-ready'));});
loaderCase('no load event: fade begins2250, CSS ends2500',r=>{vm.runInNewContext(loader,r.context);r.advance(2249);assert(!r.classes.has('mk6-ready'));r.advance(2250);assert(r.classes.has('mk6-ready'));});
loaderCase('BFCache pageshow immediately dismisses veil',r=>{vm.runInNewContext(loader,r.context);r.advance(50);r.fire('win','pageshow',{persisted:true});assert(r.classes.has('mk6-ready'));});
loaderCase('visibility return dismisses after minimum',r=>{vm.runInNewContext(loader,r.context);r.advance(600);r.fire('doc','visibilitychange');assert(r.classes.has('mk6-ready'));});
loaderCase('local debug keeps loader visible and schedules no exit',r=>{r.context.window.mk7LoaderDebug=true;vm.runInNewContext(loader,r.context);r.advance(5000);assert(r.rootClasses.has('mk7-loader-debug'));assert(!r.classes.has('mk6-ready'));assert.equal(r.jobs.size,0);});
const source=fs.readFileSync(path.join(root,'minikids-commerce-v6.js'),'utf8'),timer=source.slice(source.indexOf('  // V7 fixed absolute deadline.'),source.lastIndexOf('\n });'));
function checkout(initial,ttl=600){
 const r=runtime(),key='mk6-secure-window',storage=new Map(initial?[[key,JSON.stringify(initial)]]:[]),formEvents={},jqueryEvents={},redirects=[],notices=[],timerNode={textContent:''};let interval;
 const form={inert:false,addEventListener:(t,f)=>formEvents[t]=f,before:m=>notices.push(m)};
 const NativeDate=Date;class Clock extends NativeDate{static now(){return 1000000+r.context.performance.now();}}
 Object.assign(r.context,{Date:Clock,mk6:{ttl,cart:'http://localhost:8082/wordpress/cart/'},sessionStorage:{getItem:k=>storage.get(k)||null,setItem:(k,v)=>storage.set(k,v)},form,one:()=>timerNode,make:()=>({setAttribute(){}}),setInterval:f=>{interval=f;},$:()=>({on:(t,f)=>jqueryEvents[t]=f})});
 r.context.location.replace=u=>redirects.push(u);
 vm.runInNewContext(timer,r.context);
 return {...r,form,formEvents,jqueryEvents,storage,redirects,notices,timerNode,tick:()=>interval(),state:()=>JSON.parse(storage.get(key))};
}
let r=checkout();assert.equal(r.state().end-r.state().start,600000);assert.equal(r.timerNode.textContent,'10:00');assert.equal(r.jqueryEvents.checkout_place_order(),undefined);checks.push('normal checkout has fixed600000ms deadline and preserves earlier validator result');
r.advance(300000);r.formEvents.input({isTrusted:true});const edited=r.state();assert.equal(edited.end,1600000);assert(edited.activity);let reloaded=checkout(edited);assert.equal(reloaded.state().end,edited.end);checks.push('trusted edits and reload do not extend original deadline');
r=checkout(null,'15');assert.equal(r.timerNode.textContent,'00:15');r.advance(15000);r.fire('win','pageshow');assert(r.form.inert);assert.equal(r.notices.length,1);assert.equal(r.redirects.length,0);r.advance(16199);assert.equal(r.redirects.length,0);r.advance(16200);assert.equal(r.redirects.length,1);assert(r.redirects[0].includes('/cart/?mk-session-expired=1'));assert(r.state().end<=1015000);checks.push('15second deadline shows alert, closes form and redirects after1200ms; expired record retained');
r=checkout({end:999999,activity:true});assert(r.form.inert);assert.equal(r.notices.length,1);assert.equal(r.jqueryEvents.checkout_place_order(),false);checks.push('expired stored window is not revived and blocks order event');
r=checkout({start:1000000,end:1600000,activity:false});r.advance(600000);r.fire('doc','visibilitychange');assert(r.form.inert);checks.push('background return checks absolute expiry');
r=checkout({end:1600000,activity:true});assert.equal(r.state().start,1000000);assert.equal(r.state().end,1600000);checks.push('legacy V6 end is migrated without extending it');
const result={passed:checks.length,checks,scope:'Node VM logic with simulated clock/events/storage; does not execute browser layout, paint, getAnimations, Woo DOM/events or real navigation.'};
fs.writeFileSync(path.resolve(__dirname,'../../diseno_minikids/v5/timer-checks-V7.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
