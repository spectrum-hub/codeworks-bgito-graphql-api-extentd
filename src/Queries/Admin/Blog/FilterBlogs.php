<?php

namespace Webkul\GraphQLAPI\Queries\Admin\Blog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Webkul\GraphQLAPI\Queries\BaseFilter;

class FilterBlogs extends BaseFilter
{
    public function __invoke(Builder $query, array $input): Builder
    {
        // Exclude excluded fields


        /**
  
         * 
         * input: Энэ нь GraphQL-ийн ашиглагчийн өгсөн бүх параметрүүдийг агуулсан массив юм.
         * Тухайлбал, name, description, id, гэх мэт утгууд.
         * Arr::except(): Энэ функц нь Laravel-ийн Arr туслах классын нэг хэсэг бөгөөд өгөгдсөн массивын 
         * доторх тодорхой түлхүүрүүдийг хасаж шинэ массив буцаадаг.
         * ['page_title', 'url_key']: Энэ нь хасах түлхүүрүүдийн жагсаалт юм. Тиймээс page_title 
         * болон url_key гэсэн түлхүүрүүдийг $input массивынхаас хасна.
         * 
         */
        
        $params = Arr::except($input, ['page_title', 'url_key']);
        
        // Apply filters for name and description
        if (isset($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        }

        if (isset($input['description'])) {
            $query->where('description', 'like', '%' . $input['description'] . '%');
        }

        // Apply other filters from the input
        return $query->where($params);
    }
}
