<?php

namespace RevStrat\AgeGate;
use GeoIp2\Database\Reader;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Config\Configurable;

class GeoLite2 implements GeoIPServiceInterface {
    use Configurable;

    /**
     * @config
     */
    private static $database = "data/GeoLite2-Country.mmdb";

    public function IP2CountryCode(string $ip) {
        $dbPath = __DIR__ . $this->config()->database;
        $reader = new Reader($dbPath);
        $record = $reader->country($ip);
        return $record->country->isoCode;
    }
}