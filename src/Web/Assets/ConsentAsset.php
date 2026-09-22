<?php

namespace QuebecStudioMods\ConsentKit\CraftCms\Web\Assets;

use craft\web\AssetBundle;
use QuebecStudioMods\ConsentKit\Core\Paths;

/**
 * The core package's front-end assets. No dependencies declared on purpose —
 * the bundle must not pull in jQuery or any control panel asset.
 */
class ConsentAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = dirname(Paths::asset('consent.js'));
        $this->js = ['consent.js'];
        $this->css = ['consent.css'];

        $this->publishOptions = ['only' => ['consent.js', 'consent.css']];

        parent::init();
    }
}
