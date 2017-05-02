<?php

namespace Piwik\Plugins\AwsSqsTracking;

use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Class SystemSettings
 * @package Piwik\Plugins\AwsSqsTracking
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
    public $outputQueueUrl;

    /**
     * @var Setting
     */
    public $inputQueueUrl;

    /**
     * @var Setting
     */
    public $keepUsualBehaviour;

    /**
     * @var Setting
     */
    public $logAllCommunication;

    protected function init()
    {
        $this->accessKey = $this->createAccessKeySetting();
        $this->secretKey = $this->createSecretKeySetting();
        $this->region = $this->createRegionSetting();
        $this->outputQueueUrl = $this->createOutputQueueUrlSetting();
        $this->inputQueueUrl = $this->createInputQueueUrlSetting();
        $this->keepUsualBehaviour = $this->createKeepUsualBehaviourSetting();
        $this->logAllCommunication = $this->createLogAllCommunicationSetting();
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createAccessKeySetting()
    {
        return $this->makeSetting(
            'accessKey',
            $default = '',
            FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_AccessKey_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createSecretKeySetting()
    {
        return $this->makeSetting(
            'secretKey',
            $default = '',
            FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_SecretKey_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_PASSWORD;
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createRegionSetting()
    {
        return $this->makeSetting(
            'region',
            $default = '',
            FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_Region_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_Region_Description');
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createOutputQueueUrlSetting()
    {
        return $this->makeSetting(
            'outputQueueUrl',
            $default = '',
            FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_OutputQueueUrl_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = Piwik::translate(
                    'AwsSqsTracking_PluginSettings_Setting_OutputQueueUrl_Description'
                );
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createInputQueueUrlSetting()
    {
        return $this->makeSetting(
            'inputQueueUrl',
            $default = '',
            FieldConfig::TYPE_STRING,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_InputQueueUrl_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_TEXT;
                $field->description = Piwik::translate(
                    'AwsSqsTracking_PluginSettings_Setting_InputQueueUrl_Description'
                );
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createKeepUsualBehaviourSetting()
    {
        return $this->makeSetting(
            'keepUsualBehaviour',
            $default = true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_KeepUsualBehaviour_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            }
        );
    }

    /**
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createLogAllCommunicationSetting()
    {
        return $this->makeSetting(
            'logAllCommunication',
            $default = true,
            FieldConfig::TYPE_BOOL,
            function (FieldConfig $field) {
                $field->title = Piwik::translate('AwsSqsTracking_PluginSettings_Setting_LogAllCommunication_Title');
                $field->uiControl = FieldConfig::UI_CONTROL_CHECKBOX;
            }
        );
    }
}
