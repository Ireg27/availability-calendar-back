<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;

class UnavailabilityController extends BaseApiController
{
    public function index(User $user): \Illuminate\Http\JsonResponse
    {
        $unavailableDates = $user->unavailabilities()->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('j F Y');
        })->toArray();

        return $this->sendResponse($unavailableDates, 'User unavailable dates');
    }

    public function toggle(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $date = Carbon::createFromFormat('d F Y', $request->input('date'))->format('Y-m-d');

        $isUnavailable = $user->unavailabilities()->where('date', $date)->exists();

        if ($isUnavailable) {
            $user->unavailabilities()->where('date', $date)->delete();
            $message = 'User is now available on ' . $date;
        } else {
            $user->unavailabilities()->create(['date' => $date]);
            $message = 'User is now unavailable on ' . $date;
        }

        return response()->json(['message' => $message]);
    }

    public function setUnavailabilityForAll(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $availabilityStatus = $request->input('availabilityStatus');
        $currentMonthYear = $request->input('currentMonthYear');

        $date = Carbon::createFromFormat('F Y', $currentMonthYear)->startOfMonth();
        $endDate = Carbon::createFromFormat('F Y', $currentMonthYear)->endOfMonth();

        $datesToToggle = [];
        while ($date->lte($endDate)) {
            $datesToToggle[] = $date->format('Y-m-d');
            $date->addDay();
        }

        if ($availabilityStatus) {
            $user->unavailabilities()->whereIn('date', $datesToToggle)->delete();
            $message = 'All days of ' . $endDate->format('F Y') . 'set as available';
        } else {
            foreach ($datesToToggle as $dateToToggle) {
                $user->unavailabilities()->updateOrCreate(['date' => $dateToToggle]);
            }
            $message = 'All days of ' . $endDate->format('F Y') . ' set as unavailable';
        }

        return $this->sendResponse(null, $message);
    }

    public function checkAvailability(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $date = $request->input('date');

        $isAvailable = $user->isAvailableOnDate($date);
        $message = $isAvailable ? 'User is available on ' . $date : 'User is unavailable on ' . $date;

        return $this->sendResponse(['isAvailable' => $isAvailable], $message);
    }
}
