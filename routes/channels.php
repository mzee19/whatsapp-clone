<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chatroom.{id}', function ($user, $id) {
    return $user->isMemberOfChatroom($id);
});
