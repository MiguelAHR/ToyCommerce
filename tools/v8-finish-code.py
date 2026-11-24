from pathlib import Path
import runpy,re,json
ROOT=Path(__file__).resolve().parents[1];d=runpy.run_path(str(ROOT/'tools/v8-apply.py'));c=d['c'];apply=d['apply']
name='minikids-estetica.php';s=c.ability('minikids/read-file',{'path':'mu-plugins/'+name})['content']
# Retire owned legacy overrides that hardcoded subjects and forced a processing
# email before the gateway. Native status transitions and editable settings own mail.
start=s.index('// 14. Personalización del Correo');end=s.index('// 15. Sistema de Registro',start)
s=s[:start]+'// V8: native Woo mail settings and status-triggered email hooks; styles in commerce-v8.\n\n'+s[end:]
s=re.sub(r"add_filter\('wp_mail_from(?:_name)?', function\(\$(?:from|name)\) \{.*?\}, 99\);",'',s,flags=re.S)
s=re.sub('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f\u200d]','',s)
s=s.replace('>Limpiar filtro </button>','>Limpiar filtro</button>').replace('aria-label="Limpiar búsqueda" style="display:none;"></button>','aria-label="Limpiar búsqueda" style="display:none;">Borrar</button>')
apply(name,s)
# Shop editorial content remains Gutenberg; update via content ability after reading.
for page_id in [9,36]:
 page=c.ability('minikids/get-content',{'id':page_id});(d['BACK']/f'page-{page_id}.json').write_text(json.dumps(page,ensure_ascii=False,indent=2),encoding='utf8')
 content=re.sub('[\U0001F000-\U0001FAFF\u2600-\u27BF\ufe0f\u200d]','',page['content'])
 if content!=page['content']:c.ability('minikids/update-content',{'id':page_id,'content':content})
 if page_id==36 and page['title']=='My account':c.ability('minikids/update-content',{'id':page_id,'title':'Mi cuenta'})
