<x-layouts.app title="Barangay workspace">
    <h1 class="font-display text-3xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
    <p class="mt-3 max-w-xl text-slate-muted">This is the {{ auth()->user()->barangay->name }} SK workspace. Plans, PPAs, meetings, and reports for your barangay will be managed here.</p>
</x-layouts.app>
