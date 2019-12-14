# Laravel ePay, EasyPay, BPay, ePay World integration module
Laravel wrapper for easy and seamless integration with all available ePay payment methods:
- ePay
- EasyPay(10 digit identification number)
- BPay(Payment using ATM machine withdraw)
- ePay World(Payment using debit/credit card)

Made with love and code by [Fundamental Studio Ltd.](https://www.fundamental.bg)

## Installation

The package is compatible with Laravel 5.8+ version.

Via composer:
``` bash
$ composer require fmtl-studio/laravel-epay
```

After installing, the package should be auto-discovered by Laravel.
In order to configurate the package, you need to publish the config file using this command:
``` bash
$ php artisan vendor:publish --provider="Fundamental\Epay\EpayServiceProvider"
```

After publishing the config file, you should either add the needed keys to the global .env Laravel file:
```
EPAY_PRODUCTION=FALSE # Should the
EPAY_MIN=XXXXXXXXXX # Official KIN number from the ePay platform
EPAY_SECRET=XXXXXXXXXX # Secret token from the ePay platform
EPAY_DEFAULT_CURRENCY="BGN" # Default currency
EPAY_DEFAULT_URL_OK="https://myurl.com/"
EPAY_DEFAULT_URL_CANCEL="https://myurl.com/"
EPAY_GENERATE_INVOICE=TRUE # Should the package generate random invoice number if one isn't presented
EPAY_EXPIRATION_HOURS=72 # What is the period(in hours) that tha package should add to the current timestamp for an expiration date
```

You are up & running and ready to go.

## Documentation and Usage instructions

The usage of our package is pretty seamless and easy.
First of all, you need to use the proper namespace for our package:
```
use Fundamental\Epay\Epay;
```

Creating the instance of our package:
``` php
$epay = new Epay('paylogin', array $data, 'BG'); // Use either paylogin or credit_paydirect, the second parameter is documented in the next section and the third parameter is the request language page will be shown in: BG or EN, default: BG.
$epay->setData(
    1000000000, // Could be either number or false(will be auto-generated if EPAY_GENERATE_INVOICE=TRUE)
    40.00, // Amount of the payment, double formatted either as double or string
    '14.12.2019 20:46:00', // Could be either formatted date in d.m.Y H:i:s or false(will be auto-generated)
    'Description of the payment in less than 100 symbols.', // Could be empty
    'BGN', // Available currencies: BGN, USD, EUR, default to bgn, may be ommited
    'utf-8' // Encoding, either null or utf-8, may be ommitted
);
```
The setData function could be ommitted. The data may be set as array and second parameter to the constructor of the main class.
``` php
$epay = new Epay('paylogin', [
    'invoice' => 1000000000, // Could be either number or false(will be auto-generated if EPAY_GENERATE_INVOICE=TRUE)
    'amount' => 40.00, // Amount of the payment, double formatted either as double or string
    'expiration' => '14.12.2019 20:46:00', // Could be either formatted date in d.m.Y H:i:s or false(will be auto-generated)
    'description' => 'Description of the payment in less than 100 symbols.' // Could be empty
]);
```
All available methods are shown into the next section, including setter and getter methods.

Retrieve the correct and formatted hidden fields, form, or array with all the needed parameters.
``` php
// Both, URL OK and URL Cancel can be ommitted as not required by the ePay platform.

// Would return all hidden fields as formatted html
$epay->generatePaymentFields('https://ok.url', 'https://cancel.url');

// Would return html form with the first parameter as id
$epay->generatePaymentForm('#form-id', 'https://ok.url', 'https://cancel.url');

// Would return array with all needed parameters for the platform request you need to do on your own
$epay->getPaymentParameters();
```
All available methods are shown into the next section.

All current requests can be used as a form for redirecting the user to the ePay platform, including:
- Payment through your ePay account
- BPay code for ATM withdraw using 6 digit code
- Payment through debit/credit card throught ePay World (If ePay World is included into your ePay contract)
- EasyPay tab, which will give you 10 digit code for payment

However, if you need and want to integrate your platform with the EasyPay 10 digit code yourself, you can use:
``` php
// Using the initialization code from the upper piece of code
$easyPayIDN = $epay->requestIDNumber(); // Returning the 10 digit number for EasyPay payment or throws an exception
$epay->getEasypayIDN(); // Available method if needed and not assigned the requestIDNumber() to a variable
```

It is also possible to parse the return results and output them as array:
``` php
// Would like the $response array to have two members encoded and checksum.
$results = Fundamental\Epay\Epay::parseResult($response); // Will return full array of data, if the checksum check equals true
```

You can find our more about the available methods and differences between the paylogin and credit_paydirect types.
All available methods are shown into the next section.

Official ePay documentation can be found [here](https://www.epay.bg/v3main/img/front/tech_wire.pdf).
- Production ePay url endpoint: https://epay.bg/
- Demo ePay url endpoint: https://demo.epay.bg/

## Methods
All available methods with their arguments and return formats.

### new Epay($type, array $data, $language)
``` php
/**
 * Undocumented function
 *
 * @param String $page
 * @param array $data
 * @param String $language
 */
```

``` php
/**
 * Undocumented function
 *
 * @param string $invoice
 * @param [type] $amount
 * @param string $expiration
 * @param string $description
 * @return void
 */
public function setData($invoice = false, $amount, $expiration = false, String $description = '', $currency = 'BGN', $encoding = null)

/**
 * Undocumented function
 *
 * @param [type] $invoice
 * @return void
 */
public function setInvoice($invoice): void
```

## Changelog
All changes are available in our Changelog file.

## Support
For any further questions, feature requests, problems, ideas, etc. you can create an issue tracker or drop us a line at support@fundamental.bg

## Contributing
Read the Contribution file for further information.

## Credits

- Konstantin Rachev
- Vanya Ananieva

The package is bundled and contributed to the community by Fundamental Studio Ltd.'s team.

## Issues
If you discover any issues, please use the issue tracker.

## Security
If your discover any security-related issues, please email konstantin@fundamental.bg or support@fundamental.bg instead of using the issue tracker.

## License
The MIT License(MIT). See License file for further information and reading.