<?php
/**
 * Local configuration.
 *
 * Copy this file to `local.php` and change its settings as required.
 * `local.php` is ignored by git and safe to use for local and sensitive data like usernames and passwords.
 */

declare(strict_types=1);

return [
    "mysql" => [
        "hostname" => "itemdb",
        "dbname" => "shopmany",
        "user" => "root",
        "pass" => "root",
    ],
    "opentracing-jaeger-exporter" => [
        "options" => [
            'sampler' => [
                'type' => \Jaeger\SAMPLER_TYPE_CONST,
                'param' => true,
            ],
            'logging' => true,
            'local_agent' => [
                'reporting_host' => 'jaeger-workshop',
            ],
        ],
        "service_name" => 'item',
    ],
];
