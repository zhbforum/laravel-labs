<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('project.{id}', function (User $user, int $id) {
    return true;
});
