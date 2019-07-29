# Coinverter

<p align="center"><img src="./coinverter.png"></p>


## Introduction
Coinverter is a simple Laravel package built to interact with currency conversion APIs.

It has been built and tested using Laravel 5.8 and PHP 7.2.

## Installation
To install the package, run the command:

``` composer install ashallendesign/coinverter ```

### Drivers
Currently, this package supports the following currency conversion APIs:
* Currency Converter API: https://www.currencyconverterapi.com/
* Exchange Rates API: https://exchangeratesapi.io/

#### Currency Converter API (https://www.currencyconverterapi.com/)
To use this API, register for an account at their website.

Once you have the provided key, set the following environment variables:
```
COINVERTER_DRIVER=currencyconverterapi
CURRENCY_CONVERTER_API_KEY=your_key_to_go_here
CURRENCY_CONVERTER_API_ACCOUNT_TYPE=free
```

Note: It's important to state whether if you are using a 'free' or 'pro' account for this service because the API routes
are different depending on the account type.

####  Exchange Rates API (https://exchangeratesapi.io/)
This API does not require an API key, so to use this API you just need to set the driver in the .env as seen below:
```
COINVERTER_DRIVER=exchangeratesapi
```

### Facade
You can use the facade for the package by adding the following line to the *aliases* array in the *config/app.php* file:
```
'Coinverter' => \AshAllenDesign\Coinverter\Facades\CoinverterFacade::class
```

After doing so, you will then be able to use the ``` Coinverter ``` facade.

## Usage
The package is built using the adapter pattern so that in the near future it will be much more extendable and support many more
APIs and services. Therefore, it is best practice when using this package to either instantiate the CurrencyConverter object using
dependency injection or the facade.

## Examples
Using the facade to convert £10 (GBP) to $ (USD):
```
    <?php
    
    namespace App\Http\Controllers;
    
    use Coinverter;
    
    class TestController extends Controller
    {
        public function index()
        {
            return Coinverter::convert(10, 'GBP', 'USD');
        }
    }
```

Using dependency injection to convert £10 (GBP) to $ (USD):
```
    <?php
    
    namespace App\Http\Controllers;
    
    use AshAllenDesign\Coinverter\Contracts\Coinverter;
    
    class TestController extends Controller
    {
        public function index(Coinverter $converter)
        {
            return $converter->convert(10, 'GBP', 'USD');
        }
    }
```

To get an array of converted values between a pair of given dates, you can use the following method:
```
    <?php
    
    namespace App\Http\Controllers;
    
    use AshAllenDesign\Coinverter\Contracts\Coinverter;
    use Carbon\Carbon;
    
    class TestController extends Controller
    {
        public function index(Coinverter $converter)
        {
            return $converter->convertBetweenDateRange(10.22, 'GBP', 'EUR', Carbon::today()->subDays(8), Carbon::today());
        }
    }
```

To get an array of supported currencies:
```
    <?php
    
    namespace App\Http\Controllers;
    
    use AshAllenDesign\Coinverter\Contracts\Coinverter;
    use Carbon\Carbon;
    
    class TestController extends Controller
    {
        public function index(Coinverter $converter)
        {
            return $converter->currencies();
        }
    }
```

## Tests
At this present moment, no tests have been written for this package but they will be added in due course.

Furthermore, the 'Pro' account of Currency Converter API has not been tested for functionality.

## Issues & Future Work
In the future, more adapters will be written so that more currency conversion APIs can be interacted with. If there is
a particular API/service that you wish to have added, email mail@ashallendesign.co.uk. Alternatively, you can contribute to the package if
you wish to add the functionality yourself.

For any security related issues, please report them via email to mail@ashallendesign.co.uk.
