<?php

namespace Piwik\Plugins\AwsSqsTracking\Tracker;

use Aws\Sqs\SqsClient;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AwsSqsTracking\SystemSettings;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $settings = new SystemSettings();

        // Overwrite default response unless we keep the usual tracking behaviour
        if (!$settings->keepUsualBehaviour->getValue()) {
            $this->setResponse(new Response());
        }

        $this->client = SqsClient::factory([
            'region'  => $settings->region->getValue(),
            'version' => 'latest',
            'credentials' => [
                'key' => $settings->accessKey->getValue(),
                'secret' => $settings->secretKey->getValue(),
            ],
//            'debug' => true,
        ]);

        $this->logger =StaticContainer::get('Psr\Log\LoggerInterface');

    }

    /**
     * Appends the tracking event to the AWS SQS queue
     *
     * @param Tracker $tracker
     * @param RequestSet $requestSet
     */
    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        $settings = new SystemSettings();

        try {

            $messageBody = json_encode([
                'piwik' => true,
                'content' => $requestSet->getState()
            ]);

            if ($settings->logAllCommunication->getValue()) {
                $this->logger->debug('Will send message to SQS: ' . $messageBody);
            }
                // Write tracking event to AWS SQS queue
            $this->client->sendMessage([
                'QueueUrl' => $settings->outputQueueUrl->getValue(),
                'MessageBody' => $messageBody,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Could not send message to SQS', [$e->getCode(), $e->getMessage()]);
        }


        Common::printDebug('AwsSqsTracking plugin: Wrote RequestSet to AWS SQS output queue.');

        // Keep usual behaviour and process tracking event as if this plugin would not exist?
        if ($settings->keepUsualBehaviour->getValue()) {

            Common::printDebug('AwsSqsTracking plugin: Keep usual tracking behaviour.');

            foreach ($requestSet->getRequests() as $request) {
                $tracker->trackRequest($request);
            }

        } else {
            Common::printDebug('AwsSqsTracking plugin: Sending response immediately.');
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
        Common::sendResponseCode(200);
        $response->sendResponseToBrowserDirectly();
    }
}
