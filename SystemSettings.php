<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\AwsTracking;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for AwsTracking.
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /**
     * @var Setting
     */
    public $accessKey;

    /**
     * @var Setting
     */
    public $secretKey;

    /**
     * @var Setting
     */
    public $region;

    /**
     * @var Setting
     */
    public $queueUrl;

    /**
     * @var Setting
     */
    public $keepUsualBehaviour;

    protected function init()
    {
        $this->accessKey = $this->createAccessKeySetting();
        $this->secretKey = $this->createSecretKeySetting();
        $this->region = $this->createRegionSetting();
        $this->queueUrl = $this->createQueueUrlSetting();
        $this->keepUsualBehaviour = $this->createKeepUsualBehaviourSetting();
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

    private function createRegionSetting()
    {
        return $this->makeSetting('region', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'AWS Region';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'AWS Region, e.g. "eu-central-1"';
            $field->validate = function ($value) {
                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });
    }

    private function createQueueUrlSetting()
    {
        return $this->makeSetting('queueUrl', $default = '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title = 'AWS SQS Queue URL';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            $field->uiControlAttributes = array('size' => 100);
            $field->inlineHelp = 'AWS SQS Queue URL';
            $field->validate = function ($value) {
                if (strlen($value) > 100) {
                    throw new \Exception('Max 100 characters allowed');
                }
            };
        });
    }

    private function createKeepUsualBehaviourSetting()
    {
        return $this->makeSetting(
            'keepUsualBehaviour',
            $default = true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsTracking_PluginSettings_Setting_KeepUsualBehaviour_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            }
        );
    }
}
