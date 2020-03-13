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
    "zipkin" => [
        "serviceName" => 'items',
        "host" => 'http://zipkin',
        "port" => 9411,
    ],
];
