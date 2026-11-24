from pathlib import Path
from html.parser import HTMLParser
import urllib.request,urllib.parse
class Images(HTMLParser):
    def handle_starttag(self,tag,attrs):
        if tag!='img':return
        a=dict(attrs);url=a.get('src','');p=Path(__file__).resolve().parents[1]/urllib.parse.unquote(urllib.parse.urlparse(url).path.lstrip('/'))
        if not p.is_file():print(route,url,flush=True)
for route in ('','tienda/','product/bloques-creativos-60-piezas/'):
    req=urllib.request.Request('http://127.0.0.1:8082/wordpress/'+route,headers={'Host':'localhost:8082'})
    with urllib.request.urlopen(req)as r:Images().feed(r.read().decode('utf8'))
