<?php

namespace Webkul\GraphQLAPI\Queries\Admin\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FindBlog extends BaseFilter
{
    public function __invoke(Builder $query, array $input): Builder
    {
        $params = Arr::except($input, ['page_title', 'url_key']);
        
        // Apply filters for name and description
        if (!empty($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%')->first();
        }
    
        // Explicitly handle 'slug' input for filtering
        if (!empty($input['slug'])) {
            $query->where('slug', $args['slug'])->first();
        }

        if (!empty($input['id'])) {
            $query->findOrFail($input['id']);
        }
 
    
        // Apply other filters from the input
        return $query->where($params);
    }
    
}
