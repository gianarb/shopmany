<?php

namespace App\Handler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use App\Service\ItemService;
use Monolog\Logger;
use Monolog\Processor\TagProcessor;

class Item implements RequestHandlerInterface
{
    private $itemService;
    private $logger;

    function __construct(ItemService $itemService) {
        $this->itemService = $itemService;
        $this->logger = new Logger('item_service');
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->logger->info("Get list of items");
        $items = $this->itemService->list();
        $this->logger->info("Retrived list of items", ["num_items" => count($items)]);
        return new JsonResponse(['items' => $items]);
    }

    public function withLogger($logger) {
        $this->logger = $logger;
    }
}
