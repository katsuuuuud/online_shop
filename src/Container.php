<?php

class Container
{
    public CatalogController $catalogController;
    public CartController    $cartController;
    public OrderController   $orderController;
    public ProfileController $profileController;
    public AuthController    $authController;

    public function __construct()
    {
        $authRepo      = new AuthRepository();
        $catalogRepo   = new CatalogRepository();
        $cartRepo      = new CartRepository();
        $orderRepo     = new OrderRepository();

        $authService    = new AuthService($authRepo, $cartRepo);
        $cartService    = new CartService($cartRepo, $catalogRepo);
        $catalogService = new CatalogService($catalogRepo);
        $profileService = new ProfileService($authRepo, $orderRepo);
        $orderService   = new OrderService($orderRepo, $cartRepo);

        $this->catalogController = new CatalogController($catalogService);
        $this->cartController    = new CartController($cartService);
        $this->orderController   = new OrderController($orderService);
        $this->profileController = new ProfileController($profileService);
        $this->authController    = new AuthController($authService);
    }
}
