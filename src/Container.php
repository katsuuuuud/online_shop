<?php

class Container
{
    public CatalogController $catalogController;
    public CartController $cartController;
    public OrderController $orderController;

    public function __construct()
    {
        $catalogRepo = new CatalogRepository();
        $cartRepo    = new CartRepository();
        $orderRepo   = new OrderRepository();

        $this->catalogController = new CatalogController($catalogRepo);
        $this->cartController    = new CartController($cartRepo, $catalogRepo);
        $this->orderController   = new OrderController(new OrderService($orderRepo, $cartRepo));
    }
}