"""Read generated originals; produce contact sheets on white/dark for inspection."""
from PIL import Image,ImageDraw,ImageFont
from pathlib import Path
import json,hashlib,math,datetime
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
plan=json.loads((OUT/'catalogo_plan.json').read_text('utf8'))
font=ImageFont.truetype('C:/Windows/Fonts/arial.ttf',16)
def sheet(items,dest,cols=8):
    cellw,cellh=400,265
    canvas=Image.new('RGB',(cols*cellw,math.ceil(len(items)/cols)*cellh),'#e4e8ec');draw=ImageDraw.Draw(canvas)
    audit=[]
    for i,(s,view,path)in enumerate(items):
        x=(i%cols)*cellw;y=(i//cols)*cellh
        if not path.exists():draw.text((x+8,y+30),s['sku']+' PENDIENTE',fill='red',font=font);continue
        original=Image.open(path);im=original.convert('RGBA');alpha=im.getchannel('A');hist=alpha.histogram();pixels=im.width*im.height
        audit.append({'sku':s['sku'],'view':view,'file':path.name,'dimensions':list(im.size),'mode':original.mode,'sha256':hashlib.sha256(path.read_bytes()).hexdigest(),'transparent_fraction':hist[0]/pixels,'partial_alpha_fraction':sum(hist[1:255])/pixels,'alpha_bbox':alpha.getbbox()})
        im.thumbnail((192,210))
        for dx,color in [(0,'white'),(200,'#14263b')]:
            tile=Image.new('RGBA',(200,215),color);tile.alpha_composite(im,((200-im.width)//2,(215-im.height)//2));canvas.paste(tile.convert('RGB'),(x+dx,y))
        draw.text((x+8,y+217),s['sku']+' '+view,fill='#0756ad',font=font)
        name=s['name'];draw.text((x+8,y+240),name[:42],fill='#202020',font=font)
    canvas.save(dest,quality=90,optimize=True)
    return audit
main=[(s,'main',OUT/'imagenes'/s['image'])for s in plan]
gallery=[(s,v,OUT/'imagenes'/(s['sku']+'-'+v+'.png'))for s in plan if s['gallery_required']for v in ['side','detail']]
audit=sheet(main,OUT/'catalogo_contacto.jpg')+sheet(gallery,OUT/'catalogo_galerias_contacto.jpg')
manifest={'utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),'images':audit,'expected':121,'found':len(audit),'unique_sha256':len({x['sha256']for x in audit}),'alpha_all':all(x['mode']=='RGBA'and x['transparent_fraction']>.02 for x in audit),'visual_review_status':'requires_human_review_of_sheets_not_an_automatic_quality_certification'}
(OUT/'imagenes_auditoria.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({k:v for k,v in manifest.items()if k!='images'},ensure_ascii=False))
