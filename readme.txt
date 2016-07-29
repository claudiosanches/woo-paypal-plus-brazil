=== Plugin Name ===
Contributors: eliasjnior-1
Donate link: http://eliasjrweb.com/donate/
Tags: paypal, plus, woocommerce, payment, transaction, credit card
Requires at least: 4.4
Tested up to: 4.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por cartão de crédito utilizando o PayPal Plus

== Description ==

A sua experiência de checkout com a segurança do PayPal. O pagamento é feito diretamente em seu site, sem redirecionar seus clientes, e processado com toda a tecnologia e segurança do PayPal. Mais segurança, maior conversão de vendas. Veja mais [aqui](https://www.paypal.com/br/webapps/mpp/paypal-payments-pro).

= Compatibilidade =

Compatível à partir da versão 2.2.x até a 2.6.x do WooCommerce.

Este plugin funciona integrado com o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/), desta forma é possível enviar documentos do cliente como "CPF" ou "CNPJ", além dos campos "número" e "bairro" do endereço.

= Instalação =

Confira o nosso guia de instalação e configuração do PayPal na aba [Installation](http://wordpress.org/plugins/paypal-plus-brazil-for-woocommerce/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/plugins/paypal-plus-brazil-for-woocommerce/faq/).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/paypal-plus-brazil-for-woocommerce).
* Criando um tópico no [fórum do Github](https://github.com/eliasjnior/paypal-plus-brazil-for-woocommerce/issues).

= Colaborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/eliasjnior/paypal-plus-brazil-for-woocommerce).

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

É necessário possuir uma conta no [PayPal](https://paypal.com.br/) e ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/) e o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

Também será necessário que sua conta esteja ativada para utilizar o serviço. Verifique os requisitos na [landing page do PayPal Plus](https://www.paypal.com/br/webapps/mpp/paypal-payments-pro).

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra" e configure as opção "PayPal Plus Brazil".

Habilite a opção que você deseja, preencha as opções de **Chave do cliente** e **Chave Secreta** que você pode encontrar dentro da sua conta no PayPal.

Para obter esses dados, acesse o [PayPal Developer](https://developer.paypal.com/), faça login com a sua conta do PayPal, acesse seu dashboard e na aba de [My Apps & Credentials](https://developer.paypal.com/developer/applications/) encontre a sessão **REST API apps** e [crie uma nova aplicação](https://developer.paypal.com/developer/applications/create) ou abra o aplicativo já cadastrado.

Com os dados em mãos, configure na sessão do plugin o **Client ID** e **Secret**. À partir daí, caso sua conta seja ativado para a utilização do PayPal Plus, o plugin já estará ativo e funcionando corretamente.

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado o plugin WooCommerce 2.2 ou superior.
* Ter instalado o plugin WooCommerce Extra Checkout Fields for Brazil.
* Possuir uma conta no [PayPal](https://paypal.com.br/).
* Pegar sua **Chave de API** e **Chave de Criptografia** no PayPal.

= Quanto custa o PayPal =

Confira os preços em "[PayPal Fees](https://www.paypal.com/br/webapps/mpp/paypal-fees)"

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo ? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= É obrigatório enviar todos os campos para processar o pagamento? =

Sim, devido a análise de risco do PayPal e a necessidade para criar os endereços de pagamento pela API.

= Problemas com a integração? =

Primeiro de tudo ative a opção **Log de depuração** e tente realizar o pagamento novamente.
Feito isso copie o conteúdo do log e salve usando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com), depois basta abrir um tópico de suporte [aqui](http://wordpress.org/support/plugin/paypal-plus-brazil-for-woocommerce).

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Entre em contato [clicando aqui](http://wordpress.org/support/plugin/paypal-plus-brazil-for-woocommerce).

== Screenshots ==

1. Exemplo de checkout com cartão de crédito do PayPal no tema Storefront.
2. Exemplo de checkout com cartão de crédito salvo.
3. Configurações do plugin.

== Changelog ==

= 1.0.0 - 2016/08/01 =

* Versão inicial do plugin.

== Upgrade Notice ==

= 1.0.0 =

* Versão inicial do plugin.
