"""Local MCP writes with backups and lint; no vendor edits."""
from pathlib import Path
import runpy, json, subprocess, urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v15-backups';B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=[]
if not (B/'protected-before.json').exists():
    (B/'protected-before.json').write_text(json.dumps(runpy.run_path(str(R/'tools/v10-preflight.py'))['protected']()))
options={
 'woocommerce_review_rating_verification_required':'yes', 'woocommerce_review_rating_verification_label':'yes',
 'woocommerce_enable_myaccount_registration':'yes','woocommerce_enable_checkout_login_reminder':'yes',
 'woocommerce_registration_generate_password':'no','woocommerce_registration_generate_username':'yes'}
if not (B/'options-before.json').exists():
    before={k:d['c'].ability('minikids/get-option',{'name':k})['value'] for k in options}
    (B/'options-before.json').write_text(json.dumps(before,indent=2))
s=read('minikids-commerce-v12.php')
s=(B/'minikids-commerce-v12.php').read_text('utf8')
start=s.index('// Spanish public entry');end=s.index("add_filter('render_block',",start)
s=s[:start]+'''// V15: resolve the Spanish alias to the native page without changing saved content.
add_filter('request',function($vars){
 if(untrailingslashit(wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH))===untrailingslashit(wp_parse_url(home_url('/mi-cuenta/'),PHP_URL_PATH))))return ['page_id'=>wc_get_page_id('myaccount')];
 return $vars;
});
add_filter('redirect_canonical',function($url){return mk15_account_alias()?false:$url;});
'''.replace('PATH))))','PATH)))')+s[end:]
# Replace only the authentication renderer/handler; preserve all existing product code.
start=s.index('function mk12_auth_fields');end=s.index('// The review form remains',start)
s=s[:start]+(R/'tools/v15-auth-source.php.txt').read_text('utf8')+'\n'+s[end:]
# Replace the native verification notice after Woo has enforced purchase eligibility.
needle="$reviews=preg_replace('~(<p class=\"woocommerce-noreviews\">)"
pos=s.index(needle)
s=s[:pos]+'''$reviews=preg_replace('~<p class="woocommerce-verification-required">.*?</p>~s','<div class="mk15-review-gate"><p>Solo los clientes que compraron este producto pueden dejar un comentario.</p>'.(!is_user_logged_in()?'<a class="button" href="'.esc_url(mk12_account_url()).'">Iniciar sesión</a>':'').'</div>',$reviews);
 '''+s[pos:]
receipts.append(apply('minikids-commerce-v12.php',s))
for name in ['minikids-commerce-v12.js','minikids-commerce-v15.php','minikids-commerce-v15.css','minikids-mini-cart-v15.php']:
    receipts.append(apply(name,(R/'tools/v15-stage'/name).read_text('utf8')))
for k,v in options.items():
    d['c'].ability('minikids/update-option',{'name':k,'value':v})
    assert d['c'].ability('minikids/get-option',{'name':k})['value']==v
(O/'opciones-V15.json').write_text(json.dumps({k:{'before':json.loads((B/'options-before.json').read_text())[k],'after':v} for k,v in options.items()},indent=2))
(O/'aplicacion-V15.json').write_text(json.dumps(receipts,indent=2))
