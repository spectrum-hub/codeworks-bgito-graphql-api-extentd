<?php

namespace Webkul\GraphQLAPI\Mutations\Admin\Marketing\Promotion;

use Illuminate\Support\Facades\Event;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\GraphQLAPI\Validators\CustomException;

class CartRuleMutation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRuleRepository $cartRuleRepository,
        protected CartRuleCouponRepository $cartRuleCouponRepository,
        protected ChannelRepository $channelRepository,
        protected CustomerGroupRepository $customerGroupRepository
    ) {}

    /**
     * Store a newly created resource in storage.
     *
     * @return array
     *
     * @throws CustomException
     */
    public function store(mixed $rootValue, array $args, GraphQLContext $context)
    {
        $args['use_auto_generation'] = $args['use_auto_generation'] ?? 0;

        bagisto_graphql()->validate($args, [
            'name'                => 'required',
            'channels'            => 'required|array|min:1|in:'.implode(',', $this->channelRepository->pluck('id')->toArray()),
            'customer_groups'     => 'required|array|min:1|in:'.implode(',', $this->customerGroupRepository->pluck('id')->toArray()),
            'coupon_type'         => 'required',
            'use_auto_generation' => 'required_if:coupon_type,==,1',
            'coupon_code'         => 'required_if:use_auto_generation,==,0',
            'starts_from'         => 'nullable|date',
            'ends_till'           => 'nullable|date|after_or_equal:starts_from',
            'action_type'         => 'required',
            'discount_amount'     => 'required|numeric',
        ]);

        try {
            Event::dispatch('promotions.cart_rule.create.before');

            $cartRule = $this->cartRuleRepository->create($args);

            Event::dispatch('promotions.cart_rule.create.after', $cartRule);

            return [
                'success'   => true,
                'message'   => trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.create-success'),
                'cart_rule' => $cartRule,
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
        $args['use_auto_generation'] = $args['use_auto_generation'] ?? 0;

        bagisto_graphql()->validate($args, [
            'name'                => 'required',
            'channels'            => 'required|array|min:1|in:'.implode(',', $this->channelRepository->pluck('id')->toArray()),
            'customer_groups'     => 'required|array|min:1|in:'.implode(',', $this->customerGroupRepository->pluck('id')->toArray()),
            'coupon_type'         => 'required',
            'use_auto_generation' => 'required_if:coupon_type,==,1',
            'coupon_code'         => 'required_if:use_auto_generation,==,0',
            'starts_from'         => 'nullable|date',
            'ends_till'           => 'nullable|date|after_or_equal:starts_from',
            'action_type'         => 'required',
            'discount_amount'     => 'required|numeric',
        ]);

        $cartRule = $this->cartRuleRepository->find($args['id']);

        if (! $cartRule) {
            throw new CustomException(trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.not-found'));
        }

        try {
            Event::dispatch('promotions.cart_rule.update.before', $cartRule);

            if (isset($args['autogenerated_coupons'])) {
                $this->generateCoupons($args['autogenerated_coupons'], $cartRule->id);

                unset($args['autogenerated_coupons']);
            }

            $cartRule = $this->cartRuleRepository->update($args, $cartRule->id);

            Event::dispatch('promotions.cart_rule.update.after', $cartRule);

            return [
                'success'   => true,
                'message'   => trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.update-success'),
                'cart_rule' => $cartRule,
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
        $cartRule = $this->cartRuleRepository->find($args['id']);

        if (! $cartRule) {
            throw new CustomException(trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.not-found'));
        }

        try {
            Event::dispatch('promotions.cart_rule.delete.before', $args['id']);

            $cartRule->delete();

            Event::dispatch('promotions.cart_rule.delete.after', $args['id']);

            return [
                'success' => true,
                'message' => trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.delete-success'),
            ];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }

    /**
     * Generate coupon code for cart rule
     *
     * @return object
     *
     * @throws CustomException
     */
    public function generateCoupons(array $params, $id)
    {
        bagisto_graphql()->validate($params, [
            'coupon_qty'  => 'required|integer|min:1',
            'code_length' => 'required|integer|min:10',
            'code_format' => 'required',
        ]);

        try {
            $cartRule = $this->cartRuleRepository->find($id);

            if (! $cartRule) {
                throw new CustomException(trans('bagisto_graphql::app.admin.marketing.promotions.cart-rules.not-found'));
            }

            $coupon = $this->cartRuleCouponRepository->generateCoupons($params, $id);

            return $coupon;
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }
    }
}