<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chatroom;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="WhatsApp-like API Server",
 *      description="API documentation for chat functionality",
 * )
 *
 * @OA\Tag(
 *     name="Chatrooms",
 *     description="Chatroom management endpoints"
 * )
 */
class ChatroomController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/chatrooms",
     *     tags={"Chatrooms"},
     *     summary="Create a new chatroom",
     *     description="Creates a new chatroom with a specified name and max members.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="General Chat"),
     *             @OA\Property(property="max_members", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Chatroom created successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data"
     *     ),
     *     security={{ "sanctum": {} }}
     * )
     */
    public function createChatroom(Request $request)
    {
        $chatroom = Chatroom::create([
            'name' => $request->name,
            'max_members' => $request->max_members,
        ]);

        $chatroom->users()->attach(auth()->id());
        return response()->json($chatroom);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{id}/enter",
     *     tags={"Chatrooms"},
     *     summary="Enter a chatroom",
     *     description="Allows a user to enter a chatroom, provided it has not reached its maximum capacity.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Chatroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User successfully entered the chatroom",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Entered chatroom")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Chatroom is full"
     *     ),
     *     security={{ "sanctum": {} }}
     * )
     */
    public function enterChatroom($id)
    {
        $chatroom = Chatroom::find($id);
        if ($chatroom->users()->count() >= $chatroom->max_members) {
            return response()->json(['error' => 'Chatroom is full'], 400);
        }
        $chatroom->users()->attach(auth()->id());
        return response()->json(['message' => 'Entered chatroom']);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{id}/leave",
     *     summary="Leave a chatroom",
     *     description="Allows a user to leave a specified chatroom",
     *     operationId="leaveChatroom",
     *     tags={"Chatroom"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chatroom",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Left chatroom",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Left chatroom")
     *         )
     *     )
     * )
     */
    public function leaveChatroom($id)
    {
        $chatroom = Chatroom::find($id);
        if ($chatroom) {
            $chatroom->users()->detach(auth()->id());
            return response()->json(['message' => 'Left chatroom']);
        }
        return response()->json(['error' => 'Chatroom not found'], 404);
    }
}
