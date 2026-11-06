@extends('layouts.app')
@section('title', 'Dashboard - e-Arsip KSP')
@section('content')
<div class="card">
    <h2>Selamat datang, {{ auth()->user()->name }}</h2>
    <p>Role: <strong>{{ auth()->user()->role }}</strong></p>
    <ul>
        <li>Total anggota: {{ $totalAnggota }}</li>
        <li>Total arsip: {{ $totalArsip }}</li>
        <li>Pinjaman macet: {{ $totalMacet }}</li>
    </ul>
</div>
@endsection