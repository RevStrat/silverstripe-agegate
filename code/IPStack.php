<?php

class IPStack implements GeoIPServiceInterface {
    public function IP2CountryCode($ip) {
        if (!defined('IPSTACK_ACCESS_KEY')) {
            return null;
        }
        $access_key = IPSTACK_ACCESS_KEY;
        $endpoint = sprintf(IPSTACK_ENDPOINT, $ip, $access_key);
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json, true);
        return $api_result['country_code'];
    }
}