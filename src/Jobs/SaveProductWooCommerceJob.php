<?php

namespace Botble\WooCommerceImporter\Jobs;

use Automattic\WooCommerce\Client;
use Botble\ACL\Models\User;
use Botble\AuditLog\Events\AuditHandlerEvent;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Ecommerce\Repositories\Interfaces\ProductCategoryInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;
use Botble\Ecommerce\Repositories\Interfaces\ProductTagInterface;
use Botble\Slug\Models\Slug;
use Botble\Slug\Repositories\Interfaces\SlugInterface;
use DOMDocument;
use Exception;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Mimey\MimeTypes;
use RvMedia;
use SlugHelper;

class SaveProductWooCommerceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $consumer_sec;
    public $site_url;
    public $consumer_key;
    public $wooClient;
    public $current_page;

    /**
     * @param $consumer_sec
     * @param $site_url
     * @param $consumer_key
     * @param $current_page
     */
    public function __construct($site_url, $consumer_key, $consumer_sec, $current_page)
    {
        $this->consumer_sec = $consumer_sec;
        $this->site_url = $site_url;
        $this->consumer_key = $consumer_key;
        $this->current_page = $current_page;
    }


    public function handle()
    {
        $this->wooClient = new Client($this->site_url, $this->consumer_key, $this->consumer_sec);
        @set_time_limit(900);
        @ini_set('max_execution_time', 900);
        @ini_set('default_socket_timeout', 900);
        $this->saveProducts();

    }

    protected function saveProducts()
    {

        $products = $this->wooClient->get("products", [
            'page' => $this->current_page,
            'per_page' => 10
        ]);
        $newProducts = [];
        foreach ($products as $product) {

            $images = [];
            foreach ($product->images as $image) {
                $images[] = $this->getImage($image->src);
            }
            $tags = [];
            foreach ($product->tags as $tag) {

                $slug = Slug::where(['key' => $tag->slug, 'reference_type' => ProductTag::class])->first();
                if (!$slug) {
                    $newTag = app(ProductTagInterface::class)->createOrUpdate([
                        'order' => 0,
                        'name' => (string)$tag->name,
                        'author_id' => auth()->id(),
                        'author_type' => User::class,
                    ]);
                    Slug::create([
                        'reference_type' => ProductTag::class,
                        'reference_id' => $newTag->id,
                        'key' => Str::slug((string)$tag->slug),
                        'prefix' => SlugHelper::getPrefix(ProductTag::class),
                    ]);
                    $tags[] = $newTag->id;
                } else {
                    $tags[] = $slug->reference_id;
                }


            }
            $categories = [];
            foreach ($product->categories as $category) {
                $slug = Slug::where(['key' => $category->slug, 'reference_type' => ProductCategory::class])->first();
                if ($slug) {
                    $categories[] = $slug->reference_id;
                } else {
                    $newCategory = app(ProductCategoryInterface::class)->createOrUpdate([
                        'name' => $category->name,
                        'parent_id' => $category->parent ?? 0,
                        'description' => $category->description ?? '',
                        'order' => $category->menu_order ?? 0,
                        'image' => $category->image ?? ''
                    ]);
                    Slug::create([
                        'reference_type' => ProductCategory::class,
                        'reference_id' => $newCategory->id,
                        'key' => Str::slug((string)$category->slug),
                        'prefix' => SlugHelper::getPrefix(ProductCategory::class),
                    ]);
                    $categories[] = $newCategory->id;
                }

            }
            $newProducts[$product->slug] = [
                'name' => $product->name,
                'description' => $product->short_description,
                'content' => $product->short_description,
                'price' => $product->price,
                'categories' => array_unique($categories),
                'image' => $images[rand(0, count($images) - 1)],
                'images' => json_encode($images)
            ];
            //print_r("Import product " . $product->name . PHP_EOL);
            $newProduct = app(ProductInterface::class)->createOrUpdate($newProducts[$product->slug]);
            $slugDb = app(SlugInterface::class)->getFirstBy(['key' => Str::slug((string)$product->slug)]);
            if ($slugDb) {
                app(SlugInterface::class)->delete($slugDb);
            }
            Slug::create([
                'reference_type' => Product::class,
                'reference_id' => $newProduct->id,
                'key' => Str::slug((string)$product->slug),
                'prefix' => SlugHelper::getPrefix(Product::class),
            ]);
            $newProduct->tags()->sync(array_unique($tags));
            event(new AuditHandlerEvent(
                'woocommerce importer',
                'add product from',
                $newProduct->getKey(),
                $newProduct->name,
                'info'
            ));
        }


    }

    protected function getImage($image)
    {
        if (!empty($image)) {
            $info = pathinfo($image);

            try {
                $contents = file_get_contents($image);
            } catch (Exception $exception) {
                return $image;
            }

            if (empty($contents)) {
                return $image;
            }

            $path = '/tmp';
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755);
            }

            $path = $path . '/' . $info['basename'];
            file_put_contents($path, $contents);

            $mimeType = (new MimeTypes())->getMimeType(File::extension($image));

            $fileUpload = new UploadedFile($path, $info['basename'], $mimeType, null, true);

            $result = RvMedia::handleUpload($fileUpload, 0, 'products');

            File::delete($path);

            if ($result['error'] == false) {
                $image = $result['data']->url;
            }
        }

        return $image;
    }

    protected function changeImageInContent($content)
    {
        $htmlDom = new DOMDocument;
        libxml_use_internal_errors(true);
        @$htmlDom->loadHTML($content);
        $imageTags = $htmlDom->getElementsByTagName('img');
        foreach ($imageTags as $imageTag) {
            if (str_contains(parse_url($this->site_url, PHP_URL_HOST), $imageTag->getAttribute('src'))) {
                $newImage = $this->getImage($imageTag->getAttribute('src'));
                $content = str_replace($imageTag->getAttribute('src'), $newImage, $content);
            }

        }
        return $content;
    }
}
