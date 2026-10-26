@props(['barangay'])
{{-- "Follow on Facebook" for the SK's own page, when the administrator has added one. --}}
@if ($barangay->facebook_url)
    <a href="{{ $barangay->facebook_url }}" target="_blank" rel="noopener noreferrer" {{ $attributes->class(['inline-flex items-center gap-2 rounded-[8px] border border-line bg-white px-3.5 py-2 text-sm font-semibold text-navy hover:border-[#1877f2] hover:text-[#1877f2]']) }}>
        <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#1877f2]" fill="currentColor" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
        Follow SK {{ $barangay->name }}<span class="sr-only"> on Facebook (opens in a new tab)</span>
    </a>
@endif
