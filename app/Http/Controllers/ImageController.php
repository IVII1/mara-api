<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUpdateRequest;
use App\Http\Requests\ImageStoreRequest;
use App\Http\Resources\BulkImageResource;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Cloudinary\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function bulkUpload(Request $request, int $projectId)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048','images.required' => 'Please select at least one image.',
            'images.array' => 'Invalid format for images.',
            'images.*.required' => 'Each image is required.',
            'images.*.image' => 'File must be an image.',
            'images.*.mimes' => 'Image must be of type: jpeg, png, jpg, gif.',
            'images.*.max' => 'Image size must not exceed 2MB.',
        ],);
    
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    
        $uploadedImages = [];
    
        foreach ($request->file('images') as $image) {
            try {
                $uploadedFileResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
                
                $projectImage = Image::create([
                    'project_id' => $projectId,
                    'image_url' => $uploadedFileResponse['secure_url'],
                    'cloudinary_id' => $uploadedFileResponse['public_id'],
                ]);
    
                $uploadedImages[] = $projectImage;
            } catch (\Exception $e) {  
                return response()->json([
                    'message' => 'Failed to upload images.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    
        return BulkImageResource::collection(collect($uploadedImages));
    }

    public function destroy(int $imageId){
        try {

            $image = Image::findOrFail($imageId);
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ]
            ]);
    
            
            if ($image->cloudinary_id) {
                $cloudinary->uploadApi()->destroy($image->cloudinary_id);
            }
    
            
            $image->delete();
    
            return response()->json([
                'message' => 'Image deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting image: ' . $e->getMessage()
            ], 500);
        };
    } 
    public function update(ImageUpdateRequest $request, int $imageId)
    {
        try {
     
            $image = Image::findOrFail($imageId);
            $params = $request->all();
            $image->update($params);
            return response()->json([
                'message' => 'Image updated successfully',
                'image' => new ImageResource($image),
            ], 200);
    
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Image not found',
            ], 404);
    
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'An error occurred while updating the image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function index(){
        $images = Image::all();
        return ImageResource::collection($images);
    }
    public function show(int $id,  Request $request){
     try {
        $image = Image::findOrFail($id);
     }
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Image not found', ], 404);
        }
        if ($this->shouldInclude($request, 'project')) {
            $image->load('project');
        return new ImageResource($image);
}
}
public function upload(ImageStoreRequest $request, int $id){
    $params = $request->all();
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key'    => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
        ],
    ]);
    $uploadedFileResponse = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
    $params['image_url'] = $uploadedFileResponse['secure_url'];
    $params['cloudinary_id'] = $uploadedFileResponse['public_id'];
    $params['project_id'] = $id;
    $image = Image::create($params);
    return new ImageResource($image);
}
}