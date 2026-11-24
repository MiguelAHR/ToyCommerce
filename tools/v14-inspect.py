from pathlib import Path
import urllib.request,http.cookiejar
from lxml import html
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5'
op=urllib.request.build_opener(urllib.request.ProxyHandler({}),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
base='http://localhost:8082/wordpress/'
op.open(base+'?wc-ajax=add_to_cart',b'product_id=39&quantity=1').read()
s=op.open(base+'cart/',timeout=40).read().decode();(O/'V14-carrito-antes.html').write_text(s,encoding='utf8')
d=html.fromstring(s)
for x in d.xpath('//tr[contains(@class,"cart_item")]|//tr[@class="mk9-progress-row"]'):
 print(html.tostring(x,encoding='unicode'))
print('Styles:',d.xpath('//link[@rel="stylesheet"]/@id'))
