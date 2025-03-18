<?php

namespace App\Http\Controllers\Api;

use App\Enums\QueueStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Site;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SiteController extends Controller
{
    // API GET ROUTE
    public function getScrapingProduct($token)
    {
        $site = Site::query()
            ->where('token', $token)
            ->firstOrFail();

        $filePath = $this->getJsonFilePath($site->id);
        if (Storage::disk('local')->exists($filePath)) {
            $data = Storage::disk('local')->get($filePath);
            $data = json_decode($data, true);
        } else {
            $data = [];
        }

        return response()->json($data);
    }

    // API POST ROUTE
    public function setQueueStatusCompleted(int $siteId, Request $request): JsonResponse
    {
        try {
            $site = Site::query()
                ->where('queue_status', QueueStatusEnum::ON_QUEUE->value)
                ->findOrFail($siteId);

            $site->last_scraping_date = now()->format('Y-m-d H:i:s');
            $site->queue_status = $request->get("queue_status");
            $site->save();

            $this->generateJsonFile($site);

            $success = true;
            $message = "Queue status updated successfully";
        } catch (Exception $e) {
            $success = false;
            $message = 'An error occurred while trying to update the queue status.: ' . $e->getMessage();

            Log::channel('site_profile')->error($message);
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }

    private function generateJsonFile(Site $site): void
    {
        $formattedProducts = $this->getProducts($site->id);

        $response = [
            'created_date' => $site->last_scraping_date->format('Y-m-d H:i:s'),
            'products' => $formattedProducts
        ];

        $filePath = $this->getJsonFilePath($site->id);
        Storage::disk('local')->put($filePath, json_encode($response));
    }

    private function getJsonFilePath($siteId)
    {
        return "site_{$siteId}_products.json";
    }

    public function getProducts(int $siteId)
    {
        $products = new Product();
        $products->setSiteProfileCollection($siteId);
        $productsResult = $products->get();

        return $productsResult
            ->map(function ($product) {
                if (
                    isset($product['scraping_data']['price'])
                ) {
                    return [
                        'name' => $product['scraping_data']['name'] ?? null,
                        'price' => (float)$product['scraping_data']['price'],
                        'sku' => $product['scraping_data']['sku'] ?? null,
                        'mpn' => $product['scraping_data']['mpn'] ?? null,
                    ];
                }

                return null;
            })
            ->filter(fn($item) => !is_null($item));
    }
}
