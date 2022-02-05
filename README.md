# Laravel Perfect Money

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

## Install

Via Composer

``` bash
$ composer require tuyenlaptrinh/perfect-money
```

Add Provider

``` php
tuyenlaptrinh\PerfectMoney\PerfectMoneyServiceProvider::class,
```

Add Aliases

``` php
'PerfectMoney' => tuyenlaptrinh\PerfectMoney\PerfectMoney::class,
```

### Configuration

Publish Configuration file
```
php artisan vendor:publish --provider="tuyenlaptrinh\PerfectMoney\PerfectMoneyServiceProvider" --tag="config"
```

Edit .env

Add these lines at .env file, follow config/perfectmoney.php for configuration descriptions.
``` php
PM_ACCOUNTID=100000
PM_PASSPHRASE=your_pm_password
PM_MARCHANTID=U123456
PM_MARCHANT_NAME="My Company"
PM_UNITS=USD
PM_ALT_PASSPHRASE=your_alt_passphrase
PM_PAYMENT_URL=http://example.com/success
PM_PAYMENT_URL_METHOD=null
PM_NOPAYMENT_URL=http://example.com/fail
PM_NOPAYMENT_URL_METHOD=null
PM_STATUS_URL=null
PM_SUGGESTED_MEMO=null
```

## Usage

###Render Shopping Cart Input Form

``` php
PerfectMoney::render();
```

Sometimes you will need to customize the payment form. Just pass the parameters to render method .

``` php
PerfectMoney::render(['PAYMENT_UNITS' => 'EUR']);
```

## API MODULES
### Get Wallets
``` php
$pm = new PerfectMoney;
$wallets = $pm->wallets();

if($wallets['status'] == 'success')
{
    foreach($wallets['wallets'] as $wallet){
        echo $wallet['account'].': '.$wallet['balance']; // U1935xxx: 10.00
    }
}
```

### Send Money
``` php
// Required Fields
$amount = 10.00;
$sendTo = 'U1234567';

// Optional Fields
$description = 'Optional Description for send money';
$payment_id = 'Optional_payment_id';

$pm = new PerfectMoney;

// Send Funds with all fields
$sendMoney = $pm->transfer($sendTo, $amount, $description, $payment_id);
if($sendMoney['status'] == 'success')
{
	// Some code here
}

// Send Funds with required fields
$sendMoney = $pm->transfer($sendTo, $amount);
if($sendMoney['status'] == 'error')
{
	// Payment Failed
	return $sendMoney['message'];
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email tuyenlaptrinh@gmail.com instead of using the issue tracker.

## Credits

- [tuyenlaptrinh][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/tuyenlaptrinh/perfect-money.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tuyenlaptrinh/perfect-money.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/tuyenlaptrinh/perfect-money
[link-downloads]: https://packagist.org/packages/tuyenlaptrinh/perfect-money
[link-author]: https://github.com/tuyenlaptrinh
[link-contributors]: ../../contributors