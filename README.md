# Laravel ePay, EasyPay, BPay, ePay World integration module
Laravel wrapper for easy and seamless integration with all available ePay payment methods:
- ePay
- EasyPay(10 digit identification number)
- BPay
- ePay World

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
EPAY_PRODUCTION=FALSE
EPAY_MIN=XXXXXXXXXX
EPAY_SECRET=XXXXXXXXXX
EPAY_DEFAULT_CURRENCY="BGN"
EPAY_DEFAULT_URL_OK="https://myurl.com/"
EPAY_DEFAULT_URL_CANCEL="https://myurl.com/"
EPAY_GENERATE_INVOICE=TRUE
EPAY_EXPIRATION_HOURS=72
```

You are up & running and ready to go.

## Documentation and Usage instructions

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