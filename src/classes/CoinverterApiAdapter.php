<?php

namespace AshAllenDesign\Coinverter;

use AshAllenDesign\Coinverter\Contracts\Coinverter;
use GuzzleHttp\Client;
use Carbon\Carbon;

class CoinverterApiAdapter implements Coinverter
{
    /** @var string */
    private $API_KEY;

    /** @var string */
    private $BASE_URL;

    /** @var string */
    private $ACCOUNT_TYPE;

    /** @var Client */
    private $client;

    /**
     * CoinverterApiAdapter constructor.
     * @param Client|null $client
     * @param string|null $apiKey
     * @throws \Exception
     */
    public function __construct(Client $client = null, string $apiKey = null)
    {
        $this->API_KEY = $apiKey ?? config('coinverter.currencyconverterapi.api-key');
        $this->ACCOUNT_TYPE = $this->determineAccountType();
        $this->BASE_URL = $this->determineBaseUrl();

        $this->client = $client ?? (new Client());
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function determineAccountType()
    {
        if (config('coinverter.currencyconverterapi.account-type') == 'free') {
            return 'free';
        }

        if (config('coinverter.currencyconverterapi.account-type') == 'pro') {
            return 'pr';
        }

        throw new \Exception('The currency converter account type is invalid.');
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function determineBaseUrl()
    {
        if (config('coinverter.currencyconverterapi.account-type') == 'free') {
            return 'https://free.currconv.com/api/v7';
        }

        if (config('coinverter.currencyconverterapi.account-type') == 'pro') {
            return 'https://api.currconv.com/api/v7';
        }

        throw new \Exception('The currency converter account type is invalid.');
    }

    /**
     * @param array $currencies
     * @return mixed
     */
    public function currencies(array $currencies = [])
    {
        $response = $this->makeRequest('/currencies');

        foreach($response->results as $currency => $metaData)
        {
            $currencies[] = $currency;
        }

        return $currencies;
    }

    /**
     * @param string      $from
     * @param string      $to
     * @param Carbon|null $date
     * @return mixed
     */
    public function exchangeRate(string $from, string $to, Carbon $date = null)
    {
        $conversion = $from . '_' . $to;
        $date = $date ? $date->format('Y-m-d') : now()->format('Y-m-d');

        $response = $this->makeRequest('/convert', [
            'q'    => $conversion,
            'date' => $date,
        ]);

        return $response->results->$conversion->val->$date;
    }

    /**
     * @param string $from
     * @param string $to
     * @param Carbon $date
     * @param Carbon $endDate
     * @param array  $conversions
     * @return mixed
     * @throws \Exception
     */
    public function exchangeRateBetweenDateRange(string $from, string $to, Carbon $date, Carbon $endDate, $conversions = [])
    {
        $this->validateDateRange($date, $endDate);

        $conversion = $from . '_' . $to;

        return $this->makeRequest('/convert', [
            'q'       => $conversion,
            'date'    => $date->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ])->results->$conversion->val;
    }

    /**
     * @param float       $value
     * @param string      $from
     * @param string      $to
     * @param Carbon|null $date
     * @return float|int
     */
    public function convert(float $value, string $from, string $to, Carbon $date = null)
    {
        return $value * $this->exchangeRate($from, $to, $date);
    }

    /**
     * @param float  $value
     * @param string $from
     * @param string $to
     * @param Carbon $date
     * @param Carbon $endDate
     * @param array  $conversions
     * @return array
     * @throws \Exception
     */
    public function convertBetweenDateRange(float $value, string $from, string $to, Carbon $date, Carbon $endDate, array $conversions = [])
    {
        foreach ($this->exchangeRateBetweenDateRange($from, $to, $date, $endDate) as $date => $exchangeRate) {
            $conversions[$date] = $value * $exchangeRate;
        }

        return $conversions;
    }

    /**
     * @param string $path
     * @param array  ...$queryParams
     * @return mixed
     */
    private function makeRequest(string $path, array $queryParams = [])
    {
        $url = $this->BASE_URL . $path . '?apiKey=' . $this->API_KEY;

        foreach ($queryParams as $param => $value) {
            $url .= '&' . urlencode($param) . '=' . urlencode($value);
        }

        return json_decode($this->client->get($url)->getBody()->getContents());
    }

    /**
     * @param Carbon $date
     * @param Carbon $endDate
     * @throws \Exception
     */
    private function validateDateRange(Carbon $date, Carbon $endDate)
    {
        $dateRange = $date->diffInDays($endDate);

        if ($this->ACCOUNT_TYPE == 'free' && $dateRange > 8) {
            throw new \Exception('The free currencyconverterapi.com account only allows an 8 day date range.');
        }

        if ($this->ACCOUNT_TYPE == 'pro' && $dateRange > 365) {
            throw new \Exception('The pro currencyconverterapi.com account only allows a 365 day date range.');
        }
    }
}
