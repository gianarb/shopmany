<?php
namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class HealthFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mysqlConfig = $container->get('config')['mysql'];
        return new Health($mysqlConfig['hostname'], $mysqlConfig['user'], $mysqlConfig['pass'], $mysqlConfig['dbname']);
    }
}
