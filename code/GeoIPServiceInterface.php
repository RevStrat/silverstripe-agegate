<?php

interface GeoIPServiceInterface {
    public function IP2CountryCode($ip);
}