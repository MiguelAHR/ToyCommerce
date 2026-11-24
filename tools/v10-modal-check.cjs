// Synthetic event/DOM model, not a browser or visual/accessibility certification.
const vm=require('node:vm'),fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const root=path.resolve(__dirname,'..'),out=path.resolve(root,'../diseno_minikids/v5'),checks={};
function check(name,fn){fn();checks[name]=true;console.log('PASS '+name);}
class Element {
 constructor(kind,doc){this.kind=kind;this.doc=doc;this.dataset={};this.attrs={};this.children=[];this.parent=null;this.isConnected=true;this.disabled=false;this.tabIndex=0;this.classes=new Set();this.classList={add:x=>this.classes.add(x),remove:x=>this.classes.delete(x),contains:x=>this.classes.has(x)};}
 matches(s){return s==='.mk8-dialog'&&this.kind==='dialog';}
 contains(e){return this===e||this.children.some(x=>x.contains(e));}
 closest(s){if(s==='[data-mk8-dialog]'&&this.dataset.mk8Dialog)return this;if(s==='.mk8-close,.mk8-dismiss'&&['close','dismiss'].includes(this.kind))return this;return this.parent?.closest(s)||null;}
 setAttribute(k,v){this.attrs[k]=v;} removeAttribute(k){delete this.attrs[k];} hasAttribute(k){return k in this.attrs;}
 append(el){this.children.push(el);el.parent=this;}
 remove(){this.parent.children=this.parent.children.filter(x=>x!==this);this.isConnected=false;}
 focus(){this.doc.activeElement=this;} getClientRects(){return [{}];}
 querySelector(s){return this.children.find(x=>x.kind==='close');}
 querySelectorAll(){return this.children;}
 getBoundingClientRect(){return {left:100,right:600,top:100,bottom:500};}
}
const events={},windows={},nodes={};
const document={activeElement:null,getElementById:id=>nodes[id]||null,addEventListener:(n,fn)=>(events[n]??=[]).push(fn),createElement:()=>new Element('overlay',document)};
document.body=new Element('body',document);
const styles={overflow:'scroll'},priority={overflow:'important'};document.body.style={getPropertyValue:k=>styles[k]||'',getPropertyPriority:k=>priority[k]||'',setProperty:(k,v,p)=>{styles[k]=v;priority[k]=p;}};
const window={addEventListener:(n,fn)=>(windows[n]??=[]).push(fn)};
const fire=(name,target,extra={})=>{const e={target,preventDefault(){this.prevented=true;},...extra};(events[name]||[]).forEach(fn=>fn(e));return e;};
const context={document,window,Element,Error};vm.runInNewContext(fs.readFileSync(path.join(root,'wordpress/wp-content/mu-plugins/minikids-commerce-v8.js'),'utf8'),context);
const buttons={};for(const id of ['availability','payment','delivery','returns']){
 const b=new Element('button',document);b.dataset.mk8Dialog=id;buttons[id]=b;
 const d=new Element('dialog',document);nodes['mk8-'+id]=d;
 for(const type of ['close','dismiss'])d.append(new Element(type,document));
 d.showModal=()=>d.setAttribute('open','');d.close=()=>{d.removeAttribute('open');};
}
check('late_dialogs_all_four_open_escape_restore_scroll_focus',()=>{
 for(const [id,b] of Object.entries(buttons)){fire('click',b);assert(nodes['mk8-'+id].hasAttribute('open'));assert.equal(styles.overflow,'hidden');assert.equal(document.activeElement,nodes['mk8-'+id].children[0]);fire('keydown',document.activeElement,{key:'Escape'});assert(!nodes['mk8-'+id].hasAttribute('open'));assert.equal(document.activeElement,b);assert.equal(styles.overflow,'scroll');assert.equal(priority.overflow,'important');}
});
check('nested_svg_target_and_close_x',()=>{const svg=new Element('svg',document);buttons.payment.append(svg);fire('click',svg);const d=nodes['mk8-payment'];assert(d.hasAttribute('open'));fire('click',d.children[0]);assert(!d.hasAttribute('open'));});
check('native_backdrop_not_inside',()=>{const d=nodes['mk8-availability'];fire('click',buttons.availability);fire('click',d,{clientX:200,clientY:200});assert(d.hasAttribute('open'));fire('click',d,{clientX:0,clientY:0});assert(!d.hasAttribute('open'));});
check('native_cancel_event',()=>{fire('click',buttons.payment);assert(fire('cancel',nodes['mk8-payment']).prevented);assert.equal(document.activeElement,buttons.payment);});
check('fallback_missing_showModal_overlay_dismiss',()=>{const d=nodes['mk8-delivery'];delete d.showModal;fire('click',buttons.delivery);assert(d.hasAttribute('open')&&d.classList.contains('is-open'));assert.equal(document.body.children.length,1);fire('click',d.children[1]);assert(!d.hasAttribute('open'));assert.equal(document.body.children.length,0);});
check('fallback_throw_showModal_overlay_click',()=>{const d=nodes['mk8-returns'];d.showModal=()=>{throw Error('failed');};fire('click',buttons.returns);assert(d.classList.contains('is-open'));fire('click',document.body.children[0]);assert(!d.hasAttribute('open'));assert.equal(document.activeElement,buttons.returns);});
check('fallback_traps_tab_shift_tab_and_programmatic_focus',()=>{const d=nodes['mk8-delivery'];fire('click',buttons.delivery);fire('keydown',d.children[0],{key:'Tab',shiftKey:true});assert.equal(document.activeElement,d.children[1]);fire('keydown',d.children[1],{key:'Tab'});assert.equal(document.activeElement,d.children[0]);buttons.payment.focus();fire('focusin',buttons.payment);assert.equal(document.activeElement,d.children[0]);fire('keydown',d.children[0],{key:'Escape'});});
check('missing_dialog_noop',()=>{const b=new Element('button',document);b.dataset.mk8Dialog='absent';fire('click',b);assert.equal(styles.overflow,'scroll');});
check('replaced_button_resolved_at_click',()=>{const replacement=new Element('button',document);replacement.dataset.mk8Dialog='payment';buttons.payment.isConnected=false;fire('click',replacement);assert(nodes['mk8-payment'].hasAttribute('open'));fire('keydown',nodes['mk8-payment'],{key:'Escape'});assert.equal(document.activeElement,replacement);});
check('back_pageshow_still_opens_and_pagehide_cleans',()=>{windows.pageshow.forEach(fn=>fn());fire('click',buttons.availability);windows.pagehide.forEach(fn=>fn());assert(!nodes['mk8-availability'].hasAttribute('open'));windows.pageshow.forEach(fn=>fn());fire('click',buttons.availability);assert(nodes['mk8-availability'].hasAttribute('open'));fire('keydown',nodes['mk8-availability'],{key:'Escape'});});
check('external_close_event_restores',()=>{fire('click',buttons.availability);nodes['mk8-availability'].close();fire('close',nodes['mk8-availability']);assert.equal(styles.overflow,'scroll');assert.equal(document.activeElement,buttons.availability);});
check('queued_old_close_does_not_close_new_dialog',()=>{fire('click',buttons.availability);fire('click',buttons.payment);fire('close',nodes['mk8-availability']);assert(nodes['mk8-payment'].hasAttribute('open'));fire('keydown',nodes['mk8-payment'],{key:'Escape'});});
check('original_v8_registers_zero_clicks_when_dialogs_not_yet_parsed',()=>{
 let registered=0;
 const originalDocument={querySelectorAll:()=>Object.values(buttons),getElementById:()=>null};
 for(const button of Object.values(buttons))button.addEventListener=()=>registered++;
 vm.runInNewContext(fs.readFileSync(path.resolve(root,'../local-private/v10-backups/minikids-commerce-v8.js'),'utf8'),{document:originalDocument});
 assert.equal(registered,0);
});
fs.writeFileSync(path.join(out,'modales-checks-V10.json'),JSON.stringify({checks,total:Object.keys(checks).length,scope:'Synthetic VM model only. No browser; focus/top layer/rendering require real Chrome.'},null,2));
