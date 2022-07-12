<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 *
 */
class ProductController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request): JsonResponse
    {
        try {
            $products = Product::query();

            $perPage = $request->get('perPage', 20);
            $search = $request->get('search', '');

            if ($search && $search !== '') {
                $products = $products->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%');
                });
            }

            $categoryId = $request->get('category');

            if ($categoryId) {
                $products = $products->where('category_id', $categoryId);
            }

            $status = $request->get('status');

            if ($status) {
                $products = $products->where('status', $status);
            }

            $products = $products->paginate($perPage);

            $results = [
                'data' => $products->items(),
                'currentPage' => $products->currentPage(),
                'perPage' => $products->perPage(),
                'total' => $products->total(),
                'hasMorePages' => $products->hasMorePages()
            ];

            return $this->sendResponse($results);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(Request $request)
    {
        if ($request->has('image')) {
            $file = $request->file('image');

            $filename = 'P'.time().'.'.$file->getClientOriginalExtension();

            $path = 'products/';

            Storage::putFileAs($path, $file, $filename);

            return $path.$filename;
        }
    }

    public function getAllProductsForCategory($categoryId)
    {
        $products = Product::where('category_id', $categoryId)
            ->orWhereHas('category', function ($query) use ($categoryId) {
               $query->where('parent_id', $categoryId)
                   ->orWhereHas('parent', function ($query) use ($categoryId) {
                       $query->where('parent_id', $categoryId);
                   });
            })->get();

//        $categories = [$categoryId];
//
//        $category = Category::find($categoryId);
//
//        if (count($category->childs) > 0) {
//            foreach ($category->childs as $subCategory) {
//                $categories[] = $subCategory->id;
//
//                if (count($subCategory->childs) > 0) {
//                    foreach ($subCategory->childs as $subSubCategory) {
//                        $categories[] = $subSubCategory->id;
//                    }
//                }
//            }
//        }
//
//        $products = Product::whereIn('category_id', $categories)->get();

        return $products->toArray();
    }
}
