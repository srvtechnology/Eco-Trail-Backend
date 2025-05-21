<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpaceSubCategory;
use App\Models\SpaceCategory;
use Storage;

class SpaceSubCategoryController extends Controller
{
    public function index()
    {
        return response()->json(SpaceSubCategory::with('spaceCat')->get());
    }



    public function all_cat(){
        // dd(1);
        return response()->json(SpaceCategory::orderBy('id', 'desc')->get(), 200);
    }






public function store(Request $request)
{
    $data = $request->validate([
        'cat_id' => 'nullable|integer',
        'long_description' => 'nullable|string',
        'short_description' => 'nullable|string',
        'name' => 'nullable|string',
        'images.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
        'additional_info' => 'nullable',
    ]);

    $imagePaths = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads/subcategories', 'public');
            $imagePaths[] = '/storage/' . $path;
        }
    }

    $subcategory = SpaceSubCategory::create([
        'cat_id' => $data['cat_id'] ?? null,
        'name' => $data['name'] ?? null,
        'long_description' => $data['long_description'] ?? null,
        'short_description' => $data['short_description'] ?? null,
        'images' => json_encode($imagePaths),
        'additional_info' => $data['additional_info'] ?? '[]',
    ]);

    return response()->json($subcategory, 201);
}

    

    public function show($id)
    {
        $subcategory = SpaceSubCategory::findOrFail($id);
        return response()->json($subcategory);
    }



public function update(Request $request, $id)
{
    $subcategory = SpaceSubCategory::findOrFail($id);

    $data = $request->validate([
        'cat_id' => 'nullable|integer',
        'long_description' => 'nullable|string',
        'short_description' => 'nullable|string',
         'name' => 'nullable|string',
        'images.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp',
        'existing_images' => 'nullable|array',
        'existing_images.*' => 'string',
        'additional_info' => 'nullable',
    ]);

    $finalImagePaths = $data['existing_images'] ?? [];

    // Delete removed images from storage
    $oldImages = json_decode($subcategory->images ?? '[]');
    foreach ($oldImages as $oldImagePath) {
        if (!in_array($oldImagePath, $finalImagePaths)) {
            $path = str_replace('/storage/', '', $oldImagePath);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    // Add newly uploaded images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('uploads/subcategories', 'public');
            $finalImagePaths[] = '/storage/' . $path;
        }
    }

    // $subcategory->update([
    //     'cat_id' => $data['cat_id'] ?? $subcategory->cat_id,
    //      'name' => $data['name'] ?? null,
    //     'long_description' => $data['long_description'] ?? $subcategory->long_description,
    //     'short_description' => $data['short_description'] ?? $subcategory->short_description,
    //     'images' => json_encode($finalImagePaths),
    //     'additional_info' => $data['additional_info'] ?? '[]',
    // ]);

    $subcategory->update([
            'cat_id'            => $data['cat_id'] ?? $subcategory->cat_id,
            'name'              => $data['name'] ?? $subcategory->name,
            'long_description'  => $data['long_description'] ?? $subcategory->long_description,
            'short_description' => $data['short_description'] ?? $subcategory->short_description,
            'images'            => isset($finalImagePaths) ? json_encode($finalImagePaths) : $subcategory->images,
            'additional_info'   => $data['additional_info'] ?? '[]', 

             /*isset($data['additional_info']) ? json_encode($data['additional_info']) : $subcategory->additional_info,*/
        ]);


    return response()->json($subcategory);
}


    public function destroy($id)
    {
        $subcategory = SpaceSubCategory::findOrFail($id);
        $subcategory->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}