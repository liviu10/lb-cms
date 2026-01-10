<?php

namespace LiviuVoica\LbCms\DTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LiviuVoica\LbCms\DTO\FormFieldDTO;

/**
 * @template T
 */
final class PaginatedDTO
{
    /**
     * @param  T[]  $data
     * @param  FormFieldDTO[]  $form
     */
    private function __construct(
        public int $current_page,
        public array $data,
        public ?int $from,
        public int $last_page,
        public ?string $next_page_url,
        public ?string $path,
        public int $per_page,
        public ?string $prev_page_url,
        public ?int $to,
        public int $total,
        public array $form,
    ) {}

    /**
     * @param  LengthAwarePaginator  $paginator
     * @param  FormFieldDTO[]        $form
     * @param  callable(mixed):T     $mapper Funcția care transformă modelul în DTO
     * @return self<T>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, array $form, callable $mapper): self
    {
        $data = array_map($mapper, $paginator->items());

        return new self(
            current_page: $paginator->currentPage(),
            data: $data,
            from: $paginator->firstItem(),
            last_page: $paginator->lastPage(),
            next_page_url: $paginator->nextPageUrl(),
            path: $paginator->path(),
            per_page: $paginator->perPage(),
            prev_page_url: $paginator->previousPageUrl(),
            to: $paginator->lastItem(),
            total: $paginator->total(),
            form: $form,
        );
    }
}
