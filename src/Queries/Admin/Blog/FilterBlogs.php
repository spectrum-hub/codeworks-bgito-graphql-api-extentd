<?php

namespace Webkul\GraphQLAPI\Queries\Admin\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FilterBlogs extends BaseFilter
{
    public function __invoke(Builder $query, array $input): Builder
    {
        $params = Arr::except($input, ['page_title', 'url_key']);
        
        // Apply filters for name and description
        if (isset($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        }
    
        // Explicitly handle 'slug' input for filtering
        if (isset($input['slug'])) {
            $query->where('slug', 'like', '%' . $input['slug'] . '%');
        }
    
        if (isset($input['description'])) {
            $query->where('description', 'like', '%' . $input['description'] . '%');
        }
    
        // Apply other filters from the input
        return $query->where($params);
    }
    
}
