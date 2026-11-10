<?php
/**
 * Plugin Name: MiniKids · Compatibilidad de Entorno Local (cURL Guard)
 * Description: Previene caídas fatales si el servidor XAMPP de destino no tiene habilitada la extensión cURL en php.ini.
 */
defined( 'ABSPATH' ) || exit;

if ( ! extension_loaded( 'curl' ) ) {
    // Si cURL no está habilitado en php.ini, evitamos que Mercado Pago lance una excepción fatal
    add_filter( 'option_active_plugins', function ( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_values( array_filter( $plugins, function ( $plugin ) {
                return ( strpos( $plugin, 'mercadopago' ) === false );
            } ) );
        }
        return $plugins;
    } );

    // Notificación informativa y amigable si entra como administrador
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-warning is-dismissible"><p><strong>MiniKids · Entorno Local:</strong> La extensión <code>cURL</code> de PHP no está activada en tu <code>php.ini</code> de XAMPP. Mercado Pago se ha omitido preventivamente para mantener la tienda 100% operativa. Para activarla, descomenta <code>extension=curl</code> en tu php.ini y reinicia Apache.</p></div>';
    } );
}
