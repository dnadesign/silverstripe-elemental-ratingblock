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
$cmsModule = ModuleLoader::inst()->getManifest()->getModule('silverstripe/cms');
$helpEditor
    ->enablePlugins([
        'sslinkinternal' => $cmsModule
            ->getResource('client/dist/js/TinyMCE_sslink-internal.js'),
        'sslinkanchor' => $cmsModule
            ->getResource('client/dist/js/TinyMCE_sslink-anchor.js'),
    ]);

// Add SilverStripe link options
$adminModule = ModuleLoader::inst()->getManifest()->getModule('silverstripe/admin');
$helpEditor
    ->enablePlugins([
        'contextmenu' => null,
        'image' => null,
        'sslink' => $adminModule->getResource('client/dist/js/TinyMCE_sslink.js'),
        'sslinkexternal' => $adminModule->getResource('client/dist/js/TinyMCE_sslink-external.js'),
        'sslinkemail' => $adminModule->getResource('client/dist/js/TinyMCE_sslink-email.js'),
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
