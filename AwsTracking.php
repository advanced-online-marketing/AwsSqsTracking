<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AwsTracking;

use Piwik\Plugins\AwsTracking\Tracker\Handler;

class AwsTracking extends \Piwik\Plugin
{
    /**
     * @param bool|string $pluginName
     */
    public function __construct($pluginName = false)
    {
        // Add composer dependencies
        require_once PIWIK_INCLUDE_PATH . '/plugins/AwsTracking/vendor/autoload.php';

        parent::__construct($pluginName);
    }

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Tracker.newHandler' => 'replaceHandler'
        );
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
