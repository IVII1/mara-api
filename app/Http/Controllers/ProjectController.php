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
        $allowedSortColumns = ['id', 'title', 'created_at', 'updated_at', 'position']; 
        
        $query = Project::query();
        
        if ($request->has('sort')) {
            $sortColumn = $request->input('sort');
            $direction = $request->input('order', 'asc');
            
            
            if (in_array($sortColumn, $allowedSortColumns)) {
                $query->orderBy($sortColumn, $direction);
            }
            
          
            if (!in_array(strtolower($direction), ['asc', 'desc'])) {
                $direction = 'asc';
            }
        }
        
        return $query->get();
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
        
        $uploadedFileResponse = $cloudinary->uploadApi()->upload($request->file('thumbnail')->getRealPath());
        $thumbnailUrl = $uploadedFileResponse['secure_url'];
        $cloudinaryId = $uploadedFileResponse['public_id'];
        
        $validatedData['thumbnail'] = $thumbnailUrl;
        $validatedData['cloudinary_id'] = $cloudinaryId;
        
        $project = Project::create($validatedData);
        
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
            
            // If position is being changed
            if ($request->has('position') && $request->position != $project->position) {
                $oldPosition = $project->position;
                $newPosition = $request->position;
                
                // Find the project that currently has the requested position
                $projectToSwap = Project::where('position', $newPosition)->first();
                
                if ($projectToSwap) {
                    // Update the other project's position
                    $projectToSwap->update(['position' => $oldPosition]);
                }
            }
            
            $params = $request->all();
            $project->update($params);
            
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
