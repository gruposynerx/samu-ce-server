<?php

use Illuminate\Support\Str;

if (!function_exists('formatPercentageChange')) {
    function formatPercentageChange(int|float $initialValue, int|float $currentValue): string
    {
        if ($initialValue == 0) {
            return sprintf('%+d', $currentValue);
        }

        $percentageChange = (($currentValue - $initialValue) / $initialValue) * 100;

        return sprintf('%+d', number_format($percentageChange, 2)) . '%';
    }
}

if (!function_exists('processSequences')) {
    function processSequences(string $search): array
    {
        [$ticketSequence, $attendanceSequence] = null;

        if (preg_match('/^[0-9]+$/', $search)) {
            $ticketSequence = $search;
        }

        if (Str::contains($search, '/')) {
            $sequences = explode('/', $search);

            if (preg_match('/^[0-9]+\/$/', $search)) {
                [$ticketSequence] = $sequences;
            }

            if (preg_match('/^[0-9]+\/[0-9]+$/', $search)) {
                [$ticketSequence, $attendanceSequence] = $sequences;
            }
        }

        return [
            'ticketSequence' => $ticketSequence,
            'attendanceSequence' => $attendanceSequence,
        ];
    }
}

if (!function_exists('getMorphAlias')) {
    function getMorphAlias(string $className): string
    {
        return app($className)->getMorphClass();
    }
}

if (!function_exists('removeCharacters')) {
    function removeCharacters(string $sentence): string
    {
        return preg_replace('/[^0-9]/', '', $sentence);
    }
}
