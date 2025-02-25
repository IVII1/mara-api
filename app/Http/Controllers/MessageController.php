<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageStoreRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Notifications\MessageReceived;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    public function store(MessageStoreRequest $request ){
        $params = $request->all();
        $params['read'] = false;
        $message = Message::create($params);
        Notification::route('mail', env('RECEIVER_EMAIL'))->notify(new MessageReceived($message));
        return new MessageResource($message);
    }
    public function index(Request $request){
        $query = Message::query();

        if ($request->get('name')) {
            $query->where('name', $request->get('name'));
        }

        if ($request->get('email')) {
            $query->where('email', $request->get('email'));
        }

        if ($request->get('content')) {
            $query->whereLike('content', '%' . $request->get('content') . '%');
        }
        

        $messages = $query->get();
        return  MessageResource::collection($messages);

        
    }
    public function show(int $id){
        try{
            $message = Message::findOrFail($id);
        } catch(ModelNotFoundException){
            return response()->json(['message' => 'Message Not Found'], 404);
        }
        return new MessageResource($message);
    }
    public function destroy(int $id, Message $message){
        try{
            $message = Message::findOrFail($id);
        } catch(ModelNotFoundException){
            response()->json(['message'=> 'Message not found'],404);
        }
        $message->delete();
            return response()->json(['message'=> 'Message deleted'],200);
    }
    public function read(int $id){
        $message = Message::findOrFail($id);
        $message->read = true;
        $message->save();
        return new MessageResource($message);
    }   
}
