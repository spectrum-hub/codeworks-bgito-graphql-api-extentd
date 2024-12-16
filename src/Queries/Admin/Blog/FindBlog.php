<?php

namespace Webkul\GraphQLAPI\Queries\Admin\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FindBlog extends BaseFilter
{
    public function __invoke(Builder $query, array $input): Builder
    {
        // Extract known filters
        $filters = Arr::only($input, ['name', 'slug', 'id']);

        // Apply 'id' filter
        if (!empty($filters['id'])) {
            return $query->where('id', $filters['id'])->first();
        }

        // Apply 'name' filter
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%')->first();
        }

        // Apply 'slug' filter
        if (!empty($filters['slug'])) {
            $query->where('slug', $filters['slug'])->first();
        }

        // Return the query with remaining filters
        return $query;
    }
}
