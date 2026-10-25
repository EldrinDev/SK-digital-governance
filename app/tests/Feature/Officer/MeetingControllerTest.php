<?php

namespace Tests\Feature\Officer;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create(['name' => 'Ana Chair']);
    }

    public function test_officer_records_a_session_in_own_barangay(): void
    {
        $this->actingAs($this->officer)->post(route('officer.meetings.store'), [
            'title' => '3rd regular session',
            'kind' => 'regular',
            'held_on' => '2026-10-01',
            'venue' => 'Barangay hall',
        ])->assertRedirect();

        $meeting = Meeting::sole();
        $this->assertSame($this->officer->barangay_id, $meeting->barangay_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'meeting.created', 'subject_id' => $meeting->id]);
    }

    public function test_index_shows_attendance_not_recorded_instead_of_no_quorum(): void
    {
        Meeting::factory()->for($this->officer->barangay)->create(['title' => 'Fresh session']);

        $this->actingAs($this->officer)
            ->get(route('officer.meetings.index'))
            ->assertSeeText('Attendance not recorded')
            ->assertDontSeeText('No quorum');
    }

    public function test_attendance_is_saved_and_quorum_worked_out(): void
    {
        $meeting = Meeting::factory()->for($this->officer->barangay)->create();
        $second = User::factory()->officer()->create(['barangay_id' => $this->officer->barangay_id]);
        $third = User::factory()->officer()->create(['barangay_id' => $this->officer->barangay_id]);

        $this->actingAs($this->officer)->put(route('officer.meetings.attendance.update', $meeting), [
            'attendance' => [
                $this->officer->id => 'present',
                $second->id => 'present',
                $third->id => 'excused',
            ],
        ])->assertRedirect(route('officer.meetings.show', $meeting));

        $meeting->load('attendees');
        $this->assertSame(2, $meeting->presentCount());
        $this->assertTrue($meeting->hasQuorum());

        $this->actingAs($this->officer)->get(route('officer.meetings.show', $meeting))->assertSeeText('Quorum: 2 of 3 present');
    }

    public function test_attendance_ignores_people_outside_the_barangay_council(): void
    {
        $meeting = Meeting::factory()->for($this->officer->barangay)->create();
        $outsider = User::factory()->officer()->create();
        $youth = User::factory()->create(['barangay_id' => $this->officer->barangay_id]);

        $this->actingAs($this->officer)->put(route('officer.meetings.attendance.update', $meeting), [
            'attendance' => [$this->officer->id => 'present', $outsider->id => 'present', $youth->id => 'present'],
        ]);

        $this->assertSame([$this->officer->id], $meeting->attendees()->pluck('users.id')->all());
    }

    public function test_minutes_upload_and_download(): void
    {
        Storage::fake();
        $meeting = Meeting::factory()->for($this->officer->barangay)->create(['held_on' => '2026-10-01']);

        $this->actingAs($this->officer)->post(route('officer.meetings.minutes.store', $meeting), [
            'minutes' => UploadedFile::fake()->create('minutes.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('officer.meetings.show', $meeting));

        Storage::assertExists($meeting->fresh()->minutes_path);
        $this->actingAs($this->officer)->get(route('officer.meetings.minutes.show', $meeting))->assertDownload('Minutes 2026-10-01.pdf');
    }

    public function test_session_of_another_barangay_is_not_found(): void
    {
        $other = Meeting::factory()->create();

        $this->actingAs($this->officer)->get(route('officer.meetings.show', $other))->assertNotFound();
        $this->actingAs($this->officer)->put(route('officer.meetings.attendance.update', $other), ['attendance' => [$this->officer->id => 'present']])->assertNotFound();
    }
}
