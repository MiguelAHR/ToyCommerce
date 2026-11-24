from pathlib import Path
import runpy,json,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];B=R.parent/'local-private/v13-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
s=d['read']('minikids-commerce-v12.php')
old=''' $overview.='<div class="mk13-review-stars">'.wc_get_rating_html((float)$product->get_average_rating(),$product->get_rating_count()).'</div>';
 if(!$product->get_rating_count())$overview.='<p class="mk13-zero-stars" aria-label="Sin valoraciones">'.str_repeat('<span aria-hidden="true">☆</span>',5).'</p>';'''
new=''' $stars='<div class="mk13-review-stars">'.wc_get_rating_html((float)$product->get_average_rating(),$product->get_rating_count()).'</div>';
 if(!$product->get_rating_count())$stars='<p class="mk13-zero-stars" aria-label="Sin valoraciones">'.str_repeat('<span aria-hidden="true">☆</span>',5).'</p>';
 $overview=preg_replace('~(?=<div class="mk5-rating-row">)~',$stars,$overview,1);'''
assert old in s
receipt=d['apply']('minikids-commerce-v12.php',s.replace(old,new))
(R.parent/'diseno_minikids/v5/aplicacion-V13-refinamiento.json').write_text(json.dumps(receipt,indent=2),encoding='utf8')
