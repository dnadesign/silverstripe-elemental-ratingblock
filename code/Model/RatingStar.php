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
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

class RatingStar extends DataObject implements PermissionProvider
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
            $name->setDescription('This name will be displayed when users hover over or select this star rating. Use descriptive names like "Poor", "Fair", "Good", "Very Good", "Excellent" to help users understand what each rating level means.')
            ->setAttribute('placeholder', 'e.g. Excellent, Good, Fair...');
        }

        $tags = $fields->dataFieldByName('Tags');

        if ($tags && $this->isInDB()) {
            $tags->setDescription('Tags allow users to provide more specific feedback when they select this star rating. For example, if this is a 1-star rating, you might add tags like "Too slow", "Hard to find", "Confusing". Users can select multiple tags along with their rating. Drag to reorder tags.');

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

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('SortOrder')
            ->setDescription('The position of this star in the rating scale. Lower numbers appear first (1 star = lowest rating, 5 stars = highest rating). Use drag and drop in the parent Stars list to reorder.'));

        return $fields;
    }

    protected function onBeforeWrite()
    {
        if (!$this->SortOrder) {
            $this->SortOrder = RatingStar::get()->filter(['RatingBlockID' => $this->RatingBlock()->ID])->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }

    /**
     * Provide permissions for rating star management
     */
    public function providePermissions(): array
    {
        return [
            'MANAGE_RATING_STARS' => 'Manage Rating Stars'
        ];
    }

    /**
     * Allow users with MANAGE_RATING_STARS permission to create
     */
    public function canCreate($member = null, $context = []): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_STARS');
    }

    /**
     * Allow users with MANAGE_RATING_STARS permission to edit
     */
    public function canEdit($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_STARS');
    }

    /**
     * Allow users with MANAGE_RATING_STARS permission to delete
     */
    public function canDelete($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_STARS');
    }

    /**
     * Allow users with MANAGE_RATING_STARS permission to view
     */
    public function canView($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_STARS');
    }
}
