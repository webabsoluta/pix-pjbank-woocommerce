=== Pix para WooCommerce ===

Contributors: webabsoluta, marcos-26

Tags: woocommerce, wordpress, pjbank, payment gateway, gateway, pix

Requires WooCommerce at least: 2.1

Tested up to: 6.0

Requires PHP: 7.1

Stable Tag: 1.4.1

License: GPLv3.0

License URI: http://www.gnu.org/licenses/gpl-3.0.html

Habilita o Pix do PJBank como método de pagamento no WooCommerce com baixa automática

== Description ==

Plugin do WordPress para para receber seus pagamentos via [PIX](https://www.bb.com.br/pbb/pagina-inicial/pix#/) dinâmico com baixa automática no WooCommerce através da API do [PJBank] (https://docs.pjbank.com.br/). Plugin 

**O que esse plugin faz?**

- Adiciona um Gateway de pagamento para o WooCommerce.
- Facilita e  agiliza seus pagamentos eliminando um intermediário.
- Permite ao cliente envio de comprovantes via WhatsApp, Telegram ou E-mail.

**Veja como é fácil fazer suas vendas com o PIX:**

1. Você instala o Plugin.
2. Cadastra sua chave PIX.
3. O cliente finaliza a compra informando o PIX como meio de pagamento.
4. O cliente efetua o pagamento e envia o comprovante.
5. Você conclui a venda no Painel de administração do WooCommerce.
6. Envia o produto ao cliente.

>  **Requires: WooCommerce 2.1+**

== Pré-requisito ==

Como este plugin utiliza a API do PJBank para gerar os dados da cobrança PIX e também para a confirmação imediata do pagamento. Por esse motivo é necessário fazer o cadastro em um parceiro do PJBank. Acesse nosso <a href="https://webabsoluta.com.br/parceiro-pjbank-cadastro" target="_blank">Formulário de Cadastro aqui.</a>

== Installation ==

 1. Instalar o WooCommerce 2.1 + na sua loja, se já tem instalado pode ignorar esse passo.

 2. Instalar e ativar o Plugin PIX for WooCommerce, há três maneiras de instalar:

  - Baixar e descompactar o arquivo `*.zip` na pasta  `/wp-content/plugins/` ;
  - Fazer o upload do arquivo `*.zip`  via plugins do WordPress em  **Plugins &gt; Add New &gt; Upload**
  - Instalar e ativar o plugin por meio do menu **Plugins** no WordPress

3. Vá para  **WooCommerce &gt; Settings &gt; Payments** e selecione "Pix" para configurar.

4. Na página de Pagamentos adicione sua credencial e chave recebidas por email após o cadastro no Formulário acima.

== Configuração ==

Após a instalação e ativação, é necessário fazer a configuração dele, seguindo o caminho `WooCommerce > Configurações > Finalizar compra > PJBank - Cartão` no Painel Administrativo, onde será necessário configurar as opções abaixo:

* Habilitar/Desabilitar - Este checkbox irá ativar ou desativar o plugin.
* Credencial - Credencial PJBank da empresa, necessário para o correto funcionamento do plugin.
* Chave - Chave PJBank da empresa, necessário para o correto funcionamento do plugin.
* Título - O nome que o plugin irá exibir no final do checkout. Padrão: Boleto Bancário.
* Juros pagamento à vista - Juros para quando o pagamento for à vista. Valor será considerado como porcentagem. Padrão: 0


== Contribute ==

You can contribute with the code on [GitHub](https://github.com/webabsoluta/pix-pjbank-woocommerce).


== Credits ==

*  [Claudio Sanches](https://claudiosanches.com/) - we base this plugin on one of his plugins
* [InCuca Tech](https://br.wordpress.org/plugins/incuca-tech-pix-for-woocommerce) - Este plugin foi construído com base no plugin de PIX para Woocommerce da InCuca


== Changelog ==

= 2022.10.20 - version 1.0.0 =
