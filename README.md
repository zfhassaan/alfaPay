# AlfaPay

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zfhassaan/alfapay.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/alfa-pay)
[![Total Downloads](https://img.shields.io/packagist/dt/zfhassaan/alfapay.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/alfa-pay)

This is Bank Alfalah payment gateway package to pay using Alfa Wallet, Bank Account Number or Credit Card (Credit Card not yet implemented). You can use this package with Laravel or any PHP framework via composer.

## Installation

You can install the package via composer:

```bash
composer require zfhassaan/alfapay-php
```

## Set .env configurations
You can get these values from Bank Alfalah Merchant portal
```php
ALFAPAY_URL=https:
ALFAPAY_MODE=sandbox
ALFAPAY_CHANNEL_ID=
ALFAPAY_MERCHANT_ID=
ALFAPAY_STORE_ID=
ALFAPAY_RETURN_URL=
ALFAPAY_MERCHANT_USERNAME=
ALFAPAY_MERCHANT_PASSWORD=
ALFAPAY_MERCHANT_HASH=
ALFAPAY_KEY_1=
ALFAPAY_KEY_2=
```

## Usage
First you've to get auth token by providing your unique transaction number or order number
and then can post request the amount information along with some validation.
Please refer to YouTube video for full understanding.
```php
public function get_token(){
    // generate random transaction/order number
    $transNum = rand(0,17866120);
            
    // get AuthToken from AlfaPay API
    $alfa       = new AlfaPay();
    $response   = $alfa->setTransactionReferenceNumber($transNum)->getToken();
    //
    if( $response != null && $response->success == 'true' ) {
        return $response->AuthToken;
    } else {
        // log error
        if( $response == null ) {
            abort(403, 'Error: Timeout connection. Auth Token not generated.');
        } else {
            abort(403, 'Error: '.$response->ErrorMessage.'. Auth Token does not generated.');
        }
    }
}
```
Snipped for Proceed Transaction

```php
public function process(){
     $alfa->setAuthToken($this->get_token());
     $alfa->setCurrency('PKR');
     $alfa->setTransactionReferenceNumber($request->TransctionReferenceNumber);
     $alfa->setTransactionType($request->TransactionTypeId);
     $alfa->setAmount($request->Amount);
     $alfa->setMobileNumber($request->MobileNumber);
     $alfa->setEmail($request->Email);
     $alfa->setCountryCode('164'); // Pakistan = 164
     $alfa->sendTransactionRequest();
     return $alfa->sendTransactionRequest();
}
```


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email naeemins@gmail.com instead of using the issue tracker.

## Credits
    The repository is forked from codesocolock and fixed to work with PHP 8.1 and Laravel 9
-   [codesoclock](https://github.com/codesoclock)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
