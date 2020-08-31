<?php

namespace DNADesign\Elemental\Models;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\ReadonlyField;

class RatingStar extends DataObject
{
    private static $table_name = 'RatingStar';

    private static $db = [
        'Name' => 'Varchar(50)',
        'SortOrder' => 'Int'
    ];

    private static $has_one = [
        'RatingBlock' => ElementRatingBlock::class
    ];

    private static $has_many = [
        'Tags' => RatingTag::class
    ];

    private static $summary_fields = [
        'SortOrder' => 'Order',
        'Name' => 'Name'
    ];

    private static $default_sort = 'SortOrder ASC';

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('RatingBlockID');
        $fields->removeByName('SortOrder');

        $name = $fields->dataFieldByName('Name');
        if ($name) {
            $name->setDescription('This name will be displayed when hovering/selecting star');
        }

        $tags = $fields->dataFieldByName('Tags');

        if ($tags && $this->isInDB()) {
            $injector = Injector::inst();

            $config = $tags->getConfig();
            $config->removeComponentsByType(GridFieldAddExistingSearchButton::class);
            $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            $config->removeComponentsByType(GridFieldArchiveAction::class);
            $config->removeComponentsByType(GridFieldDeleteAction::class);
            $config->removeComponentsByType(GridFieldFilterHeader::class);
            $config->addComponent($injector->create(GridFieldDeleteAction::class));
            $config->addComponent(new GridFieldOrderableRows('SortOrder'));
        }

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('SortOrder'));

        return $fields;
    }

    protected function onBeforeWrite()
    {
        if (!$this->SortOrder) {
            $this->SortOrder = RatingStar::get()->filter(['RatingBlockID' => $this->RatingBlock()->ID])->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }
}
