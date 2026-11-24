const fs=require('fs'),vm=require('vm'),assert=require('assert');
const path=require('path'),root=path.resolve(__dirname,'..'),source=fs.readFileSync(path.join(root,'wordpress/wp-content/mu-plugins/minikids-commerce-v18.js'),'utf8');
const checks={};
function fixture({width=390,height=600,content='width=device-width, initial-scale=1',authExists=true,visualExists=true}={}){
 const events={},vvEvents={},authEvents={},props={};let scrolls=0;
 const viewport={content},auth={open:false,style:{setProperty:(k,v,p)=>props[k]=[v,p],removeProperty:k=>delete props[k]},contains:()=>true,addEventListener:(k,v)=>authEvents[k]=v};
 const visual={height,addEventListener:(k,v)=>vvEvents[k]=v};
 const document={readyState:'complete',activeElement:{scrollIntoView:()=>scrolls++},querySelector:()=>viewport,getElementById:()=>authExists?auth:null};
 const window={innerWidth:width,visualViewport:visualExists?visual:null,addEventListener:(k,v)=>events[k]=v};
 vm.runInNewContext(source,{document,window});return{viewport,auth,visual,window,props,events,vvEvents,authEvents,get scrolls(){return scrolls;}};
}
let f=fixture();checks.cover_added=f.viewport.content.endsWith('viewport-fit=cover');checks.keyboard_height=f.props['max-height'][0]==='576px';checks.closed_dialog_does_not_scroll=f.scrolls===0;
f.auth.open=true;f.visual.height=330;f.visual.offsetTop=80;f.vvEvents.resize();checks.keyboard_resize=f.props['max-height'][0]==='306px';checks.open_focus_scrolled=f.scrolls===1;
checks.keyboard_position_in_visual_viewport=f.props.top[0]==='92px'&&f.props.bottom[0]==='auto'&&f.props['margin-top'][0]==='0';
f.window.innerWidth=1200;f.events.resize();checks.desktop_limit_removed=Object.keys(f.props).length===0;
f=fixture({content:'width=device-width, viewport-fit=contain'});checks.existing_cover_normalized=f.viewport.content==='width=device-width, viewport-fit=cover';
f=fixture({visualExists:false});checks.no_visual_viewport_supported=!('max-height' in f.props);
fixture({authExists:false});checks.no_auth_supported=true;
assert(Object.values(checks).every(Boolean),JSON.stringify(checks));
const result={checks,passed:Object.values(checks).filter(Boolean).length,total:Object.keys(checks).length,scope:'Node VM with mocked viewport/dialog. No mobile keyboard, DOM geometry or native focus certification.'};
fs.writeFileSync(path.join(root,'../diseno_minikids/v5/cliente-V18.json'),JSON.stringify(result,null,2));console.log(result);
