<?php

declare(strict_types=1);

final class ProductController
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function index(): never
    {
        $filters = [];

        if (!empty($_GET['type'])) {
            $filters['type'] = trim($_GET['type']);
        }

        if (isset($_GET['featured'])) {
            $filters['featured'] = (int) $_GET['featured'];
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = trim($_GET['search']);
        }

        if (!empty($_GET['limit'])) {
            $filters['limit'] = (int) $_GET['limit'];
        }

        $products = $this->repository->getAll($filters);

        Response::success(
            $products,
            'Products retrieved successfully'
        );
    }

    public function show(string $slug): void
    {
        $product = $this->repository->findBySlug($slug);

        if (!$product) {

            Response::error(
                'Product not found',
                HTTP_NOT_FOUND
            );

            return;
        }

        Response::success(
            $product,
            'Product retrieved successfully'
        );
    }
}