<?php

namespace App\Handler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use App\Service\ItemService;

class Item implements RequestHandlerInterface
{
    private $itemService;

    function __construct(ItemService $itemService) {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $items = $this->itemService->list();
        return new JsonResponse(['items' => $items]);
    }
}
