<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\FeedbackReceived;
use App\Notifications\RemarkReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_remark_lands_in_officers_bell_and_opening_it_marks_read(): void
    {
        $barangay = Barangay::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $barangay->id]);
        $inactive = User::factory()->officer()->create(['barangay_id' => $barangay->id, 'deactivated_at' => now()]);

        $this->actingAs(User::factory()->federation()->create())
            ->post(route('federation.barangays.remarks.store', $barangay), ['body' => 'Please upload evidence.']);

        $this->assertSame(1, $officer->unreadNotifications()->count());
        $this->assertSame(0, $inactive->notifications()->count());
        $notification = $officer->notifications()->sole();
        $this->assertSame(RemarkReceived::class, $notification->type);

        $this->actingAs($officer)
            ->get(route('officer.dashboard'))
            ->assertSee('Notifications, 1 unread');

        $this->actingAs($officer)
            ->get(route('notifications.show', $notification->id))
            ->assertRedirect(route('officer.dashboard'));

        $this->assertSame(0, $officer->unreadNotifications()->count());
    }

    public function test_new_feedback_notifies_officers_in_platform(): void
    {
        $youth = User::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);

        $this->actingAs($youth)->post(route('feedback.store'), ['message' => 'More workshops please.']);

        $this->assertSame(FeedbackReceived::class, $officer->notifications()->sole()->type);
    }

    public function test_users_cannot_open_someone_elses_notification(): void
    {
        $owner = User::factory()->officer()->create();
        $owner->notify(new FeedbackReceived(Feedback::factory()->create()));

        $this->actingAs(User::factory()->officer()->create())
            ->get(route('notifications.show', $owner->notifications()->sole()->id))
            ->assertNotFound();
    }

    public function test_mark_all_as_read(): void
    {
        $officer = User::factory()->officer()->create();
        $officer->notify(new FeedbackReceived(Feedback::factory()->create()));
        $officer->notify(new FeedbackReceived(Feedback::factory()->create()));

        $this->actingAs($officer)->post(route('notifications.read'));

        $this->assertSame(0, $officer->unreadNotifications()->count());
    }
}
