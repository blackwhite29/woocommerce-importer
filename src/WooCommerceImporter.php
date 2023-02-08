<?php

namespace Botble\WooCommerceImporter;


use Automattic\WooCommerce\Client;
use Botble\ACL\Models\User;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Repositories\Interfaces\GlobalOptionInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductAttributeSetInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductCollectionInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductLabelInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductTagInterface;
use Botble\Language\Models\LanguageMeta;
use Botble\Slug\Models\Slug;
use Botble\WooCommerceImporter\Http\Requests\WooCommerceImporterRequest;
use Botble\WooCommerceImporter\Jobs\SaveProductWooCommerceJob;
use DOMDocument;
use Exception;
use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Language;
use Mimey\MimeTypes;
use RvMedia;
use SlugHelper;

class WooCommerceImporter
{


    protected $products = [];

    protected $categories = [];
    protected $tags = [];
    protected $variations = [];
    protected $attributes = [];

    protected Client $wooClient;

    public function __construct()
    {
        @set_time_limit(900);
        @ini_set('max_execution_time', 900);
        @ini_set('default_socket_timeout', 900);
    }


    public function verifyRequest(WooCommerceImporterRequest $request): array
    {
        $site_url = $request->get("site_url");
        $consumer_key = $request->get("consumer_key");
        $consumer_sec = $request->get("consumer_sec");
        $this->wooClient = new Client($site_url, $consumer_key, $consumer_sec);

        $this->saveProducts();
        return [
            'products' => count($this->products),
            'current_page' => 1,
            'has_next' => true
        ];
    }

    /**
     * @return array
     */
    public function import(WooCommerceImporterRequest $request)
    {

        $has_next = false;
        $site_url = $request->get("site_url");
        $consumer_key = $request->get("consumer_key");
        $consumer_sec = $request->get("consumer_sec");
        $current_page=$request->get("current_page");
        dispatch(new SaveProductWooCommerceJob($site_url, $consumer_key, $consumer_sec, $current_page));
        $total_page = ceil(((int)$request->get('products') / 10)) ;
        if ($current_page < $total_page) {
            $has_next = true;
            $current_page++;
        }
        return [
            'products' => $request->get('products'),
            'current_page' => $current_page,
            'has_next' => $has_next,
            'total_page'=>$total_page
        ];
    }

    protected function saveProducts()
    {
        $current_page = 1;
        $products = $this->wooClient->get("products", [
            'page' => $current_page,
            'per_page' => 10
        ]);
        while (count($products) > 0) {
            foreach ($products as $product) {

                $this->products[] = $product;
            }
            $current_page++;
            $products = $this->wooClient->get("products", [
                'page' => $current_page,
                'per_page' => 10
            ]);
        }
        return $this->products;
    }

    protected function saveCategories()
    {
        $current_page = 1;
        $categories = $this->wooClient->get("products/categories", [
            'page' => $current_page,
            'per_page' => 10
        ]);
        while (count($categories) > 0) {

            foreach ($categories as $category) {
                $this->categories[] = $category;
            }
            $current_page++;
            $categories = $this->wooClient->get("products/categories", [
                'page' => $current_page,
                'per_page' => 10
            ]);
        }

        return $this->categories;
    }

    protected function saveTags()
    {

        $current_page = 1;
        $tags = $this->wooClient->get("products/tags", [
            'page' => $current_page,
            'per_page' => 10
        ]);
        while (count($tags) > 0) {
            foreach ($tags as $tag) {
                $this->tags[] = $tag;
            }
            $current_page++;
            $tags = $this->wooClient->get("products/tags", [
                'page' => $current_page,
                'per_page' => 10
            ]);
        }
        return $this->tags;
    }

    protected function saveVariations()
    {

        $current_page = 1;
        $variations = $this->wooClient->get("products/variations", [
            'page' => $current_page,
            'per_page' => 10
        ]);
        while (count($variations) > 0) {
            foreach ($variations as $variation) {
                $this->variations[] = $variation;

            }
            $current_page++;
            $variations = $this->wooClient->get("products/variations", [
                'page' => $current_page,
                'per_page' => 10
            ]);
        }

    }

    protected function saveAttributes()
    {

        $current_page = 1;
        $attributes = $this->wooClient->get("products/attributes", [
            'page' => $current_page,
            'per_page' => 10
        ]);
        while (count($attributes) > 0) {
            foreach ($attributes as $attribute) {
                $this->attributes[] = $attribute;

            }
            $current_page++;
            $attributes = $this->wooClient->get("products/attributes", [
                'page' => $current_page,
                'per_page' => 10
            ]);
        }

    }

}
