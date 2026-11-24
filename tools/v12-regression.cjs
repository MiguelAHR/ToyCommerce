const fs=require('fs'),path=require('path');
for(const name of ['v10-modal-check.cjs','v9-timer-check.cjs','v9-flow-check.cjs','v11-failure-check.cjs']){
 let code=fs.readFileSync(path.join(__dirname,name),'utf8');
 code=code.replaceAll('modales-checks-V10.json','modales-checks-V12-regresion.json').replaceAll('timer-checks-V9.json','timer-checks-V12-regresion.json').replaceAll('pasos-checks-V9.json','pasos-checks-V12-regresion.json').replaceAll('fallo-checks-V11.json','fallo-checks-V12-regresion.json');
 new Function('require','__dirname',code)(require,__dirname);
}
