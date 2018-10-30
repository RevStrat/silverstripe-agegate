<?php

use GeoIp2\Database\Reader;

class GeoLite2 implements GeoIPServiceInterface {
    use Configurable;

    /**
     * @config
     */
    private static $database = "data/GeoLite2-Country.mmdb";

    public function IP2CountryCode(string $ip) {
        $dbPath = __DIR__ . $this->owner->config()->database;
        $reader = new Reader($dbPath);
        $record = $reader->country($ip);
        return $record->country->isoCode;
    }
}