"""Preserve image_gen outputs; normalize size/format, generate inspection composites."""
from pathlib import Path
from PIL import Image,ImageDraw
import json,hashlib
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5';DEST=OUT/'imagenes-v7';DEST.mkdir(exist_ok=True)
GEN=Path('C:/Users/Cristopher/.codex/generated_images/01a20c0f-ff14-7320-b111-363287651d1c')
files={136:'cd734321-6cd6-48f6-9ec9-8c9f380b7efe',134:'7f912acb-efac-4310-a3bb-9108177c43ea',91:'56a727f2-59bd-476c-a1b9-1853d5e720c8',44:'3fafd89f-0426-46d2-93be-ddea1d074a29',43:'6fba46f4-8ad8-4c17-bb85-a5114a9f99ed',41:'b9fd4055-42a6-4508-86bf-b2f711ecb5d6',40:'0a69d26d-3dd9-4179-b404-411aa3c93f27',39:'0f593849-33da-40bf-9833-1493fdf1ed5e'}
before=json.loads((ROOT.parent/'local-private/v6-backups/native-before.json').read_text('utf8'));products={p['id']:p for p in before['products']}
sheet=Image.new('RGB',(1440,len(files)*390),'#ddd');draw=ImageDraw.Draw(sheet);manifest=[]
for row,(pid,name) in enumerate(files.items()):
    source=GEN/('exec-'+name+'.png');raw=DEST/f'minikids-{pid}-imagegen-original.png';raw.write_bytes(source.read_bytes())
    im=Image.open(raw).convert('RGBA');canvas=Image.new('RGBA',(1200,1200));im.thumbnail((1100,1100),Image.Resampling.LANCZOS);canvas.alpha_composite(im,((1200-im.width)//2,(1200-im.height)//2))
    png=DEST/f'minikids-{pid}-sin-fondo-v7.png';webp=png.with_suffix('.webp');canvas.save(png);canvas.save(webp,'WEBP',quality=82,method=6)
    # A contact sheet plus full resolution edge checks; no pixel retouching.
    thumb=canvas.copy();thumb.thumbnail((350,350))
    original=Image.open(products[pid]['source']).convert('RGB');original.thumbnail((410,350));sheet.paste(original,(20,row*390+30))
    draw.text((20,row*390+6),f'ID {pid} original / white / dark',fill='black')
    for col,color in enumerate(['white','#172a41']):
        tile=Image.new('RGBA',(1200,1200),color);tile.alpha_composite(canvas);tile.convert('RGB').save(DEST/f'{pid}-check-{col}.jpg',quality=95)
        tile.thumbnail((350,350));sheet.paste(tile.convert('RGB'),(480+col*460,row*390+30))
    manifest.append({'product':pid,'original_media':int(products[pid]['image']),'original_sha256':hashlib.sha256(Path(products[pid]['source']).read_bytes()).hexdigest(),'original_source':products[pid]['source'],'generated':str(raw),'png':str(png),'file':str(webp),'width':1200,'height':1200,'quality':82,'alpha_extrema':canvas.getchannel('A').getextrema(),'status':'review','tool':'built-in image_gen edit with original reference'})
sheet.save(OUT/'V7-originales-recortes-blanco-oscuro.jpg',quality=94)
(OUT/'imagenes-V7.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf8')
print('Saved eight reference edits, PNG/WebP and inspection composites.')
