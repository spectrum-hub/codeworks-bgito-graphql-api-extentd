<?php

namespace Webkul\GraphQLAPI\Mutations\Admin\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\GraphQLAPI\Validators\CustomException;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeMutation extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeCustomizationRepository $themeCustomizationRepository,
        protected ChannelRepository $channelRepository
    ) {}

    /**
     * Store a newly created resource in storage.
     *
     * @return array
     */
    public function store(mixed $rootValue, array $args, GraphQLContext $context)
    {
        $channels = core()->getAllChannels();

        bagisto_graphql()->validate($args, [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'in:product_carousel,category_carousel,static_content,image_carousel,footer_links,services_content',
            'channel_id' => 'required|in:'.implode(',', ($channels->pluck('id')->toArray())),
            'theme_code' => 'required|in:'.implode(',', ($channels->pluck('theme')->toArray())),
        ]);

        Event::dispatch('theme_customization.create.before');

        $theme = $this->themeCustomizationRepository->create([
            'name'       => $args['name'],
            'sort_order' => $args['sort_order'],
            'type'       => $args['type'],
            'channel_id' => $args['channel_id'],
            'theme_code' => $args['theme_code'],
        ]);

        Event::dispatch('theme_customization.create.after', $theme);

        return [
            'success' => true,
            'message' => trans('bagisto_graphql::app.admin.settings.themes.create-success'),
            'theme'   => $theme,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array
     */
    public function update(mixed $rootValue, array $args, GraphQLContext $context)
    {
        $channels = core()->getAllChannels();

        bagisto_graphql()->validate($args, [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'in:product_carousel,category_carousel,static_content,image_carousel,footer_links',
            'channel_id' => 'required|in:'.implode(',', ($channels->pluck('id')->toArray())),
            'theme_code' => 'required|in:'.implode(',', ($channels->pluck('theme')->toArray())),
        ]);

        $args['locale'] = $locale = core()->getRequestedLocaleCode();

        $themeCustomization = $this->themeCustomizationRepository->find($args['id']);

        if (! $themeCustomization) {
            throw new CustomException(trans('bagisto_graphql::app.admin.settings.themes.not-found'));
        }

        $args['type'] = $themeCustomization->type;

        if ($args['type'] == 'product_carousel') {
            $args[$locale]['options']['title'] = $args['options']['title'];

            $args[$locale]['options']['filters'] = [];

            foreach ($args['options']['filtersInput'] as $filtersInput) {
                $args[$locale]['options']['filters'][$filtersInput['key']] = $filtersInput['value'];
            }

            unset($args['options']);
        }

        if ($args['type'] == 'static_content') {
            $args[$locale]['options']['html'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $args[$locale]['options']['html']);
            $args[$locale]['options']['css'] = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $args[$locale]['options']['css']);
        }

        if ($args['type'] == 'image_carousel') {
            unset($args['options']);
        }

        Event::dispatch('theme_customization.update.before', $themeCustomization->id);

        $theme = $this->themeCustomizationRepository->update($args, $themeCustomization->id);

        if ($args['type'] == 'image_carousel') {
            $this->themeCustomizationRepository->uploadImage(
                request()->all('options'),
                $theme,
                request()->input('deleted_sliders', [])
            );
        }

        Event::dispatch('theme_customization.update.after', $theme);

        return [
            'success' => true,
            'message' => trans('bagisto_graphql::app.admin.settings.themes.update-success'),
            'theme'   => $theme,
        ];
    }

    /**
     * Delete the specified resource from storage.
     *
     * @return array
     */
    public function delete(mixed $rootValue, array $args, GraphQLContext $context)
    {
        $theme = $this->themeCustomizationRepository->find($args['id']);

        if (! $theme) {
            throw new CustomException(trans('bagisto_graphql::app.admin.settings.themes.not-found'));
        }

        Event::dispatch('theme_customization.delete.before', $args['id']);

        $theme->delete();

        Storage::deleteDirectory("theme/{$theme->id}");

        Event::dispatch('theme_customization.delete.after', $args['id']);

        return [
            'success' => true,
            'message' => trans('bagisto_graphql::app.admin.settings.themes.delete-success'),
        ];
    }
}
