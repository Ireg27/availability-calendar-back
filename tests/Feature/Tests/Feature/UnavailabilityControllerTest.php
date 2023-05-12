<?php

namespace Tests\Feature;

use App\Models\Unavailability;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UnavailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $user = User::factory()->create();

        Unavailability::create([
            'user_id' => $user->id,
            'date' => '2023-05-12'
        ]);

        Unavailability::create([
            'user_id' => $user->id,
            'date' => '2023-05-13'
        ]);

        $res = $this->get('/api/users/' . $user->id . '/unavailabilities');

        $res->assertStatus(201)
            ->assertJson([
                'data' => [
                    '12 May 2023',
                    '13 May 2023',
                ]
            ]);
    }

    public function testToggle()
    {
        $user = User::factory()->create();
        $date = '12 May 2023';
        $formattedDate = Carbon::createFromFormat('d F Y', $date)->format('Y-m-d');

        $this->assertDatabaseMissing('unavailabilities', [
            'user_id' => $user->id,
            'date' => $formattedDate,
        ]);

        $res = $this->put('/api/users/' . $user->id . '/unavailability', [
            'date' => $date,
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'message' => 'User is now unavailable on ' . $formattedDate,
            ]);

        $this->assertDatabaseHas('unavailabilities', [
            'user_id' => $user->id,
            'date' => $formattedDate,
        ]);

        $res = $this->put('/api/users/' . $user->id . '/unavailability', [
            'date' => $date,
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'message' => 'User is now available on ' . $formattedDate,
            ]);

        $this->assertDatabaseMissing('unavailabilities', [
            'user_id' => $user->id,
            'date' => $formattedDate,
        ]);
    }

    public function testSetUnavailabilityForAll()
    {
        $user = User::factory()->create();
        $month = 5; // May
        $year = 2023;

        $this->assertDatabaseMissing('unavailabilities', [
            'user_id' => $user->id,
            'date' => Carbon::createFromDate($year, $month, 1)->format('Y-m-d'),
        ]);

        $res = $this->post('/api/users/' . $user->id . '/unavailabilities', [
            'availabilityStatus' => false,
            'currentMonthYear' => 'May 2023',
        ]);

        $res->assertStatus(201)
            ->assertJson([
                'message' => 'All days of May 2023 set as unavailable',
            ]);

        $daysInGivenDate = CarbonPeriod::create("{$year}-{$month}-01", "{$year}-{$month}-31")->count();

        $this->assertEquals($daysInGivenDate, $user->unavailabilities()->count());

        $this->post('/api/users/' . $user->id . '/unavailabilities', [
            'availabilityStatus' => true,
            'currentMonthYear' => 'May 2023',
        ]);

        $this->assertEmpty($user->unavailabilities);
    }

    public function testCheckAvailability()
    {
        $user = User::factory()->create();
        $date = '2023-05-12';

        $user->unavailabilities()->create(['date' => $date]);

        $res = $this->post('/api/users/' . $user->id . '/unavailabilities/check-availability', [
            'date' => $date,
        ]);

        $res->assertStatus(201)
            ->assertJson([
                'data' => [
                    'isAvailable' => false,
                ],
                'message' => 'User is unavailable on ' . $date,
            ]);
    }
}
