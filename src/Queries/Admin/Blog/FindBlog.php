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
     * @param array|object $input
     * @return Builder
     */
    public function __invoke(Builder $query, $input): Builder
    {
        // Ensure input is an array
        $input = (array) $input;

        // Apply the filter based on the key provided
        if (!empty($input['key']) && !empty($input['value'])) {
            switch ($input['key']) {
                case 'id':
                    $query->where('id', $input['value']);
                    break;

                case 'name':
                    $query->where('name', 'like', '%' . $input['value'] . '%');
                    break;

                case 'slug':
                    $query->where('slug', $input['value']);
                    break;

                default:
                    // No action needed for unsupported keys
                    break;
            }
        }

        // Limit the query to ensure only one result
        return $query->limit(1);
    }
}
