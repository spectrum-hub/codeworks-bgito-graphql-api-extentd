<?php

namespace Webkul\GraphQLAPI\Queries\Admin\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FindBlog extends BaseFilter
{
    /**
     * Apply filters to the blog query.
     *
     * @param Builder $query
     * @param array $input
     * @return Builder
     */
    public function __invoke(Builder $query, array $input): Builder
    {
        // Extract known filters
        $filters = Arr::only($input, ['name', 'slug', 'id']);

        // Apply filters iteratively
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', $filters['slug']);
        }

        // Return the builder instance for further processing
        return $query;
    }
}
