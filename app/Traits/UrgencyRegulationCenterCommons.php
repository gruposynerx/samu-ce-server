<?php

namespace App\Traits;

trait UrgencyRegulationCenterCommons
{
    public function compareUrc(?string $urcId): void
    {
        if (!$urcId || $urcId !== auth()->user()->urc_id) {
            abort(403, 'Você não tem permissão para realizar essa ação, pois as bases diferem.');
        }
    }
}
