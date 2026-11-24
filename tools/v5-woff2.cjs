// WOFF2 container using the specification's null transforms and Node's Brotli.
// Input is a Latin glyph subset produced by Windows GlyphTypeface.ComputeSubset.
const fs=require('node:fs'),zlib=require('node:zlib');
const [source,dest]=process.argv.slice(2),input=fs.readFileSync(source);
if(fs.existsSync(dest))throw Error('Refusing to replace a font without backup');
function base128(n){const a=[n&127];while(n>>>=7)a.unshift((n&127)|128);return Buffer.from(a);}
const n=input.readUInt16BE(4),directory=[],tables=[];let sfntSize=12,count=0;
const known=['cmap','head','hhea','hmtx','maxp','name','OS/2','post','cvt ','fpgm','glyf','loca','prep','CFF ','VORG','EBDT','EBLC','gasp','hdmx','kern','LTSH','PCLT','VDMX','vhea','vmtx','BASE','GDEF','GPOS','GSUB','EBSC','JSTF','MATH','CBDT','CBLC','COLR','CPAL','SVG ','sbix','acnt','avar','bdat','bloc','bsln','cvar','fdsc','feat','fmtx','fvar','gvar','hsty','just','lcar','mort','morx','opbd','prop','trak','Zapf','Silf','Glat','Gloc','Feat','Sill'];
const order=Array.from({length:n},(_,i)=>i);
const gi=order.find(i=>input.toString('ascii',12+i*16,16+i*16)==='glyf'),li=order.find(i=>input.toString('ascii',12+i*16,16+i*16)==='loca');
order.splice(order.indexOf(li),1);order.splice(order.indexOf(gi)+1,0,li);
for(const i of order){
  const p=12+i*16,tag=input.toString('ascii',p,p+4),offset=input.readUInt32BE(p+8),length=input.readUInt32BE(p+12);
  if(tag==='DSIG')continue;
  count++;sfntSize+=16+((length+3)&~3);
  // Custom tag index 63; glyf and loca use transform version 3 (null).
  const index=known.indexOf(tag),flag=(index<0?63:index)|((tag==='glyf'||tag==='loca')?192:0);
  directory.push(Buffer.from([flag]));if(index<0)directory.push(input.subarray(p,p+4));directory.push(base128(length));
  tables.push(input.subarray(offset,offset+length));
}
const compressed=zlib.brotliCompressSync(Buffer.concat(tables),{params:{[zlib.constants.BROTLI_PARAM_QUALITY]:11,[zlib.constants.BROTLI_PARAM_MODE]:zlib.constants.BROTLI_MODE_FONT}});
const dir=Buffer.concat(directory),header=Buffer.alloc(48);
header.write('wOF2');input.copy(header,4,0,4);header.writeUInt32BE(48+dir.length+compressed.length,8);header.writeUInt16BE(count,12);header.writeUInt32BE(sfntSize,16);header.writeUInt32BE(compressed.length,20);header.writeUInt16BE(1,24);
fs.writeFileSync(dest,Buffer.concat([header,dir,compressed]));
console.log(source,input.length,'->',dest,fs.statSync(dest).size);
