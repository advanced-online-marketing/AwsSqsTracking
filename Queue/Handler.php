<?php

namespace Piwik\Plugins\AwsSqsTracking\Queue;

use Piwik\Common;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet;
use Exception;

class Handler
{
    protected $transactionId;

    private $hasError = false;
    private $requestSetsToRetry = [];
    private $count = 0;
    private $numTrackedRequestsBeginning = 0;

    public function init(Tracker $tracker)
    {
        $this->requestSetsToRetry = [];
        $this->hasError = false;
        $this->numTrackedRequestsBeginning = $tracker->getCountOfLoggedRequests();
        $this->transactionId = $this->getDb()->beginTransaction();
    }

    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        $requestSet->restoreEnvironment();

        $this->count = 0;

        foreach ($requestSet->getRequests() as $request) {
            try {
                $startMs = round(microtime(true) * 1000);

                $tracker->trackRequest($request);

                $diffInMs = round(microtime(true) * 1000) - $startMs;
                if ($diffInMs > 2000) {
                    Common::printDebug(sprintf('The following request took more than 2 seconds (%d ms) to be tracked: %s', $diffInMs, var_export($request->getParams(), 1)));
                }

                $this->count++;
            } catch (UnexpectedWebsiteFoundException $ex) {
                // empty
            }
        }

        $this->requestSetsToRetry[] = $requestSet;
    }

    public function onException(RequestSet $requestSet, Exception $e)
    {
        // todo: how do we want to handle DbException or RedisException?
        $this->hasError = true;

        if ($this->count > 0) {
            // remove the first one that failed and all following (standard bulk tracking behavior)
            $insertedRequests = array_slice($requestSet->getRequests(), 0, $this->count);
            $requestSet->setRequests($insertedRequests);
            $this->requestSetsToRetry[] = $requestSet;
        }
    }

    public function hasErrors()
    {
        return $this->hasError;
    }

    public function rollBack(Tracker $tracker)
    {
        $tracker->setCountOfLoggedRequests($this->numTrackedRequestsBeginning);
        $this->getDb()->rollBack($this->transactionId);
    }

    /**
     * @return RequestSet[]
     */
    public function getRequestSetsToRetry()
    {
        return $this->requestSetsToRetry;
    }

    public function commit()
    {
        $this->getDb()->commit($this->transactionId);
        $this->requestSetsToRetry = [];
    }

    protected function getDb()
    {
        return Tracker::getDatabase();
    }
}
