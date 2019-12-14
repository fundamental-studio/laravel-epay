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

``` php
/**
 * Constructing the Epay class instance.
 *
 * @param String $type Can be either paylogin or credit_paydirect.
 * @param array $data May be ommitted and use the setData function.
 * @param String $language Can be either BG or EN.
 */
new Epay(String $type = 'paylogin', array $data = [], String $language = 'BG')

/**
 * Setting main data for creating and sending the request.
 *
 * @param String $invoice
 * @param [type] $amount The amount
 * @param String $expiration
 * @param String $description Invoice description content in less than 100 symbols.
 * @param string $currency
 * @param [type] $encoding
 * @return void
 */
public function setData($invoice = false, $amount, $expiration = false, String $description = '', $currency = 'BGN', $encoding = null)

/**
 * Setter for invoice number.
 *
 * @param String $invoice The invoice number.
 * @return void
 */
public function setInvoice($invoice): void

/**
 * Get the set or generated invoice number.
 *
 * @return String
 */
public function getInvoice(): String

/**
 * Setter for amount number.
 *
 * @param double|float|String $amount The invoice amount.
 * @return void
 */
public function setAmount($amount): void

/**
 * Get the invoice amount.
 *
 * @return Double
 */
public function getAmount(): Double

/**
 * Setter for expiration date in format d.m.Y H:i:s
 *
 * @param String $expiration Date format: d.m.Y H:i:s
 * @return void
 */
public function setExpiration($expiration): void

/**
 * Get the already set expiration time.
 *
 * @return String
 */
public function getExpiration(): String

/**
 * Setter for invoice description parameter.
 *
 * @param String $description Length should be less than 100 symbols.
 * @return void
 */
public function setDescription($description): void

/**
 * Get the already set description parameter.
 *
 * @return String
 */
public function getDescription(): String

/**
 * Send request to the ePay platform for 10 digit code generation and retrieve
 *
 * @return String
 */
public function requestIDNumber(): String

/**
 * Retrieve the requested and generated IDN for in place payment at EasyPay
 *
 * @return String
 */
public function getEasypayIDN(): String

/**
 * Parse result and get all status and ePay generated fields as array.
 *
 * @param array $data Should include encoded and checksum members of the array.
 * @return array
 */
public static function parseResult(array $data): array

/**
 * Generate the checksum of the send or already initialized data array.
 *
 * @param boolean $data
 * @return void
 */
public function generateChecksum($data = false)

/**
 * Get the encoded data string.
 *
 * @return String
 */
public function getEncoded(): String

/**
 * Get the calculated checksum string.
 *
 * @return String
 */
public function getChecksum(): String

/**
 * Get the target url for the ePay platform, using the english version and the test parameter.
 *
 * @return String
 */
public function getTargetUrl(): String

/**
 * Get all hidden input fields for the needed request.
 *
 * @param boolean $urlOk Using the default value from the config or being ommitted.
 * @param boolean $urlCancel Using the default value from the config or being ommitted.
 * @return String All needed hidden input fields
 */
public function generatePaymentFields($urlOk = false, $urlCancel = false): String

/**
 * Returns a html form with all hidden input fields for the needed request.
 *
 * @param String $id The id element of the generated form.
 * @param boolean $urlOk Using the default value from the config or being ommitted.
 * @param boolean $urlCancel Using the default value from the config or being ommitted.
 * @return String Html form with all hidden fields and set id attribute
 */
public function generatePaymentForm(String $id = '', $urlOk = false, $urlCancel = false): String

/**
 * Get all request parameters for making the ePay request on your own.
 *
 * @return array
 */
public function getPaymentParameters(): array
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