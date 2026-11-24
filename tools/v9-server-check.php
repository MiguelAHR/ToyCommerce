<?php
// Real native hooks, isolated in-memory WC_Session, never order creation.
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='localhost:8082';$_SERVER['REQUEST_URI']='/wordpress/';
require dirname(__DIR__).'/wordpress/wp-load.php';
if(home_url('/')!=='http://localhost:8082/wordpress/')exit(2);
class MK9_MemorySession extends WC_Session {}
$original=WC()->session;WC()->session=new MK9_MemorySession();$checks=[];
function mk9_test($name,$ok){global $checks;$checks[$name]=(bool)$ok;if(!$ok)throw new RuntimeException($name);}
function mk9_seed($age,$ttl=600){WC()->session=new MK9_MemorySession();WC()->session->set('mk_secure_start',time()-$age);WC()->session->set('mk_secure_ttl',$ttl);WC()->session->set('mk_secure_token','memory-only');}
try{
    mk9_seed(10);mk9_test('active_600_seconds',mk9_valid('memory-only'));
    $start=WC()->session->get('mk_secure_start');mk9_test('wrong_token_rejected',!mk9_valid('old-tab'));
    mk9_test('wrong_token_cannot_reset',WC()->session->get('mk_secure_start')===$start);
    mk9_seed(600);mk9_test('600_boundary_rejected',!mk9_valid('memory-only'));
    mk9_test('expiry_clears_start_and_token',!WC()->session->get('mk_secure_start')&&!WC()->session->get('mk_secure_token')&&WC()->session->get('mk_secure_expired'));
    mk9_seed(15,15);mk9_test('15_boundary_rejected',!mk9_valid('memory-only'));
    mk9_seed(601);$_POST['mk_secure_token']='memory-only';wc_clear_notices();do_action('woocommerce_checkout_process');
    mk9_test('checkout_process_notice',wc_has_notice(mk9_message(),'error'));
    $errors=new WP_Error();do_action('woocommerce_after_checkout_validation',[],$errors);
    mk9_test('after_validation_error',$errors->get_error_message('mk_secure_expired')===mk9_message());
    mk9_seed(10);mk9_clear();mk9_test('completion_clears_window',!WC()->session->get('mk_secure_start')&&!WC()->session->get('mk_secure_token'));
    mk9_test('missing_start_rejected',!mk9_valid('memory-only'));
    $out=['checks'=>$checks,'passed'=>count($checks),'scope'=>'Native hooks in CLI with in-memory WC_Session; no order, DB write, gateway or mail trigger. Boundary checks deliberately set a past timestamp only in memory.'];
    file_put_contents(dirname(__DIR__,2).'/diseno_minikids/v5/server-checks-V9.json',wp_json_encode($out,JSON_PRETTY_PRINT));
    echo wp_json_encode($out).PHP_EOL;
}finally{WC()->session=$original;unset($_POST['mk_secure_token']);}
