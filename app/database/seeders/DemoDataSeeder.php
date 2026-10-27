<?php

namespace Database\Seeders;

use App\AnnouncementCategory;
use App\AttendanceStatus;
use App\CenterOfParticipation;
use App\CheckInMethod;
use App\EventKind;
use App\Models\Abyip;
use App\Models\Announcement;
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

        // Every barangay is on the public portal for the demo; the three pilot barangays
        // also have officers, plans and PPAs.
        Barangay::query()->update(['is_participating' => true]);

        $this->seedSantaRita();
        $this->seedBarretto();
        $this->seedKalaklan();
        $this->seedRestOfTheCity();
        $this->seedLogos();
    }

    /**
     * Give each barangay SK its own logo when one has been dropped into
     * database/seeders/images/logos, named after the barangay (santa-rita.png,
     * east-bajac-bajac.jpg). Barangays without a file keep the national SK logo.
     * Add official logos only with the SK's permission.
     */
    private function seedLogos(): void
    {
        foreach (Barangay::all() as $barangay) {
            $slug = str($barangay->name)->slug();

            foreach (['png', 'webp', 'jpg', 'jpeg'] as $extension) {
                $file = database_path("seeders/images/logos/{$slug}.{$extension}");

                if (is_file($file)) {
                    $barangay->logo_path = "barangays/{$barangay->id}/logo.{$extension}";
                    Storage::put($barangay->logo_path, file_get_contents($file));
                    $barangay->save();

                    break;
                }
            }
        }
    }

    /**
     * One sample post (and for some, an upcoming event) for each of the other 14 barangays,
     * written in the Taglish style SK pages use. Photos are real street photos of each
     * barangay from Wikimedia Commons (CC0 / public domain), credited on the post.
     */
    private function seedRestOfTheCity(): void
    {
        $posts = [
            'Asinan' => [AnnouncementCategory::Event, 'Liga ng Kabataan 2026: bukas na ang registration!', ['🏀 Basketball at 🏐 Volleyball, boys and girls division!', '', '🗓️ Opening: '.now()->addDays(12)->format('F j, Y'), '📍 Asinan Covered Court', '', 'Bumuo na ng team (8 hanggang 12 players) at mag-register sa Events page. Libre ang jersey para sa unang 6 na team!', '', '#SKAsinan #LigaNgKabataan #BatangGapo'], 3, [EventKind::Event, 'Liga ng Kabataan opening day', 12, 'Asinan Covered Court', 96]],
            'Banicain' => [AnnouncementCategory::Reminder, 'PAALALA: Katipunan ng Kabataan Profiling', ['📋 PAALALA SA LAHAT NG KABATAAN NG BANICAIN!', '', 'Patuloy ang KK Profiling para sa edad 15 hanggang 30. Kailangan ito para maisama ka sa mga programa ng SK at sa paggawa ng CBYDP.', '', '🕘 Lunes hanggang Biyernes, 9:00 AM hanggang 4:00 PM', '📍 Banicain Barangay Hall', '', 'Magdala ng kahit anong valid ID. 🙏', '', '#SKBanicain #KKProfiling #BatangGapo'], 5, null],
            'East Bajac-Bajac' => [AnnouncementCategory::Event, 'Tree Planting Activity: Kabataan para sa Kalikasan', ['🌱 TARA, MAGTANIM TAYO!', '', 'Samahan ang SK East Bajac-Bajac sa ating tree planting activity sa tabi ng ilog.', '', '🗓️ '.now()->addDays(9)->format('F j, Y'), '🕖 7:00 AM', '📍 East Bajac-Bajac riverside park', '', 'Magdala ng tumbler at sumbrero. Sagot ng SK ang seedlings at merienda!', '', '#SKEastBajacBajac #KabataanParaSaKalikasan #BatangGapo'], 2, [EventKind::Event, 'Tree planting by the river', 9, 'East Bajac-Bajac riverside park', 60]],
            'East Tapinac' => [AnnouncementCategory::Recognition, 'Salamat sa pagdalo sa Linggo ng Kabataan dito sa East Tapinac!', ['💚 MARAMING SALAMAT, KABATAAN!', '', 'Naging tahanan ang East Tapinac ng Mobile Legends: Bang Bang Inter-Barangay Tournament ng SK Olongapo noong Agosto 22 bilang bahagi ng Linggo ng Kabataan 2026.', '', 'Salamat sa lahat ng team, sa mga nanood at sa mga volunteer na tumulong sa venue. Kita-kits sa susunod na taon! 🎮', '', '#LinggoNgKabataan2026 #SKOlongapo #SKEastTapinac'], 60, null],
            'Gordon Heights' => [AnnouncementCategory::Announcement, 'Libreng Tutorial Program para sa Grade School', ['📚 LIBRENG TUTORIAL, TUWING SABADO!', '', 'Para sa mga batang Grade 1 hanggang Grade 6 na kailangan ng tulong sa Reading at Math. Ang mga tutor ay mga kabataang volunteer ng Gordon Heights.', '', '🕘 Tuwing Sabado, 9:00 hanggang 11:00 AM', '📍 Gordon Heights Barangay Hall', '', 'Gusto mo bang maging volunteer tutor? Mag-message sa amin! 🙌', '', '#SKGordonHeights #EdukasyonParaSaLahat #BatangGapo'], 4, null],
            'Mabayuan' => [AnnouncementCategory::Event, 'Bloodletting Activity: Dugtong-Buhay', ['🩸 ISANG BAG NG DUGO, TATLONG BUHAY ANG MASASAGIP', '', 'Inaanyayahan ang lahat ng kabataang 18 pataas na nasa mabuting kalusugan na makiisa sa ating Bloodletting Activity.', '', '🗓️ '.now()->addDays(15)->format('F j, Y'), '🕗 8:00 AM hanggang 12:00 NN', '📍 Mabayuan Covered Court', '', 'Matulog nang sapat at kumain bago pumunta. Bawal ang may sipon o lagnat.', '', '#SKMabayuan #DugtongBuhay #BatangGapo'], 1, [EventKind::Event, 'Bloodletting activity', 15, 'Mabayuan Covered Court', 80]],
            'New Cabalan' => [AnnouncementCategory::Announcement, 'Mental Health Awareness Talk: Okay lang na hindi okay', ['🧠 USAP TAYO TUNGKOL SA MENTAL HEALTH', '', 'Magkakaroon ng libreng talk tungkol sa stress, anxiety at kung paano humingi ng tulong. Bukas ito sa mga estudyante at working youth.', '', 'Abangan ang petsa dito sa ating page. Kung kailangan mo ng kausap ngayon, tumawag sa NCMH Crisis Hotline: 1553. 💚', '', '#SKNewCabalan #MentalHealthMatters #BatangGapo'], 7, null],
            'New Ilalim' => [AnnouncementCategory::Event, 'River Clean-up Drive ngayong buwan', ['🌊 LINIS-ILOG, PARA SA LAHAT!', '', 'Tulong-tulong nating linisin ang ilog sa ating barangay bago dumating ang tag-ulan.', '', '🗓️ '.now()->addDays(6)->format('F j, Y'), '🕕 6:00 AM', '📍 New Ilalim riverbank', '', 'Gloves, sako at merienda, sagot ng SK!', '', '#SKNewIlalim #MalinisNaGapo #BatangGapo'], 2, [EventKind::Event, 'River clean-up drive', 6, 'New Ilalim riverbank', 50]],
            'New Kababae' => [AnnouncementCategory::Announcement, 'Bukas na ang aplikasyon sa SK Educational Assistance', ['🎓 PARA SA MGA ESTUDYANTE NG NEW KABABAE', '', 'Tumatanggap na ang SK ng aplikasyon para sa educational assistance ngayong semestre.', '', 'Mga kailangan: certificate of enrollment, kopya ng grades at barangay certificate. Ipasa sa Barangay Hall hanggang katapusan ng buwan.', '', '#SKNewKababae #EdukasyonParaSaLahat #BatangGapo'], 6, null],
            'New Kalalake' => [AnnouncementCategory::Event, 'Zumba sa Plaza tuwing Sabado', ['💃 GALAW-GALAW, KABATAAN!', '', 'Libreng Zumba session para sa lahat ng edad. Magdala ng tubig at towel!', '', '🗓️ Simula '.now()->addDays(5)->format('F j, Y'), '🕕 5:30 AM', '📍 New Kalalake plaza', '', '#SKNewKalalake #HealthyKabataan #BatangGapo'], 1, [EventKind::Event, 'Zumba sa plaza', 5, 'New Kalalake plaza', null]],
            'Old Cabalan' => [AnnouncementCategory::Announcement, 'Youth Skills Training: Basic Computer Literacy', ['💻 MATUTO NG BASIC COMPUTER SKILLS!', '', 'Libreng training sa MS Word, Excel at paggawa ng resume para sa out-of-school youth at mga naghahanap ng trabaho.', '', 'Limitado ang slots. Mag-message sa amin o pumunta sa Barangay Hall para magpalista.', '', '#SKOldCabalan #SkillsParaSaKabataan #BatangGapo'], 8, null],
            'Pag-asa' => [AnnouncementCategory::Reminder, 'PAALALA: Katipunan ng Kabataan Assembly', ['📢 PAALALA, KABATAAN NG PAG-ASA!', '', 'Inaanyayahan ang lahat sa ating KK Assembly. Pag-uusapan natin ang mga programa ng SK at ang budget para sa susunod na taon.', '', '🗓️ '.now()->addDays(10)->format('F j, Y'), '🕑 2:00 PM', '📍 Pag-asa Barangay Hall', '', '#SKPagAsa #KatipunanNgKabataan #BatangGapo'], 3, null],
            'West Bajac-Bajac' => [AnnouncementCategory::Event, 'Anti-Drug Abuse Symposium para sa Kabataan', ['🚫 KABATAAN, IWAS DROGA!', '', 'Isang symposium tungkol sa panganib ng ipinagbabawal na gamot at kung paano tumanggi sa barkada.', '', '🗓️ '.now()->addDays(18)->format('F j, Y'), '🕑 1:00 PM', '📍 West Bajac-Bajac Barangay Hall', '', '#SKWestBajacBajac #DrugFreeKabataan #BatangGapo'], 4, [EventKind::Event, 'Anti-drug abuse symposium', 18, 'West Bajac-Bajac Barangay Hall', 120]],
            'West Tapinac' => [AnnouncementCategory::Recognition, 'Salamat sa mga volunteer ng Brigada Eskwela!', ['🏫 SALAMAT, MGA VOLUNTEER!', '', 'Maraming salamat sa mga kabataang tumulong maglinis, magpintura at mag-ayos ng mga silid-aralan para sa pagbubukas ng klase.', '', 'Kayo ang patunay na ang kabataan ng West Tapinac ay handang tumulong! 💚', '', '#SKWestTapinac #BrigadaEskwela #BatangGapo'], 75, null],
        ];

        $credits = json_decode(file_get_contents(database_path('seeders/images/barangays/credits.json')), true);

        foreach ($posts as $name => [$category, $title, $lines, $daysAgo, $event]) {
            $barangay = Barangay::firstWhere('name', $name);
            $photo = $credits[$name];

            $this->announcement($barangay, null, $category, $title, implode("\n", $lines), now()->subDays($daysAgo)->setTime(18, 30),
                image: database_path('seeders/images/barangays/'.$photo['file']),
                imageCredit: "{$photo['author']}, Wikimedia Commons ({$photo['license']})");

            if ($event !== null) {
                [$kind, $eventTitle, $inDays, $venue, $capacity] = $event;
                $this->event($barangay, $kind, $eventTitle, $inDays, $venue, $capacity);
            }
        }
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
            // Most were checked in by scanning their digital ID; a couple by hand at the table.
            $pastEvent->registrations()->create(['user_id' => $member->id])->forceFill([
                'attended_at' => $pastEvent->starts_at->copy()->addMinutes(5 * $index),
                'check_in_method' => $index % 3 === 2 ? CheckInMethod::Manual : CheckInMethod::Qr,
                'checked_in_by' => $officers[$index % 2 === 0 ? 0 : 1]->id,
            ])->save();

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
        $answered->forceFill([
            'created_at' => now()->subDays(5),
            'response' => 'Yes! It is in next year’s ABYIP draft. Join the consultation to add your ideas.',
            'responded_by' => $chair->id,
            'responded_at' => now()->subDays(2),
        ])->save();
        $this->feedback($youth[2], null, null, 'The court lights are broken in the evening.');

        $this->seedSantaRitaAnnouncements($barangay, $chair, $officers[1], $year);
    }

    /**
     * Posts written the way barangay SK pages in Olongapo caption theirs: Taglish, with emoji
     * markers for date, time and venue, and the SK's hashtags. The recap follows SK Olongapo's
     * real Mobile Legends inter-barangay tournament for Linggo ng Kabataan (August 22, 2026, East Tapinac).
     */
    private function seedSantaRitaAnnouncements(Barangay $barangay, User $chair, User $secretary, int $year): void
    {
        $assembly = now()->next(Carbon::SATURDAY)->addWeek();
        $this->announcement($barangay, $chair, AnnouncementCategory::Reminder, 'PAALALA: Katipunan ng Kabataan Assembly', implode("\n", [
            '📢 PAALALA, KABATAAN NG SANTA RITA!',
            '',
            'Inaanyayahan ang lahat ng kabataang edad 15 hanggang 30 sa ating Katipunan ng Kabataan (KK) Assembly.',
            '',
            '🗓️ '.$assembly->format('F j, Y').' ('.$assembly->format('l').')',
            '🕑 2:00 PM',
            '📍 Santa Rita Covered Court',
            '',
            'Pag-uusapan natin ang mga nagawa ng SK ngayong taon at ang mga programang isasama sa ABYIP '.($year + 1).'. Boses mo ang kailangan namin! Magdala ng valid ID o school ID para sa attendance.',
            '',
            '#SKSantaRita #KatipunanNgKabataan #BatangGapo',
        ]), now()->subDay(), pinned: true);

        $this->announcement($barangay, $chair, AnnouncementCategory::Event, 'Coastal Clean-up Drive ngayong Sabado', implode("\n", [
            '🌊 TARA, LINIS TAYO SA DALAMPASIGAN!',
            '',
            'Samahan ang SK Santa Rita sa ating Coastal Clean-up Drive.',
            '',
            '📍 Santa Rita seawall',
            '🕕 6:00 AM',
            '🧤 Magdala ng gloves, tumbler at sumbrero. Sagot ng SK ang sako at merienda!',
            '',
            'Mag-register sa Events page para makapaghanda kami ng sapat na gamit. Bahagi ito ng ating ABYIP program para sa kalikasan.',
            '',
            '#SKSantaRita #MalinisNaGapo #KabataanParaSaKalikasan',
        ]), now()->subDays(4), image: database_path('seeders/images/barangays/santa-rita.jpg'), imageCredit: 'Judgefloro, Wikimedia Commons (CC0)');

        $this->announcement($barangay, $secretary, AnnouncementCategory::Announcement, "ABYIP {$year}, bukas na sa publiko", implode("\n", [
            "📊 Naka-post na sa Transparency Board ang ABYIP {$year} ng SK Santa Rita, na inaprubahan ng konseho sa Res. No. 7, s. {$year}.",
            '',
            'Makikita rito ang bawat PPA, ang budget nito at ang progreso. Ang SK fund ay pondo ng kabataan, kaya karapatan ninyong malaman kung saan ito napupunta. 💚',
            '',
            '#SKSantaRita #FullDisclosure #TransparencySaSK',
        ]), now()->subDays(12));

        $this->announcement($barangay, $chair, AnnouncementCategory::Recognition, 'Salamat sa lahat ng sumali sa MLBB Inter-Barangay Tournament!', implode("\n", [
            '🎮 MARAMING SALAMAT SA LAHAT NG NAKIISA!',
            '',
            'Bilang bahagi ng Linggo ng Kabataan 2026, lumaban ang ating mga manlalaro sa Mobile Legends: Bang Bang Inter-Barangay Tournament ng SK Olongapo noong Agosto 22 sa East Tapinac.',
            '',
            'Ipinakita ng ating team ang teamwork, disiplina at sportsmanship. Salamat sa mga magulang at kaibigang sumuporta. Hanggang sa susunod na laban! 💚',
            '',
            '#LinggoNgKabataan2026 #SKOlongapo #SKSantaRita',
        ]), Carbon::parse('2026-08-24 18:30')->min(now()->subDays(30)), image: public_path('images/skyline-freeport.jpg'));
    }

    /**
     * Post an announcement, optionally pinned or with a sample photo.
     */
    private function announcement(Barangay $barangay, ?User $author, AnnouncementCategory $category, string $title, string $body, Carbon $postedAt, bool $pinned = false, ?string $image = null, ?string $imageCredit = null): void
    {
        $announcement = new Announcement(['category' => $category, 'title' => $title, 'body' => $body]);
        $announcement->forceFill([
            'barangay_id' => $barangay->id,
            'user_id' => $author?->id,
            'image_credit' => $imageCredit,
            'pinned_at' => $pinned ? $postedAt : null,
            'created_at' => $postedAt,
            'updated_at' => $postedAt,
        ]);

        if ($image !== null) {
            $announcement->image_path = "announcements/{$barangay->id}/demo-".str($title)->slug()->limit(40, '').'.jpg';
            Storage::put($announcement->image_path, file_get_contents($image));
        }

        $announcement->save();
    }

    /**
     * In progress: approved CBYDP, this year's ABYIP still a draft.
     */
    private function seedBarretto(): void
    {
        $barangay = Barangay::firstWhere('name', 'Barretto');
        $chair = $this->officers($barangay, ['Paolo Cruz' => 'officer.barretto@sk.test'])->first();
        $this->youth($barangay, 4);
        $this->announcement($barangay, $chair, AnnouncementCategory::Event, 'Youth Consultation para sa ABYIP', implode("\n", [
            '🗣️ ANO ANG GUSTO MONG PROGRAMA?',
            '',
            'Binubuo na ng SK Barretto ang ABYIP para sa susunod na taon, at gusto naming marinig ang boses mo! Dumalo sa Youth Consultation sa Barretto Barangay Hall, o mag-iwan ng mungkahi sa Feedback ng platform.',
            '',
            'Sports, edukasyon, kalusugan o trabaho? Ikaw ang magsabi. 🙌',
            '',
            '#SKBarretto #KabataanAngBoses #BatangGapo',
        ]), now()->subDays(2), pinned: true, image: database_path('seeders/images/barangays/barretto.jpg'), imageCredit: 'Judgefloro, Wikimedia Commons (CC0)');

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

        $this->announcement($barangay, User::firstWhere('email', 'officer.kalaklan@sk.test'), AnnouncementCategory::Announcement, 'Scholarship Information Drive para sa mga estudyante', implode("\n", [
            '🎓 PARA SA MGA ESTUDYANTE NG KALAKLAN',
            '',
            'Magkakaroon ang SK Kalaklan ng Scholarship Information Drive para sa senior high at college students. Ituturo namin kung paano mag-apply sa mga scholarship ng lungsod, ng CHED at ng iba pang ahensya.',
            '',
            'Abangan ang petsa at lugar dito! 📚',
            '',
            '#SKKalaklan #EdukasyonParaSaLahat #BatangGapo',
        ]), now()->subDays(6), image: database_path('seeders/images/barangays/kalaklan.jpg'), imageCredit: 'Judgefloro, Wikimedia Commons (CC0)');

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
