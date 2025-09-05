<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BaseScheduleReportsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

      $prevMinutes = Carbon::parse($this->prev_start_date)->diffInMinutes(Carbon::parse($this->prev_end_date));
      $workedMinutes = Carbon::parse(optional($this->userScheduleClocks->first())->clock_in)->diffInMinutes(Carbon::parse(optional($this->userScheduleClocks->first())->clock_out));
      $balanceMinutes = $workedMinutes - $prevMinutes;

      $formatMinutesToHours = function ($minutes) {
          $sign = $minutes < 0 ? '-' : '';
          $minutes = abs($minutes);
          $hours = floor($minutes / 60);
          $mins = $minutes % 60;
          return sprintf('%s%02d:%02d', $sign, $hours, $mins);
      };

      $isJustifiedFoul = function ($events) {
        return collect($events)->contains(function ($event) {
          return $event->schedule_event_type_id ===3;
        });
      };

      $isChange = function ($events) {
        return collect($events)->contains(function ($event) {
          return $event->schedule_event_type_id ===1;
        });
      };

      $isConvocate = function ($events) {
        return collect($events)->contains(function ($event) {
          return $event->schedule_event_type_id ===2;
        });
      };

      $getReverseProfessionalName = function ($events) {
        foreach ($events as $event) {
                if (isset($event->reverseProfessional) && !empty($event->reverseProfessional->name)) {
                    return $event->reverseProfessional->name;
                }
        }
        return null;
    };

    $getReverseProfessionalCbo = function ($events) {
        foreach ($events as $event) {
                if (isset($event->reverseProfessional) && !empty($event->reverseProfessional->cbo)) {
                    return $event->reverseProfessional->cbo;
                }
        }
        return null;
    };

    $getReverseProfessionalCboName = function ($events) {
        foreach ($events as $event) {
                if (isset($event->reverseProfessional->occupation) && !empty($event->reverseProfessional->occupation->name)) {
                    return $event->reverseProfessional->occupation->name;
                }
        }
        return null;
    };

    $getProfessionalName = function ($events) {
      foreach ($events as $event) {
              if (isset($event->professional) && !empty($event->professional->name)) {
                  return $event->professional->name;
              }
      }
      return null;
    };

    $getProfessionalCbo = function ($events) {
      foreach ($events as $event) {
              if (isset($event->professional) && !empty($event->professional->cbo)) {
                  return $event->professional->cbo;
              }
      }
      return null;
    };

    $getProfessionalCboName = function ($events) {
      foreach ($events as $event) {
              if (isset($event->professional->occupation) && !empty($event->professional->occupation->name)) {
                  return $event->professional->occupation->name;
              }
      }
      return null;
    };

    $getRegionalGroupName = function ($base) {
      if (isset($base->regionalGroup) && isset($base->regionalGroup->name)) {
          return $base->regionalGroup->name;
      }
      return null;
    };

    $getUrcName = function ($urc) {
      if (isset($urc) && isset($urc->name)) {
          return $urc->name;
      }
      return null;
    };

    return [
      'id' => $this->id,
      'base' => $this->base->name,
      'professional' => $this->user->name,
      'cbo' => $this->user->cbo,
      'cbo_name' => optional($this->user->occupation)->name,
      'prev_start_date' => $this->prev_start_date,
      'prev_end_date' => $this->prev_end_date,
      'starts_at' => optional($this->userScheduleClocks->first())->clock_in,
      'link' => $this->link,
      'observation' => $this->observation,
      'ends_at' => optional($this->userScheduleClocks->first())->clock_out,
      'prev_hours' => $formatMinutesToHours($prevMinutes),
      'worked_hours' => $formatMinutesToHours($workedMinutes),
      'balance_hours' => $formatMinutesToHours($balanceMinutes),
      'is_justified_foul' => $isJustifiedFoul($this->scheduleEvents),
      'is_change' => $isChange($this->scheduleEvents),
      'is_convocate' => $isConvocate($this->scheduleEvents),
      'reverse_professional_name' => $getReverseProfessionalName($this->scheduleEvents)
        ?? $getProfessionalName($this->reverseScheduleEvents),
      'reverse_professional_cbo' => $getReverseProfessionalCbo($this->scheduleEvents)
        ?? $getProfessionalCbo($this->reverseScheduleEvents),
      'reverse_professional_cbo_name' => $getReverseProfessionalCboName($this->scheduleEvents)
        ?? $getProfessionalCboName($this->reverseScheduleEvents),
      'regional_group_name' => $getRegionalGroupName($this->base),
      'urc_name' => $getUrcName($this->urc)
      ];
    }
}
