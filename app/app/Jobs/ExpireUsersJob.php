<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();
        $inactivityDays = (int) config('burracoup.user_inactivity_days', 120);
        $inactiveThreshold = $now->copy()->subDays($inactivityDays);

        // 1) Scadenza annuale: expires_at < now()
        User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    $user->deactivate('expired');
                    $user->save();
                }
            });

        // 2) Inattività: baselineLastSeen < threshold
        User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where(function (Builder $q) use ($inactiveThreshold) {
                // last_seen_at OR setup_completed_at OR created_at < threshold
                $q->where(function (Builder $qq) use ($inactiveThreshold) {
                    $qq->whereNotNull('last_seen_at')->where('last_seen_at', '<', $inactiveThreshold);
                })->orWhere(function (Builder $qq) use ($inactiveThreshold) {
                    $qq->whereNull('last_seen_at')
                       ->whereNotNull('setup_completed_at')
                       ->where('setup_completed_at', '<', $inactiveThreshold);
                })->orWhere(function (Builder $qq) use ($inactiveThreshold) {
                    $qq->whereNull('last_seen_at')
                       ->whereNull('setup_completed_at')
                       ->where('created_at', '<', $inactiveThreshold);
                });
            })
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    $user->deactivate('inactive');
                    $user->save();
                }
            });
    }
}