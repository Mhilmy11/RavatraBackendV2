<?php

declare(strict_types=1);

final class ProductController
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    /**
     * GET /products
     */
    public function index(): never
    {
        $products = $this->repository->getAll();

        Response::success(
            $products,
            'Products retrieved successfully'
        );
    }
}