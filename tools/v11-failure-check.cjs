// Actual readiness watchdog in a synthetic VM. No rendering or browser certification.
const fs=require('fs'),path=require('path'),vm=require('vm'),assert=require('assert/strict');
const source=fs.readFileSync(path.join(__dirname,'../wordpress/wp-content/mu-plugins/minikids-commerce-v9.js'),'utf8');
const code=source.slice(source.indexOf('  function failIfUnready()'),source.indexOf('  if(mk9.state?.expired)'));
function fixture(now=0,start=0){
 const classes=new Set(),callbacks=[],events={};
 const body={classList:{contains:x=>classes.has(x),add:x=>classes.add(x)}};
 const context={document:{body},one:()=>({addEventListener:(name,fn)=>events[name]=fn}),performance:{now:()=>now,getEntriesByType:()=>[{responseStart:start}]},setTimeout:(fn,delay)=>callbacks.push({fn,delay})};
 vm.runInNewContext(code,context);return {classes,callbacks,events};
}
const checks={};
let f=fixture();assert.equal(f.callbacks[0].delay,4000);assert(!f.classes.has('mk-secure-failed'));checks.neutral_before_timeout=true;
f.callbacks[0].fn();assert(f.classes.has('mk-secure-failed'));checks.unready_at_4s_fails=true;
f=fixture();f.classes.add('mk-secure-ready');f.callbacks[0].fn();assert(!f.classes.has('mk-secure-failed'));checks.ready_suppresses_timeout=true;
f=fixture(1100,1000);assert.equal(f.callbacks[0].delay,3900);checks.elapsed_since_response_accounted=true;
f=fixture();f.events.animationend({animationName:'unrelated'});assert(!f.classes.has('mk-secure-failed'));f.events.animationend({animationName:'mk11-watch'});assert(f.classes.has('mk-secure-failed'));checks.css_watch_event_fails_only_when_unready=true;
f=fixture();f.classes.add('mk-secure-ready');f.events.animationend({animationName:'mk11-watch'});assert(!f.classes.has('mk-secure-failed'));checks.ready_suppresses_animation_watch=true;
const result={passed:Object.keys(checks).length,checks,scope:'Production watchdog in Node VM, synthetic clock/events; CSS visibility and first paint require Chrome.'};
fs.writeFileSync(path.join(__dirname,'../../diseno_minikids/v5/fallo-checks-V11.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result));
