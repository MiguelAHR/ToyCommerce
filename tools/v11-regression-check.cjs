// Reuse existing synthetic checks with V11 output names; preserve prior evidence.
const fs=require('fs'),path=require('path');
for(const name of ['v10-modal-check.cjs','v9-timer-check.cjs','v9-flow-check.cjs']){
 let code=fs.readFileSync(path.join(__dirname,name),'utf8');
 code=code.replaceAll('modales-checks-V10.json','modales-checks-V11-regresion.json').replaceAll('timer-checks-V9.json','timer-checks-V11-regresion.json').replaceAll('pasos-checks-V9.json','pasos-checks-V11-regresion.json');
 new Function('require','__dirname',code)(require,__dirname);
}
