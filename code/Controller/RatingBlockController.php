<?php

namespace DNADesign\Elemental\Controllers;

use SilverStripe\View\Requirements;
use DNADesign\Elemental\Controllers\ElementController;

class RatingBlockController extends ElementController
{
    public function init()
    {
        parent::init();

        Requirements::css('dnadesign/silverstripe-elemental-ratingblock: client/dist/main.css');
        Requirements::javascript('dnadesign/silverstripe-elemental-ratingblock: client/dist/bundle.js');
    }

    public function getBootData()
    {
        $bootData = [];

        $this->extend('updateBootdata', $bootData);

        return json_encode($bootData, JSON_UNESCAPED_UNICODE);
    }
}
