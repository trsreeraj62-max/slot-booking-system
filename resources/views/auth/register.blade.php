@extends('layouts.app')

@section('title', 'Register - Slot Booking System')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join us and start booking your slots today</p>
    </div>

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Register Now</button>
    </form>

    <div class="social-auth">
        <span>Or sign up with</span>
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
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
</div>
@endsection
