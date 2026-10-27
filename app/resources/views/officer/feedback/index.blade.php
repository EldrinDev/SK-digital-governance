@php
    $tabs = ['' => 'All', 'unanswered' => 'Waiting for reply', 'answered' => 'Replied'];
    $initials = fn (string $name): string => collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<x-layouts.app title="Feedback" heading="Feedback from youth" :description="$unansweredCount > 0 ? trans_choice(':count message is waiting for a reply. Youth get your reply by email and on their dashboard.|:count messages are waiting for a reply. Youth get your reply by email and on their dashboard.', $unansweredCount) : 'Everything has a reply. Youth see your replies by email and on their dashboard.'">
    <div class="max-w-4xl">
        <nav class="mb-5 inline-flex flex-wrap rounded-[10px] border border-line bg-white p-1" aria-label="Filter feedback">
            @foreach ($tabs as $value => $label)
                @php
                    $isActive = ($filter ?? '') === $value;
                @endphp
                <a href="{{ route('officer.feedback.index', array_filter(['show' => $value])) }}" @class([
                    'inline-flex items-center gap-2 rounded-[8px] px-3.5 py-1.5 text-sm font-semibold transition-colors',
                    'bg-navy text-white' => $isActive,
                    'text-navy/70 hover:text-navy' => ! $isActive,
                ]) @if ($isActive) aria-current="page" @endif>
                    {{ $label }}
                    <span @class([
                        'rounded-full px-1.5 text-xs tabular-nums',
                        'bg-white/20' => $isActive,
                        'bg-gold/25 text-[#92400e]' => ! $isActive && $value === 'unanswered' && $counts[$value] > 0,
                        'bg-mist text-slate-muted' => ! $isActive && ! ($value === 'unanswered' && $counts[$value] > 0),
                    ])>{{ $counts[$value] }}</span>
                </a>
            @endforeach
        </nav>

        @if ($feedback->isEmpty())
            <div class="rounded-[14px] border border-dashed border-line bg-white px-6 py-10 text-center">
                <p class="font-display text-lg font-semibold text-navy">{{ $filter === 'unanswered' ? 'You’re all caught up' : 'No messages here' }}</p>
                <p class="mt-1 text-sm text-slate-muted">Youth send ideas and concerns from their dashboard, and rate events they attended.</p>
            </div>
        @else
            <ul class="divide-y divide-line overflow-hidden rounded-[14px] border border-line bg-white">
                @foreach ($feedback as $item)
                    <li class="px-5 py-5">
                        <article class="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-x-4" aria-labelledby="feedback-{{ $item->id }}">
                            <span class="relative flex h-10 w-10 items-center justify-center rounded-full bg-navy/5 text-sm font-semibold text-navy" aria-hidden="true">
                                {{ $initials($item->author->name) }}
                                @unless ($item->isAnswered())
                                    <span class="absolute -right-0.5 -top-0.5 h-3 w-3 rounded-full border-2 border-white bg-gold"></span>
                                @endunless
                            </span>
                            <div class="min-w-0">
                                <header class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                    <h2 id="feedback-{{ $item->id }}" class="font-medium text-navy">{{ $item->author->name }}</h2>
                                    <time datetime="{{ $item->created_at->toIso8601String() }}" class="text-xs text-slate-muted" title="{{ $item->created_at->format('F j, Y g:i A') }}">{{ $item->created_at->diffForHumans() }}</time>
                                </header>
                                <p class="text-xs text-slate-muted">
                                    @if ($item->event)
                                        Rated <span class="font-medium text-navy/80">{{ $item->event->title }}</span>
                                        <span class="ml-1 text-gold" aria-hidden="true">{{ str_repeat('★', $item->rating) }}</span><span class="text-line" aria-hidden="true">{{ str_repeat('★', 5 - $item->rating) }}</span>
                                        <span class="sr-only">{{ $item->rating }} out of 5</span>
                                    @else
                                        Idea or concern
                                    @endif
                                    @unless ($item->isAnswered())
                                        <span class="ml-1 font-semibold text-[#92400e]">Waiting for a reply</span>
                                    @endunless
                                </p>

                                <p class="mt-2 whitespace-pre-line leading-relaxed text-navy/90">{{ $item->message }}</p>

                                @if ($item->isAnswered())
                                    <div class="mt-4 border-l-2 border-emerald/40 pl-4">
                                        <p class="text-xs font-semibold text-emerald">Your SK replied</p>
                                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-navy/85">{{ $item->response }}</p>
                                        <p class="mt-1 text-xs text-slate-muted">{{ $item->responder?->name ?? 'An officer' }}, {{ $item->responded_at->format('M j, Y') }}</p>
                                    </div>
                                @else
                                    <details class="group mt-3" @if ($errors->has('response') && old('feedback_id') == $item->id) open @endif>
                                        <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-[8px] bg-emerald px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-emerald-dark group-open:hidden [&::-webkit-details-marker]:hidden">
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 5 5v6"/></svg>
                                            Reply<span class="sr-only"> to {{ $item->author->name }}</span>
                                        </summary>
                                        <form method="POST" action="{{ route('officer.feedback.update', $item) }}" class="rounded-[12px] border border-line bg-mist/40 p-3">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="feedback_id" value="{{ $item->id }}">
                                            <label for="response-{{ $item->id }}" class="text-xs font-semibold text-navy/80">Reply to {{ str($item->author->name)->before(' ') }}</label>
                                            <textarea id="response-{{ $item->id }}" name="response" rows="3" placeholder="They’ll get it by email and see it on their dashboard." class="mt-1.5 block w-full rounded-[8px] border border-line bg-white px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20">{{ old('feedback_id') == $item->id ? old('response') : '' }}</textarea>
                                            @if (old('feedback_id') == $item->id)
                                                @error('response')
                                                    <p class="mt-1.5 text-sm text-[#b91c1c]">{{ $message }}</p>
                                                @enderror
                                            @endif
                                            <div class="mt-2.5 flex items-center justify-end gap-2">
                                                <button type="button" onclick="this.closest('details').open = false" class="rounded-[8px] px-3 py-1.5 text-sm font-medium text-slate-muted hover:text-navy">Cancel</button>
                                                <x-form.submit busy="Sending…" :block="false" class="py-1.5">Send reply</x-form.submit>
                                            </div>
                                        </form>
                                    </details>
                                @endif
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-4">{{ $feedback->links() }}</div>
    </div>
</x-layouts.app>
