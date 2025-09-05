<?php

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('primary-attendance.refresh.{urc_id}', function (User $user, $urc_id) {
    return $user->urc_id === $urc_id;
});

Broadcast::channel('secondary-attendance.refresh.{urc_id}', function (User $user, $urc_id) {
    return $user->urc_id === $urc_id;
});

Broadcast::channel('radio-operation.refresh.{urc_id}', function (User $user, $urc_id) {
    return $user->urc_id === $urc_id;
});

Broadcast::channel('device.invalidate.{pinId}', function (User $user, $pinId) {
    $pin = \App\Models\Pin::find($pinId);

    return $pin && $pin->id;
});

Broadcast::channel('bpa-reports.{key}', function (User $user, $key) {
    return true;
});

Broadcast::channel('cancel-attendance.refresh.{attendance_id}', function (User $user, $attendance_id) {
    return Attendance::find($attendance_id) !== null;
});

Broadcast::channel('change-places.{urc_id}', function (User $user, $urc_id) {
    return $user->urc_id === $urc_id;
});

Broadcast::channel('user.notifications.{id}', function (User $user, $id) {
    Log::info('Channel auth debug', [
        'user_id' => $user->id,
        'user_id_type' => gettype($user->id),
        'channel_id' => $id,
        'channel_id_type' => gettype($id),
        'comparison' => $user->id === $id,
        'string_comparison' => (string) $user->id === (string) $id,
    ]);
    
    return (string) $user->id === (string) $id;
});
