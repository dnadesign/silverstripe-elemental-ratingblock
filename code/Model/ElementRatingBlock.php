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

    private static $description = "Allow visitors to rate pages with stars and optional tags/comments. Perfect for collecting user feedback and satisfaction ratings.";

    private static $table_name = 'ElementRatingBlock';

    private static $singular_name = 'Rating';

    private static $plural_name = 'Rating Blocks';

    private static $controller_class = RatingBlockController::class;

    private static $icon = 'font-icon-check-mark-2';

    /**
     * Configuration properties for CMS streamlining
     */
    private static $hide_enable_rating_form = false;
    private static $hide_rating_form_intro = false;
    private static $hide_enable_rating_comments = false;
    private static $remove_settings_tab = false;
    private static $hide_rating_form_title = false;
    private static $hide_rating_form_success_message = false;
    private static $hide_enable_rating_tags = false;

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
     * Get the default value for EnableRatingForm based on configuration
     */
    public function populateDefaults()
    {
        parent::populateDefaults();

        // If hide_enable_rating_form is true, default EnableRatingForm to true
        if ($this->config()->get('hide_enable_rating_form')) {
            $this->EnableRatingForm = true;
        }
    }

    /**
     * Update the fields of the page to include Rating specific fields
     */
    public function getCMSFields()
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fieldsToAdd = [];

            // Conditionally add fields based on configuration
            if (!$this->config()->get('hide_enable_rating_form')) {
                $fieldsToAdd[] = CheckboxField::create('EnableRatingForm', 'Enable Rating form on this page')
                    ->setDescription('When enabled, visitors can submit ratings for this page. The rating form will appear on the frontend where this block is placed.');
            }

            if (!$this->config()->get('hide_enable_rating_tags')) {
                $fieldsToAdd[] = CheckboxField::create('EnableRatingTags', 'Enable Rating form tags')
                    ->setDescription('When enabled, users can select predefined tags along with their star rating. Configure tags in the "Stars" tab.');
            }

            if (!$this->config()->get('hide_rating_form_title')) {
                $fieldsToAdd[] = TextField::create('RatingFormTitle', 'Rating form title')
                    ->setDescription('The heading that appears above the rating form. Example: "Rate this page" or "How would you rate this content?"')
                    ->setAttribute('placeholder', 'Rate this page');
            }

            if (!$this->config()->get('hide_rating_form_intro')) {
                $fieldsToAdd[] = HTMLEditorField::create('RatingFormIntro', 'Rating form intro')
                    ->setEditorConfig('help')
                    ->setRows(3)
                    ->setDescription('Optional introductory text that appears before the rating form. Use this to provide context or instructions to users about what they are rating.');
            }

            if (!$this->config()->get('hide_enable_rating_comments')) {
                $fieldsToAdd[] = CheckboxField::create('EnableRatingComments', 'Enable Rating comments')
                    ->setDescription('When enabled, users can leave written feedback along with their star rating. Comments are optional for users.');
            }

            if (!$this->config()->get('hide_rating_form_success_message')) {
                $fieldsToAdd[] = HTMLEditorField::create('RatingFormSuccessMessage', 'Rating form success message')
                    ->setEditorConfig('help')
                    ->setRows(3)
                    ->setDescription('Message shown to users after they successfully submit a rating. If left empty, a default "Thank you" message will be displayed.');
            }

            if (!empty($fieldsToAdd)) {
                $fields->addFieldsToTab('Root.Main', $fieldsToAdd);
            }

            $fields->removeByName(['UseDefaultTags']);
        });

        $fields = parent::getCMSFields();

        if ($this->config()->get('hide_enable_rating_form')) {
            $fields->removeByName('EnableRatingForm');
        }

        if ($this->config()->get('hide_rating_form_intro')) {
            $fields->removeByName('RatingFormIntro');
        }

        if ($this->config()->get('hide_enable_rating_comments')) {
            $fields->removeByName('EnableRatingComments');
        }

        if ($this->config()->get('hide_rating_form_title')) {
            $fields->removeByName('RatingFormTitle');
        }

        if ($this->config()->get('hide_rating_form_success_message')) {
            $fields->removeByName('RatingFormSuccessMessage');
        }

        if ($this->config()->get('hide_enable_rating_tags')) {
            $fields->removeByName('EnableRatingTags');
        }
        // Remove Settings tab if configured
        if ($this->config()->get('remove_settings_tab')) {
            $fields->removeByName('Settings');
        }

        $fields->removeByName(['UseDefaultTags']);
        $config = $this->config();
        $starsConfig = $config ? $config->get('stars') : null;

        if ($starsConfig && !$this->isInDB()) {
            $fields->insertAfter(
                'EnableRatingTags',
                CheckboxField::create('UseDefaultTags', 'Enable use default tags')
                    ->setDescription('When enabled, this will automatically create default stars and tags based on your site configuration. Uncheck to manually configure stars and tags in the "Stars" tab.')
            );
        }

        $stars = $fields->dataFieldByName('Stars');

        if ($stars) {
            $stars->setDescription('Configure the star ratings for your form. Each star represents a rating level (1-5 stars). You can add tags that users can select for each rating level. Stars are displayed in the order shown here - drag to reorder.');

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
        $page = $this->getPage();
        $bootData['RatingPageName'] = $page ? $page->Title : '';
        $bootData['RatingPageID'] = $page ? $page->ID : 0;
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
