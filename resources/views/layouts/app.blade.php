<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'e-Arsip KSP')</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; color: #222; }
        nav { background: #1f3864; color: #fff; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; margin-right: 16px; text-decoration: none; font-weight: 500; }
        nav a:hover { text-decoration: underline; }
        nav form { display: flex; align-items: center; gap: 12px; margin: 0; }
        main { max-width: 1180px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.1); margin-bottom: 20px; }
        .error { color: #b00020; }
        input, select, textarea { width: 100%; padding: 8px 10px; margin: 4px 0 12px; box-sizing: border-box; border: 1px solid #ccd2dc; border-radius: 4px; font-size: 14px; }
        input:focus, select:focus { outline: none; border-color: #1f3864; box-shadow: 0 0 0 2px rgba(31,56,100,.15); }
        button { padding: 8px 16px; cursor: pointer; border-radius: 4px; border: 1px solid transparent; font-size: 14px; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { border-bottom: 1px solid #e3e7ee; padding: 10px 8px; text-align: left; vertical-align: top; }
        th { background: #eef3fa; font-weight: 600; color: #2d3748; }
        .bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px; }
        .btn { display: inline-block; background: #1f3864; color: #fff; padding: 8px 14px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .btn:hover { background: #162947; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-success { background: #2e7d32; color: #fff; }
        .btn-success:hover { background: #1b5e20; }
        .btn-danger { background: #c62828; color: #fff; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .ok { border-left: 4px solid #2e7d32; margin-bottom: 12px; }

        /* Badge Styles */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-vital { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .badge-non-vital { background: #f1f5f9; color: #475569; }
        .badge-aktif { background: #dcfce7; color: #166534; }
        .badge-inaktif { background: #fef3c7; color: #92400e; }
        .badge-musnah { background: #fee2e2; color: #991b1b; }
        .badge-permanen { background: #e0e7ff; color: #3730a3; }
        .badge-macet { background: #dc2626; color: #fff; }
        .badge-lunas { background: #16a34a; color: #fff; }
        .badge-berjalan { background: #2563eb; color: #fff; }
        .badge-pdf { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }

        /* Grid Utilities */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }

        /* Filter Box */
        .filter-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .filter-grid label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 2px; display: block; }
        .filter-grid input, .filter-grid select { margin: 0; }

        /* Info Item */
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 500; }
        .info-val { font-weight: 600; text-align: right; }

        /* Rack Visualizer */
        .rack-frame { background: #334155; border: 4px solid #1e293b; border-radius: 8px; padding: 16px; color: #f8fafc; box-shadow: inset 0 2px 4px rgba(0,0,0,0.4); }
        .rack-tier { background: #475569; border-bottom: 6px solid #1e293b; padding: 12px 8px; margin-bottom: 8px; border-radius: 4px; display: flex; align-items: center; gap: 8px; overflow-x: auto; }
        .rack-tier-label { width: 70px; font-weight: bold; font-size: 12px; color: #cbd5e1; text-transform: uppercase; }
        .rack-boxes { display: flex; gap: 8px; flex-wrap: wrap; flex: 1; }
        .rack-box { background: #64748b; border: 1px solid #94a3b8; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: 600; color: #fff; text-align: center; min-width: 60px; }
        .rack-box-active { background: #f59e0b; border: 2px solid #fff; color: #000; box-shadow: 0 0 10px #f59e0b; animation: pulse 2s infinite; font-weight: bold; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
    </style>
</head>
<body>
@auth
    <nav>
        <div>
            <strong>e-Arsip KSP</strong> &nbsp;
            <a href="/dashboard">Dashboard</a>
            <a href="/arsip">Arsip (Pencarian & Registrasi)</a>
            <a href="/klasifikasi">Klasifikasi</a>
            <a href="/lokasi">Lokasi Depo</a>
            @if (auth()->user()->role === 'pimpinan')
                <a href="/approval">Approval</a>
            @endif
        </div>
        <form method="POST" action="/logout">
            @csrf
            <span>{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
            <button type="submit" class="btn-secondary">Keluar</button>
        </form>
    </nav>
@endauth
<main>
    @if (session('success'))
       <div class="card ok">{{ session('success') }}</div>
   @endif
   @if (session('error'))
       <div class="card error" style="border-left: 4px solid #b00020; margin-bottom: 12px;">{{ session('error') }}</div>
   @endif
    @yield('content')
</main>
</body>
</html>
