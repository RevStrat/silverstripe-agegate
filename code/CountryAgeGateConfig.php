<?php

class CountryAgeGateConfig extends DataExtension {
    private static $db = array(
        'AgeGateContent' => 'HTMLText',
        'AccessDeniedURL' => 'Varchar(512)',
        'GlobalAgeGate' => 'Boolean'
    );

    private static $has_many = array(
        'AgeGateCountries' => 'AgeGateCountry'
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab('Root.AgeGateControl', CheckboxField::create('GlobalAgeGate', 'Activate age gate for all pages'));
        $fields->addFieldToTab('Root.AgeGateControl', HTMLEditorField::create('AgeGateContent', 'Age Gate Content'));
        $fields->addFieldToTab('Root.AgeGateControl', TextField::create('AccessDeniedURL', 'Redirect for access denied'));
        $fields->addFieldToTab('Root.AgeGateControl', new GridField('AgeGateCountries', 'Age Gate Countries', AgeGateCountry::get(), GridFieldConfig_RecordEditor::create()));
    }
}

class AgeGateCountry extends DataObject {
    private static $db = array(
        'CountryCode' => 'Varchar(4)',
        'Age' => 'Int'
    );

    private static $summary_fields = array(
        'CountryCode',
        'Age'
    );
}