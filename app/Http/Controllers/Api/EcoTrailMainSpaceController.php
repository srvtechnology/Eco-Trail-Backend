<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcoTrailMainSpace;
use App\Models\EcoTrailNearbyPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Storage;

class EcoTrailMainSpaceController extends Controller
{
   

    // public function index()
    // {
    //     $mainSpaces = EcoTrailMainSpace::with('nearbyPlaces','CatDetails')->orderBy('id','desc')->paginate(10);
    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $mainSpaces
    //     ]);
    // }


public function index(Request $request)
{
    $query = EcoTrailMainSpace::with('nearbyPlaces', 'CatDetails')->orderBy('id', 'desc');

    // Filter by search (e.g. 'xy')
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('place_name', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%");
        });
    }

    // Filter by cat_id (e.g. 39)
    if ($request->has('cat_id') && !empty($request->cat_id)) {
        $query->where('category_id', $request->cat_id);
    }

    $mainSpaces = $query->paginate(10);

    return response()->json([
        'status' => 'success',
        'data' => $mainSpaces
    ]);
}







 public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'place_name' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|integer',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'google_maps_link' => 'nullable|url',
        'full_address' => 'required|string|max:500',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'gallery_images' => 'nullable|array',
        'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',

        'nearby_places' => 'nullable',
        'nearby_places.*.place_name' => 'required|string|max:255',
        'nearby_places.*.latitude' => 'required|numeric|between:-90,90',
        'nearby_places.*.longitude' => 'required|numeric|between:-180,180',
        'nearby_places.*.address' => 'required|string|max:500',
        'nearby_places.*.description' => 'required|string',
        'nearby_places.*.distance_from_main' => 'required|numeric|min:0',
        'nearby_places.*.distance_unit' => 'required|string|in:km,miles',
        'nearby_places.*.images' => 'nullable|array',
        'nearby_places.*.images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'nearby_places.*.trees' => 'nullable|array',
        'nearby_places.*.trees.*' => 'string',
        'nearby_places.*.wildlife' => 'nullable|string',
        'nearby_places.*.best_time_to_visit' => 'nullable|string|max:100',
        'nearby_places.*.entry_fee' => 'nullable|numeric|min:0',
        'nearby_places.*.opening_hours' => 'nullable|string|max:255',
        'nearby_places.*.facilities_available' => 'nullable|array',
        'nearby_places.*.facilities_available.*' => 'string',
        'nearby_places.*.safety_tips' => 'nullable|string',
        'nearby_places.*.estimated_time_spend' => 'nullable|string|max:50',
        'nearby_places.*.distance_from_last_point' => 'nullable|numeric|min:0',
        'nearby_places.*.additional_info' => 'nullable|array'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

     // return response()->json([
     //        'data' => json_decode($request->nearby_places),
            
     //    ], 422);

    // Handle featured image upload
    $featuredImagePath = null;
    if ($request->hasFile('featured_image')) {
        $featuredImagePath = $request->file('featured_image')->store('uploads/main_featured', 'public');
    }

    // Handle gallery images
    $galleryImagePaths = [];
    if ($request->hasFile('gallery_images')) {
        foreach ($request->file('gallery_images') as $image) {
            $galleryImagePaths[] = $image->store('uploads/main_gallery', 'public');
        }
    }

    // Create main space
    $mainSpace = EcoTrailMainSpace::create([
        'place_name' => $request->place_name,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'google_maps_link' => $request->google_maps_link,
        'full_address' => $request->full_address,
        'featured_image' => $featuredImagePath,
        'gallery_images' => json_encode($galleryImagePaths),
    ]);

    // Handle nearby places
    if ($request->has('nearby_places')) {
       foreach (json_decode($request->nearby_places, true) as $index => $nearbyPlace) {

            // $a=$nearbyPlace->toArray();
            // Handle nearby images
            $nearbyImagePaths = [];
            if ($request->hasFile("nearby_places.$index.images")) {
                foreach ($request->file("nearby_places.$index.images") as $img) {
                    $nearbyImagePaths[] = $img->store("uploads/nearby_places", 'public');
                }
            }

            // return response()->json([
            //   'data' => $nearbyPlace['place_name']
            
            // ], 422);


            $mainSpace->nearbyPlaces()->create([
                'sub_cat_id' =>  $nearbyPlace['sub_cat_id'],
                'place_name' => $nearbyPlace['place_name'],
                'latitude' => $nearbyPlace['latitude'],
                'longitude' => $nearbyPlace['longitude'],
                'address' => $nearbyPlace['address'],
                'description' => $nearbyPlace['description'],
                'distance_from_main' => $nearbyPlace['distance_from_main'],
                'distance_unit' => $nearbyPlace['distance_unit'],
                'images' => json_encode($nearbyImagePaths),
                'trees' => isset($nearbyPlace['trees']) ? json_encode($nearbyPlace['trees']) : null,
                'wildlife' => $nearbyPlace['wildlife'] ?? null,
                'best_time_to_visit' => $nearbyPlace['best_time_to_visit'] ?? null,
                'entry_fee' => $nearbyPlace['entry_fee'] ?? null,
                'opening_hours' => $nearbyPlace['opening_hours'] ?? null,
                'facilities_available' => isset($nearbyPlace['facilities_available']) ? json_encode($nearbyPlace['facilities_available']) : null,
                'safety_tips' => $nearbyPlace['safety_tips'] ?? null,
                'estimated_time_spend' => $nearbyPlace['estimated_time_spend'] ?? null,
                'distance_from_last_point' => $nearbyPlace['distance_from_last_point'] ?? null,
                'additional_info' => isset($nearbyPlace['additional_info']) ? json_encode($nearbyPlace['additional_info']) : null,
            ]);
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Main space created successfully',
        'data' => $mainSpace->load('nearbyPlaces')
    ], 201);
}








    public function show($id)
    {
        $mainSpace = EcoTrailMainSpace::with('nearbyPlaces')->find($id);

        if (!$mainSpace) {
            return response()->json([
                'status' => 'error',
                'message' => 'Main space not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mainSpace
        ]);
    }






   public function update(Request $request, $id)
{
    $mainSpace = EcoTrailMainSpace::find($id);

    if (!$mainSpace) {
        return response()->json([
            'status' => 'error',
            'message' => 'Main space not found'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'place_name' => 'sometimes|required|string|max:255',
        'description' => 'sometimes|required|string',
        'category_id' => 'sometimes|required|integer',
        'latitude' => 'sometimes|required|numeric|between:-90,90',
        'longitude' => 'sometimes|required|numeric|between:-180,180',
        'google_maps_link' => 'nullable|url',
        'full_address' => 'sometimes|required|string|max:500',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'gallery_images' => 'nullable|array',
        'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',

        'nearby_places' => 'nullable',
        'nearby_places.*.id' => 'sometimes|integer|exists:eco_trail_nearby_places,id',
        'nearby_places.*.place_name' => 'required|string|max:255',
        'nearby_places.*.latitude' => 'required|numeric|between:-90,90',
        'nearby_places.*.longitude' => 'required|numeric|between:-180,180',
        'nearby_places.*.address' => 'required|string|max:500',
        'nearby_places.*.description' => 'required|string',
        'nearby_places.*.distance_from_main' => 'required|numeric|min:0',
        'nearby_places.*.distance_unit' => 'required|string|in:km,miles',
        'nearby_places.*.images' => 'nullable|array',
        'nearby_places.*.images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'nearby_places.*.trees' => 'nullable|array',
        'nearby_places.*.trees.*' => 'string',
        'nearby_places.*.wildlife' => 'nullable|string',
        'nearby_places.*.best_time_to_visit' => 'nullable|string|max:100',
        'nearby_places.*.entry_fee' => 'nullable|numeric|min:0',
        'nearby_places.*.opening_hours' => 'nullable|string|max:255',
        'nearby_places.*.facilities_available' => 'nullable|array',
        'nearby_places.*.facilities_available.*' => 'string',
        'nearby_places.*.safety_tips' => 'nullable|string',
        'nearby_places.*.estimated_time_spend' => 'nullable|string|max:50',
        'nearby_places.*.distance_from_last_point' => 'nullable|numeric|min:0',
        'nearby_places.*.additional_info' => 'nullable|array'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

    // Handle featured image upload
    $featuredImagePath = $mainSpace->featured_image;
    if ($request->hasFile('featured_image')) {
        // Delete old featured image if exists
        if ($featuredImagePath && Storage::disk('public')->exists($featuredImagePath)) {
            Storage::disk('public')->delete($featuredImagePath);
        }
        $featuredImagePath = $request->file('featured_image')->store('uploads/main_featured', 'public');
    }

    // Handle gallery images
    $galleryImagePaths = json_decode($mainSpace->gallery_images, true) ?? [];
    if ($request->hasFile('gallery_images')) {
        // Delete old gallery images if exists
        foreach ($galleryImagePaths as $oldImage) {
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
        }
        $galleryImagePaths = [];
        foreach ($request->file('gallery_images') as $image) {
            $galleryImagePaths[] = $image->store('uploads/main_gallery', 'public');
        }
    }

    // Update main space
    $mainSpace->update([
        'place_name' => $request->has('place_name') ? $request->place_name : $mainSpace->place_name,
        'description' => $request->has('description') ? $request->description : $mainSpace->description,
        'category_id' => $request->has('category_id') ? $request->category_id : $mainSpace->category_id,
        'latitude' => $request->has('latitude') ? $request->latitude : $mainSpace->latitude,
        'longitude' => $request->has('longitude') ? $request->longitude : $mainSpace->longitude,
        'google_maps_link' => $request->has('google_maps_link') ? $request->google_maps_link : $mainSpace->google_maps_link,
        'full_address' => $request->has('full_address') ? $request->full_address : $mainSpace->full_address,
        'featured_image' => $featuredImagePath,
        'gallery_images' => json_encode($galleryImagePaths),
    ]);

    // Handle nearby places
    if ($request->has('nearby_places')) {
        $existingNearbyPlaceIds = $mainSpace->nearbyPlaces->pluck('id')->toArray();
        $updatedNearbyPlaceIds = [];

        foreach (json_decode($request->nearby_places, true) as $index => $nearbyPlaceData) {
            $nearbyImagePaths = [];
            
            // Handle nearby images upload
            if ($request->hasFile("nearby_places.$index.images")) {
                // Delete old images if this is an update
                if (isset($nearbyPlaceData['id'])) {
                    $existingPlace = EcoTrailNearbyPlace::find($nearbyPlaceData['id']);
                    if ($existingPlace && $existingPlace->images) {
                        $oldImages = json_decode($existingPlace->images, true);
                        foreach ($oldImages as $oldImage) {
                            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                                Storage::disk('public')->delete($oldImage);
                            }
                        }
                    }
                }
                
                // Store new images
                foreach ($request->file("nearby_places.$index.images") as $img) {
                    $nearbyImagePaths[] = $img->store("uploads/nearby_places", 'public');
                }
            } elseif (isset($nearbyPlaceData['images']) && is_array($nearbyPlaceData['images'])) {
                // Keep existing images if not updated
                $nearbyImagePaths = $nearbyPlaceData['images'];
            }

            if (isset($nearbyPlaceData['id'])) {
                // Update existing nearby place
                $nearbyPlace = EcoTrailNearbyPlace::where('id', $nearbyPlaceData['id'])
                    ->where('main_space_id', $mainSpace->id)
                    ->first();

                if ($nearbyPlace) {
                    $nearbyPlace->update([
                        'sub_cat_id' => $nearbyPlaceData['sub_cat_id'] ?? $nearbyPlace->sub_cat_id,
                        'place_name' => $nearbyPlaceData['place_name'],
                        'latitude' => $nearbyPlaceData['latitude'],
                        'longitude' => $nearbyPlaceData['longitude'],
                        'address' => $nearbyPlaceData['address'],
                        'description' => $nearbyPlaceData['description'],
                        'distance_from_main' => $nearbyPlaceData['distance_from_main'],
                        'distance_unit' => $nearbyPlaceData['distance_unit'],
                        'images' => !empty($nearbyImagePaths) ? json_encode($nearbyImagePaths) : $nearbyPlace->images,
                        'trees' => isset($nearbyPlaceData['trees']) ? json_encode($nearbyPlaceData['trees']) : $nearbyPlace->trees,
                        'wildlife' => $nearbyPlaceData['wildlife'] ?? $nearbyPlace->wildlife,
                        'best_time_to_visit' => $nearbyPlaceData['best_time_to_visit'] ?? $nearbyPlace->best_time_to_visit,
                        'entry_fee' => $nearbyPlaceData['entry_fee'] ?? $nearbyPlace->entry_fee,
                        'opening_hours' => $nearbyPlaceData['opening_hours'] ?? $nearbyPlace->opening_hours,
                        'facilities_available' => isset($nearbyPlaceData['facilities_available']) ? json_encode($nearbyPlaceData['facilities_available']) : $nearbyPlace->facilities_available,
                        'safety_tips' => $nearbyPlaceData['safety_tips'] ?? $nearbyPlace->safety_tips,
                        'estimated_time_spend' => $nearbyPlaceData['estimated_time_spend'] ?? $nearbyPlace->estimated_time_spend,
                        'distance_from_last_point' => $nearbyPlaceData['distance_from_last_point'] ?? $nearbyPlace->distance_from_last_point,
                        'additional_info' => isset($nearbyPlaceData['additional_info']) ? json_encode($nearbyPlaceData['additional_info']) : $nearbyPlace->additional_info
                    ]);
                    $updatedNearbyPlaceIds[] = $nearbyPlaceData['id'];
                }
            } else {
                // Create new nearby place
                $newNearbyPlace = $mainSpace->nearbyPlaces()->create([
                    'sub_cat_id' => $nearbyPlaceData['sub_cat_id'] ?? 1,
                    'place_name' => $nearbyPlaceData['place_name'],
                    'latitude' => $nearbyPlaceData['latitude'],
                    'longitude' => $nearbyPlaceData['longitude'],
                    'address' => $nearbyPlaceData['address'],
                    'description' => $nearbyPlaceData['description'],
                    'distance_from_main' => $nearbyPlaceData['distance_from_main'],
                    'distance_unit' => $nearbyPlaceData['distance_unit'],
                    'images' => !empty($nearbyImagePaths) ? json_encode($nearbyImagePaths) : null,
                    'trees' => isset($nearbyPlaceData['trees']) ? json_encode($nearbyPlaceData['trees']) : null,
                    'wildlife' => $nearbyPlaceData['wildlife'] ?? null,
                    'best_time_to_visit' => $nearbyPlaceData['best_time_to_visit'] ?? null,
                    'entry_fee' => $nearbyPlaceData['entry_fee'] ?? null,
                    'opening_hours' => $nearbyPlaceData['opening_hours'] ?? null,
                    'facilities_available' => isset($nearbyPlaceData['facilities_available']) ? json_encode($nearbyPlaceData['facilities_available']) : null,
                    'safety_tips' => $nearbyPlaceData['safety_tips'] ?? null,
                    'estimated_time_spend' => $nearbyPlaceData['estimated_time_spend'] ?? null,
                    'distance_from_last_point' => $nearbyPlaceData['distance_from_last_point'] ?? null,
                    'additional_info' => isset($nearbyPlaceData['additional_info']) ? json_encode($nearbyPlaceData['additional_info']) : null,
                ]);
                $updatedNearbyPlaceIds[] = $newNearbyPlace->id;
            }
        }

        // Delete nearby places that weren't included in the update
        $toDelete = array_diff($existingNearbyPlaceIds, $updatedNearbyPlaceIds);
        if (!empty($toDelete)) {
            // First delete associated images
            $placesToDelete = EcoTrailNearbyPlace::whereIn('id', $toDelete)->get();
            foreach ($placesToDelete as $place) {
                if ($place->images) {
                    $images = json_decode($place->images, true);
                    foreach ($images as $image) {
                        if ($image && Storage::disk('public')->exists($image)) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
            }
            // Then delete the records
            EcoTrailNearbyPlace::whereIn('id', $toDelete)->delete();
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Main space updated successfully',
        'data' => $mainSpace->load('nearbyPlaces')
    ]);
}





    public function destroy($id)
    {
        $mainSpace = EcoTrailMainSpace::find($id);

        if (!$mainSpace) {
            return response()->json([
                'status' => 'error',
                'message' => 'Main space not found'
            ], 404);
        }

        // Delete all related nearby places first
        $mainSpace->nearbyPlaces()->delete();
        $mainSpace->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Main space and all related nearby places deleted successfully'
        ]);
    }






    public function map_markers($id)
    {
        $mainSpace = EcoTrailMainSpace::find($id);

        if (!$mainSpace) {
            return response()->json([
                'status' => 'error',
                'message' => 'Main space not found'
            ], 404);
        }

        $markers=[];
        $markers[0]['lat']=$mainSpace->latitude;
        $markers[0]['lng']=$mainSpace->longitude;
        $markers[0]['place']=$mainSpace->full_address;


        $findNearby=EcoTrailNearbyPlace::where('main_space_id',$id)->get();
        foreach($findNearby as $key => $val){
            if($key==0){

            }
            $markers[$key+1]['lat']=$val->latitude;
            $markers[$key+1]['lng']=$val->longitude;
            $markers[$key+1]['place']=$val->address;
        }

        return response()->json([
            'status' => 'success',
            'data' => $markers,
            // 'mainSpace'=>$mainSpace
        ]);
    }



}