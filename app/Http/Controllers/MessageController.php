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
    public function index(Request $request)
    {
        $query = Message::query();

        $limit = $request->get('limit', 20);    
        $offset = $request->get('offset', 0);  

        if ($request->get('name')) {
            $query->where('name', $request->get('name'));
        }

        if ($request->get('email')) {
            $query->where('email', $request->get('email'));
        }

        if ($request->get('content')) {
            $query->whereLike('content', '%' . $request->get('content') . '%');
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');

        if ($sortBy === 'read') {
            $query->orderBy('read', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $query->offset($offset)->limit($limit);

        $messages = $query->get();
        return MessageResource::collection($messages);
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
    public function readAll(){
        $messages = Message::where('read', false)->get();
        foreach($messages as $message){
            $message->read = true;
            $message->save();
        }
        return response()->json(['message'=> 'All messages read'],200);
    }
    public function unreadCount(){
        $count = Message::where('read', false)->count();
        return response()->json(['count'=> $count],200);
    }
}
