"""Validate individually authored seed copy and immutable native snapshots. No DB access."""
from pathlib import Path
import json,re,collections,sys
R=Path(__file__).resolve().parents[2]; O=R/'diseno_minikids/v5'
B=R/'local-private/v5-backups/v18'
before=json.loads((B/'before.json').read_text('utf8'))
texts={}
for line in (R/'ToyCommerce/tools/v18-textos.txt').read_text('utf8').splitlines():
    cid,text=line.split('|',1); assert cid not in texts; texts[cid]=text
checks={}; errors=[]; words=[]; phrases=collections.defaultdict(set); starts=collections.defaultdict(set)
def check(k,v):
    checks[k]=bool(v)
check('exactly_135_existing_ids',set(texts)=={str(c['id']) for c in before['comments']} and len(texts)==135)
seed=json.loads((O/'comentarios_demo.json').read_text('utf8'))
check('matches_seed_inventory',sorted((c['id'],c['product_id'],int(c['rating'])) for c in before['comments'])==sorted((int(c['comment_id']),int(c['product_id']),int(c['rating'])) for c in seed['comments']))
check('no_identical_texts',len(set(texts.values()))==135)
name_mentions=0; authors=collections.defaultdict(set)
for c in before['comments']:
    cid=str(c['id']);text=texts[cid];p=before['products'][str(c['product_id'])];n=len(text.split());words.append(n)
    bad=[]
    if not 8<=n<=45:bad.append('word_count')
    if '..' in text or '  ' in text or not text.endswith('.') or not text.lstrip('¡¿')[0].isupper():bad.append('punctuation')
    if text.count('¡')!=text.count('!') or text.count('¿')!=text.count('?'):bad.append('opening_punctuation')
    if re.search(r'\b(demo\w*|prueba\w*)\b|[\U0001F000-\U0001FAFF]|S/|\bsoles\b|\d+[.,]\d{2}',text,re.I):bad.append('forbidden')
    if re.search(r'\bA\s+'+re.escape(p['name']),text,re.I):bad.append('A_product_name')
    if p['name'].casefold() in text.casefold():name_mentions+=1
    first=re.split(r'[.!?]',text)[0].casefold()
    if first in starts[c['product_id']]:bad.append('repeated_first_sentence')
    starts[c['product_id']].add(first)
    # Stronger than whole-sentence check: no normalized 7-word sequence in >2 reviews.
    tokens=re.findall(r'\w+',text.casefold())
    for i in range(len(tokens)-6):phrases[' '.join(tokens[i:i+7])].add(cid)
    if c['author'] in authors[c['product_id']]:bad.append('duplicate_author')
    authors[c['product_id']].add(c['author'])
    if bad:errors.append({'id':cid,'errors':bad,'words':n})
repeats={p:sorted(v) for p,v in phrases.items() if len(v)>2}
check('copy_rules',not errors);check('long_phrases_at_most_twice',not repeats)
check('full_name_mentions_at_most_one_in_six',name_mentions<=135//6)
check('varied_lengths',max(words)-min(words)>=20 and len(set(words))>=15)
if '--after' in sys.argv:
    after=json.loads((B/'after.json').read_text('utf8'))
    all_site=json.loads((B/'all-site-reviews.json').read_text('utf8'))
    for c in all_site:
        tokens=re.findall(r'\w+',c['text'].casefold())
        for i in range(len(tokens)-6):phrases[' '.join(tokens[i:i+7])].add(str(c['id']))
    repeats={p:sorted(v) for p,v in phrases.items() if len(v)>2}
    check('all_site_reviews_long_phrases_at_most_twice',not repeats)
    check('product_counts_averages_and_histograms_unchanged',before['products']==after['products'])
    check('same_135_ids',len(after['comments'])==135 and [c['id'] for c in before['comments']]==[c['id'] for c in after['comments']])
    check('only_text_changed',all({k:v for k,v in a.items() if k!='text'}=={k:v for k,v in b.items() if k!='text'} and b['text']==texts[str(a['id'])] and b['text']!=a['text'] for a,b in zip(before['comments'],after['comments'])))
result={'phase':'after' if '--after' in sys.argv else 'before','checks':checks,'errors':errors,'repeated_7_word_sequences':repeats,'comments':len(texts),'products':len(before['products']),'min_words':min(words),'max_words':max(words),'distinct_lengths':len(set(words)),'full_product_name_mentions':name_mentions,'words_by_comment':{k:len(t.split()) for k,t in texts.items()},'scope':'Automatic copy constraints and native snapshots; grammar/product relevance additionally read by author. Synthetic local seed, not verified customer experiences.'}
if '--after' in sys.argv:result['site_reviews_checked']=len(all_site)
(O/'comentarios_verificacion.json').write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf8')
print(json.dumps({k:v for k,v in result.items() if k!='words_by_comment'},ensure_ascii=True,indent=2))
assert all(checks.values())
if '--after' not in sys.argv:(O/'comentarios_v18.json').write_text(json.dumps(texts,ensure_ascii=False,indent=2),encoding='utf8')
