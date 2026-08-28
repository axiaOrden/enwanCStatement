<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased auth-page">
        <div class="auth-orb auth-orb-one" aria-hidden="true"></div>
        <div class="auth-orb auth-orb-two" aria-hidden="true"></div>
        <div class="auth-shell">
            <section class="auth-story">
                <a href="/" class="auth-brand">
                    <span class="auth-logo"><x-application-logo /></span>
                    <span><small>Finance workspace</small><strong>Statements</strong></span>
                </a>
                <div class="auth-story-copy">
                    <span class="auth-kicker">Simple. Clear. Ready to share.</span>
                    <h1>Every account story, beautifully stated.</h1>
                    <p>Find customers, preview account activity, and send polished PDF statements from one focused workspace.</p>
                </div>
                <div class="auth-feature-row" aria-hidden="true">
                    <span><b>01</b> Find</span><i></i><span><b>02</b> Preview</span><i></i><span><b>03</b> Share</span>
                </div>
            </section>

            <main class="auth-panel">
                @php($authCopy = match (true) {
                    request()->routeIs('password.request') => ['Account recovery', 'Reset your password', 'We’ll send a secure reset link to your inbox.'],
                    request()->routeIs('password.reset') => ['Choose a new password', 'Reset your password', 'Create a strong password for your account.'],
                    request()->routeIs('password.confirm') => ['Protected area', 'Confirm it’s you', 'Enter your password to continue securely.'],
                    request()->routeIs('verification.notice') => ['One last step', 'Verify your email', 'Check your inbox to activate your account.'],
                    default => ['Welcome back', 'Sign in to continue', 'Access your customer statement workspace.'],
                })
                <div class="auth-panel-heading">
                    <span class="auth-kicker">{{ $authCopy[0] }}</span>
                    <h2>{{ $authCopy[1] }}</h2>
                    <p>{{ $authCopy[2] }}</p>
                </div>
                <div class="auth-form-card">{{ $slot }}</div>
            </main>
        </div>
    </body>
</html>
