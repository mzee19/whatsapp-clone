<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Message;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Messages",
 *     description="Messaging endpoints"
 * )
 */
class MessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/chatrooms/{id}/messages",
     *     tags={"Messages"},
     *     summary="Send a message in a chatroom",
     *     description="Allows a user to send a message or attachment in a specified chatroom.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Chatroom ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, everyone!"),
     *             @OA\Property(property="attachment", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid message data"
     *     ),
     *     security={{ "sanctum": {} }}
     * )
     */
    public function sendMessage(Request $request, $chatroomId)
    {
        $message = Message::create([
            'chatroom_id' => $chatroomId,
            'user_id' => auth()->id(),
            'content' => $request->message,
            'type' => $request->hasFile('attachment') ? 'attachment' : 'text',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store("root/{$request->attachment_type}");
            Attachment::create(['message_id' => $message->id, 'path' => $path]);
        }

        broadcast(new MessageSent($message))->toOthers();
        return response()->json($message);
    }

    /**
     * @OA\Get(
     *     path="/api/chatrooms/{id}/messages",
     *     summary="List messages in a chatroom",
     *     description="Lists all messages in a specified chatroom",
     *     operationId="listMessages",
     *     tags={"Message"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chatroom",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="content", type="string", example="Hello!"),
     *                 @OA\Property(property="type", type="string", example="text"),
     *                 @OA\Property(property="created_at", type="string", example="2024-11-02 10:00:00")
     *             )
     *         )
     *     )
     * )
     */
    public function listMessages($chatroomId)
    {
        $messages = Message::where('chatroom_id', $chatroomId)->with('attachments')->get();
        return response()->json($messages);
    }

}
