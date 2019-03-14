<?php

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            App\Service\ItemService::class => App\Service\ItemServiceFactory::class,
            App\Handler\Item::class => App\Handler\ItemFactory::class,
            App\Handler\Health::class => App\Handler\HealthFactory::class,
            "Logger" => App\Service\LoggerFactory::class,
            App\Middleware\LoggerMiddleware::class => App\Middleware\LoggerMiddlewareFactory::class,
            App\Middleware\TracerMiddleware::class => App\Middleware\TracerMiddlewareFactory::class,
        ],
    ],
];
