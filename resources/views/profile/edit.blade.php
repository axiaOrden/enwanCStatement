<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="account-container">
        <div class="account-heading"><span>Account settings</span><h1>Your profile</h1></div>
        <div class="account-grid">
            <div class="account-card">
                <div>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="account-card">
                <div>
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="account-card account-card-danger">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
