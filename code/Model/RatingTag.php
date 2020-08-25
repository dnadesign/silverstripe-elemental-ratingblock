<?php

namespace DNADesign\Elemental\Models;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;

class RatingTag extends DataObject
{
    private static $table_name = 'RatingTag';

    private static $db = [
        'Name' => 'Varchar(100)',
        'Description' => 'Varchar(255)',
        'SortOrder' => 'Int'
    ];

    private static $summary_fields = [
        'SortOrder' => 'Order',
        'Name' => 'Name',
        'Description' => 'Description'
    ];

    private static $has_one = [
        'Star' => RatingStar::class
    ];

    private static $default_sort = 'SortOrder ASC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('SortOrder');
        $fields->removeByName('StarID');

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('SortOrder'));

        return $fields;
    }

    public function validate()
    {
        $result = parent::validate();

        if (!$this->isInDB() || ($this->isInDB() && $this->isChanged('Name'))) {
            return $this->validateTag($result);
        }

        return $result;
    }

    private function validateTag(&$result)
    {
        if (empty($this->Name)) {
            $result->addError(
                'Please enter a name for the tag',
                ValidationResult::TYPE_ERROR
            );
        } else if (RatingTag::get()->filter(['Name' => $this->Name])->first()) {
            $result->addError(
                'The ' . $this->Name . ' tag already exists',
                ValidationResult::TYPE_ERROR
            );
        }

        return $result;
    }

    protected function onBeforeWrite()
    {
        if (!$this->SortOrder) {
            $this->SortOrder = RatingTag::get()->filter(['StarID' => $this->Star()->ID])->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }
}
