(function(){
 'use strict';
 const root=document.documentElement,started=performance.now();
 if(window.mk7LoaderDebug){root.classList.add('mk7-loader-debug');return;}
 const nav=performance.getEntriesByType('navigation')[0];
 let internal=false;try{internal=nav?.type==='navigate'&&new URL(document.referrer).origin===location.origin;}catch(_){}
 if(internal)root.classList.add('mk7-fast-nav');
 let shown=internal?null:started,done=false,hideTimer;
 const el=()=>document.getElementById('mk6-loader');
 function finish(immediate=false){
  if(done)return;
  if(!immediate&&shown!==null&&performance.now()-shown<400){clearTimeout(hideTimer);hideTimer=setTimeout(()=>finish(),400-(performance.now()-shown));return;}
  done=true;clearTimeout(hideTimer);el()?.classList.add('mk6-ready');
 }
 if(internal)setTimeout(()=>{if(!done&&document.readyState!=='complete'){shown=performance.now();el()?.classList.add('mk7-show');}else finish(true);},400);
 window.addEventListener('load',()=>finish(),{once:true});
 window.addEventListener('pageshow',e=>{if(e.persisted)finish(true);else if(document.readyState==='complete')finish();});
 document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')finish();});
 document.addEventListener('DOMContentLoaded',()=>{if(done)el()?.classList.add('mk6-ready');if(document.readyState==='complete')finish();},{once:true});
 // Start fading at 2250ms; entirely hidden by 2500ms, even when load never fires.
 setTimeout(()=>finish(true),Math.max(0,2250-(performance.now()-started)));
})();
