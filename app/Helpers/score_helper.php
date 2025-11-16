<?php

use App\Models\User;
use App\Models\UserScore;

function add_score(User $user, int $points, string $reason = "")
{
    $newTotal = $user->score + $points;

    // Save in user_scores history
    UserScore::create([
        'user_id'     => $user->id,
        'points'      => $points,
        'reason'      => $reason,
        'total_score' => $newTotal,
    ]);

    // Update main score
    $user->score = $newTotal;
    $user->save();

    return $newTotal;
}
