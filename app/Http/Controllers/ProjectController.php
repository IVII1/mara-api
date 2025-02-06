<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Units;
use Cloudinary\Cloudinary;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
   
    public function index()
    {
        return Project::all();
    }

    
    public function store(ProjectStoreRequest $request)
    {
     $validatedData = $request->validated();
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
        
        return new ProjectResource($project);
    }

    }

    public function update(ProjectUpdateRequest $request, Project $project, int $id)
    {    
        try{$project = Project::findOrFail($id);
        }
        catch(ModelNotFoundException $e){
            return response()->json(['message'=> 'Project Not Found'],404);
        }
        
        $params = $request->all();
        $project->update($params);
        return new ProjectResource($project) ;
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
    
            // Delete from Cloudinary if we have an ID
            if ($project->cloudinary_id) {
                $cloudinary->uploadApi()->destroy($project->cloudinary_id);
            }
    
            // Delete the project from database
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
