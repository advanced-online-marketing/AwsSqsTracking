<?php

namespace Piwik\Plugins\AwsSqsTracking\Queue;

use Aws\Sqs\SqsClient;
use Piwik\Plugins\AwsSqsTracking\SystemSettings;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet;
use Exception;
use Psr\Log\LoggerInterface;

class Processor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Handler
     */
    private $handler;

    /**
     * Processor constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->handler = new Handler();

        $settings = new SystemSettings();

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
     * @param string|bool $message Either a specific message that should be processed or false
     * @return Tracker
     */
    public function process($message)
    {
        $tracker = new Tracker();

        if (!$tracker->shouldRecordStatistics()) {
            return $tracker;
        }

        $request = new RequestSet();
        $request->rememberEnvironment();

        $settings = new SystemSettings();

        // Should we process only one specific message that has been provided when calling this command?
        if ($message) {

            $requestSetArray = json_decode($message, true);
            if ($requestSetArray === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Invalid tracking request set (JSON): ' . $message);
            }

            if (!is_array($requestSetArray)
                || !array_key_exists('content', $requestSetArray)
                || !is_array($requestSetArray['content'])
            ) {
                $this->logger->error('Invalid tracking request set: ' . $message);
            }

            $requestSet = new RequestSet();

            $requestSet->restoreState($requestSetArray['content']);

            $this->processRequestSet($tracker, $requestSet);

        } else {

            while (true) {

                // Stop command before we run out of memory (500 MB)
                if (memory_get_usage(true) > 500000000) {
                    $this->logger->warning('Stopping command due to its high memory-consumption.');
                    return $tracker;
                }

                $result = $this->client->receiveMessage([
                    'QueueUrl' => $settings->inputQueueUrl->getValue(),
                    'MaxNumberOfMessages' => 10,
                    'WaitTimeSeconds' => 10,
                ]);

                if (null !== $result->get('Messages')) {
                    $processedMessages = [];

                    foreach ($result->get('Messages') as $message) {

                        if ($settings->logAllCommunication->getValue()) {
                            $this->logger->debug('Got message from SQS: ' . $message['Body']);
                        }

                        $requestSetArray = json_decode($message['Body'], true);
                        if ($requestSetArray === null && json_last_error() !== JSON_ERROR_NONE) {
                            $this->logger->error('Invalid tracking request set (JSON): ' . $message['Body']);
                        }

                        if (!is_array($requestSetArray)
                            || !array_key_exists('content', $requestSetArray)
                            || !is_array($requestSetArray['content'])
                        ) {
                            $this->logger->error('Invalid tracking request set: ' . $message['Body']);
                        }

                        $requestSet = new RequestSet();

                        $requestSet->restoreState($requestSetArray['content']);

                        $this->processRequestSet($tracker, $requestSet);

                        $processedMessages[] = [
                            'Id' => $message['MessageId'],
                            'ReceiptHandle' => $message['ReceiptHandle']
                        ];
                    }

                    $this->client->deleteMessageBatch([
                        'QueueUrl' => $settings->inputQueueUrl->getValue(),
                        'Entries' => $processedMessages
                    ]);
                }
            }
        }

        $request->restoreEnvironment();

        return $tracker;
    }

    /**
     * @param Tracker $tracker
     * @param RequestSet $requestSet
     * @throws Exception
     */
    protected function processRequestSet(Tracker $tracker, $requestSet)
    {
        $this->handler->init($tracker);

        try {
            $this->handler->process($tracker, $requestSet);
        } catch (\Exception $e) {
            $this->logger->error('Failed to process a queued request set: ' . $e->getMessage());
            $this->handler->onException($requestSet, $e);

            // We do not want to consume events that we won't process correctly
            if (strpos($e->getMessage(), 'Piwik\Tracker\VisitorRecognizer.findKnownVisitor()')) {
                throw new \Exception('Aborting as ExternalVisitId installation is incomplete: ' . $e->getMessage());
            }
        }

        if ($this->handler->hasErrors()) {
            $this->handler->rollBack($tracker);
        } else {
            $this->handler->commit();
        }
    }
}
