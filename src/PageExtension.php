<?php

namespace RevStrat\AgeGate;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

class PageExtension extends DataExtension {
    private static $db = [
        'AgeGated' => 'Boolean',
        'MinimumAgeOverrride' => 'Int',
        'AccessDeniedURLOverride' => 'Varchar(512)',
        'AgeGateContentOverride' => 'HTMLText'
    ];

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab('Root.AgeGateControl', CheckboxField::create('AgeGated', 'Age gated'));
        $fields->addFieldToTab('Root.AgeGateControl', NumericField::create('MinimumAgeOverrride', 'Override minimum age'));
        $fields->addFieldToTab('Root.AgeGateControl', TextField::create('AccessDeniedURLOverride', 'Override access denied URL'));
        $fields->addFieldToTab('Root.AgeGateControl', HTMLEditorField::create('AgeGateContentOverride', 'Override content for age gate on this page'));
    }
}