<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class PageControllerExtension extends DataExtension {
    /**
     * @config
     */
    private static $default_age = 19;
    
    /**
     * @config
     */
    private static $checkbox_label = "I confirm I am at least %d years old";

    /**
     * @config
     */
    private static $submit_label = "Enter";

    /**
     * @config
     */
    private static $storage_key = "agegate";

    /**
     * @config
     */
    private static $geoip_source = "IPStack";

    private static $allowed_actions = array(
        'AgeGateForm'
    );

    private $confirmedAge = NULL;
    private $minimumAge = NULL;
    private $countryCode = NULL;

    public function onAfterInit() {
        // If we have an age gate and no session, get the party started
        $config = SiteConfig::current_site_config(); 
        if (!$this->owner->AgeGated && !$config->GlobalAgeGate) {
            return;
        }

        // Check if we've already saved this information in the session
        $request = Controller::curr()->getRequest();
        $clientSettings = json_decode(Session::get($this->owner->config()->storage_key));

        // We have an override set - use this.
        if ($this->owner->MinimumAgeOverrride) {
            $this->minimumAge = $this->owner->MinimumAgeOverrride;
        }

        if ($clientSettings) {
            if (property_exists($clientSettings, 'ConfirmedAge')) {
                $this->confirmedAge = $clientSettings->ConfirmedAge;
            }
            if (property_exists($clientSettings, 'CountryCode')) {
                $this->countryCode = $clientSettings->CountryCode;
            }
        }
        
        if (!$this->minimumAge && !$this->countryCode) {
            // No minimum age is set - perform GeoIP
            try {
                $ip = $request->getIP();
                if (!$ip) {
                    throw new Exception('Could net get IP address from request. Falling back on age gate defaults.');
                }
                $resolverClass = $this->owner->config()->geoip_source;
                $resolver = new $resolverClass;
                $this->countryCode = $resolver->IP2CountryCode($ip);
                Session::set($this->owner->config()->storage_key, json_encode([
                    'ConfirmedAge' => $this->confirmedAge,
                    'CountryCode' => $this->countryCode
                ]));
                $this->minimumAge = $this->AgeForCountryCode($this->countryCode);
            } catch (Exception $lookupError) {
                // Lookup failed. If we need to track this, insert code here
            }
        }

        // Still no age set, fall back to default
        if (!$this->minimumAge) {
            $this->minimumAge = $this->owner->config()->default_age;
        }
    }

    public function GetShowAgeGate() {
        $CrawlerDetect = new CrawlerDetect;
        if($CrawlerDetect->isCrawler()) {
            return false; // Don't age-gate crawlers
        }
        
        $config = SiteConfig::current_site_config();
        $ageGateActive = $this->owner->AgeGated || $config->GlobalAgeGate;
        $sufficientAge = $this->confirmedAge >= $this->minimumAge;

        if (method_exists($this->owner, 'updateGetShowAgeGate')) {
            $this->owner->updateGetShowAgeGate($ageGateActive, $sufficientAge);
        }

        if (!$ageGateActive || $sufficientAge) {
            return false;
        }

        return true;
    }

    private function AgeForCountryCode($countryCode) {
        $countrySettings = AgeGateCountry::get()->filter([
            'CountryCode' => $countryCode
        ])->first();
        if ($countrySettings) {
            return $countrySettings->Age;
        }
        return NULL;
    }

    public function AgeGateForm() {
        $fields = new FieldList(
            CheckboxField::create('OfAge', sprintf($this->owner->config()->checkbox_label, $this->minimumAge))
        );

        $actions = new FieldList(
            FormAction::create('doAgeGate')->setTitle($this->owner->config()->submit_label)
        );

        if (method_exists($this->owner, 'updateAgeGateForm')) {
            $this->owner->updateAgeGateForm($fields, $actions, $this->minimumAge);
        }

        $form = new Form($this->owner, 'AgeGateForm', $fields, $actions);

        return $form;
    }

    public function doAgeGate($data, Form $form) {
        $request = $this->owner->getRequest();
        $ajax = $request->isAjax();
        if (array_key_exists('OfAge', $data) && $data['OfAge']) {
            Session::set($this->owner->config()->storage_key, json_encode([
                'ConfirmedAge' => $this->minimumAge,
                'CountryCode' => $this->countryCode
            ]));
        } else {
            $config = SiteConfig::current_site_config();
            if ($ajax) {
                return json_encode([ 
                    'success'  => false,
                    'redirect' => $this->owner->AccessDeniedURLOverride ? 
                                    $this->owner->AccessDeniedURLOverride :
                                    $config->AccessDeniedURL 
                ]);
            } else {
                $this->owner->redirect($this->owner->AccessDeniedURLOverride ? 
                                    $this->owner->AccessDeniedURLOverride :
                                    $config->AccessDeniedURL);
                return NULL;
            }
        }

        if ($ajax) {
            return json_encode(array('success' => true));
        }

        $this->owner->redirectBack();
    }
}