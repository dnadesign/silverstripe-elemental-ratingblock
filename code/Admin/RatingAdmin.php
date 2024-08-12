<?php

namespace DNADesign\Elemental\Admins;

use Colymba\BulkManager\BulkAction\ArchiveHandler;
use Colymba\BulkManager\BulkAction\DeleteHandler;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Security\Security;
use Colymba\BulkManager\BulkManager;
use SilverStripe\Security\Permission;
use DNADesign\Elemental\Models\Rating;
use Colymba\BulkManager\BulkAction\EditHandler;
use Colymba\BulkManager\BulkAction\UnlinkHandler;
use DNADesign\Elemental\Models\ElementRatingBlock;
use SilverStripe\Versioned\GridFieldArchiveAction;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;

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
        $member = Security::getCurrentUser();

        if ($this->modelClass === Rating::class) {
            if (Permission::check('ARCHIVE_RATING', 'any', $member)) {
                // Add bulk option
                $manager = BulkManager::create();
                $manager->removeBulkAction(EditHandler::class);
                $manager->removeBulkAction(UnlinkHandler::class);
                $manager->removeBulkAction(DeleteHandler::class);
                $manager->addBulkAction(ArchiveHandler::class);
                $config->addComponent($manager);
            }
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