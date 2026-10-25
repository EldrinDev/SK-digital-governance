@php
    use App\UserRole;

    $user = auth()->user();
@endphp
<x-layouts.public :title="$event->title" :description="$event->kind->label().' by the '.$event->barangay->name.' SK on '.$event->starts_at->format('F j, Y').'.'">
    <p class="text-sm"><a href="{{ route('events.index') }}" class="font-medium text-slate-muted hover:text-emerald">&larr; All events</a></p>

    <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <article>
            <p class="text-sm font-semibold text-emerald">{{ $event->kind->label() }} · {{ $event->barangay->name }} SK</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl">{{ $event->title }}</h1>
            <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-muted">When</dt><dd class="font-medium">{{ $event->starts_at->format('l, F j, Y · g:i A') }}</dd></div>
                <div><dt class="text-slate-muted">Where</dt><dd class="font-medium">{{ $event->venue }}</dd></div>
            </dl>
            @if ($event->description)
                <p class="mt-6 max-w-2xl whitespace-pre-line leading-relaxed text-navy/80">{{ $event->description }}</p>
            @endif
        </article>

        <aside class="h-fit rounded-[12px] border border-line bg-white p-5" aria-labelledby="register-heading">
            <h2 id="register-heading" class="font-display text-lg font-semibold">Registration</h2>
            <p class="mt-1 text-sm text-slate-muted">
                @if ($event->capacity === null)
                    {{ trans_choice(':count youth registered so far.|:count youth registered so far.', $event->registrations_count) }}
                @elseif ($event->isFull())
                    Every place is taken.
                @else
                    {{ $event->spotsLeft() }} of {{ $event->capacity }} places left.
                @endif
            </p>

            @if (session('status'))
                <p role="status" class="mt-4 rounded-[8px] bg-success/10 p-3 text-sm">{{ session('status') }}</p>
            @endif
            @error('registration')
                <p role="alert" class="mt-4 rounded-[8px] bg-error/5 p-3 text-sm text-[#b91c1c]">{{ $message }}</p>
            @enderror

            <div class="mt-4">
                @if ($event->hasStarted())
                    <p class="text-sm text-slate-muted">This event has started, so registration is closed.</p>
                @elseif (! $user)
                    <a href="{{ route('login') }}" class="block rounded-[8px] bg-emerald px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-emerald-dark">Log in to register</a>
                    <p class="mt-3 text-center text-xs text-slate-muted">No account? <a href="{{ route('register') }}" class="font-semibold text-emerald hover:underline">Create one</a></p>
                @elseif (! $user->hasRole(UserRole::Youth))
                    <p class="text-sm text-slate-muted">Registration is for youth accounts.</p>
                @elseif ($registration)
                    <p class="mb-3 text-sm font-medium text-[#047857]">You’re registered.</p>
                    <form method="POST" action="{{ route('events.registration.destroy', $event) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-[8px] border border-line px-4 py-2.5 text-sm font-semibold hover:border-navy">Cancel my registration</button>
                    </form>
                @elseif ($user->barangay_id !== $event->barangay_id)
                    <p class="text-sm text-slate-muted">This event is for youth of {{ $event->barangay->name }}.</p>
                @elseif ($event->isFull())
                    <p class="text-sm text-slate-muted">No places left.</p>
                @else
                    <form method="POST" action="{{ route('events.registration.store', $event) }}">
                        @csrf
                        <x-form.submit busy="Registering…">Register</x-form.submit>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</x-layouts.public>
