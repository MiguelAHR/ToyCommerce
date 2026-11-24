"""Reuse existing photo extractions; preserve original media. No invented substitute toys."""
from pathlib import Path
from PIL import Image,ImageDraw,ImageFilter
import json,hashlib
import numpy as np
from collections import deque
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';DEST=OUT/'imagenes-v6';DEST.mkdir(exist_ok=True)
before=json.loads((ROOT.parent/'local-private/v6-backups/native-before.json').read_text('utf8'))
provenance=json.loads((ROOT.parent/'diseno_minikids/v3/generaciones_completas.json').read_text('utf8'))
manifest=[]
for p in before['products']:
    if p['demo']:continue
    cut=ROOT.parent/f'diseno_minikids/v3/imagenes/minikids-producto-{p["id"]}-sin-fondo-v3.png'
    source=Path(p['source'])
    entry={'product':p['id'],'original_media':p['image'],'original_sha256':hashlib.sha256(source.read_bytes()).hexdigest(),'original_file':source.name}
    if p['id']==91:
        # White studio background: remove only light neutral pixels connected to an edge.
        im=Image.open(source).convert('RGBA');im.thumbnail((1200,1200),Image.Resampling.LANCZOS);rgb=np.array(im)[:,:,:3];h,w=rgb.shape[:2]
        candidate=(rgb.min(axis=2)>210)&((rgb.max(axis=2).astype(int)-rgb.min(axis=2).astype(int))<25)
        bg=np.zeros((h,w),dtype=bool);queue=deque()
        for y,x in [(y,x)for y in range(h)for x in [0,w-1]]+[(y,x)for x in range(w)for y in [0,h-1]]:
            if candidate[y,x]and not bg[y,x]:bg[y,x]=True;queue.append((y,x))
        while queue:
            y,x=queue.popleft()
            for yy,xx in [(y-1,x),(y+1,x),(y,x-1),(y,x+1)]:
                if 0<=yy<h and 0<=xx<w and candidate[yy,xx]and not bg[yy,xx]:bg[yy,xx]=True;queue.append((yy,xx))
        im.putalpha(Image.fromarray(np.where(bg,0,255).astype('uint8')).filter(ImageFilter.GaussianBlur(.3)))
        cut=DEST/'camion-extraccion-v6.png';im.save(cut)
    if not cut.exists():
        entry['status']='pending';entry['reason']='No verified source-preserving cutout; rembg/model unavailable offline. Original retained.';manifest.append(entry);continue
    im=Image.open(cut).convert('RGBA');im.thumbnail((1200,1200),Image.Resampling.LANCZOS)
    dest=DEST/f'minikids-original-{p["id"]}-sin-fondo-v6.webp';im.save(dest,'WEBP',quality=90,method=6)
    entry.update({'status':'prepared','file':str(dest),'extraction':'Connected white background removal preserving foreground pixels' if p['id']==91 else 'Existing V3 photo extraction from the original; not rerun through rembg','provenance_prompt':provenance['cutouts'].get(str(p['id']),{}).get('prompt','No generated toy; original pixel extraction'),'width':im.width,'height':im.height,'sha256':hashlib.sha256(dest.read_bytes()).hexdigest()});manifest.append(entry)
ready=[p for p in manifest if p['status']=='prepared'];sheet=Image.new('RGB',(800,len(ready)*220),'#e5e5e5');draw=ImageDraw.Draw(sheet)
for n,p in enumerate(ready):
    im=Image.open(p['file']).convert('RGBA');im.thumbnail((200,200))
    for j,color in enumerate(['white','#172a41']):
        tile=Image.new('RGBA',(220,200),color);tile.alpha_composite(im,((220-im.width)//2,(200-im.height)//2));sheet.paste(tile.convert('RGB'),(140+j*260,n*220+16))
    draw.text((10,n*220+10),'ID '+str(p['product']),fill='black')
sheet.save(OUT/'V6-recortes-blanco-oscuro.jpg',quality=92)
(OUT/'imagenes-V6.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf8')
print('Prepared',len(ready),'pending',len(manifest)-len(ready))
