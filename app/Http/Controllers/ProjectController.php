<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Cloudinary\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Http\Resources\ImageResource;


class ProjectController extends Controller
{
   
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'title', 'created_at', 'updated_at', 'position', 'production_year'];
        
        $query = Project::query();
        
       
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);
        
        if ($request->has('sort')) {
            $sortColumn = $request->input('sort', 'position');
            $direction = $request->input('order', 'asc');
            
            if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                $direction = 'asc';
            }
            
            if (in_array($sortColumn, $allowedSortColumns)) {
                $query->orderBy($sortColumn, $direction);
            }
        }
        
        
        if ($request->has('category_id')) {
            $categoryIds = $request->input('category_id'); 
        
            
            if (!is_array($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }
        
            $query->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds); 
            });
        }
        
        if ($this->shouldInclude($request, 'categories')) {
            $query->with('categories');
        }
        
        if ($this->shouldInclude($request, 'images')) {
            $query->with('images');
        }
        
      
        $query->offset($offset)->limit($limit)->orderBy('position');
        
        return ProjectResource::collection($query->get());
    }
    
    public function store(ProjectStoreRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
        
            $highestPosition = Project::max('position') ?? 0;
            $validatedData['position'] = $highestPosition + 1;
            
          
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ]
            ]);

           
            if ($request->hasFile('image_url')) {
                $uploadedFileResponse = $cloudinary->uploadApi()->upload($request->file('image_url')->getRealPath());
                $validatedData['image_url'] = $uploadedFileResponse['secure_url'];
                $validatedData['cloudinary_id'] = $uploadedFileResponse['public_id'];
            } else {
                throw new \Exception('Main image is required');
            }

        
            if ($request->hasFile('hover_image_url')) {
                $uploadedFileResponse = $cloudinary->uploadApi()->upload($request->file('hover_image_url')->getRealPath());
                $validatedData['hover_image_url'] = $uploadedFileResponse['secure_url'];
                $validatedData['hover_image_cloudinary_id'] = $uploadedFileResponse['public_id'];
            }
            
           
            $project = Project::create($validatedData);
            
          
            if ($request->has('category_ids')) {
                $project->categories()->sync($request->category_ids);
            }
            
           
            $project->load(['categories', 'images']);
            
            return new ProjectResource($project);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    
  

    public function show(int $id, Request $request)
    {
        try {
            $project = Project::findOrFail($id);
            
            if ($this->shouldInclude($request, 'categories')) {
                $project->load('categories');
            }
            
            if ($this->shouldInclude($request, 'images')) {
                $project->load('images');
            }
            
            return new ProjectResource($project);
        } catch(ModelNotFoundException $e) {
            return response()->json(['message' => 'Project Not Found'], 404);
        }
    }

    public function update(ProjectUpdateRequest $request, int $id)
{
    try {
        $project = Project::findOrFail($id);
        
        $params = $request->validated();
        
       
        if ($request->has('position') && $request->position != $project->position) {
            $oldPosition = $project->position;
            $newPosition = $request->position;
            
            if ($newPosition > Project::max('position')) {
                $newPosition = Project::max('position');
            }
            
            $params['position'] = $newPosition;
            
            $projectToSwap = Project::where('position', $newPosition)->first();
            
            if ($projectToSwap) {
                $projectToSwap->update(['position' => $oldPosition]);
            }
        }
        
       
        if ($request->has('category_ids')) {
            $project->categories()->sync($request->category_ids);
        }
        
        
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ]
        ]);
        
        // Handle image deletions only if new images are provided
        if ($request->has('images')) {
            if ($project->cloudinary_id) {
                $cloudinary->uploadApi()->destroy($project->cloudinary_id);
            }
            if ($project->hover_image_cloudinary_id) {
                $cloudinary->uploadApi()->destroy($project->hover_image_cloudinary_id);
            }
            if ($project->images) {
                foreach ($project->images as $image) {
                    $cloudinary->uploadApi()->destroy($image->cloudinary_id);
                    $image->delete();
                }
            }
        }
        
        $project->update($params);
        
       
        $project->load(['categories', 'images']);
        
        return new ProjectResource($project);
        
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Project Not Found'], 404);
    }
}

    
    public function destroy(int $id)
    {
        try {
            $project = Project::findOrFail($id);
            
        
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ]
            ]);

          
            if ($project->cloudinary_id) {
                $cloudinary->uploadApi()->destroy($project->cloudinary_id);
            }
            
            
            if ($project->hover_image_cloudinary_id) {
                $cloudinary->uploadApi()->destroy($project->hover_image_cloudinary_id);
            }
            
           
            if ($project->images) {
                foreach ($project->images as $image) {
                    if ($image->cloudinary_id) {
                        $cloudinary->uploadApi()->destroy($image->cloudinary_id);
                    }
                    if ($image->hover_image_cloudinary_id) {
                        $cloudinary->uploadApi()->destroy($image->hover_image_cloudinary_id);
                    }
                    $image->delete();
                }
            }

           
            $deletedProjectPosition = $project->position;
            
            
            $project->delete();
            
            $projectsToUpdate = Project::where('position', '>', $deletedProjectPosition)->get();
            foreach ($projectsToUpdate as $projectToUpdate) {
                $projectToUpdate->position = $projectToUpdate->position - 1;
                $projectToUpdate->save();
            }

            return response()->json([
                'message' => 'Project deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkUpload(Request $request, int $id)
    {
        try {
            $project = Project::findOrFail($id);
            
            if (!$request->hasFile('images')) {
                return response()->json(['message' => 'No images uploaded'], 400);
            }

            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ]
            ]);

            $uploadedImages = [];
            
            foreach ($request->file('images') as $image) {
                $uploadedFileResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
                
                $imageData = [
                    'image_url' => $uploadedFileResponse['secure_url'],
                    'cloudinary_id' => $uploadedFileResponse['public_id'],
                    'project_id' => $project->id
                ];
                
                $uploadedImages[] = Image::create($imageData);
            }

            return response()->json([
                'message' => 'Images uploaded successfully',
                'images' => ImageResource::collection($uploadedImages)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Project not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error uploading images: ' . $e->getMessage()], 500);
        }
    }
}
