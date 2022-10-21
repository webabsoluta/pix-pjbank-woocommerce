<?php
/**
 * Gateway class
 *
 * @package Pix_For_WooCommerce/Classes/Gateway
 * @version 1.3.6
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Gateway.
 */
class WC_Pix_Gateway extends WC_Payment_Gateway
{

	/**
	 * Constructor for the gateway.
	 */
	public function __construct()
	{
		$this->domain = 'woocommerce-pix-pjbank';
		$this->id = 'pix_pjbank_gateway';
		$color = $this->get_option('icon_color') ? $this->get_option('icon_color') : 'black';
		$this->icon = apply_filters('woocommerce_gateway_icon', WC_PIX_PLUGIN_URL . 'assets/icon-pix-' . $color . '.png');
		$this->has_fields = false;
		$this->method_title = __('Pix', $this->domain);
		$this->method_description = __('Receba pagamentos via PIX do PJBank', $this->domain);

		// Define where the key was generated
		$this->key_origin = $this->get_option('key_origin');

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->instructions = $this->get_option('instructions');
		$this->key = $this->get_option('key');
		$this->chave_pjbank = $this->get_option('chave_pjbank') ? $this->get_option('chave_pjbank') : 'ID';
		$this->merchant = $this->get_option('merchant');
		$this->city = $this->get_option('city');
		$this->whatsapp = $this->get_option('whatsapp');
		$this->telegram = $this->get_option('telegram');
		$this->email = $this->get_option('email');
		$this->send_on_hold_email = $this->get_option('send_on_hold_email');

		// Actions
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
		if ('yes' == $this->send_on_hold_email) {
			add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 4);
		}
		if (is_account_page()) {
			add_action('woocommerce_order_details_after_order_table', array($this, 'order_page'));
		}
	}

	/**
	 * Get templates path.
	 *
	 * @return string
	 */
	public static function get_templates_path()
	{
		return plugin_dir_path(WC_PIX_PLUGIN_FILE) . 'templates/';
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency()
	{
		return 'BRL' === get_woocommerce_currency();
	}

	/**
	 * Get WhatsApp.
	 *
	 * @return string
	 */
	public function get_whatsapp()
	{
		return $this->whatsapp;
	}

	/**
	 * Get Telegram.
	 *
	 * @return string
	 */
	public function get_telegram()
	{
		return $this->telegram;
	}

	/**
	 * Get Email.
	 *
	 * @return string
	 */
	public function get_email()
	{
		return $this->email;
	}

	/**
	 * Get key.
	 *
	 * @return string
	 */
	public function get_key()
	{
		return $this->key;
	}

	/**
	 * Get chave_pjbank.
	 *
	 * @return string
	 */
	public function get_chave_pjbank()
	{
		return $this->chave_pjbank;
	}

	/**
	 * Get lojista.
	 *
	 * @return string
	 */
	public function get_merchant()
	{
		return $this->merchant;
	}

	/**
	 * Get city.
	 *
	 * @return string
	 */
	public function get_city()
	{
		return $this->city;
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available()
	{
		// Test if is valid for use.
		$available = 'yes' === $this->get_option('enabled') && '' !== $this->get_key() && '' !== $this->get_city() && '' !== $this->get_merchant() && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields()
	{
			
		$this->form_fields = array(
			'enabled'              => array(
				'title'   => __('Habilitar/Desabilitar', 'woocommerce-pix-pjbank'),
				'type'    => 'checkbox',
				'label'   => __('Habilitar Pix PJBank', 'woocommerce-pix-pjbank'),
				'default' => 'yes',
			),
			'homologacao' => array(
                'title' => __( 'Ambiente Homologação ?', 'woocommerce-pix-pjbank'),
                'type' => 'checkbox',
                'description' => __( 'Informa ao plugin ambiente de homologação', 'woocommerce-pix-pjbank'),
                'default' => 'yes',
            ),
			'title'                => array(
				'title'       => __('Título', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Representa o título visível para o usuário comprador', 'woocommerce-pix-pjbank'),
				'desc_tip'    => true,
				'default'     => __('Faça um Pix', 'woocommerce-pix-pjbank'),
			),
			'icon_color'                => array(
				'title'       => __('Cor do ícone do PIX', 'woocommerce-pix-pjbank'),
				'type'        => 'select',
				'description' => __('Cor do ícone que vai aparecer ao lado do título', 'woocommerce-pix-pjbank'),
				'desc_tip'    => true,
				'options'     => array(
					'black' => 'Preto',
					'white' => 'Branco',
					'green' => 'Verde'
				),
				'default'     => 'green',
			),
			'key_origin'                => array(
				'title'       => __('Origem da Credencial PJBank', 'woocommerce-pix-pjbank'),
				'type'        => 'select',
				'description' => __('Se a Credencial for retirad do VTigerCRM utilize formato Json (cnpj:credencial)', 'woocommerce-pix-pjbank'),
				'desc_tip'    => true,
				'options'     => array(
					'vtigercrm' => 'VtigerCRM',
					'fixed' =>  'Fixa'
				),
				'default'     => 'random',
			),
			'description'          => array(
				'title'       => __('Descrição', 'woocommerce-pix-pjbank'),
				'type'        => 'textarea',
				'description' => __('Representa a descrição que o usuário verá na tela de checkout', 'woocommerce-pix-pjbank'),
				'default'     => __('Ao finalizar a compra, iremos gerar o código copia-cola e QRCode Pix para pagamento na próxima tela.', 'woocommerce-pix-pjbank'),
			),
			'integration'          => array(
				'title'       => __('Integração com PJBank', 'woocommerce-pix-pjbank'),
				'type'        => 'title',
				'description' => '',
			),
			'key'                => array(
				'title'       => __('Credencial PJBank (obrigatório)', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Por favor, informe sua Credencial, ela foi informada no momento do seu credenciamento. Ela é necessária para poder processar as cobranças.', 'woocommerce-pix-pjbank'),
				'default'     => '',
				'required'	  => true,
			),
			'chave_pjbank' => array(
				'title'       => __('Chave PJBank (obrigatório)', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Por favor, informe a Chave, ela foi informada no momento do seu credenciamento .<br>Máximo de 10 caracteres.<br>Ela é importante identificar o pagamento no extrato do PIX.', 'woocommerce-pix-pjbank'),
				'default'     => '',
				'required'	  => true,
			),
			'juros' => array(
                'title' => __( 'Juros pagamento à vista', 'woocommerce-pix-pjbank'),
                'type' => 'number',
                'description' => __( 'Taxa de juros ao mês em Porcentagem (caso seja enviado junto com o campo juros_fixo = 1, a taxa será em Reais). A taxa diária será calculada a partir do valor informado dividido por 30. Casas decimais devem ser separadas por ponto, máximo de 2 casas decimais, não enviar caracteres diferentes de número ou ponto.', 'woocommerce-pix-pjbank'),
                'desc_tip' => true,
				'default'     => 0,
            ),
			'juros_fixo' => array(
                'title' => __( 'Juros Fixo', 'woocommerce-pix-pjbank'),
                'type' => 'number',
                'description' => __( 'Valor do juros enviado no campo juros será fixo. Não enviar caracteres diferentes de número.', 'woocommerce-pix-pjbank'),
                'desc_tip' => true,
				'default'     => 0,
            ),
			'merchant'                => array(
				'title'       => __('Nome do titular (obrigatório)', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Por favor, informe o nome do titular da conta bancária da chave PIX cadastrada.<br>Máximo de 25 caracteres.<br>Não abreviar o nome, apenas descartar os caracteres que excederem esse limite.<br>Retirar acentuação para melhor compatibilidade entre bancos: utilize apenas <code>A-Z</code>, <code>a-z</code> e <code>espaço</code>.', 'woocommerce-pix-pjbank'),
				'default'     => '',
				'required'	  => true,
				'custom_attributes' => [
					'maxlength' => 55
				]
			),
			'city'                => array(
				'title'       => __('Cidade do titular (obrigatório)', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Por favor, informe a cidade do titular da conta bancária da chave PIX cadastrada.<br>Máximo de 15 caracteres.<br>Não abreviar a cidade, apenas descartar os caracteres que excederem esse limite.<br>Retirar acentuação para melhor compatibilidade entre bancos: utilize apenas <code>A-Z</code>, <code>a-z</code> e <code>espaço</code>', 'woocommerce-pix-pjbank'),
				'default'     => '',
				'required'	  => true,
				'custom_attributes' => [
					'maxlength' => 25
				]
			),
			'webhook' => array(
                'title' => __( 'URL Webhook'),
                'type' => 'text',
                'description' => __( 'URL que será chamada em caso de alterações na transação (consultar documentação do PJBank)', 'woocommerce-pix-pjbank'),
                'desc_tip' => true,
				'default'     => get_site_url() . '/wp-content/plugins/woocommerce-pix-pjbank/webhook-pix.php',
            ),
			'whatsapp'                => array(
				'title'       => __('WhatsApp para contato', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Seu número de WhatsApp será informado ao cliente para compartilhar o comprovante de pagamento. Modelo: 5548999999999', 'woocommerce-pix-pjbank'),
				'default'     => '',
			),
			'telegram'                => array(
				'title'       => __('Telegram para contato', 'woocommerce-pix-pjbank'),
				'type'        => 'text',
				'description' => __('Seu username do Telegram será informado ao cliente para compartilhar o comprovante de pagamento. Informe o username sem @.
				Exemplo: jondoe.', 'woocommerce-pix-pjbank'),
				'default'     => '',
			),
			'email'                => array(
				'title'       => __('Email para contato', 'woocommerce-pix-pjbank'),
				'type'        => 'email',
				'description' => __('Seu email será informado ao cliente para compartilhar o comprovante de pagamento.', 'woocommerce-pix-pjbank'),
				'default'     => get_option('admin_email'),
			),
			'instructions' => array(
				'title'       => __('Instruções', 'woocommerce-pix-pjbank'),
				'type'        => 'textarea',
				'description' => __('Instruções na página de obrigado pela compra', 'woocommerce-pix-pjbank'),
				'default'     => 'Utilize o seu aplicativo favorito do Pix para ler o QR Code ou copiar o código abaixo e efetuar o pagamento.',
			),
			'send_on_hold_email' => array(
				'title'       => __('Enviar o QR Code e o código Pix no e-mail para pagamento?', 'woocommerce-pix-pjbank'),
				'type'    => 'checkbox',
				'label'   => __('Enviar o QR Code e o código Pix no e-mail para pagamento?', 'woocommerce-pix-pjbank'),
				'description' => __('A imagem de cada QR Code será salva no seu servidor para ser renderizada no e-mail.', 'woocommerce-pix-pjbank'),
				'default' => 'no',
			),
		);
	}

	/**
	 * Admin page.
	 */
	public function admin_options()
	{

		include dirname(__FILE__) . '/admin/views/html-admin-page.php';
	}

	/**
	 * Send email notification.
	 *
	 * @param string $subject Email subject.
	 * @param string $title   Email title.
	 * @param string $message Email message.
	 */
	protected function send_email($subject, $title, $message)
	{
		$mailer = WC()->mailer();

		$mailer->send(get_option('admin_email'), $subject, $mailer->wrap_message($title, $message));
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields()
	{

		$description = $this->get_description();
		if ($description) {
			echo wpautop(wptexturize($description)); // WPCS: XSS ok.
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 * @return array
	 */

	public function pix_Pjbank($order_id)
	{
		global $woocommerce;
		$order = wc_get_order($order_id);

		// Páginda de Configuração
		$juros = $this->get_option('juros');
		$juros_fixo = $this->get_option('juros_fixo');
		$multa = 0;
		$multa_fixo = 0;
		$desconto = 0;
		$diasdesconto1 = 0;
		$desconto2 = 0;
		$diasdesconto2 = 0;
		$desconto3 = 0;
		$diasdesconto3 = 0;
		$nunca_atualizar_boleto = 0;

		$credencial_cliente = $this->get_option('key');
		$chave_pjbank = $this->get_option('chave_pjbank');

		$logo_url = $this->get_option('logo_url');
		$texto = $this->get_option('instructions');
		$instrucoes = $this->get_option('instructions');
		$instrucao_adicional = $this->get_option('instructions');
		$webhook = $this->get_option('webhook');

		$add_dias = $this->get_option('add_dias');

		$add_dias = '+' . $add_dias . ' day';
		$hoje = new DateTime('Now');
		$hoje->modify($add_dias);
		$vencimento =  $hoje->format('m/d/Y');

		// fixos
		$especie_documento = 'DS';
		$pix = 'pix';
		$grupo = "";
		$exibir_zoom_boleto = '1';

// Get the Customer data
// Customer billing information details
$nome_cliente = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ' ' . $order->get_billing_company();
$email_cliente = $order->get_billing_email();

// Get the user ID from an Order ID
$user_id = get_post_meta( $order_id, '_customer_user', true );

// Get the WP_User instance Object
$user = new WP_User( $user_id );
$cpf_cliente = $user->billing_cpf;

$telefone_cliente = $order->get_billing_phone();
$endereco_cliente  = $order->get_billing_address_1();
$numero_cliente  = $order->get_billing_number(); // billing_number
$complemento_cliente  = $order->get_billing_address_2();
$bairro_cliente = $order->get_billing_neighborhood(); // billing_neighborhood
$cidade_cliente       = $order->get_billing_city();
$estado_cliente      = $order->get_billing_state();
$cep_cliente   = $order->get_billing_postcode();

		// Pedido
		if(empty($nome_cliente)){
			$nome_cliente =  'Cliente';
		}
		if(empty($email_cliente)){
			$email_cliente =  '';
		}
		if(empty($cpf_cliente)){
			$cpf_cliente =  '';
		}
		if(empty($telefone_cliente)){
			$telefone_cliente =  '';
		}
		if(empty($endereco_cliente)){
			$endereco_cliente =  '';
		}
		if(empty($numero_cliente)){
			$numero_cliente =  '';
		}
		if(empty($complemento_cliente)){
			$complemento_cliente =  '';
		}
		if(empty($bairro_cliente)){
			$bairro_cliente =  '';
		}		
		if(empty($cidade_cliente)){
			$cidade_cliente =  '';
		}
		if(empty($estado_cliente)){
			$estado_cliente =  '';
		}
		if(empty($cep_cliente)){
			$cep_cliente =  '';
		}

		$pedido_numero = $order_id;
		$valor = $order->total;

		$curl = curl_init();

		$data_request_pix = json_encode(array(
			"vencimento" => $vencimento,
			"valor" => $valor,
			"juros" => $juros,
			"juros_fixo" => $juros_fixo,
			"multa" => $multa,
			"multa_fixo" => $multa_fixo,
			"desconto" => $desconto,
			"nunca_atualizar_boleto" => $nunca_atualizar_boleto,
			"nome_cliente" => $nome_cliente,
			"email_cliente" => $email_cliente,
			"telefone_cliente" => $telefone_cliente,
			"cpf_cliente" => $cpf_cliente,
			"endereco_cliente" => $endereco_cliente,
			"numero_cliente" => $numero_cliente,
			"complemento_cliente" => $complemento_cliente,
			"bairro_cliente" => $bairro_cliente,
			"cidade_cliente" => $cidade_cliente,
			"estado_cliente" => $estado_cliente,
			"cep_cliente" => $cep_cliente,
			"logo_url" => $logo_url,
			"texto" => $texto,
			"instrucoes" => $instrucoes,
			"instrucao_adicional" => $instrucao_adicional,
			"grupo" => $grupo,
			"webhook" => $webhook,
			"pedido_numero" => $pedido_numero,
			"especie_documento" => $especie_documento,
			"pix" => $pix,
			"exibir_zoom_boleto" => $exibir_zoom_boleto
		));
		
		$homologacao = $this->get_option('homologacao');
        $api = $homologacao =="yes" ? "sandbox" : "api";

		// Testes
		$homologacao = 'yes';

		
		$url1 = "https://".$api.".pjbank.com.br/recebimentos/" . $credencial_cliente . "/transacoes/";
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $data_request_pix,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);

		$id_unico = $response['id_unico'];

		
		// Add the notes
		if($homologacao == "yes"){
			$order->add_order_note( 'URK 1: ' . print_r($url1,true) );
			$order->add_order_note( 'REQUEST 1: ' . print_r($data_request_pix,true) );
			$order->add_order_note( 'RETORNO 1: ' . print_r($response,true) );
			$order->add_order_note( 'CREDENCIAL PJBank: ' . $credencial_cliente );
			$order->add_order_note( 'Chave PJBank: ' . $chave_pjbank );	
		}
		$order->add_order_note( 'ID UNICO: ' .  $id_unico );
		
		// Aguardar 20 segundos para gerar o boleto
		sleep(20);

		$curl = curl_init();

		$url2 = "https://".$api.".pjbank.com.br/recebimentos/" . $credencial_cliente . '/transacoes/' . $id_unico;

		$contulta_por_idunico = array(
			CURLOPT_URL => $url2,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'X-CHAVE: ' . $chave_pjbank . ''
			),
		);

		curl_setopt_array($curl, $contulta_por_idunico);
		
		$response = curl_exec($curl);

		curl_close($curl);

		$response = json_decode($response, true);
		$response_Qrcode = $response[0]['qrcode'];

		if($homologacao == "yes"){
			$order->add_order_note( 'URL 2: ' . print_r($url2,true) );
			$order->add_order_note( 'QRCODE: ' . $response_Qrcode );
			$order->add_order_note( 'RESPONSE 21: ' . print_r($response,true));	
		}

		return $response_Qrcode;
	
	}



	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status('on-hold', __('Awaiting offline payment', $this->domain));

		// Remove cart
		WC()->cart->empty_cart();

		// Reduce stock for billets.
		if (function_exists('wc_reduce_stock_levels')) {
			wc_reduce_stock_levels($order_id);
		}

		// Return thankyou redirect
		return array(
			'result'     => 'success',
			'redirect'    => $this->get_return_url($order)
		);
	}

	/**
	 * Render Pix code.
	 *
	 * @param int $order_id Order ID.
	 */
	public function render_pix($order_id)
	{
		$order = wc_get_order($order_id);
		if ($order->get_payment_method() != 'woocommerce-pix-pjbank') {
			return;
		}

		$pix = $this->generate_pix($order_id);
		if (!empty($pix)) { ?>
			<div class="wcpix-container" style="text-align: center;margin: 20px 0">
				<div class="wcpix-instructions">
					<?php
					if ($this->instructions) {
						echo wpautop(wptexturize($this->instructions));
					}
					?>
				</div>
				<input type="hidden" value="<?php echo wp_kses_post($pix['link']); ?>" id="copiar">
				<img style="cursor:pointer; display: initial;" class="wcpix-img-copy-code" onclick="copyCode()" src="<?php echo wp_kses_post($pix['image']); ?>" alt="QR Code" />
				<br>
				<p class="wcpix-p" style="font-size: 14px;margin-bottom:0;word-break: break-all;"><?php echo wp_kses_post($pix['link']); ?></p>
				<br><button class="button wcpix-button-copy-code" style="margin-bottom: 20px;margin-left: auto;margin-right: auto;" onclick="copyCode()"><?php echo wp_kses_post(__('Clique aqui para copiar o Código acima', 'woocommerce-pix-pjbank')); ?> </button><br>
				<div class="wcpix-response-output inactive" style="margin: 2em 0.5em 1em;padding: 0.2em 1em;border: 2px solid #46b450;display: none;" aria-hidden="true" style=""><?php echo wp_kses_post(__('O código foi copiado para a área de transferência.', 'woocommerce-pix-pjbank')); ?></div>
				<?php
				if ($this->whatsapp || $this->telegram || $this->email) {
					echo wp_kses_post('<br>' . __('<span class="wcpix-explain">Você pode compartilhar conosco o comprovante via:</span>', 'woocommerce-pix-pjbank'));
					if ($this->whatsapp) {
						echo wp_kses_post(' <a class="wcpix-whatsapp" style="margin-right: 15px;" target="_blank" href="https://wa.me/' . $this->whatsapp . '?text=Segue%20meu%20comprovante%20para%20o%20pedido%20' . $order_id . '"> WhatsApp </a>');
					}
					if ($this->telegram) {
						echo wp_kses_post(' <a class="wcpix-telegram" style="margin-right: 15px;" target="_blank" href="https://t.me/' . $this->telegram . '?text=Segue%20meu%20comprovante%20para%20o%20pedido%20' . $order_id . '">Telegram </a>');
					}
					if ($this->email) {
						echo wp_kses_post(' <a class="wcpix-email" style="margin-right: 15px;" target="_blank" href="mailto:' . $this->email . '?subject=Comprovante%20pedido%20' . $order_id . '&body=Segue%20meu%20comprovante%20anexo%20para%20o%20pedido%20' . $order_id . '">Email.</a>');
					}
				}
				?>
			</div>
			<script>
				function copyCode() {
					var copyText = document.getElementById("copiar");
					copyText.type = "text";
					copyText.select();
					copyText.setSelectionRange(0, 99999)
					document.execCommand("copy");
					copyText.type = "hidden";

					if (jQuery("div.wcpix-response-output")) {
						jQuery("div.wcpix-response-output").show();
					} else {
						alert('O código foi copiado para a área de transferência.');
					}

					return false;
				}
			</script>
<?php
		}
	}

	/**
	 * Order Page message.
	 *
	 * @param int $order Order.
	 */
	public function order_page($order)
	{
		$order_id = $order->get_id();
		return $this->render_pix($order_id);
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page($order_id)
	{
		return $this->render_pix($order_id);
	}

	public function generate_pix($order_id)
	{
		$order = wc_get_order($order_id);
		$pix = new ICPFW_QRCode();
		// $this->pix_Pjbank($order_id);

		$link = $this->pix_Pjbank($order_id);

		$image = $pix->toImage($link);

		$pix = array(
			'image' => $image,
			'link' => $link,
			'instructions' => $this->instructions,
		);

		return $pix;
	}

	/**
	 * Add content to the WC emails.
	 */
	public function email_instructions($order, $sent_to_admin, $plain_text, $email)
	{
		if ($order->get_payment_method() === $this->id && get_class($email) === 'WC_Email_Customer_On_Hold_Order') {
			$pix = $this->generate_pix($order->get_id());
			wc_get_template(
				'email-on-hold-pix.php',
				$pix,
				'',
				$this->get_templates_path()
			);
		}
	}
}
