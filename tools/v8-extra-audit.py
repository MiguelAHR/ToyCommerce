from pathlib import Path
import re,json,runpy
from lxml import html
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
d=html.fromstring((OUT/'V8-404.html').read_text('utf8'))
for value in d.xpath('//@aria-label|//@title|//@placeholder'):
 if re.search(r'\bsearch\b',value,re.I):print('404 attribute:',value)
for p in (ROOT/'wordpress/wp-content/mu-plugins').glob('*'):
 if p.suffix not in ['.css','.js']:continue
 for line in p.read_text('utf8').splitlines():
  if re.search('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f]',line):print(p.name,line[:250])
env=runpy.run_path(str(ROOT/'tools/v8-apply.py'));c=env['c'];result=[]
pages=c.ability('minikids/list-content',{'post_type':'page','status':'publish','limit':100})
(OUT/'paginas-auditadas-V8.json').write_text(json.dumps(pages,ensure_ascii=False,indent=2),encoding='utf8')
print('list shape',type(pages).__name__, list(pages)[:3] if isinstance(pages,dict) else len(pages))
for item in pages['items']:
 page=c.ability('minikids/get-content',{'id':item['id']});text=page['content'];clean=re.sub('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f\u200d]','',text)
 if clean!=text:
  backup=env['BACK']/('page-'+str(item['id'])+'.json')
  if not backup.exists():backup.write_text(json.dumps(page,ensure_ascii=False,indent=2),encoding='utf8')
  c.ability('minikids/update-content',{'id':item['id'],'content':clean});assert c.ability('minikids/get-content',{'id':item['id']})['content']==clean
  result.append(item['id'])
(OUT/'paginas-limpiadas-V8.json').write_text(json.dumps({'audited':pages['count'],'changed_ids':result}),encoding='utf8')
name='minikids-bloques.css';s=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content'];s=s.replace('content: "\U0001F6D2" !important;','content: none !important;');env['apply'](name,s)
