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

class CartMutation extends Controller
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

    /**
     * Returns a current cart detail.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function cart(mixed $rootValue, array $args, GraphQLContext $context)
    {
        try {
            return Cart::getCart();
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    public function cartInfos(mixed $rootValue, array $args, GraphQLContext $context)
    {
        try {

            /**
             * 
             * {"args":{"id":"75"}}
             * {"rootValue":null}
             * {
             *  "context":{
             *      "user":null,"request":{
             *           "attributes":{},"request":{},"query":{},"server":{},"files":{},"cookies":{},"headers":{}}
             *  }
             * }
             * 
             */

            
            $cartId = $args['id'];
            
            $cart = $this->cartRepository->findOrFail($cartId);

            if(!empty($cart['id'])){

                Cart::setCart($cart);
             
                return Cart::getCart();
            }
            return Cart::getCart();
            
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Returns a current cart's detail.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function cartItems(mixed $rootValue, array $args, GraphQLContext $context)
    {
        try {
            $cart = Cart::getCart();

            return $cart?->items ?? [];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    

    public function storeNew(mixed $rootValue, array $args, GraphQLContext $context)
    {
        bagisto_graphql()->validate($args, [
            'quantity'   => 'required|min:1',
            'product_id' => 'required|integer|exists:products,id',
        ]);



        try {

            if(!empty($args['cart_id'])){

                $cart = $this->cartRepository->findOrFail($args['cart_id']);                

                Cart::setCart($cart);
             
            }
            
            $product = $this->productRepository->findOrFail($args['product_id']);

            $data = bagisto_graphql()->manageInputForCart($product, $args);

            $cart = Cart::addProduct($product, $data);

            return [
                'success' => ! empty($cart),
                'message' => ! empty($cart)
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.add-to-cart')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.add-to-cart'),
                'cart'    => $cart,
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function store(mixed $rootValue, array $args, GraphQLContext $context)
    {
        bagisto_graphql()->validate($args, [
            'quantity'   => 'required|min:1',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        try {
            $product = $this->productRepository->findOrFail($args['product_id']);

            $data = bagisto_graphql()->manageInputForCart($product, $args);

            $cart = Cart::addProduct($product, $data);

            return [
                'success' => ! empty($cart),
                'message' => ! empty($cart)
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.add-to-cart')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.add-to-cart'),
                'cart'    => $cart,
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function update(mixed $rootValue, array $args, GraphQLContext $context)
    {
        bagisto_graphql()->validate($args, [
            'qty'                => 'required|array',
            'qty.*.cart_item_id' => 'required|integer|exists:cart_items,id',
            'qty.*.quantity'     => 'required|integer|min:1',
        ]);

        try {
            $qty = [];

            foreach ($args['qty'] as $item) {
                if (! $this->cartItemRepository->find($item['cart_item_id'])) {
                    throw new CustomException(trans('bagisto_graphql::app.shop.checkout.cart.item.fail.item-not-found'));
                }

                $qty[$item['cart_item_id']] = $item['quantity'] ?: 1;
            }

            $args['qty'] = $qty;

            $cartUpdated = Cart::updateItems($args);

            return [
                'success' => $cartUpdated,
                'message' => $cartUpdated
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.update-to-cart')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.update-to-cart'),
                'cart'    => Cart::getCart(),
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function delete(mixed $rootValue, array $args, GraphQLContext $context)
    {
        bagisto_graphql()->validate($args, [
            'id' => 'required|integer|exists:cart_items,id',
        ]);

        try {
            $isRemoved = Cart::removeItem($args['id']);

            Cart::collectTotals();

            return [
                'success' => $isRemoved,
                'message' => $isRemoved
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.delete-cart-item')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.delete-cart-item'),
                'cart'    => Cart::getCart(),
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Remove all resource from storage.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function deleteAll(mixed $rootValue, array $args, GraphQLContext $context)
    {
        $cart = Cart::getCart();

        if (! $cart) {
            throw new CustomException(trans('bagisto_graphql::app.shop.checkout.cart.item.fail.not-found'));
        }

        try {
            Event::dispatch('checkout.cart.delete.all.before', $cart);

            $isDeleted = $this->cartRepository->delete($cart->id);

            Cart::resetCart();

            Event::dispatch('checkout.cart.delete.all.after', $cart);

            return [
                'success' => $isDeleted,
                'message' => $isDeleted
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.all-remove')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.all-remove'),
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Move the specified resource to Wishlist.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function moveToWishlist(mixed $rootValue, array $args, GraphQLContext $context)
    {
        bagisto_graphql()->validate($args, [
            'id' => 'required|integer|exists:cart_items,id',
        ]);

        try {
            $isMoved = Cart::moveToWishlist($args['id']);

            return [
                'success' => $isMoved,
                'message' => $isMoved
                    ? trans('bagisto_graphql::app.shop.checkout.cart.item.success.move-to-wishlist')
                    : trans('bagisto_graphql::app.shop.checkout.cart.item.fail.move-to-wishlist'),
                'cart'    => Cart::getCart(),
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }


    public function writeLog($message, $level = 'INFO', $file = 'app.log') {
        // Define the log directory and file
        $logDir = '/var/www/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true); // Create the directory if it doesn't exist
        }
        $logFile = $logDir . '/' . $file;
    
        // Create the log message with a timestamp
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
        // Write the log message to the file
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    
}
