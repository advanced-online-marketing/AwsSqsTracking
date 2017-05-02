# Piwik AwsSqsTracking Plugin

## Description

This plugin is based on [Piwik's Queued Tracking plugin](https://plugins.piwik.org/QueuedTracking). 

When activated, it writes all incoming tracking events into a configurable AWS SQS queue.

You can configure if the events should only be written to the AWS SQS queue or if they should be both written to the 
AWS SQS queue and be processed regularly.

By running `./console aws-sqs-tracking:process` the tracking events can be retrieved and processed from that queue or 
from another queue. The queue separation allows you to modify the events before they are consumed again.


## Event format

The events that are both written to the queue and consumed from the queue are in JSON format. **This is different from 
Piwik's Queued Tracking plugin.** Here's an example:

    {
        "piwik": true,
        "content": {
            "requests": [
                {
                    "action_name": "Web Shop Category Title",
                    "idsite": "1",
                    "rec": "1",
                    "r": "277578",
                    "h": "23",
                    "m": "20",
                    "s": "29",
                    "url": "http://local.web-shop.de/app_dev.php/category-1.html",
                    "urlref": "http://local.web-shop.de/app_dev.php/index.html",
                    "_id": "147bf64b6743afe9",
                    "_idts": "1485555611",
                    "_idvc": "1",
                    "_idn": "0",
                    "_refts": "0",
                    "_viewts": "1485555611",
                    "send_image": "1",
                    "pdf": "1",
                    "qt": "0",
                    "realp": "0",
                    "wma": "0",
                    "dir": "0",
                    "fla": "1",
                    "java": "0",
                    "gears": "0",
                    "ag": "0",
                    "cookie": "1",
                    "res": "1680x1050",
                    "gt_ms": "10098",
                    "pv_id": "GYV5HA"
                }
            ],
            "env": {
                "server": {
                    "USER": "vagrant",
                    "HOME": "/home/vagrant",
                    "SCRIPT_NAME": "/piwik.php",
                    "REQUEST_URI": "/piwik.php?action_name=Web%20Shop%20Category%20Title&idsite=1&rec=1&r=277578&h=23&m=20&s=29&url=http%3A%2F%2Flocal.web-shop.de%2Fapp_dev.php%2Fcategory-1.html&urlref=http%3A%2F%2Flocal.web-shop.de%2Fapp_dev.php%2Findex.html&_id=147bf64b6743afe9&_idts=1485555611&_idvc=1&_idn=0&_refts=0&_viewts=1485555611&send_image=1&pdf=1&qt=0&realp=0&wma=0&dir=0&fla=1&java=0&gears=0&ag=0&cookie=1&res=1680x1050&gt_ms=10098&pv_id=GYV5HA",
                    "QUERY_STRING": "action_name=Web%20Shop%20Category%20Title&idsite=1&rec=1&r=277578&h=23&m=20&s=29&url=http%3A%2F%2Flocal.web-shop.de%2Fapp_dev.php%2Fcategory-1.html&urlref=http%3A%2F%2Flocal.web-shop.de%2Fapp_dev.php%2Findex.html&_id=147bf64b6743afe9&_idts=1485555611&_idvc=1&_idn=0&_refts=0&_viewts=1485555611&send_image=1&pdf=1&qt=0&realp=0&wma=0&dir=0&fla=1&java=0&gears=0&ag=0&cookie=1&res=1680x1050&gt_ms=10098&pv_id=GYV5HA",
                    "REQUEST_METHOD": "GET",
                    "SERVER_PROTOCOL": "HTTP/1.1",
                    "GATEWAY_INTERFACE": "CGI/1.1",
                    "REMOTE_PORT": "62520",
                    "SCRIPT_FILENAME": "/srv/www/piwik/piwik.php",
                    "SERVER_ADMIN": "[no address given]",
                    "CONTEXT_DOCUMENT_ROOT": "/srv/www/piwik",
                    "CONTEXT_PREFIX": "",
                    "REQUEST_SCHEME": "http",
                    "DOCUMENT_ROOT": "/srv/www/piwik",
                    "REMOTE_ADDR": "10.0.13.1",
                    "SERVER_PORT": "80",
                    "SERVER_ADDR": "10.0.13.37",
                    "SERVER_NAME": "local.piwik.de",
                    "SERVER_SOFTWARE": "Apache/2.4.7 (Ubuntu)",
                    "SERVER_SIGNATURE": "<address>Apache/2.4.7 (Ubuntu) Server at local.piwik.de Port 80</address>\n",
                    "PATH": "/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin",
                    "HTTP_COOKIE": "PIWIK_SESSID=...; piwik_auth=login%3DczoxMjoiYW5k8s2ua29sZWxsIjs%3D%3Atoken_auth%...%3D%3D%3A_%...",
                    "HTTP_ACCEPT_LANGUAGE": "de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4",
                    "HTTP_ACCEPT_ENCODING": "gzip, deflate, sdch",
                    "HTTP_REFERER": "http://local.web-shop.de/app_dev.php/category-1.html",
                    "HTTP_ACCEPT": "image/webp,image/*,*/*;q=0.8",
                    "HTTP_USER_AGENT": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36",
                    "HTTP_CONNECTION": "keep-alive",
                    "HTTP_HOST": "local.piwik.de",
                    "downgrade-1_0": "",
                    "proxy-nokeepalive": "1",
                    "force-response-1_0": "1",
                    "force-proxy-request-1_0": "1",
                    "WEBSITE_LOG_DIR": "/var/log/piwik",
                    "WEBSITE_CACHE_DIR": "/var/cache/piwik",
                    "FCGI_ROLE": "RESPONDER",
                    "PHP_SELF": "/piwik.php",
                    "REQUEST_TIME_FLOAT": 1485555629.7651,
                    "REQUEST_TIME": 1485555629
                }
            },
            "tokenAuth": false,
            "time": 1485555630
        }
    }


## Logging

When the setting `log all communication` is enables, all AWS SQS queue communication (raw events) is written to 
`aws-output-queue.txt` and `aws-input-queue.txt` respectively.
