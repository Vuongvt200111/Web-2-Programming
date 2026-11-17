<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Category domain model managing Tokusatsu genre CRUD and film count aggregations.
 */
final class CategoryModel extends BaseModel
{
    public function __construct(\PDO $pdo)
    {
        parent::__construct($pdo, 'category', 'id');
    }

    public function findAllAlphabetical(): array
    {
        return $this->findAll('category_name', 'ASC');
    }

    public function findAllWithFilmCount(): array
    {
        return $this->gateway->customQuery(
            'SELECT c.*, COUNT(f.id) AS film_count
             FROM category c
             LEFT JOIN films f ON c.id = f.category_id
             GROUP BY c.id
             ORDER BY c.id ASC'
        );
    }
}
