<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\AwsTracking\Tracker;

use Aws\Sqs\SqsClient;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AwsTracking\SystemSettings;
use Piwik\Tracker;
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

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        /** @var SystemSettings $settings */
        $settings = StaticContainer::get('Piwik\Plugins\AwsTracking\SystemSettings');

        $this->client = SqsClient::factory([
            'region'  => $settings->region->getValue(),
            'version' => 'latest',
            'credentials' => [
                'key' => $settings->accessKey->getValue(),
                'secret' => $settings->secretKey->getValue(),
            ],
//            'debug' => true,
        ]);
    }

    /**
     * Appends the tracking event to the AWS SQS queue
     *
     * @param Tracker $tracker
     * @param RequestSet $requestSet
     */
    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        /** @var SystemSettings $settings */
        $settings = StaticContainer::get('Piwik\Plugins\AwsTracking\SystemSettings');

        // Write tracking event to AWS SQS queue
        $this->client->sendMessage(array(
            'QueueUrl'    => $settings->queueUrl->getValue(),
            'MessageBody' => json_encode($requestSet->getState()),
        ));

        // Keep usual behaviour and process tracking event as if this plugin would not exist?
        if ($settings->keepUsualBehaviour->getValue()) {
            foreach ($requestSet->getRequests() as $request) {
                $tracker->trackRequest($request);
            }
        } else {
            $this->sendResponseNow($tracker, $requestSet);
        }
    }

    /**
     * @param Tracker $tracker
     * @param RequestSet $requestSet
     */
    private function sendResponseNow(Tracker $tracker, RequestSet $requestSet)
    {
        $response = $this->getResponse();
        $response->outputResponse($tracker);
        $this->redirectIfNeeded($requestSet);
        $response->sendResponseToBrowserDirectly();
    }
}
