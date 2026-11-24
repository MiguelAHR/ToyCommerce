<?php
/** MiniKids V7: residual native Woo strings. Human editable JSON, no mail handlers. */
defined('ABSPATH') || exit;
function mk7_translate_residual($translation,$original,$domain){
    if(!in_array($domain,['woocommerce','blocksy'],true)||!str_starts_with(get_locale(),'es_'))return $translation;
    // Prefer the installed official catalog and existing explicit business translations.
    if($translation!==$original)return $translation;
    static $map=null;
    if($map===null)$map=json_decode(file_get_contents(__DIR__.'/minikids-language-v7.json'),true)?:[];
    return $map[$original]??$translation;
}
add_filter('gettext','mk7_translate_residual',180,3);
add_filter('gettext_with_context',function($translated,$original,$context,$domain){return mk7_translate_residual($translated,$original,$domain);},180,4);

add_filter('ngettext',function($translated,$single,$plural,$number,$domain){
    return mk7_translate_residual($translated,$number==1?$single:$plural,$domain);
},180,5);
add_filter('ngettext_with_context',function($translated,$single,$plural,$number,$context,$domain){
    return mk7_translate_residual($translated,$number==1?$single:$plural,$domain);
},180,6);
