<x-layouts.app title="Feedback" heading="Feedback from youth" :description="$unansweredCount > 0 ? trans_choice(':count message is waiting for a reply.|:count messages are waiting for a reply.', $unansweredCount) : 'Everything has a reply.'">
    <nav class="mb-5 flex gap-2 text-sm" aria-label="Filter feedback">
        @foreach ([null => 'All', 'unanswered' => 'Waiting for reply', 'answered' => 'Replied'] as $value => $label)
            <a href="{{ route('officer.feedback.index', array_filter(['show' => $value])) }}" @class([
                'rounded-full px-3.5 py-1.5 font-medium',
                'bg-navy text-white' => $filter === ($value ?: null),
                'border border-line bg-white text-navy/80 hover:border-navy' => $filter !== ($value ?: null),
            ])>{{ $label }}</a>
        @endforeach
    </nav>

    <ul class="space-y-4">
        @forelse ($feedback as $item)
            <li class="rounded-[12px] border border-line bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium">{{ $item->author->name }}</p>
                        <p class="text-xs text-slate-muted">
                            {{ $item->created_at->format('M j, Y') }}
                            @if ($item->event)
                                · evaluation of {{ $item->event->title }} · {{ $item->rating }} / 5
                            @else
                                · general feedback
                            @endif
                        </p>
                    </div>
                    <x-ui.badge :tone="$item->isAnswered() ? 'success' : 'warning'">{{ $item->isAnswered() ? 'Replied' : 'Waiting for reply' }}</x-ui.badge>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed">{{ $item->message }}</p>

                @if ($item->isAnswered())
                    <div class="mt-4 rounded-[8px] bg-mist p-3 text-sm">
                        <p class="whitespace-pre-line">{{ $item->response }}</p>
                        <p class="mt-1 text-xs text-slate-muted">{{ $item->responder?->name ?? 'An officer' }} · {{ $item->responded_at->format('M j, Y') }}</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('officer.feedback.update', $item) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <label for="response-{{ $item->id }}" class="sr-only">Reply to {{ $item->author->name }}</label>
                        <textarea id="response-{{ $item->id }}" name="response" rows="2" placeholder="Write a reply. They’ll get it by email." class="block w-full rounded-[8px] border border-line px-3.5 py-2.5 text-sm outline-none focus:border-emerald focus:ring-3 focus:ring-emerald/20"></textarea>
                        <x-form.submit busy="Sending…" :block="false" class="py-2">Send reply</x-form.submit>
                    </form>
                @endif
            </li>
        @empty
            <li class="rounded-[12px] border border-dashed border-line bg-white p-8 text-center text-slate-muted">No feedback here.</li>
        @endforelse
    </ul>

    <div class="mt-4">{{ $feedback->links() }}</div>
</x-layouts.app>
