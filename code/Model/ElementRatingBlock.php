<?php

namespace DNADesign\Elemental\Models;

use DNADesign\Elemental\Controllers\RatingBlockController;
use DNADesign\Elemental\Models\BaseElement;
use Page;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Versioned\GridFieldArchiveAction;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Security\SecurityToken;

/**
 * @package elemental
 */
class ElementRatingBlock extends BaseElement
{
    private static $title = "Rating";

    private static $description = "Custom rating block";

    private static $table_name = 'ElementRatingBlock';

    private static $singular_name = 'Rating';

    private static $plural_name = 'Ratings';

    private static $controller_class = RatingBlockController::class;

    private static $icon = 'font-icon-check-mark-2';

    private static $db = [
        'EnableRatingForm' => DBBoolean::class,
        'EnableRatingTags' => DBBoolean::class,
        'RatingFormTitle' => 'Varchar(100)',
        'RatingFormIntro' => DBHTMLText::class,
        'EnableRatingComments' => DBBoolean::class,
        'RatingFormSuccessMessage' => DBHTMLText::class,
        'UseDefaultTags' => DBBoolean::class
    ];

    private static $has_many = [
        'Stars' => RatingStar::class
    ];

    private static $defaults = [
        'EnableRatingTags' => true,
        'UseDefaultTags' => true
    ];

    /**
     * Update the fields of the page to include Rating specific fields
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {

            $fields->addFieldsToTab(
                'Root.Main',
                [
                    CheckboxField::create('EnableRatingForm', 'Enable Rating form on this page'),
                    CheckboxField::create('EnableRatingTags', 'Enable Rating form tags'),
                    TextField::create('RatingFormTitle', 'Rating form title'),
                    HTMLEditorField::create('RatingFormIntro', 'Rating form intro')
                        ->setEditorConfig('help')
                        ->setRows(33),
                    CheckboxField::create('EnableRatingComments', 'Enable Rating comments'),
                    HTMLEditorField::create('RatingFormSuccessMessage', 'Rating form sucess message')
                        ->setEditorConfig('help')
                        ->setRows(3)
                ]
            );

            $fields->removeByName(['UseDefaultTags']);
        });
        
        $fields = parent::getCMSFields();

        $fields->removeByName(['UseDefaultTags']);
        $config = $this->config();
        $starsConfig = $config ? $config->get('stars') : null;

        if ($starsConfig && !$this->isInDB()) {
            $fields->insertAfter(
                'EnableRatingTags',
                CheckboxField::create('UseDefaultTags', 'Enable use default tags')
            );
        }

        $stars = $fields->dataFieldByName('Stars');

        if ($stars) {
            $injector = Injector::inst();

            $config = $stars->getConfig();
            $config->removeComponentsByType(GridFieldAddExistingSearchButton::class);
            $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            $config->removeComponentsByType(GridFieldArchiveAction::class);
            $config->removeComponentsByType(GridFieldDeleteAction::class);
            $config->removeComponentsByType(GridFieldFilterHeader::class);
            $config->addComponent($injector->create(GridFieldDeleteAction::class));
            $config->addComponent(new GridFieldOrderableRows('SortOrder'));
        }

        return $fields;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->UseDefaultTags) {
            if ($this->Stars()->Count() == 0) {
                $config = $this->config();
                $stars = $config ? $config->get('stars') : null;
                if ($stars) {
                    $sort = 0;
                    foreach ($stars as $starIndex => $star) {
                        $ratingStar = new RatingStar();
                        $sort += 1;
                        $ratingStar->Name = $starIndex;
                        $ratingStar->SortOrder = $sort;
                        $ratingStar->write();
                        $sortTag = 0;
                        if ($ratingStar->Tags()->Count() == 0) {
                            foreach ($star['tags'] as $index => $tag) {
                                $ratingTag = new RatingTag();
                                $sortTag += 1;
                                $ratingTag->Name = $tag;
                                $ratingTag->SortOrder = $sortTag;
                                $ratingTag->write();
                                $ratingStar->Tags()->add($ratingTag);
                            }
                        }

                        $this->Stars()->add($ratingStar);
                    }
                }
            }
        }
    }

    /**
     * Update bootData to inject rating content into the app
     */
    public function getBootData()
    {
        $bootData = [];

        $bootData['EnableRatingForm'] = $this->owner->EnableRatingForm;
        $bootData['RatingFormTitle'] = $this->owner->RatingFormTitle;
        $bootData['RatingFormIntro'] = (string) $this->owner->dbObject('RatingFormIntro');
        $bootData['EnableRatingComments'] = $this->owner->EnableRatingComments;
        $bootData['RatingFormSuccessMessage'] = (string) $this->owner->dbObject('RatingFormSuccessMessage');
        $bootData['RatingPageName'] = $this->getPage()->Title;
        $bootData['RatingPageID'] = $this->getPage()->ID;
        $bootData['RatingStars'] = $this->getStars();
        $bootData['SecurityToken'] = $this->getSecurityToken();

        return json_encode($bootData, JSON_UNESCAPED_UNICODE);
    }

    public function getSecurityToken()
    {
        $securityToken = SecurityToken::create();
        return $securityToken->getSecurityID();
    }

    public function getStars()
    {
        $result = [];

        $stars = $this->owner->Stars()->sort('SortOrder ASC');

        $result = [
            'Max' => $stars->count(),
            'Labels' => $this->getStarLabels($stars)
        ];

        if ($this->EnableRatingTags) {
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

    public function getType()
    {
        return _t(__class__ . '.BlockType', 'Rating');
    }

    public function inlineEditable()
    {
        return false;
    }
}
