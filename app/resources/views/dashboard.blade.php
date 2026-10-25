<x-layouts.app title="Dashboard">
    <h1 class="font-display text-3xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
    <p class="mt-3 max-w-xl text-slate-muted">You’re logged in. Events, consultations, and announcements from the {{ auth()->user()->barangay->name }} SK will appear here.</p>
</x-layouts.app>
