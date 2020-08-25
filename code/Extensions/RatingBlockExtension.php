<?php

namespace DNADesign\Elemental\Extensions;

use DNADesign\Elemental\Models\RatingStar;
use DNADesign\Elemental\Models\RatingTag;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class RatingBlockExtension extends DataExtension
{
    private static $db = [
        'EnableRatingForm' => DBBoolean::class,
        'EnableRatingTags' => DBBoolean::class,
        'RatingPageName' => 'Varchar(50)',
        'RatingFormTitle' => 'Varchar(100)',
        'RatingFormIntro' => DBHTMLText::class,
        'EnableRatingComments' => DBBoolean::class,
        'RatingFormSuccessMessage' => DBHTMLText::class
    ];

    private static $many_many = [
        'Stars' => RatingStar::class
    ];

    private static $defaults = [
        'EnableRatingTags' => true
    ];

    private static $many_many_extrafields = [
        'Stars' => [
            'SortOrder' => 'Int'
        ]
    ];

    /**
     * Update the fields of the page to include Rating specific fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Rating.Main',
            [
                CheckboxField::create('EnableRatingForm', 'Enable Rating form'),
                CheckboxField::create('EnableRatingTags', 'Enable Rating form tags'),
                TextField::create('RatingPageName', 'Page name to appear for rating')
                    ->setDescription('For fallback reference. One word, no spaces'),
                TextField::create('RatingFormTitle', 'Rating form title'),
                HTMLEditorField::create('RatingFormIntro', 'Rating form intro')
                    ->setEditorConfig('help')
                    ->setRows(3),
                CheckboxField::create('EnableRatingComments', 'Enable Rating comments'),
                HTMLEditorField::create('RatingFormSuccessMessage', 'Rating form sucess message')
                    ->setEditorConfig('help')
                    ->setRows(3)
            ]
        );

        $stars = $fields->dataFieldByName('Stars');

        if ($stars) {
            $config = $stars->getConfig();
        } else {
            $fields->removeByName('Stars');
            $config = GridFieldConfig_RelationEditor::create();
        }

        $injector = Injector::inst();
        $config->removeComponentsByType(GridFieldAddExistingSearchButton::class);
        $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
        $config->removeComponentsByType(GridFieldArchiveAction::class);
        $config->removeComponentsByType(GridFieldDeleteAction::class);
        $config->removeComponentsByType(GridFieldFilterHeader::class);
        $config->addComponent($injector->create(GridFieldDeleteAction::class));
        $config->addComponent(new GridFieldOrderableRows('SortOrder'));

        if (!$stars) {
            $starsGrid = GridField::create('Stars', 'Stars', $this->owner->Stars(), $config);
            $fields->addFieldToTab('Root.Rating.Stars', $starsGrid);
        }

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->owner->Stars()->Count() == 0) {
            $config = $this->owner->config();
            if ($config && $config->stars) {
                foreach ($config->stars as $starIndex => $star) {
                    $ratingStar = new RatingStar();
                    $ratingStar->Name = $starIndex;
                    $ratingStar->write();

                    if ($ratingStar->Tags()->Count() == 0) {
                        foreach ($star['tags'] as $tag) {
                            $ratingTag = new RatingTag();
                            $ratingTag->Name = $tag;
                            $ratingTag->write();
                            $ratingStar->Tags()->add($ratingTag);
                        }
                    }

                    $this->owner->Stars()->add($ratingStar);
                }
            }
        }
    }

    /**
     * provide a fallback in case the PageName is not entered into the CMS
     * This provides us with a means of storing a page reference if
     * the linked page evcer gets deleted or it's name changes
     */
    public function getActualRatingPageName()
    {
        if ($this->owner->RatingPageName) {
            return $this->owner->RatingPageName;
        }

        return $this->owner->dbObject('ClassName')->getShortName();
    }

    /**
     * Update bootData to inject rating content into the app
     */
    public function updateBootdata(array &$bootData)
    {
        $bootData['EnableRatingForm'] = $this->owner->EnableRatingForm;
        $bootData['RatingFormTitle'] = $this->owner->RatingFormTitle;
        $bootData['RatingFormIntro'] = (string) $this->owner->dbObject('RatingFormIntro');
        $bootData['EnableRatingComments'] = $this->owner->EnableRatingComments;
        $bootData['RatingFormSuccessMessage'] = (string) $this->owner->dbObject('RatingFormSuccessMessage');
        $bootData['RatingPageName'] = $this->owner->getActualRatingPageName();
        $bootData['RatingPageID'] = $this->owner->ID;
        $bootData['RatingStars'] = $this->owner->getStars();
    }

    public function getStars()
    {
        $result = [];

        $stars = $this->owner->Stars()->sort('SortOrder ASC');

        $result = [
            'Max' => $stars->count(),
            'Labels' => $this->getStarLabels($stars)
        ];

        if ($this->owner->EnableRatingTags) {
            $tagsArray = [];
            foreach ($stars as $key => $star) {
                $tags = $star->Tags()->sort('SortOrder ASC')->map('SortOrder', 'Name')->toArray();
                array_push(
                    $tagsArray,
                    $tags
                );
            }
            $result['Tags'] = $tagsArray;
        }

        return $result;
    }

    private function getStarLabels($stars)
    {
        $labels = [];
        $disable = false;

        if ($stars) {
            $labels = array_map(function ($label) use (&$disable) {
                if (empty($label) && !$disable) {
                    $disable = true;
                }
                return $label;
            }, $stars->map('SortOrder', 'Name')->toArray());
        }

        return $disable ? [] : $labels;
    }
}
