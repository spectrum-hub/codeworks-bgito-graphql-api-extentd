<?php

namespace Webkul\GraphQLAPI\Mutations\Shop\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Product\Repositories\ProductRepository;

use Webkul\GraphQLAPI\Models\CidsCartShifts;
use Webkul\GraphQLAPI\Models\CidsCustomer;

class CidsCustomer extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected CartItemRepository $cartItemRepository,
        protected ProductRepository $productRepository
    ) {
        Auth::setDefaultDriver('api');

        $this->middleware('auth:api');
    }

 
    

    public function createOrUpdate(){



        /*
        
        user_id
        cid_value
        status

        
            $customerId = request()->header('sid-customer-s');

            $this->writeLog(json_encode(['args' => $args]));
            $this->writeLog(json_encode(['rootValue' => $rootValue]));
            $this->writeLog(json_encode(['context' => $context]));
            $this->writeLog(json_encode(['requestheaders' => request()->header('sid-customer-s')]));

        */
        
        $customerId = request()->header('sid-customer-s');
        
        $article = CidsCustomer::create(['cid_value' => $customerId]);

        // $flight = CidsCustomer::where('number', 'FR 900')->first();
 
        // $article->id; // "8f8e8478-9035-4d23-b9a7-62f4d2612ce5"


    }
    
}
