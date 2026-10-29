@extends('layouts.guest')

@section('title', 'Login — '.config('permit.municipality'))

@section('content')
    <div class="auth-wrap inspector-auth-wrap">
        <section class="inspector-auth-intro">
            <p class="inspector-eyebrow">MUNICIPAL FIELD OPERATIONS</p>
            <h1>Verify with confidence.</h1>
            <p>Scan permit QR codes in the field and monitor the validity of permits issued by BPLO.</p>
            <div class="inspector-auth-feature">
                <span aria-hidden="true">01</span>
                <div><strong>Scan permit QR codes</strong><small>See live validity results and record each verification.</small></div>
            </div>
            <div class="inspector-auth-feature">
                <span aria-hidden="true">02</span>
                <div><strong>Monitor issued permits</strong><small>Search permit records and review scan history.</small></div>
            </div>
        </section>
        <div class="auth-card">
            <div class="inspector-auth-heading">
                <span class="inspector-auth-mark" aria-hidden="true">QR</span>
                <p class="inspector-eyebrow">INSPECTOR ACCESS</p>
                <h2>Welcome back</h2>
                <p class="muted">Sign in with your active inspector account.</p>
            </div>

            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Work email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@bansud.gov.ph" autocomplete="username" required autofocus>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    @error('password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="inspector-login-options">
                    <label class="inspector-remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
                </div>
                <button type="submit" class="btn btn-block">Sign in to Inspector Portal <span aria-hidden="true">→</span></button>
            </form>
            <p class="inspector-auth-note"><span aria-hidden="true">●</span> Authorized municipal inspectors only</p>
        </div>
    </div>
@endsection
