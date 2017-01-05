<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AwsTracking;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Plugins\AwsTracking\Settings\NumWorkers;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Storage\Backend;
use Piwik\Plugins\AwsTracking\Queue\Factory;
use Piwik\Piwik;
use Exception;

/**
 * Defines Settings for AwsTracking.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $accessKey;

    /** @var Setting */
    public $secretKey;

    /** @var Setting */
    public $profile;

    /** @var Setting */
    public $region;

    /** @var Setting */
    public $queueUrl;

    protected function init()
    {
        $this->accessKey = $this->createAccessKeySetting();
        $this->secretKey = $this->createSecretKeySetting();
        $this->profile = $this->createProfileSetting();
        $this->region = $this->createRegionSetting();
        $this->queueUrl = $this->createQueueUrlSetting();
    }

    public function isUsingSentinelBackend()
    {
        return $this->useSentinelBackend->getValue();
    }

    public function getRegion()
    {
        return $this->region->getValue();
    }

    public function isUsingUnixSocket()
    {
        return substr($this->accessKey->getValue(), 0, 1) === '/';
    }

    private function createAccessKeySetting()
    {
        $self = $this;

        return $this->makeSetting('accessKey', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) use ($self) {
            $field->title = 'AWS Access Key';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 50);
            $field->inlineHelp = 'AWS Access Key';

            $field->validate = function ($value) use ($self) {
                if (strlen($value) > 50) {
                    throw new \Exception('Max 50 characters allowed');
                }
            };
        });
    }


    private function createQueueUrlSetting()
    {
        $setting = $this->makeSetting('queueUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'queueUrl for SQS';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'queueUrl for SQS';
            $field->validate = function ($value) {


                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });

        return $setting;
    }


    private function createSecretKeySetting()
    {
        return $this->makeSetting('secretKey', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'AWS Secret Key';
            $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'AWS Secret Key';
            $field->validate = function ($value) {
                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });
    }

    private function createProfileSetting()
    {
        return $this->makeSetting('profile', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'AWS Profile';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'AWS Profile.';
            $field->validate = function ($value) {
                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });
    }


    private function createRegionSetting()
    {
        return $this->makeSetting('region', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'AWS Region';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'AWS Region.';
            $field->validate = function ($value) {
                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });
    }
}
