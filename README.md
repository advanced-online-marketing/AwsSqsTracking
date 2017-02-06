# Piwik AwsSqsTracking Plugin

## Description

This plugin is based on [Piwik's Queued Tracking plugin](https://plugins.piwik.org/QueuedTracking). 

When activated, it writes all incoming tracking events into a configurable AWS SQS queue.

You can configure if the events should only be written to the AWS SQS queue or if they should be both written to the 
AWS SQS queue and be processed regularly.

By running `./console aws-sqs-tracking:process` the tracking events can be retrieved and processed from that queue or 
from another queue.
