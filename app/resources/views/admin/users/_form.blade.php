@php
    use App\OfficerPosition;
    use App\UserRole;

    /** @var \App\Models\User|null $user */
    $user ??= null;
    $selectedRole = old('role', $user?->role->value);
    $selectedBarangay = (string) old('barangay_id', $user?->barangay_id);
    $selectedPosition = old('position', $user?->position?->value);
@endphp
<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="name" label="Full name" :value="old('name', $user?->name)" required />
    <x-form.input name="email" type="email" label="Email" :value="old('email', $user?->email)" required />

    <x-form.select name="role" label="Role" placeholder="Choose a role" required>
        @foreach (UserRole::cases() as $role)
            <option value="{{ $role->value }}" @selected($selectedRole === $role->value)>{{ $role->label() }}</option>
        @endforeach
    </x-form.select>

    <div>
        <x-form.select name="barangay_id" label="Barangay" placeholder="No barangay (city-wide)" placeholder-selectable>
            @foreach ($barangays as $barangay)
                <option value="{{ $barangay->id }}" @selected($selectedBarangay === (string) $barangay->id)>{{ $barangay->name }}</option>
            @endforeach
        </x-form.select>
        <p class="mt-1.5 text-xs text-slate-muted">Required for youth and barangay SK officers. Ignored for the SK Federation and administrators.</p>
    </div>

    <div>
        <x-form.select name="position" label="Council position" placeholder="Not an officer" placeholder-selectable>
            @foreach (OfficerPosition::cases() as $position)
                <option value="{{ $position->value }}" @selected($selectedPosition === $position->value)>{{ $position->label() }}</option>
            @endforeach
        </x-form.select>
        <p class="mt-1.5 text-xs text-slate-muted">Required for barangay SK officers. The Chairperson approves public disclosure, the Secretary uploads minutes, and the Treasurer records spending.</p>
    </div>
</div>
