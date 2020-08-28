<?php

namespace DNADesign\Elemental\Admins;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Versioned\GridFieldArchiveAction;
use DNADesign\Elemental\Models\ElementRatingBlock;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;

class RatingBlockAdmin extends ModelAdmin
{
    private static $managed_models = [
        ElementRatingBlock::class,
    ];

    private static $menu_title = 'Rating blocks';

    private static $url_segment = 'rating-blocks';

    private static $menu_icon_class = 'font-icon-check-mark-2';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $field = $form->Fields()->dataFieldByName($this->sanitiseClassName(ElementRatingBlock::class));
        $config = $field->getConfig();

        $config->removeComponentsByType(GridFieldArchiveAction::class);
        $config->removeComponentsByType(GridFieldExportButton::class);
        $config->removeComponentsByType(GridFieldPrintButton::class);
        $config->removeComponentsByType(GridFieldImportButton::class);

        return $form;
    }
}
