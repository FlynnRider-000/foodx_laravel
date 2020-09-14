<?php
/**
 * File name: ProductsByPriorityCriteria.php
 * Last modified: 2020.08.08 at 21:55:08
 * Author: Vadim
 * Copyright (c) 2020
 *
 */

namespace App\Criteria\Products;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class ProductsByPriorityCriteria.
 *
 * @package namespace App\Criteria\Products;
 */
class ProductsByPriorityCriteria implements CriteriaInterface
{
    /**
     * @var int
     */

    /**
     * ProductsByPriorityCriteria constructor.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->orderBy('priority');
    }
}
