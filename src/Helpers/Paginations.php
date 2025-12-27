<?php

namespace Core\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginations
{
    /**
     * Генерирует пустую пагинацию
     *
     * @param int $perPage = 25
     * @param int $currentPage = 1
     * @param int $total = 0
     *
     * @return LengthAwarePaginator
     */
    public static function generateEmpty(
        int $perPage = 25,
        int $currentPage = 1,
        int $total = 0
    ): LengthAwarePaginator {
        return new LengthAwarePaginator(
            collect([]),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }
}
