# NETS A/S - Opencart 2.3 Payment Module
============================================

|Module | Nets Easy Payment Module for Opencart 2.3
|------|----------
|Author | `Nets eCom`
|Prefix | `EASY-OC23`
|Shop Version | `2.3+`
|Version | `1.1.1`
|Guide | https://tech.nets.eu/shopmodules
|Github | https://github.com/DIBS-Payment-Services/opencart2.3_easy

## INSTALLATION

### Download / Installation

1. Copy module files to your Opencart installation directory.
2. It is possible to install module using modman.
   Run the below commands in the terminal on opencart root directory.
	- git clone https://github.com/sitewards/modman-php OR Copy files into opencart root directory from https://github.com/sitewards/modman-php
	- php modman.php clone https://github.com/DIBS-Payment-Services/opencart2.3_easy
3. Apply modifications in (Admin -> Modifications) click Refresh button
3. Set module settings in (Admin -> Extensions -> Payments -> Easy-> Edit) and save

NOTE : .

### Features
1. Supports shipping methods in embedded checkout
2. Supports traditional checkout with Hosted Payment Window


### Configuration
1. To configure and setup the plugin navigate to : Admin > Extensions > Extensions > Choose the extension type > Payments 
2. Locate the Nets payment plugin and press the Edit button to access Configuration.

* Settings Description
1. Login to your Nets Easy account (https://portal.dibspayment.eu/). Test and Live Keys can be found in Company > Integration.
2. Payment Environment. Select between Test/Live transactions. Live mode requires an approved account. Testcard information can be found here: https://tech.dibspayment.com/easy/test-information 
3. Checkout Flow. Redirect / Embedded. Select between 2 checkout types. Redirect - Nets Hosted loads a new payment page. Embedded checkout inserts the payment window directly on the checkout page.
4. Enable auto-capture. This function allows you to instantly charge a payment straight after the order is placed.
   NOTE. Capturing a payment before shipment of the order might be lia ble to restrictions based upon legislations set in your country. Misuse can result in your Easy account bei ng forfeit.

### Operations
* Order Details / Order Status
1. Navigate to admin > Sales > Orders. Press on view (Eye Icon) to access order details.
2. Choose your desired order status in order history. Payment Id is searchable in Nets Easy portal.
3. All transactions by Nets are accessible in our portal : https://portal.dibspayment.eu/login

### Troubleshooting
* Nets payment plugin is not visible as a payment method
- Ensure the Nets plugin is available in the extension configuration.
- Edit the Easy Checkout configuration, Choose the status Enable.

* Nets payment window is blank
- Ensure your keys in Nets plugin Settings are correct and with no additional blank spaces.
- Temporarily deactivate 3.rd party plugins that might effect the functionality of the Nets plugin.
- Check if there is any temporary technical inconsistencies : https://nets.eu/Pages/operational-status.aspx

* Payments in live mode dont work
- Ensure you have an approved Live Easy account for production.
- Ensure your Live Easy account is approved for payments with selected currency.
- Ensure payment method data is correct and supported by your Nets Easy agreement.

### Contact
* Nets customer service
- Nets Easy provides support for both test and live Easy accounts. Contact information can be found here : https://nets.eu/en/payments/customerservice/

** CREATE YOUR FREE NETS EASY TEST ACCOUNT HERE : https://portal.dibspayment.eu/registration **
