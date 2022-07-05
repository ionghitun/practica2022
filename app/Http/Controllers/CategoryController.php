<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 *
 */
class CategoryController extends ApiController
{
    /**
     * @param Request $request
     */
    public function getAll(Request $request)
    {

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:50',
                'parent_id' => 'nullable|exists:categories,id'
            ]);

            if ($validator->fails()) {
                return $this->sendError('Bad request!', $validator->messages()->toArray());
            }

            $name = $request->get('name');
            $parentId = $request->get('parent_id');

            if ($parentId) {
                $parent = Category::find($parentId);

                if ($parent->parent?->parent) {
                    return $this->sendError('You can\'t add a 3rd level subcategory!');
                }
            }

            $category = new Category();
            $category->name = $name;
            $category->parent_id = $parentId;
            $category->save();

            return $this->sendResponse($category->toArray());
        } catch (\Exception $exception) {
            Log::error($exception);

            return $this->sendError('Something went wrong, please contact administrator!');
        }
    }

    /**
     * @param $id
     */
    public function get($id)
    {

    }

    /**
     * @param $id
     * @param Request $request
     */
    public function update($id, Request $request)
    {

    }

    /**
     * @param $id
     */
    public function delete($id)
    {

    }
}
