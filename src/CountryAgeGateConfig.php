<?php

namespace RevStrat\AgeGate;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;

class CountryAgeGateConfig extends DataExtension {
    private static $db = [
        'AgeGateContent' => 'HTMLText',
        'AccessDeniedURL' => 'Varchar(512)',
        'GlobalAgeGate' => 'Boolean'
    ];

    private static $has_many = [
        'AgeGateCountries' => AgeGateCountry::class
    ];

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab('Root.AgeGateControl', CheckboxField::create('GlobalAgeGate', 'Activate age gate for all pages'));
        $fields->addFieldToTab('Root.AgeGateControl', HTMLEditorField::create('AgeGateContent', 'Age Gate Content'));
        $fields->addFieldToTab('Root.AgeGateControl', TextField::create('AccessDeniedURL', 'Redirect for access denied'));
        $fields->addFieldToTab('Root.AgeGateControl', new GridField('AgeGateCountries', 'Age Gate Countries', AgeGateCountry::get(), GridFieldConfig_RecordEditor::create()));
    }
}

class AgeGateCountry extends DataObject {
    private static $db = [
        'CountryCode' => 'Varchar(4)',
        'Age' => 'Int'
    ];

    private static $summary_fields = [
        'CountryCode',
        'Age'
    ];

    private static $table_name = "AgeGateCountry";
}