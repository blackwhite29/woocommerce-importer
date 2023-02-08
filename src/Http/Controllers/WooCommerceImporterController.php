<?php

namespace Botble\WooCommerceImporter\Http\Controllers;


use Botble\WooCommerceImporter\Jobs\SaveProductWooCommerceJob;
use Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;


use Botble\WooCommerceImporter\Http\Requests\WooCommerceImporterRequest;
use Botble\WooCommerceImporter\WooCommerceImporter;
use Botble\WordpressImporter\Http\Requests\WordpressImporterRequest;
use Botble\WordpressImporter\WordpressImporter;

class WooCommerceImporterController extends BaseController
{
    /**
     * @return BaseHttpResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        Assets::addScriptsDirectly('vendor/core/plugins/woocommerce-importer/js/woocommerce-importer.js')
            ->addStylesDirectly('vendor/core/plugins/woocommerce-importer/css/woocommerce-importer.css');

        page_title()->setTitle(trans('plugins/woocommerce-importer::woocommerce-importer.name'));

        return view('plugins/woocommerce-importer::import');
    }

    /**
     * @param WooCommerceImporterRequest $request
     * @param BaseHttpResponse $response
     * @param WooCommerceImporter $woocommerceImporter
     * @return BaseHttpResponse
     */
    public function import(
        WooCommerceImporterRequest $request,
        BaseHttpResponse           $response,
        WooCommerceImporter        $woocommerceImporter
    )
    {
        if (!$request->has('has_next')) {
            $data = $woocommerceImporter->verifyRequest($request);
            return $response
                ->setData($data)
                ->setMessage(trans('plugins/woocommerce-importer::woocommerce-importer.check_success', $data));
        }
        $dataImports = $woocommerceImporter->import($request);
        return $response
            ->setData($dataImports)
            ->setMessage(trans('plugins/woocommerce-importer::woocommerce-importer.import_success', $dataImports));
    }
}
