<?php

namespace Piwik\Plugins\AwsSqsTracking\Commands;

use Piwik\Application\Environment;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Tracker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Process extends ConsoleCommand
{

    protected function configure()
    {
        $this->setName('aws-sqs-tracking:process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $trackerEnvironment = new Environment('tracker');
        $trackerEnvironment->init();

        Log::unsetInstance();
        // TODO: We might need this, to set the datetime?!
        $trackerEnvironment->getContainer()->get('Piwik\Access')->setSuperUserAccess(false);
        $trackerEnvironment->getContainer()->get('Piwik\Plugin\Manager')->setTrackerPluginsNotToLoad(array('Provider'));
        Tracker::loadTrackerEnvironment();

        if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
            $GLOBALS['PIWIK_TRACKER_DEBUG'] = true;
        }

        $output->writeln("<info>Starting to process request sets, this can take a while</info>");


        $startTime = microtime(true);
        $processor = new Processor($queueManager);
        $processor->setNumberOfMaxBatchesToProcess(1000);
        $tracker   = $processor->process();

        $neededTime = (microtime(true) - $startTime);
        $numRequestsTracked = $tracker->getCountOfLoggedRequests();
        $requestsPerSecond  = $this->getNumberOfRequestsPerSecond($numRequestsTracked, $neededTime);

        Piwik::postEvent('Tracker.end');

        $trackerEnvironment->destroy();

        $this->writeSuccessMessage($output, array(sprintf('This worker finished queue processing with %sreq/s (%s requests in %02.2f seconds)', $requestsPerSecond, $numRequestsTracked, $neededTime)));
    }

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
