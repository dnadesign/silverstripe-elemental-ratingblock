<?php

namespace DNADesign\Elemental\Extensions;

use DNADesign\Elemental\Models\ElementRatingBlock;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;

class RatingBlockExtension extends DataExtension
{
    private static $many_many = [
        'RatingBlock' => ElementRatingBlock::class
    ];

    // private static $cascade_deletes = [
    //     ElementRatingBlock::class
    // ];

    /**
     * Update the fields of the page to include Rating specific fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $blocks = $fields->dataFieldByName('RatingBlock');

        if ($blocks) {
            $config = $blocks->getConfig();
        } else {
            $fields->removeByName('RatingBlock');
            $config = GridFieldConfig_RelationEditor::create();
        }

        $config->removeComponentsByType(GridFieldAddNewButton::class);
        $config->removeComponentsByType(GridFieldArchiveAction::class);
        $config->removeComponentsByType(GridFieldFilterHeader::class);
        $config->removeComponentsByType(GridFieldExportButton::class);
        $config->removeComponentsByType(GridFieldPrintButton::class);
        $config->removeComponentsByType(GridFieldImportButton::class);

        $config->getComponentByType(GridFieldAddExistingAutocompleter::class)->setSearchFields(
            [
                'Title',
            ]
        );

        if ($this->owner->RatingBlock()->count() === 1) {
            $config->removeComponentsByType(GridFieldAddExistingSearchButton::class);
            $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        }

        if (!$blocks) {
            $grid = GridField::create('RatingBlock', 'Rating Block', $this->owner->RatingBlock(), $config);
            $fields->addFieldToTab('Root.Rating', $grid);
        }

        return $fields;
    }

    /**
     * Update bootData to inject rating content into the app
     */
    public function updateBootdata(array &$bootData)
    {
        $block = $this->owner->RatingBlock()->First();
        if (!$block) {
            $bootData['EnableRatingForm'] = 0;
        } else {
            $bootData['EnableRatingForm'] = $block->EnableRatingForm;
            $bootData['RatingFormTitle'] = $block->RatingFormTitle;
            $bootData['RatingFormIntro'] = (string) $block->dbObject('RatingFormIntro');
            $bootData['EnableRatingComments'] = $block->EnableRatingComments;
            $bootData['RatingFormSuccessMessage'] = (string) $block->dbObject('RatingFormSuccessMessage');
            $bootData['RatingPageName'] = $this->owner->Title;
            $bootData['RatingPageID'] = $this->owner->ID;
            $bootData['RatingStars'] = $block->getStars();
        }
    }
}
