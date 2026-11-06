@extends('layouts.app')
@section('title', 'Login - e-Arsip KSP')
@section('content')
<div class="card" style="max-width: 380px; margin: 60px auto;">
    <h2>Login e-Arsip KSP</h2>
    <form method="POST" action="/login">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        @error('email')
            <p class="error">{{ $message }}</p>
        @enderror
        <button type="submit">Masuk</button>
    </form>
</div>
@endsection