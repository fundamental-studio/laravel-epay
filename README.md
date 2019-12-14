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
$epay = new Epay('paylogin'); // Use either paylogin or credit_paydirect
$epay->setData(
    1000000000, // Could be either number or false(will be auto-generated if EPAY_GENERATE_INVOICE=TRUE)
    40.00, // Amount of the payment, double formatted either as double or string
    '14.12.2019 20:46:00', // Could be either formatted date in d.m.Y H:i:s or false(will be auto-generated)
    'Description of the payment in less than 100 symbols.', // Could be empty
    'BGN', // Available currencies: BGN, USD, EUR, default to bgn, may be ommited
    'utf-8' // Encoding, either null or utf-8, may be ommitted
);

$paymentFields = $epay->generatePaymentFields('https://ok.url', 'https://cancel.url'); // Would return all hidden fields as formatted html
$paymentFieldsForm = $epay->generatePaymentForm('#form-id', 'https://ok.url', 'https://cancel.url'); // Would return html form with the first parameter as id
```
The setData function could be ommitted. The data may be set as array and second parameter to the constructor of the main class.


Official ePay documentation can be found [here](https://www.epay.bg/v3main/img/front/tech_wire.pdf).
- Production ePay url endpoint: https://epay.bg/
- Demo ePay url endpoint: https://demo.epay.bg/

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