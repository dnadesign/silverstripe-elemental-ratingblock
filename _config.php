<?php

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;

$helpEditor = HTMLEditorConfig::get('help');
// Start with the same configuration as 'cms' config (defined in framework/admin/_config.php).
$helpEditor->setOptions([
    'friendly_name' => 'Help Text',
    'skin' => 'silverstripe'
]);

// Enable insert-link to internal pages
$tinymceModule = ModuleLoader::inst()->getManifest()->getModule('silverstripe/htmleditor-tinymce');
$helpEditor
    ->enablePlugins([
        'sslinkinternal' => $tinymceModule
            ->getResource('client/dist/js/TinyMCE_sslink-internal.js'),
        'sslinkanchor' => $tinymceModule
            ->getResource('client/dist/js/TinyMCE_sslink-anchor.js'),
    ]);

// Add SilverStripe link options
$helpEditor
    ->enablePlugins([
        'contextmenu' => null,
        'image' => null,
        'sslink' => $tinymceModule->getResource('client/dist/js/TinyMCE_sslink.js'),
        'sslinkexternal' => $tinymceModule->getResource('client/dist/js/TinyMCE_sslink-external.js'),
        'sslinkemail' => $tinymceModule->getResource('client/dist/js/TinyMCE_sslink-email.js'),
    ])
    ->setOption('contextmenu', 'sslink ssmedia ssembed inserttable | cell row column deletetable');

$helpEditor->removeButtons(
    'alignleft',
    'aligncenter',
    'alignright',
    'alignjustify',
    'indent',
    'outdent',
    'bullist',
    'numlist',
    'formatselect',
    'paste',
    'pastetext',
    'code',
    'table',
    'sslink'
);

// Second line:
$helpEditor->addButtonsToLine(1, 'sslink', 'code');
