<?php

namespace DNADesign\Elemental\Models;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

class RatingTag extends DataObject implements PermissionProvider
{
    private static $table_name = 'RatingTag';

    private static $db = [
        'Name' => 'Varchar(100)',
        'Description' => 'Varchar(255)',
        'SortOrder' => 'Int',
        'UniqueID' => 'Varchar(255)'
    ];

    private static $summary_fields = [
        'SortOrder' => 'Order',
        'Name' => 'Name',
        'Description' => 'Description',
        'UniqueID' => 'Unique ID'
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

        // Add help text to Name field
        $name = $fields->dataFieldByName('Name');
        if ($name) {
            $name->setDescription('A short, descriptive tag that users can select. Examples: "Too slow", "Easy to use", "Helpful content", "Confusing layout". Keep it concise and specific.')
            ->setAttribute('placeholder', 'e.g. Easy to use, Too slow...');
        }

        // Add help text to Description field
        $description = $fields->dataFieldByName('Description');
        if ($description) {
            $description->setDescription('Optional longer description of what this tag means. This is for internal reference and is not shown to users.');
        }

        $fields->addFieldToTab('Root.Main', ReadonlyField::create('SortOrder')
            ->setDescription('The order this tag appears in the list for this star rating. Use drag and drop in the parent Tags list to reorder.'));

        // Add UniqueID field
        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create('UniqueID', 'Unique ID')
                ->setDescription('Automatically generated identifier based on the tag name. Used internally for tracking and analytics. This is created automatically when you save the tag.')
        );

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
        } else if ($this->Star()->Tags()->filter(['Name' => $this->Name])->first()) {
            $result->addError(
                'The ' . $this->Name . ' tag already exists',
                ValidationResult::TYPE_ERROR
            );
        }

        return $result;
    }

    protected function onBeforeWrite()
    {
        // Generate UniqueID if Name exists and (UniqueID is empty or Name has changed)
        if ($this->Name && (!$this->UniqueID || $this->isChanged('Name'))) {
            $this->UniqueID = $this->generateUniqueID($this->Name);
        }

        if (!$this->SortOrder) {
            $this->SortOrder = RatingTag::get()->filter(['StarID' => $this->Star()->ID])->max('SortOrder') + 1;
        }

        parent::onBeforeWrite();
    }

    /**
     * Generate a unique ID from the name by:
     * - Converting to lowercase
     * - Replacing "&" with "and"
     * - Replacing spaces with hyphens
     * - Removing non-alphanumeric characters (except hyphens)
     *
     * @param string $name The original term name
     * @return string The generated unique identifier
     */
    private function generateUniqueID($name)
    {
        return preg_replace(
            '/[^a-z0-9\-]/',
            '',
            str_replace(
                [' ', '&'],
                ['-', 'and'],
                strtolower($name)
            )
        );
    }

    /**
     * Provide permissions for rating tag management
     */
    public function providePermissions(): array
    {
        return [
            'MANAGE_RATING_TAGS' => 'Manage Rating Tags'
        ];
    }

    /**
     * Allow users with MANAGE_RATING_TAGS permission to create
     */
    public function canCreate($member = null, $context = []): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_TAGS');
    }

    /**
     * Allow users with MANAGE_RATING_TAGS permission to edit
     */
    public function canEdit($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_TAGS');
    }

    /**
     * Allow users with MANAGE_RATING_TAGS permission to delete
     */
    public function canDelete($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_TAGS');
    }

    /**
     * Allow users with MANAGE_RATING_TAGS permission to view
     */
    public function canView($member = null): bool
    {
        return Permission::checkMember($member, 'MANAGE_RATING_TAGS');
    }
}
