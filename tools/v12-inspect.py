from pathlib import Path
from lxml import html
import sys
sys.stdout.reconfigure(encoding='utf8')
O=Path(__file__).resolve().parents[2]/'diseno_minikids/v5'
for name in ['V12-tienda.html','V12-auto-con-control-remoto.html']:
 d=html.fromstring((O/name).read_text('utf8'));print(name)
 for x in d.xpath('//span[contains(@class,"mk12-tag")]')[:1]:print('tag parents',[(p.tag,p.get('class')) for p in list(x.iterancestors())[:3]])
 for x in d.xpath('//li[contains(concat(" ",normalize-space(@class)," ")," product ")]')[:1]:print(html.tostring(x,encoding='unicode')[:4800])
 for x in d.xpath('//div[contains(@class,"mk8-left")]/div[contains(@class,"woocommerce-product-gallery")]')[:1]:print('gallery',html.tostring(x,encoding='unicode')[:1800])
 print('sale_note',[(x.getprevious().tag,x.getprevious().get('class')) for x in d.xpath('//div[@class="mk12-sale-note"]')])
