<?php

namespace RevStrat\AgeGate;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\View\Requirements;
use Silverstripe\SiteConfig\SiteConfig;

class PageControllerExtension extends DataExtension {
    use Configurable;

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
    private static $geoip_source = "RevStrat\\AgeGate\\IPStack";

    private static $allowed_actions = [
        'AgeGateForm'
    ];

    private $confirmedAge = NULL;
    private $minimumAge = NULL;
    private $countryCode = NULL;

    public function onAfterInit() {
        // If we have an age gate and no session, get the party started
        if (!$this->owner->AgeGated) {
            return;
        }

        // Check if we've already saved this information in the session
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $clientSettings = json_decode($session->get($this->config()->storage_key));

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
                $resolverClass = $this->config()->geoip_source;
                error_log($resolverClass);
                $resolver = new $resolverClass;
                $this->countryCode = $resolver->IP2CountryCode($ip);
                $session->set($this->config()->storage_key, json_encode([
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
            $this->minimumAge = $this->config()->default_age;
        }
    }

    public function GetShowAgeGate() {
        if (!$this->owner->AgeGated) {
            return false;
        }

        if ($this->confirmedAge >= $this->minimumAge) {
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
            CheckboxField::create('OfAge', sprintf($this->config()->checkbox_label, $this->minimumAge))
        );

        $actions = new FieldList(
            FormAction::create('doAgeGate')->setTitle($this->config()->submit_label)
        );

        $form = new Form($this->owner, 'AgeGateForm', $fields, $actions);

        return $form;
    }

    public function doAgeGate($data, Form $form) {
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $ajax = $request->isAjax();
        if (array_key_exists('OfAge', $data) && $data['OfAge']) {
            $session->set($this->config()->storage_key, json_encode([
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
            return json_encode(['success' => true]);
        }

        $this->owner->redirectBack();
    }
}