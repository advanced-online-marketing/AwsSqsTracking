<?php

namespace Piwik\Plugins\AwsSqsTracking;

use Piwik\Plugins\AwsSqsTracking\Tracker\Handler;

class AwsSqsTracking extends \Piwik\Plugin
{
    /**
     * @param bool|string $pluginName
     */
    public function __construct($pluginName = false)
    {
        // Add composer dependencies
        require_once PIWIK_INCLUDE_PATH . '/plugins/AwsSqsTracking/vendor/autoload.php';

        parent::__construct($pluginName);
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'Tracker.newHandler' => 'replaceHandler',
        ];
    }

    public function isTrackerPlugin()
    {
        return true;
    }

    public function replaceHandler(&$handler)
    {
        $handler = new Handler();
    }
}
