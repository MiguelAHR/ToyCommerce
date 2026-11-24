from pathlib import Path
import runpy,json,subprocess,urllib.request
urllib.request.install_opener(urllib.request.build_opener(urllib.request.ProxyHandler({})))
print('Connecting local MCP',flush=True)
R=Path(__file__).resolve().parents[1]; O=R.parent/'diseno_minikids/v5'; B=R.parent/'local-private/v12-backups';B.mkdir(exist_ok=True)
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply']; receipts=[]
print('MCP connected; protected snapshot',flush=True)
protected=runpy.run_path(str(R/'tools/v10-preflight.py'))['protected'];(B/'protected-before.json').write_text(json.dumps(protected()))
native=(R/'tools/v10-native.php').read_text('utf8').replace('v10-backups/','v12-backups/')
helper=B/'native-snapshot.php';helper.write_text(native.replace("require dirname(__DIR__).'/wordpress/wp-load.php';", "require '"+str(R/'wordpress/wp-load.php').replace('\\','/')+"';").replace("dirname(__DIR__,2).'/local-private/v12-backups/'", "'"+str(B).replace('\\','/')+"/'"),encoding='utf8')
subprocess.run(['C:/xampp/php/php.exe','-l',str(helper)],check=True);subprocess.run(['C:/xampp/php/php.exe',str(helper),'before'],check=True)
s=read('minikids-commerce-v8.php')
s=s.replace("echo '</section><section class=\"mk8-comments\"><h2>Comentarios</h2>';mk5_review_overview();mk10_native_reviews();echo '</section></div></div>';", "echo '</section></div></div>';")
# Capture the sale label and place it immediately after the purchase button.
s=s.replace("$n=$product->get_stock_quantity();", "$n=$product->get_stock_quantity();\n ob_start();")
s=s.replace("if(!($product->is_on_sale()&&$s>0&&$s<$r))echo '</div>';", "$note=ob_get_clean();if(!($product->is_on_sale()&&$s>0&&$s<$r))echo $note.'</div>';")
s=s.replace("woocommerce_template_single_add_to_cart();", "woocommerce_template_single_add_to_cart();if($product->is_on_sale()&&$s>0&&$s<$r)echo '<div class=\"mk12-sale-note\">'.$note.'</div>';")
s=s.replace("$body='<div class=\"mk8-callout\">'.nl2br(esc_html($s['availability_text'])).'</div>';", "$body=mk12_shipping_public($methods,$s);")
s=s.replace("esc_html($gateway->get_title())", "esc_html(preg_replace('/\\s*\\(prueba local\\)\\s*/iu','',$gateway->get_title()))")
s=s.replace("esc_html($s['delivery_time'])", "esc_html(mk12_delivery_time($s))")
receipts.append(apply('minikids-commerce-v8.php',s))
s=read('minikids-retail.js').replace("if(!add)continue;", "if(!add||item.querySelector('.mk12-card-buy'))continue;");receipts.append(apply('minikids-retail.js',s))
s=read('minikids-commerce-v5.js').replace("if(add){add.textContent=", "if(add&&!add.closest('.mk12-card-buy')){add.textContent=").replace("if(brand){const label=", "if(brand&&!qs('.mk5-card-brand',el)){const label=");receipts.append(apply('minikids-commerce-v5.js',s))
for ext in ['php','css','js']:receipts.append(apply('minikids-commerce-v12.'+ext,(R/'tools'/('v12-source.'+ext)).read_text('utf8')))
(O/'aplicacion-V12.json').write_text(json.dumps(receipts,indent=2),encoding='utf8')
