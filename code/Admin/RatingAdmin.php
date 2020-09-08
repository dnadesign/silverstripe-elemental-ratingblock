<?php

namespace DNADesign\Elemental\Admins;

use SilverStripe\Admin\ModelAdmin;
use DNADesign\Elemental\Models\Rating;
use DNADesign\Elemental\Models\ElementRatingBlock;
use Colymba\BulkManager\BulkManager;
use Colymba\BulkManager\BulkAction\UnlinkHandler;
use Colymba\BulkManager\BulkAction\EditHandler;

class RatingAdmin extends ModelAdmin
{
    private static $managed_models = [
        Rating::class,
        ElementRatingBlock::class,
    ];

    private static $menu_title = 'Ratings';

    private static $url_segment = 'ratings';

    private static $menu_icon_class = 'font-icon-menu-reports';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $field = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));
        $config = $field->getConfig();

        if ($this->modelClass === Rating::class) {
            // Add bulk option
            $manager = new BulkManager();
            $manager->removeBulkAction(EditHandler::class);
            $manager->removeBulkAction(UnlinkHandler::class);
            $config->addComponent($manager);
        }

        if ($this->modelClass === ElementRatingBlock::class) {
            $config->removeComponentsByType(GridFieldArchiveAction::class);
            $config->removeComponentsByType(GridFieldDeleteAction::class);
            $config->removeComponentsByType(GridFieldExportButton::class);
            $config->removeComponentsByType(GridFieldPrintButton::class);
            $config->removeComponentsByType(GridFieldImportButton::class);
        }

        return $form;
    }
}
