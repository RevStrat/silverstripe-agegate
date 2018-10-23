<?php

namespace RevStrat\AgeGate;

interface GeoIPServiceInterface {
    public function IP2CountryCode(string $ip);
}