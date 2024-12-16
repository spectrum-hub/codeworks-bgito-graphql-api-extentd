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
    public function __invoke(Builder $query, $input): Builder
    {
        // Extract known filters
        $filters = Arr::only($input, ['name', 'slug', 'id']);

        $input = (array) $input;

        // Apply filters iteratively
        if (!empty($input['key']) && $input['key'] === "id") {
            $query->where('id', $input['value']);
        }

        if (!empty($input['key']) && $input['key'] === "name") {
            $query->where('name', 'like', '%' . $input['value'] . '%');
        }

        if (!empty($input['key']) && $input['key'] === "slug") {
            $query->where('slug', $input['value']);
        }

        // Return the builder instance for further processing
        return $query->limit(1); // Limits the query to one result
    }
}

