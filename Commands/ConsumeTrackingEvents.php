<?php

namespace Piwik\Plugins\AwsSqsTracking\Commands;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Piwik\Application\Environment;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\AwsSqsTracking\Queue\Processor;
use Piwik\Plugins\AwsSqsTracking\SystemSettings;
use Piwik\Tracker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeTrackingEvents extends ConsoleCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    protected function configure()
    {
        $this
            ->setName('aws-sqs-tracking:process')
            ->addOption('event', null, InputOption::VALUE_OPTIONAL, 'A specific tracking event to process', false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We use our own logger
        $format = '%level_name% [%datetime%]: %message% %context% %extra%';
        $this->logger = new Logger('aws-sqs-tracking');
        $tasksLoggerFileStreamHandler = new StreamHandler(PIWIK_INCLUDE_PATH . '/aws-sqs-tracking.log', Logger::DEBUG);
        $tasksLoggerFileStreamHandler->setFormatter(new LineFormatter($format . "\n", null, true, true));
        $this->logger->pushHandler($tasksLoggerFileStreamHandler);
        $tasksLoggerConsoleStreamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $tasksLoggerConsoleStreamHandler->setFormatter(new ColoredLineFormatter(null, $format, null, true, true));
        $this->logger->pushHandler($tasksLoggerConsoleStreamHandler);

        // Increase memory-limit as this command requires some resources
        $this->logger->info('Current "memory_limit": ' . ini_get('memory_limit'));
        ini_set('memory_limit', '1024M');
        $this->logger->info('Current "memory_limit" (after "ini_set(...)"): ' . ini_get('memory_limit'));

        $trackerEnvironment = new Environment('tracker');
        $trackerEnvironment->init();

        Log::unsetInstance();
        // This is for security; token_auth should be added e.g. by an AWS lambda function to set datetime and similar!
        $trackerEnvironment->getContainer()->get('Piwik\Access')->setSuperUserAccess(false);
        $trackerEnvironment->getContainer()->get('Piwik\Plugin\Manager')->setTrackerPluginsNotToLoad(['Provider']);
        Tracker::loadTrackerEnvironment();

        // Enable extensive logging
        $settings = new SystemSettings();
        if ($settings->logAllCommunication->getValue()) {
            $GLOBALS['PIWIK_TRACKER_DEBUG'] = true;
            $this->logger->debug('Logging of all AWS SQS queue communication and Piwik tracker debugging enabled.');
        }

        $this->logger->debug('Starting to process tracking events from AWS SQS input queue.');


        $startTime = microtime(true);
        $processor = new Processor($this->logger);
        $tracker = $processor->process($input->getOption('event'));

        $neededTime = (microtime(true) - $startTime);
        $numRequestsTracked = $tracker->getCountOfLoggedRequests();
        $requestsPerSecond  = $this->getNumberOfRequestsPerSecond($numRequestsTracked, $neededTime);

        Piwik::postEvent('Tracker.end');

        $trackerEnvironment->destroy();

        $this->logger->info(sprintf(
            'This worker finished queue processing with %sreq/s (%s requests in %02.2f seconds)',
            $requestsPerSecond, $numRequestsTracked, $neededTime)
        );
    }

    /**
     * @param $numRequestsTracked
     * @param $neededTimeInSeconds
     * @return float
     */
    private function getNumberOfRequestsPerSecond($numRequestsTracked, $neededTimeInSeconds)
    {
        if (empty($neededTimeInSeconds)) {
            $requestsPerSecond = $numRequestsTracked;
        } else {
            $requestsPerSecond = round($numRequestsTracked / $neededTimeInSeconds, 2);
        }

        return $requestsPerSecond;
    }
}
