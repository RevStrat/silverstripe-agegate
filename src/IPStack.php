<?php

namespace RevStrat\AgeGate;
use SilverStripe\Core\Environment;

class IPStack implements GeoIPServiceInterface {
    public function IP2CountryCode(string $ip) {
        $access_key = Environment::getEnv('IPSTACK_ACCESS_KEY');
        $endpoint = sprintf(Environment::getEnv('IPSTACK_ENDPOINT'), $ip, $access_key);
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json, true);
        return $api_result['country_code'];
    }
}