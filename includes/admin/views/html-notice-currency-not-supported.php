<?php
/**
 * Admin View: Notice - Currency not supported.
 *
 * @package Pix_For_WooCommerce/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'Pix PJbank Desabilitado', 'woocommerce-pix-pjbank' ); ?></strong>: <?php printf( __( 'Moeda <code>%s</code> não suportada. É aceito apenas BRL', 'woocommerce-pix-pjbank' ), get_woocommerce_currency() ); ?>
	</p>
</div>
