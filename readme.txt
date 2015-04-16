=== WooCommerce Checkout Cielo ===
Contributors: claudiosanches, Gabriel Reguly
Donate link: http://claudiosmweb.com/doacoes/
Tags: woocommerce, cielo, payment
Requires at least: 3.9
Tested up to: 4.1.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Checkout Cielo gateway to the WooCommerce plugin

== Description ==

Adicione o Checkout Cielo como método de pagamento em sua loja WooCommerce e permita seus clientes a pagarem usando cartão de crédito, cartão de débito, débito online e boleto bancário.

Checkout Cielo é uma solução de pagamento da [Cielo](https://www.cielo.com.br/).

O plugin WooCommerce Checkout Cielo foi desenvolvido sem nenhum incentivo da Cielo. Nenhum dos desenvolvedores deste plugin possuem vínculos com esta empresas.

Este plugin foi desenvolvido a partir da [Manual do desenvolvedor v1.3 da Cielo](https://www.cielo.com.br/wps/wcm/connect/9bed8c0a-ae62-4834-b322-8ad1d966e78f/CheckoutCielo+-+Manual+do+desenvolvedor+v1.3.pdf?MOD=AJPERES&CONVERT_TO=url).

= Compatibilidade =

Compatível com as versões 2.2.x e 2.3.x do WooCommerce.

Este plugin também é compatível com o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/), desta forma é possível enviar os campos de "CPF/CNPJ", "número do endereço" e "bairro".

= Instalação =

Confira o nosso guia de instalação e configuração na aba [Installation](http://wordpress.org/plugins/woocommerce-checkout-cielo/installation/).

= Integração =

Este plugin funciona perfeitamente em conjunto com:

* [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).
* [WooCommerce Multilingual](https://wordpress.org/plugins/woocommerce-multilingual/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* A nossa sessão de [FAQ](http://wordpress.org/plugins/woocommerce-checkout-cielo/faq/).
* Utilizando o nosso [fórum no Github](https://github.com/claudiosmweb/woocommerce-checkout-cielo).
* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/woocommerce-checkout-cielo).

= Coloborar =

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/claudiosmweb/woocommerce-checkout-cielo).

== Installation ==

= Instalação do plugin: =

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

= Requerimentos: =

É necessário possuir uma conta na [Cielo](https://www.cielo.com.br/sitecielo/afiliacao/credenciamentoafiliacaonaologado.html), onde deve ser solicitado acesso ao **Checkout Cielo** (conhecido anteriormente como *Solução Integrada*).

Também é necessário ter instalada a última versão do [WooCommerce](http://wordpress.org/plugins/woocommerce/).

= Configurações na Cielo: =

Antes de começar é necessário recuperar seu Merchant ID em [Cielo Backoffice > Configurações > Dados cadastrais](https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Account/Details).

Depois indo até [Cielo Backoffice > Configurações > Configurações da loja](https://cieloecommerce.cielo.com.br/Backoffice/Merchant/Configuration/Show#paymentMethods) é possível configurar para funcionar ou não é modo de testes e configurar as URLs de retorno, notificação e mudança de status (note que são indicadas as URLs certas dentro da página de configurações do plugin).

= Configurações do Plugin: =

Com o plugin instalado acesse o admin do WordPress e entre em "WooCommerce" > "Configurações" > "Finalizar compra" > "Checkout Cielo".

Nesta págína você pode habitar o Checkout Cielo, adicionando o Merchant ID indicado nos passos anteriores, além de poder visualizar as URLs que serão utilizadas para retorno, notificação e mudança de status.

Também é possível ativar e desativar o Antifraude e também ativar uma opção capaz de colocar o pedido como "processando" quando o pagamento é autorizado (neste caso, o status do pedido muda antes do pagamento ser capturado).

Você ainda pode definir o comportamento da integração utilizando as opções:

- **Ativar Antifraude:** Permite ativar e desativar o Antifraude da Cielo.
- **Completar pagamentos para cartões de crédito autorizados:** Muda o status do pedido para "processando" quando o cartão de crédito é apenas autorizado, normalmente isso acontece apenas quando o dinheiro é capturado.
- **Enviar apenas o total do pedido:** Permite enviar apenas o total do pedido no lugar da lista de itens, esta opção deve ser utilizada apenas quando o total do pedido no WooCommerce esta ficando diferente do total na Cielo.

= Configurações no WooCommerce =

No WooCommerce existe uma opção para cancelar a compra e liberar o estoque depois de alguns minutos.

Esta opção não funciona muito bem com a Cielo, pois pagamentos por débito online e boleto bancário podem demorar até 48 horas para serem validados.

Para impedir que os pagamentos sejam cancelados automaticamente pelo WooCommerce é necessário ir em "WooCommerce" > "Configurações" > "Produtos" > "Inventário" e limpar (deixe em branco) a opção **Manter estoque (minutos)**.

Pronto, sua loja já pode receber pagamentos pelo Checkout Cielo.

== Frequently Asked Questions ==

= Qual é a licença do plugin? =

Este plugin esta licenciado como GPL.

= O que eu preciso para utilizar este plugin? =

* Ter instalado a última versão do [WooCommerce](http://wordpress.org/plugins/woocommerce/).
* Possuir uma conta na Cielo habilitada para funcionar com o Checkout Cielo (antiga Solução Integrada).
* Recuperar seu Merchant ID no Backoffice da Cielo.
* Configurar as URLs de retorno, notificação de mudança de status.
* Seguir o nosso processo de [instalação](http://wordpress.org/plugins/woocommerce-checkout-cielo/installation/).

= Como funciona o Checkout Cielo? =

Veja como funciona em [Vendas online | Cielo e-Commerce](https://www.cielo.com.br/ecommerce).

= Como que plugin faz integração com a Cielo? =

Fazemos a integração baseada no [Manual do Desenvolvedor](https://www.cielo.com.br/wps/wcm/connect/9bed8c0a-ae62-4834-b322-8ad1d966e78f/CheckoutCielo+-+Manual+do+desenvolvedor+v1.3.pdf?MOD=AJPERES&CONVERT_TO=url).

= É possível enviar os dados de "Número", "Bairro" e "CPF" para a Cielo? =

Sim é possível, basta utilizar o plugin [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

= O pedido foi pago e ficou com o status de "processando" e não como "concluído", isto esta certo ? =

Sim, esta certo e significa que o plugin esta trabalhando como deveria.

Todo gateway de pagamentos no WooCommerce deve mudar o status do pedido para "processando" no momento que é confirmado o pagamento e nunca deve ser alterado sozinho para "concluído", pois o pedido deve ir apenas para o status "concluído" após ele ter sido entregue.

Para produtos baixáveis a configuração padrão do WooCommerce é permitir o acesso apenas quando o pedido tem o status "concluído", entretanto nas configurações do WooCommerce na aba *Produtos* é possível ativar a opção **"Conceder acesso para download do produto após o pagamento"** e assim liberar o download quando o status do pedido esta como "processando".

= Ao retornar a loja, não é exibida as informações do pedido, o que esta acontecendo de errado? =

O que acontece de errado, é que a API do Checkout Cielo ainda é muito pobre, não é possível enviar uma URL dinâmica para a Cielo e também a Cielo não retorna o cliente de volta utilizando qualquer parametro para que seja indentificado o pedido em questão.

Não gostou disso? Ótimo, porque eu também não estou safisteito com isso, entretanto tudo que podemos fazer é reclamar com a Cielo para tornar isto possível.

= Não consigo configurar o Google Analytics para detectar as vendas por este plugin, o que tem de errado? =

O mesmo problema da questão a cima, como o Checkout Cielo ainda tem uma API pobre, é impossível saber qual é o pedido do retorno.

= É possível funcionar com pagamentos recorrentes? =

O Checkout Cielo não possui nenhuma API ou forma para trabalhar com pagamentos recorrentes.

Entretanto é possível usar a opção de pagamentos recorrentes de forma manual do [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions/).

= A compra é cancelada após alguns minutos, mesmo com o pedido sendo pago, como resolvo isso? =

Para resolver este problema vá até "WooCommerce" > "Configurações" > "Produtos" > "Inventário" e limpe (deixe em branco) a opção **Manter Estoque (minutos)**.

= O total do pedido no WooCommerce é diferente do enviado para a Cielo, como eu resolvo isso? =

Caso você tenha este problema, basta marcar ativar a opção **Enviar apenas o total do pedido** na página de configurações do plugin.

= Mais dúvidas relacionadas ao funcionamento do plugin? =

Por favor, caso você tenha algum problema com o funcionamento do plugin, envie o log (ative ele nas opções do plugin e tente fazer uma compra, ele vai ficar dentro da pasta wp-content/plugins/woocommerce/logs/) usando o [pastebin.com](http://pastebin.com) ou o [gist.github.com](http://gist.github.com), desta forma fica mais rápido para fazer o diagnóstico.

Com o log em mãos abra um [tópico em nosso fórum](http://wordpress.org/support/plugin/woocommerce-checkout-cielo).

== Screenshots ==

1. Configurações do plugin.
2. Exemplo do método de pagamento na página de finalização do pedido.

== Changelog ==

= 1.0.2 - 2015/04/16 =

* Corrigida a compatibilidade com o WooCommerce 2.2.

= 1.0.1 - 2015/04/12 =

* Atualizada a tradução do plugin.
* Criada documentação em Português.

= 1.0.0 - 2015/04/07 =

* Versão inicial do plugin.

== Upgrade Notice ==

= 1.0.2 =

* Corrigida a compatibilidade com o WooCommerce 2.2.

== License ==

WooCommerce Checkout Cielo is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

WooCommerce Checkout Cielo is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with WooCommerce Checkout Cielo. If not, see <http://www.gnu.org/licenses/>.
