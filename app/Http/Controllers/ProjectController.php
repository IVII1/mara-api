<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Cloudinary\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
   
    public function index(Request $request)
    {
        $allowedSortColumns = ['id', 'title', 'created_at', 'updated_at', 'position', 'production_year'];
        
        $query = Project::query();
        
        if ($request->has('sort')) {
            $sortColumn = $request->input('sort');
            $direction = $request->input('order', 'asc');
            
            if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                $direction = 'asc';
            }
            
            if (in_array($sortColumn, $allowedSortColumns)) {
                $query->orderBy($sortColumn, $direction);
            }
        }
        
        if ($this->shouldInclude($request, 'categories')) {
            $query->with('categories');
        }
        
        if ($this->shouldInclude($request, 'images')) {
            $query->with('images');
        }
        
        $projects = $query->get();
        return ProjectResource::collection($projects);
    }
    
    public function store(ProjectStoreRequest $request)
    {
        $validatedData = $request->all();
        $highestPosition = Project::max('position') ?? 1;
        $validatedData['position'] = $highestPosition + 1;
        
        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
        
        $uploadedFileResponse = $cloudinary->uploadApi()->upload($request->file('image_url')->getRealPath());
        $thumbnailUrl = $uploadedFileResponse['secure_url'];
        $cloudinaryId = $uploadedFileResponse['public_id'];
        
        $validatedData['image_url'] = $thumbnailUrl;
        $validatedData['cloudinary_id'] = $cloudinaryId;

        $hoverImageFileResponse = $cloudinary->uploadApi()->upload($request->file('hover_image_url')->getRealPath());
        $hoverImageUrl = $hoverImageFileResponse['secure_url'];
        $hoverImageCloudinaryId = $hoverImageFileResponse['public_id'];
        
        $validatedData['hover_image_url'] = $hoverImageUrl;
        $validatedData['hover_image_cloudinary_id'] = $hoverImageCloudinaryId;
        
        $project = Project::create($validatedData);
        
        if ($request->has('categories')) {
            $project->categories()->sync($request->categories);
        }
        
        return new ProjectResource($project);
    }

    
  

    public function show(int $id, Request $request){
        try{
            $project = Project::findOrFail($id);
        } catch(ModelNotFoundException){
            return response()->json(['message' => 'Project Not Found'], 404);
        }
        if ($this->shouldInclude($request, 'images')) {
            $project->load('images');
        }
    
        return new ProjectResource($project);
    }

    public function update(ProjectUpdateRequest $request, Project $project, int $id)
{    
    try {
        $project = Project::findOrFail($id);
        
        $params = $request->all();
        
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
            $project->categories()->sync($request->input('category_ids'));
        }
        
        $project->update($params);
        
        $project->load('categories');
        
        return new ProjectResource($project);
        
    } catch(ModelNotFoundException $e) {
        return response()->json(['message' => 'Project Not Found'], 404);
    }
}

    
    public function destroy( int $id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json(['message' => 'Project Not Found'], 404);
        }


        try {
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
    
           
            $project->delete();
    
            return response()->json([
                'message' => 'Project deleted successfully'
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting project: ' . $e->getMessage()
            ], 500);
        }
        
       
    }
}
