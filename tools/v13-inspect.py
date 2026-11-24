from pathlib import Path
from lxml import html
P=Path(__file__).resolve().parents[2]/'diseno_minikids/v5'
for file,xpath in [('V12-carrito.html','//td[@class="product-remove"]|//tr[contains(@class,"shipping")]|//tr[@class="mk9-progress-row"]'),('V12-abaco-pedagogico-y-laberinto-de-madera.html','//section[@class="mk12-comments"]|//li[contains(@class,"product")][1]')]:
 d=html.fromstring((P/file).read_text('utf8'))
 for x in d.xpath(xpath)[:4]:print(html.tostring(x,encoding='unicode')[:16000])
