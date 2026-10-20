"""Mantiene el mismo código propio en subcarpeta local y dominio raíz."""
from mcp_local import MCP
client=MCP()
path='mu-plugins/minikids-estetica.php'
s=client.ability('minikids/read-file',{'path':path})['content']
import re
s=re.sub(r'((?:src|href)=")/wordpress/([^"\s]*)"',lambda m:m[1]+"<?php echo esc_url(home_url('/"+m[2]+"')); ?>\"",s)
s=s.replace("'/wordpress/cart/'", "home_url('/cart/')").replace("'/wordpress/checkout/'", "home_url('/checkout/')")
needle="}, 36);"
if "wp_localize_script('minikids-catalogo'" not in s:
    s=s.replace(needle,"    wp_localize_script('minikids-catalogo', 'minikidsCatalog', array('shopUrl' => home_url('/tienda/')));\n"+needle)
client.ability('minikids/write-file',{'path':path,'content':s,'backup':True})
path='mu-plugins/minikids-catalogo.js'
s=client.ability('minikids/read-file',{'path':path})['content']
s=s.replace("'/wordpress/tienda/?q='", "minikidsCatalog.shopUrl + '?q='")
client.ability('minikids/write-file',{'path':path,'content':s,'backup':True})
path='mu-plugins/minikids-estetica.css'
s=client.ability('minikids/read-file',{'path':path})['content']
s=s.replace("url('/wordpress/wp-content/uploads/", "url('../uploads/")
client.ability('minikids/write-file',{'path':path,'content':s,'backup':True})
print('Portabilidad aplicada mediante MCP con respaldos')
