<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\File\Domain\Repositories\CategoryRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class SearchCategoryQuery implements QueryInterface
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(private readonly array $filters)
    {
        $this->categoryRepository = app()->make(CategoryRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->categoryRepository->searchAll($this->filters);
    }
}
