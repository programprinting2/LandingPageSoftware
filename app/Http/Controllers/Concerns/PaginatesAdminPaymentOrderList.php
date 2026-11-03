<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

trait PaginatesAdminPaymentOrderList
{
    protected function normalizeAdminTrxStatusFilter(mixed $status): string
    {
        $status = is_string($status) ? strtolower(trim($status)) : 'all';

        return in_array($status, ['all', 'paid', 'expired'], true) ? $status : 'all';
    }

    protected function applyAdminTrxStatusFilter(Builder $query, string $filter): void
    {
        if ($filter === 'paid') {
            $query->where('status', 'paid');
        } elseif ($filter === 'expired') {
            $query->where('status', 'expired');
        }
    }

    /**
     * @return array{current_page: int, last_page: int, per_page: int, total: int, from: ?int, to: ?int}
     */
    protected function paginationPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
