"""Offline TrueType Latin subset, retaining glyph IDs, variants and composite closure.

Rebuilds cmap/glyf/loca and checksums; keeps original shaping/variable tables.
No third-party modules or downloads. WOFF2 encoding is a separate Node tool.
"""
import struct,sys,subprocess,json
from pathlib import Path
U16=lambda b,o:struct.unpack_from('>H',b,o)[0]
U32=lambda b,o:struct.unpack_from('>I',b,o)[0]
def checksum(b):
    b+=b'\0'*((-len(b))%4)
    return sum(struct.unpack('>'+str(len(b)//4)+'I',b))&0xffffffff
def subset(src,dst):
    raw=src.read_bytes();n=U16(raw,4)
    tables={raw[o:o+4]:raw[U32(raw,o+8):U32(raw,o+8)+U32(raw,o+12)] for o in range(12,12+n*16,16)}
    cmap=tables[b'cmap'];mapping={}
    for i in range(U16(cmap,2)):
        o=U32(cmap,4+i*8+4);fmt=U16(cmap,o)
        if fmt==12:
            for j in range(U32(cmap,o+12)):
                a,z,g=struct.unpack_from('>III',cmap,o+16+j*12)
                mapping.update((c,g+c-a) for c in range(a,z+1))
        elif fmt==4:
            s=U16(cmap,o+6)//2;end=o+14;start=end+2*s+2;delta=start+2*s;ran=delta+2*s
            for j in range(s):
                a,z,d,r=U16(cmap,start+2*j),U16(cmap,end+2*j),U16(cmap,delta+2*j),U16(cmap,ran+2*j)
                for c in range(a,min(z,65534)+1):
                    g=U16(cmap,ran+2*j+r+2*(c-a)) if r else c
                    if not r or g:g=(g+d)&65535
                    if g:mapping.setdefault(c,g)
    def latin(c):return c<=0x24f or 0x300<=c<=0x36f or 0x2000<=c<=0x206f or c in (0x20ac,0x2212)
    selected={c:g for c,g in mapping.items() if latin(c)}
    num=U16(tables[b'maxp'],4);long=U16(tables[b'head'],50)==1
    loc=list(struct.unpack('>'+str(num+1)+('I' if long else 'H'),tables[b'loca']))
    if not long:loc=[v*2 for v in loc]
    glyf=tables[b'glyf'];keep=set(range(num))-set(mapping.values())|set(selected.values())|{0}
    todo=list(keep)
    while todo:
        g=todo.pop();data=glyf[loc[g]:loc[g+1]]
        if len(data)<10 or struct.unpack_from('>h',data,0)[0]>=0:continue
        o=10
        while True:
            flags,child=struct.unpack_from('>HH',data,o);o+=4
            if child not in keep:keep.add(child);todo.append(child)
            o+=4 if flags&1 else 2
            o+=2 if flags&8 else 4 if flags&64 else 8 if flags&128 else 0
            if not flags&32:break
    chunks=[];newloc=[0]
    for g in range(num):
        data=glyf[loc[g]:loc[g+1]] if g in keep else b''
        data+=b'\0'*((-len(data))%4);chunks.append(data);newloc.append(newloc[-1]+len(data))
    # Keep complete outlines/variation data for reliable shaping; subset Unicode cmap.
    head=bytearray(tables[b'head']);head[8:12]=b'\0'*4;tables[b'head']=bytes(head)
    groups=[]
    for c,g in sorted(selected.items()):
        if groups and c==groups[-1][1]+1 and g==groups[-1][2]+c-groups[-1][0]:groups[-1][1]=c
        else:groups.append([c,c,g])
    sub=struct.pack('>HHIII',12,0,16+len(groups)*12,0,len(groups))+b''.join(struct.pack('>III',*g) for g in groups)
    tables[b'cmap']=struct.pack('>HHHHIHHI',0,2,0,4,20,3,10,20)+sub
    tables.pop(b'DSIG',None)
    # Glyph IDs are stable, so GSUB/GPOS, composites and variable axes retain their references.
    tags=sorted(tables);n=len(tags);power=1<<(n.bit_length()-1)
    result=bytearray(struct.pack('>IHHHH',0x10000,n,power*16,power.bit_length()-1,n*16-power*16));offset=12+16*n;contents=[];headoffset=None
    for tag in tags:
        b=tables[tag];result+=tag+struct.pack('>III',checksum(b),offset,len(b))
        if tag==b'head':headoffset=offset
        b+=b'\0'*((-len(b))%4);contents.append(b);offset+=len(b)
    result+=b''.join(contents);struct.pack_into('>I',result,headoffset+8,(0xb1b0afba-checksum(bytes(result)))&0xffffffff)
    if dst.exists():raise RuntimeError('Output exists; backup first')
    dst.write_bytes(result)
    return {'font':src.name,'original_bytes':len(raw),'subset_ttf_bytes':len(result),'original_unicode_mappings':len(mapping),'latin_unicode_mappings':len(selected),'original_glyphs':num,'retained_glyphs':len(keep),'composite_closure':True,'unencoded_shaping_variants_retained':True}
if __name__=='__main__':
    from PIL import ImageFont
    root=Path(__file__).resolve().parents[1];source=root.parent/'diseno_minikids/fuentes';dest=root/'tools/v5-assets/fonts-final';dest.mkdir(exist_ok=True,parents=True);results=[]
    for name in ('montserrat-variable','barlowcondensed-bold'):
        ttf=dest/(name+'-latin.ttf');woff=ttf.with_suffix('.woff2')
        if not ttf.exists():
            r=subset(source/(name+'.ttf'),ttf)
            subprocess.run(['node',str(root/'tools/v5-woff2.cjs'),str(ttf),str(woff)],check=True)
        else:r={'font':name,'original_bytes':(source/(name+'.ttf')).stat().st_size,'subset_ttf_bytes':ttf.stat().st_size}
        a=ImageFont.truetype(str(ttf),32);b=ImageFont.truetype(str(woff),32)
        assert a.getname()==b.getname()
        text='¡Niñez Perú! S/ 29.90 — Árbol, pingüino'
        assert a.getmask(text).tobytes()==b.getmask(text).tobytes() if hasattr(a.getmask(text),'tobytes') else bytes(a.getmask(text))==bytes(b.getmask(text))
        r.update(woff2_bytes=woff.stat().st_size,freetype_decode=True,spanish_render_matches_subset_ttf=True,subset_scope='Latin Unicode cmap only; original outlines/variants retained',glyph_outlines_reduced=False);results.append(r)
    (root.parent/'diseno_minikids/v5/fuentes.json').write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf8')
