<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserScore;

class ScoreService
{
    public static function add(User $user, int $points, string $reason)
    {
        $total = $user->score + $points;

        // Save history entry
        UserScore::create([
            'user_id'     => $user->id,
            'points'      => $points,
            'reason'      => $reason,
            'total_score' => $total,
        ]);

        // Update the user
        $user->score = $total;
        $user->save();

        return $points;
    }
}
