from pathlib import Path
import runpy,json
R=Path(__file__).resolve().parents[1];O=R.parent/'diseno_minikids/v5';B=R.parent/'local-private/v11-backups'
d=runpy.run_path(str(R/'tools/v9-apply.py'));d['read'].__globals__['BACK']=B
read,apply=d['read'],d['apply'];receipts=json.loads((O/'aplicacion-V11.json').read_text('utf8'))
s=read('minikids-commerce-v9.php').replace("echo preg_replace('~<td\\b~','<td colspan=\"2\"',$html);", """$html=preg_replace_callback('~(<p class="woocommerce-shipping-destination">)(.*?)(</p>)~s',function($m){
  return preg_match('~<strong>.*?</strong>~s',$m[2],$destination)?$m[1].$destination[0].$m[3]:$m[0];
 },$html);
 echo preg_replace('~<td\\b~','<td colspan="2"',$html);""")
receipts.append(apply('minikids-commerce-v9.php',s))
s=read('minikids-commerce-v9.css').replace('.woocommerce-shipping-totals :is(ul,li,p,form)', '.woocommerce-shipping-totals :is(ul.woocommerce-shipping-methods,ul.woocommerce-shipping-methods>li,p.woocommerce-shipping-destination,form.woocommerce-shipping-calculator,form.woocommerce-shipping-calculator>p:first-child)')
s+='''
/* Compact default summary; expanding the native address calculator may increase its height. */
body.minikids-retail .mk5-native-cart .cart-collaterals{padding:12px!important}
body.minikids-retail .mk5-native-cart .cart_totals h2{margin-bottom:4px!important}
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table :is(th,td){padding-block:6px!important}
body.minikids-retail .mk5-native-cart .cart_totals table.shop_table .mk9-progress-row td{padding-block:4px!important}
body.minikids-retail .mk5-native-cart .cart_totals .order-total :is(th,td){padding-block:8px!important}
body.minikids-retail .mk5-native-cart .mk6-cart-consent{margin-block:6px!important}
'''
receipts.append(apply('minikids-commerce-v9.css',s))
(O/'aplicacion-V11.json').write_text(json.dumps(receipts,ensure_ascii=False,indent=2),encoding='utf8')
