from pathlib import Path
import urllib.request,json
from lxml import html
R=Path(__file__).resolve().parents[2];B=R/'local-private/v5-backups/v18'
op=urllib.request.build_opener(urllib.request.ProxyHandler({}))
for name,path in [('product','?p=136'),('home',''),('shop','tienda/')]:
 s=op.open('http://localhost:8082/wordpress/'+path,timeout=45).read().decode();(B/(name+'-before.html')).write_text(s,encoding='utf8');d=html.fromstring(s)
 if name=='product':print('gallery',[(e.tag,e.get('class'),e.getparent().get('class')) for e in d.xpath('//*[contains(@class,"flexy")]')][:30]);print('pills',html.tostring(d.xpath('//*[contains(@class,"flexy-pills")]')[0],encoding='unicode')[:1900])
 if name=='home':print('footer',[(e.tag,e.get('class')) for e in d.xpath('//footer | //footer//*')][:35])
 if name=='shop':print('body',d.xpath('//body/@class')); print('layout',[(e.tag,e.get('class')) for e in d.xpath('//*[contains(@class,"catalog-layout")]/..')])
