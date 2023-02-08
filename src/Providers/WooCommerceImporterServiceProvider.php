<?php

namespace Botble\WooCommerceImporter\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Event;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class WooCommerceImporterServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setNamespace('plugins/woocommerce-importer')
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets()
            ->loadRoutes(['web']);

        Event::listen(RouteMatched::class, function () {
            if (! dashboard_menu()->hasItem('cms-core-tools')) {
                dashboard_menu()->registerItem([
                    'id' => 'cms-core-tools',
                    'priority' => 96,
                    'parent_id' => null,
                    'name' => 'core/base::base.tools',
                    'icon' => 'fas fa-tools',
                    'url' => '',
                    'permissions' => [],
                ]);
            }

            dashboard_menu()
                ->registerItem([
                    'id' => 'cms-plugin-woocommerce-importer',
                    'priority' => 99,
                    'parent_id' => 'cms-core-tools',
                    'name' => 'plugins/woocommerce-importer::woocommerce-importer.name',
                    'icon' => 'fab fa-wordpress',
                    'url' => route('woocommerce-importer'),
                    'permissions' => ['settings.options'],
                ]);
        });
    }
}
