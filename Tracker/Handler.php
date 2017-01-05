<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\AwsTracking\Tracker;

use Aws\Credentials\Credentials;
use Aws\Sqs\SqsClient;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AwsTracking\SystemSettings;
use Piwik\Tracker;
use Piwik\Plugins\AwsTracking\Queue\Backend;
use Piwik\Plugins\AwsTracking\Queue\Processor;
use Piwik\Tracker\RequestSet;

/**
 * @method Response getResponse()
 */
class Handler extends Tracker\Handler
{
    /**
     * @var SqsClient|static
     */
    private  $client;

    public function __construct()
    {
        $this->setResponse(new Response());

        /** @var SystemSettings $settings */
        $settings = StaticContainer::get('Piwik\Plugins\AwsTracking\SystemSettings');

        $credentials = new Credentials($settings->accessKey->getValue(), $settings->secretKey->getValue());

        $this->client = SqsClient::factory(array(
            'credentials' => $credentials,
            'version' => '2012-11-05',
//            'debug' => true,
            'profile' => $settings->profile->getValue(),
            'region'  => $settings->region->getValue()
        )
        );
    }

    // here we write add the tracking requests to a list
    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        /** @var SystemSettings $settings */
        $settings = StaticContainer::get('Piwik\Plugins\AwsTracking\SystemSettings');

//        $this->client->sendMessage(array(
//            'QueueUrl'    => $settings->queueUrl->getValue(),
//            'MessageBody' => json_encode($requestSet->getState()),
//        ));

        $this->sendResponseNow($tracker, $requestSet);
    }

    private function sendResponseNow(Tracker $tracker, RequestSet $requestSet)
    {
        $response = $this->getResponse();
        $response->outputResponse($tracker);
        $this->redirectIfNeeded($requestSet);
        $response->sendResponseToBrowserDirectly();
    }
}
