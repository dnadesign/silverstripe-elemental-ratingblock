<?php

namespace DNADesign\Elemental\Models;

use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Versioned\Versioned;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;

class Rating extends DataObject implements PermissionProvider
{
    private static $table_name = 'Rating';

    private static $db = [
        'RatingScore' => 'Int',
        'Comments' => 'Text',
        'Tags' => 'Varchar(255)',
        'PageName' => 'Varchar(255)',
        'URL' => 'Varchar(255)'
    ];

    private static $has_one = [
        'Page' => SiteTree::class
    ];

    private static $summary_fields = [
        'ID' => 'ID',
        'RatingScore' => 'Rating',
        'getPageType' => 'Page type',
        'Page.Title' => 'Rated Page',
        'getAverage' => 'Page Average',
        'getTotalRatings' => 'Page Ratings count',
        'Comments' => 'Comment',
        'Tags' => 'Tags',
        'URL' => 'Rated URL',
        'Created' => 'Created'
    ];

    private static $default_sort = 'ID DESC';

    private static $extensions = [
        Versioned::class
    ];

    /**
     * Average rating for this PageID
     *
     * @return Int
     */
    public function getAverage()
    {
        if ($this->PageID) {
            return round(Rating::get()->filter('PageID', $this->PageID)->avg('RatingScore'), 2);
        }
    }

    public function getPageType()
    {
        if ($this->PageID) {
            return $this->Page()->dbObject('ClassName')->ShortName;
        }
    }

    /**
     * Total count of ratings for this page ID
     *
     * @return Int
     */
    public function getTotalRatings()
    {
        if ($this->PageID) {
            return Rating::get()->filter('PageID', $this->PageID)->count();
        }
    }

    /**
     * Monthly average of ratings for this pageID
     *
     * @return Int
     */
    public function getMonthlyAverage()
    {
        if ($this->PageID) {
            $ratings = RatingFeedback::get()->filter([
                'PageID' => $this->PageID,
                'Created:GreaterThan' => sprintf('%s 00:00:00', date("Y-m-1", strtotime($this->Created))),
                'Created:LessThan' => sprintf('%s 23:59:59', date("Y-m-t", strtotime($this->Created)))
            ]);

            return round($ratings->avg('RatingScore'), 2);
        }
    }

    /**
     * Friendly format for Created month
     *
     * @return string
     */
    public function getCreatedMonth()
    {
        return $this->dbObject('Created')->Format('m y');
    }

    /**
     * Total count of ratings for this page ID per month
     *
     * @return Int
     */
    public function getMonthlyTotalRatings()
    {
        if ($this->PageID) {
            $ratings = RatingFeedback::get()->filter([
                'PageID' => $this->PageID,
                'Created:GreaterThan' => sprintf('%s 00:00:00', date("Y-m-1", strtotime($this->Created))),
                'Created:LessThan' => sprintf('%s 23:59:59', date("Y-m-t", strtotime($this->Created)))
            ]);

            return $ratings->count();
        }
    }

    /**
     * URL link for summary fields
     */
    public function getSummaryURL()
    {
        return DBField::create_field('HTMLText', sprintf(
            "<a href=\"%s\" target=\"_blank\">%s</a>",
            $this->URL,
            $this->URL
        ));
    }

    /**
     * Noone should be able to create or edit a rating
     */
    public function providePermissions()
    {
        return [
            'VIEW_RATING' => [
                'name' => 'View Ratings',
                'category' => 'Ratings'
            ],
            'ARCHIVE_RATING' => [
                'name' => 'Archive Ratings',
                'category' => 'Ratings'
            ]
        ];
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canView($member = null)
    {
        return Permission::check('VIEW_RATING');
    }

    public function canArchive($member = null)
    {
        return Permission::check('ARCHIVE_RATING');
    }
}
