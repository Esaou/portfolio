<?php

declare(strict_types=1);

namespace App\Model\Repository\Interfaces;

interface EntityRepositoryInterface
{
    public function find(int $id): ?object;

    public function findOneBy(array $criteria, array $orderBy = null): ?object;

    public function findAll(): ?array;

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array;

    public function create(object $entity): bool;

    public function update(object $entity): bool;

    public function delete(object $entity): bool;
}
