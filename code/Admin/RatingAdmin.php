<?php

namespace DNADesign\Elemental\Admins;

use SilverStripe\Admin\ModelAdmin;
use Colymba\BulkManager\BulkManager;
use Colymba\BulkManager\BulkAction\UnlinkHandler;
use Colymba\BulkManager\BulkAction\EditHandler;
use DNADesign\Elemental\Models\Rating;

class RatingAdmin extends ModelAdmin
{
    private static $managed_models = [
        Rating::class,
    ];

    private static $menu_title = 'User ratings';

    private static $url_segment = 'ratings';

    private static $menu_icon_class = 'font-icon-menu-reports';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $field = $form->Fields()->dataFieldByName($this->sanitiseClassName(Rating::class));
        $config = $field->getConfig();

        // Add bulk option
        $manager = new BulkManager();
        $manager->removeBulkAction(EditHandler::class);
        $manager->removeBulkAction(UnlinkHandler::class);
        $config->addComponent($manager);

        return $form;
    }
}
