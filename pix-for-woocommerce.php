<?php

/**
 * Plugin Name: Pix PJBank for WooCommerce
 * Plugin URI: https://github.com/webabsoluta/pix-pjbank-woocommerce
 * Description: Receba com Pix do PJBanjk com confirmação automática e mudança de status do pedido. É necessário fazer o cadastro em um parceiro do PJBank. Acesse nosso <a href="https://webabsoluta.com.br/parceiro-pjbank-cadastro" target="_blank">Formulário de Cadastro PJBank aqui.</a>
 * Version: 1.0.0
 * Author: Agência Digital WA - Web Absoluta
 * Author URI: https://webabsoluta.com.br/plugins-para-wordpress
 * Tested up to: 6.0
 * License: GNU General Public License v3.0
 * 
 * Plugin Name: Cartão PJBank
 *
 * @package Pix_PJBank_for_WooCommerce
 */

defined('ABSPATH') or exit;

define('WC_PIX_VERSION', '1.0.2');
define('WC_PIX_PLUGIN_FILE', __FILE__);
define('WC_PIX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_PIX_PLUGIN_PATH', plugin_dir_path(__FILE__));

if (!class_exists('WC_Pix')) {
	include_once dirname(__FILE__) . '/includes/class-wc-pix.php';
	include_once dirname(__FILE__) . '/webhook-pix.php';
	add_action('plugins_loaded', array('WC_Pix', 'init'));
}
