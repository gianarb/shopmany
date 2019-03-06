<?php
namespace App\Service;

use Psr\Container\ContainerInterface;

class ItemServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mysqlConfig = $container->get('config')['mysql'];
        return new ItemService($mysqlConfig['hostname'], $mysqlConfig['user'], $mysqlConfig['pass'], $mysqlConfig['dbname']);
    }
}

