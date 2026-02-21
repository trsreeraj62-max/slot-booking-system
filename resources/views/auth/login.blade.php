@extends('layouts.app')

@section('title', 'Login - Slot Booking System')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Please enter your details to sign in</p>
    </div>

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com" required autofocus>
        </div>

        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <label for="password" style="margin-bottom: 0;">Password</label>
                <a href="#" style="font-size: 0.8125rem; color: var(--primary); text-decoration: none; font-weight: 500;">Forgot password?</a>
            </div>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
            <input type="checkbox" id="remember" name="remember" style="width: 16px; height: 16px; cursor: pointer;">
            <label for="remember" style="margin-bottom: 0; cursor: pointer; font-size: 0.875rem; color: var(--text-muted);">Remember for 30 days</label>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>
    </form>

    <div class="social-auth">
        <span>Or continue with</span>
    </div>

    <div class="social-buttons">
        <button class="social-btn">
            <img src="https://www.svgrepo.com/show/355037/google.svg" alt="Google" width="20" height="20">
        </button>
        <button class="social-btn">
            <img src="https://www.svgrepo.com/show/448234/linkedin.svg" alt="LinkedIn" width="20" height="20">
        </button>
        <button class="social-btn">
            <img src="https://www.svgrepo.com/show/354012/facebook.svg" alt="Facebook" width="20" height="20">
        </button>
    </div>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Create account</a>
    </div>
</div>
@endsection
