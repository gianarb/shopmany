<?php
namespace App\Handler;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Service\ItemService;

class ItemFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $h = new Item($container->get(ItemService::class));
        return $h;
    }
}
