<?php

namespace Database\Seeders;

use App\AttendanceStatus;
use App\CenterOfParticipation;
use App\EventKind;
use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Cbydp;
use App\Models\CbydpPriority;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Feedback;
use App\Models\Meeting;
use App\Models\Ppa;
use App\Models\Resolution;
use App\Models\User;
use App\OfficerPosition;
use App\PlanStatus;
use App\PpaStatus;
use App\ResolutionOutcome;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Realistic sample data for demos and the capstone defense: three pilot barangays
 * at different stages, so every screen has something meaningful to show.
 *
 * Run on an empty database:  php artisan migrate:fresh --seeder=DemoDataSeeder
 * Every demo account uses the password "password123".
 */
class DemoDataSeeder extends Seeder
{
    /**
     * The three pilot barangays used for the demo.
     *
     * @var list<string>
     */
    public const array PILOT = ['Santa Rita', 'Barretto', 'Kalaklan'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo data must not be created in production.');
        }

        if (Cbydp::query()->exists()) {
            throw new RuntimeException('The database already has plans. Run this on an empty database: php artisan migrate:fresh --seeder=DemoDataSeeder');
        }

        $this->call(DemoAccountSeeder::class);

        Barangay::query()->update(['is_participating' => false]);
        Barangay::whereIn('name', self::PILOT)->update(['is_participating' => true]);

        $this->seedSantaRita();
        $this->seedBarretto();
        $this->seedKalaklan();
    }

    /**
     * On track: approved and published plans, PPAs in every state, sessions, events and feedback.
     */
    private function seedSantaRita(): void
    {
        $barangay = Barangay::firstWhere('name', 'Santa Rita');
        $chair = User::firstWhere('email', 'officer@sk.test');
        $officers = collect([$chair])->merge($this->officers($barangay, [
            'Bea Santos' => 'secretary.santarita@sk.test',
            'Carlo Mendoza' => 'treasurer.santarita@sk.test',
            'Dana Villanueva' => 'kagawad1.santarita@sk.test',
            'Enzo Ramos' => 'kagawad2.santarita@sk.test',
        ]));
        $youth = collect([User::firstWhere('email', 'youth@sk.test')])->merge($this->youth($barangay, 8));

        $year = (int) now()->format('Y');
        $cbydp = $this->cbydp($barangay, $year, approvedBy: $chair, resolution: "Res. No. 2, s. {$year}");
        $priorities = $this->priorities($cbydp, [
            [CenterOfParticipation::Education, 'Reduce out-of-school youth'],
            [CenterOfParticipation::Health, 'Mental health awareness for teens'],
            [CenterOfParticipation::Environment, 'Cleaner coastlines along Subic Bay'],
            [CenterOfParticipation::ActiveCitizenship, 'More youth taking part in barangay decisions'],
            [CenterOfParticipation::EconomicEmpowerment, 'Job readiness for out-of-school youth'],
        ]);

        $abyip = $this->abyip($cbydp, $year, approvedBy: $chair, resolution: "Res. No. 7, s. {$year}", published: true);

        $als = $this->ppa($abyip, $priorities[0], 'Alternative Learning System review classes', '60 learners enrolled', 85000, -200, 35, PpaStatus::Ongoing);
        $this->milestones($als, [['Learners registered', -190, true], ['Mid-term assessment', -90, true], ['Mock A&E test', 10, false], ['A&E exam day', 30, false]]);

        $caravan = $this->ppa($abyip, $priorities[1], 'Teen mental health caravan', '300 youth reached', 42000, -170, -140, PpaStatus::Completed);
        $this->milestones($caravan, [['Speakers confirmed', -165, true], ['Caravan day', -140, true]]);
        $this->evidence($caravan, 'Caravan day photo', 'ulo-ng-apo.jpg', public: true);
        $this->evidence($caravan, 'Attendance sheets (names of minors)', 'skyline-freeport.jpg', public: false);

        $cleanup = $this->ppa($abyip, $priorities[2], 'Coastal clean-up drive', '2 km of shoreline cleared', 18500, -60, -10, PpaStatus::Ongoing);
        $this->milestones($cleanup, [['Volunteers signed up', -50, true], ['Clean-up day', -12, false]]);

        $forum = $this->ppa($abyip, $priorities[3], 'Youth assembly and budget forum', '150 youth attend', 25000, -120, -100, PpaStatus::Completed);
        $this->milestones($forum, [['Venue booked', -115, true]]);

        foreach ([[$als, 52000], [$caravan, 41250.75], [$cleanup, 9800], [$forum, 26400]] as [$spentPpa, $amount]) {
            $spentPpa->forceFill(['amount_spent' => $amount, 'spending_recorded_at' => now()->subDays(3)])->save();
        }

        $this->ppa($abyip, $priorities[4], 'Job readiness bootcamp', '40 youth trained', 60000, 20, 50, PpaStatus::Planned);
        $this->ppa($abyip, $priorities[3], 'Youth leadership summit', '120 youth leaders trained', 65000, 40, 70, PpaStatus::Planned);

        $session = $this->meeting($barangay, '5th regular session', -95, $officers, absent: 1);
        $this->resolution($barangay, $session, "Res. No. 7, s. {$year}", "Approving the {$year} ABYIP", -95);
        $this->resolution($barangay, $session, "Res. No. 8, s. {$year}", 'Partnering with the city ALS office', -95);
        $special = $this->meeting($barangay, 'Special session on the clean-up drive', -20, $officers, absent: 3);
        $this->resolution($barangay, $special, "Res. No. 9, s. {$year}", 'Moving the clean-up to a later date', -20, ResolutionOutcome::Deferred);

        $pastEvent = $this->event($barangay, EventKind::Event, 'Teen mental health caravan', -140, 'Santa Rita covered court', 80, $caravan);
        foreach ($youth->take(6) as $index => $member) {
            $pastEvent->registrations()->create(['user_id' => $member->id])->forceFill(['attended_at' => $pastEvent->starts_at])->save();

            // The main demo youth (index 0) is left with an evaluation to give, to show that prompt.
            if ($index >= 1 && $index <= 3) {
                $this->feedback($member, $pastEvent, [5, 4, 5][$index - 1], ['Very helpful talks.', 'Good, but it started late.', 'Please do this again next year.'][$index - 1]);
            }
        }

        $consultation = $this->event($barangay, EventKind::Consultation, 'Youth consultation on next year’s ABYIP', 9, 'Barangay hall', 50);
        $sports = $this->event($barangay, EventKind::Event, 'Inter-purok basketball league opening', 16, 'Santa Rita covered court', 12);
        foreach ($youth->skip(1)->take(4) as $member) {
            $consultation->registrations()->create(['user_id' => $member->id]);
        }
        foreach ($youth->take(10) as $member) {
            $sports->registrations()->create(['user_id' => $member->id]);
        }

        $survey = $barangay->surveys()->create([
            'title' => 'What should the SK focus on next year?',
            'description' => 'Your answers shape the next ABYIP. They are anonymous.',
        ]);
        $survey->forceFill(['opened_at' => now()->subDays(5)])->save();
        $focus = $survey->questions()->create(['position' => 1, 'prompt' => 'Which area matters most to you?', 'type' => 'choice', 'options' => ['Sports', 'Education', 'Jobs and skills', 'Mental health']]);
        $rating = $survey->questions()->create(['position' => 2, 'prompt' => 'How well is the SK doing so far?', 'type' => 'rating']);
        $idea = $survey->questions()->create(['position' => 3, 'prompt' => 'One project you’d like to see', 'type' => 'text']);
        foreach ($youth->skip(1)->take(5)->values() as $index => $member) {
            $response = $survey->responses()->create(['user_id' => $member->id]);
            $response->answers()->create(['survey_question_id' => $focus->id, 'value' => ['Jobs and skills', 'Sports', 'Jobs and skills', 'Mental health', 'Education'][$index]]);
            $response->answers()->create(['survey_question_id' => $rating->id, 'value' => (string) [4, 5, 3, 4, 4][$index]]);
            $response->answers()->create(['survey_question_id' => $idea->id, 'value' => ['Free review classes for the board exam', 'A futsal court', 'Resume writing workshop', 'Peer counseling group', 'Study hub with Wi-Fi'][$index]]);
        }

        $answered = $this->feedback($youth[1], null, null, 'Could we have a study hub with Wi-Fi at the barangay hall?');
        $answered->forceFill(['response' => 'Yes! It is in next year’s ABYIP draft. Join the consultation to add your ideas.', 'responded_by' => $chair->id, 'responded_at' => now()->subDays(2)])->save();
        $this->feedback($youth[2], null, null, 'The court lights are broken in the evening.');
    }

    /**
     * In progress: approved CBYDP, this year's ABYIP still a draft.
     */
    private function seedBarretto(): void
    {
        $barangay = Barangay::firstWhere('name', 'Barretto');
        $chair = $this->officers($barangay, ['Paolo Cruz' => 'officer.barretto@sk.test'])->first();
        $this->youth($barangay, 4);

        $year = (int) now()->format('Y');
        $cbydp = $this->cbydp($barangay, $year, approvedBy: $chair, resolution: "Res. No. 1, s. {$year}");
        $priorities = $this->priorities($cbydp, [
            [CenterOfParticipation::Health, 'Teen pregnancy prevention'],
            [CenterOfParticipation::Agriculture, 'Urban gardening for youth'],
        ]);

        $abyip = $this->abyip($cbydp, $year);
        $this->ppa($abyip, $priorities[0], 'Youth health caravan', '200 youth reached', 30000, 15, 45, PpaStatus::Planned);
        $this->ppa($abyip, $priorities[1], 'Container gardening workshop', '50 youth trained', 15000, 30, 60, PpaStatus::Planned);

        $this->event($barangay, EventKind::Event, 'Container gardening workshop', 21, 'Barretto plaza', 50);

        $federation = User::firstWhere('email', 'federation@sk.test');
        $barangay->remarks()->make(['body' => "The {$year} ABYIP is still a draft. Please hold the session to approve it so PPAs can start."])
            ->author()->associate($federation)->save();
    }

    /**
     * Behind: only a draft CBYDP.
     */
    private function seedKalaklan(): void
    {
        $barangay = Barangay::firstWhere('name', 'Kalaklan');
        $this->officers($barangay, ['Rina Aquino' => 'officer.kalaklan@sk.test']);
        $this->youth($barangay, 2);

        $cbydp = $this->cbydp($barangay, (int) now()->format('Y'));
        $this->priorities($cbydp, [
            [CenterOfParticipation::Education, 'Scholarship information drive'],
            [CenterOfParticipation::PeaceBuilding, 'Anti-bullying campaign in schools'],
        ]);
    }

    /**
     * @param  array<string, string>  $people  name => email
     * @return Collection<int, User>
     */
    private function officers(Barangay $barangay, array $people): Collection
    {
        return collect($people)->map(function (string $email, string $name) use ($barangay): User {
            $user = new User(['name' => $name, 'email' => $email, 'password' => DemoAccountSeeder::PASSWORD, 'barangay_id' => $barangay->id]);
            $position = match (Str::before($email, '.')) {
                'secretary' => OfficerPosition::Secretary,
                'treasurer' => OfficerPosition::Treasurer,
                'kagawad1', 'kagawad2' => OfficerPosition::Kagawad,
                default => OfficerPosition::Chairperson,
            };
            $user->forceFill(['role' => UserRole::Officer, 'position' => $position, 'email_verified_at' => now()])->save();

            return $user;
        })->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function youth(Barangay $barangay, int $count): Collection
    {
        $slug = str($barangay->name)->slug('');

        return collect(range(1, $count))->map(fn (int $number): User => User::factory()->create([
            'name' => fake()->firstName().' '.fake()->lastName(),
            'email' => "youth{$number}.{$slug}@sk.test",
            'password' => DemoAccountSeeder::PASSWORD,
            'barangay_id' => $barangay->id,
        ]));
    }

    private function cbydp(Barangay $barangay, int $startYear, ?User $approvedBy = null, ?string $resolution = null): Cbydp
    {
        $cbydp = new Cbydp(['start_year' => $startYear, 'end_year' => $startYear + 2]);
        $cbydp->barangay_id = $barangay->id;

        if ($approvedBy) {
            $cbydp->forceFill([
                'status' => PlanStatus::Approved,
                'resolution_number' => $resolution,
                'approved_on' => now()->subMonths(5),
                'approval_recorded_by' => $approvedBy->id,
            ]);
        }

        $cbydp->save();

        return $cbydp;
    }

    /**
     * @param  list<array{CenterOfParticipation, string}>  $items
     * @return list<CbydpPriority>
     */
    private function priorities(Cbydp $cbydp, array $items): array
    {
        return array_map(fn (array $item): CbydpPriority => $cbydp->priorities()->create(['center' => $item[0], 'title' => $item[1]]), $items);
    }

    private function abyip(Cbydp $cbydp, int $year, ?User $approvedBy = null, ?string $resolution = null, bool $published = false): Abyip
    {
        $abyip = new Abyip(['year' => $year]);
        $abyip->forceFill(['cbydp_id' => $cbydp->id, 'barangay_id' => $cbydp->barangay_id]);

        if ($approvedBy) {
            $abyip->forceFill([
                'status' => PlanStatus::Approved,
                'resolution_number' => $resolution,
                'approved_on' => now()->subMonths(3),
                'approval_recorded_by' => $approvedBy->id,
                'published_at' => $published ? now()->subMonths(3)->addDay() : null,
            ]);
        }

        $abyip->save();

        return $abyip;
    }

    /**
     * Dates are days from today, so "delayed" and "upcoming" stay true whenever the seeder runs.
     */
    private function ppa(Abyip $abyip, CbydpPriority $priority, string $title, string $target, int $budget, int $startsInDays, int $endsInDays, PpaStatus $status): Ppa
    {
        $ppa = new Ppa([
            'cbydp_priority_id' => $priority->id,
            'title' => $title,
            'target' => $target,
            'budget' => $budget,
            'starts_on' => today()->addDays($startsInDays),
            'ends_on' => today()->addDays($endsInDays),
        ]);
        $ppa->forceFill(['barangay_id' => $abyip->barangay_id, 'status' => $status]);
        $abyip->ppas()->save($ppa);

        return $ppa;
    }

    /**
     * @param  list<array{string, int, bool}>  $items  title, due in days, done
     */
    private function milestones(Ppa $ppa, array $items): void
    {
        foreach ($items as [$title, $dueInDays, $done]) {
            $milestone = $ppa->milestones()->create(['title' => $title, 'due_on' => today()->addDays($dueInDays)]);

            if ($done) {
                $milestone->forceFill(['completed_at' => today()->addDays($dueInDays)])->save();
            }
        }
    }

    private function evidence(Ppa $ppa, string $title, string $sampleImage, bool $public): void
    {
        $path = "evidence/{$ppa->barangay_id}/demo-".str($title)->slug().'.jpg';
        $contents = file_get_contents(public_path("images/{$sampleImage}"));
        Storage::put($path, $contents);

        $evidence = new Evidence(['title' => $title, 'is_public' => $public]);
        $evidence->forceFill([
            'barangay_id' => $ppa->barangay_id,
            'uploaded_by' => User::where('barangay_id', $ppa->barangay_id)->where('role', UserRole::Officer)->value('id'),
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'image/jpeg',
            'size' => strlen($contents),
        ]);
        $ppa->evidence()->save($evidence);
    }

    /**
     * @param  Collection<int, User>  $officers
     */
    private function meeting(Barangay $barangay, string $title, int $daysAgo, Collection $officers, int $absent): Meeting
    {
        $meeting = $barangay->meetings()->create([
            'title' => $title,
            'kind' => str_starts_with($title, 'Special') ? 'special' : 'regular',
            'held_on' => today()->addDays($daysAgo),
            'venue' => 'Barangay hall',
        ]);

        $meeting->attendees()->sync($officers->values()->mapWithKeys(fn (User $officer, int $index): array => [
            $officer->id => ['status' => $index >= $officers->count() - $absent ? AttendanceStatus::Absent->value : AttendanceStatus::Present->value],
        ])->all());

        return $meeting;
    }

    private function resolution(Barangay $barangay, Meeting $meeting, string $number, string $title, int $daysAgo, ResolutionOutcome $outcome = ResolutionOutcome::Approved): void
    {
        $resolution = new Resolution([
            'meeting_id' => $meeting->id,
            'number' => $number,
            'title' => $title,
            'outcome' => $outcome,
            'adopted_on' => today()->addDays($daysAgo),
        ]);
        $resolution->barangay_id = $barangay->id;
        $resolution->save();
    }

    private function event(Barangay $barangay, EventKind $kind, string $title, int $daysFromNow, string $venue, ?int $capacity, ?Ppa $ppa = null): Event
    {
        return $barangay->events()->create([
            'kind' => $kind,
            'title' => $title,
            'description' => 'Open to youth aged 15 to 30 living in '.$barangay->name.'.',
            'starts_at' => Carbon::today()->addDays($daysFromNow)->setTime(9, 0),
            'venue' => $venue,
            'capacity' => $capacity,
            'ppa_id' => $ppa?->id,
        ]);
    }

    private function feedback(User $youth, ?Event $event, ?int $rating, string $message): Feedback
    {
        $feedback = new Feedback(['event_id' => $event?->id, 'rating' => $rating, 'message' => $message]);
        $feedback->barangay_id = $youth->barangay_id;
        $youth->feedback()->save($feedback);

        return $feedback;
    }
}
