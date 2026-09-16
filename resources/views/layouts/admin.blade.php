<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin Recruitment')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{--bg:#212529;--surface:#2b2f33;--border:#343a40;--primary:#0d6efd;}
body{background:var(--bg)!important;color:#fff;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
.header-bar{position:sticky;top:0;z-index:1000;background:var(--bg);padding:8px 12px;border-bottom:1px solid var(--border);}
.nav-grid{display:flex;flex-wrap:wrap;gap:6px;}
.nav-button{background:var(--surface);border:1px solid var(--border);color:#fff;padding:6px 14px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:700;}
.nav-button:hover{color:#fff;background:#343a40;text-decoration:none;}
.nav-button.active{background:var(--primary);border-color:var(--primary);}
.content-card{background:#fff;color:#212529;border-radius:12px;}
.badge-status{font-size:11px;}
@media(min-width:768px){.container{max-width:720px;}}
@media(min-width:992px){.container{max-width:992px;}}
.table{font-size:13px;}
</style>
</head>
<body>
<div class="container" style="min-height:100vh">
  <div class="header-bar d-flex justify-content-between align-items-center">
    <strong><i class="bi bi-briefcase-fill"></i> HRD Panel</strong>
    <small class="text-muted">{{ Auth::user()->name ?? '' }}</small>
  </div>
  <div class="nav-grid my-2">
    <a class="nav-button {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a class="nav-button {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}"><i class="bi bi-inbox"></i> Lamaran</a>
    <a class="nav-button {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}" href="{{ route('admin.positions.index') }}"><i class="bi bi-megaphone"></i> Loker</a>
    <a class="nav-button" href="{{ route('jobs.index') }}" target="_blank"><i class="bi bi-eye"></i> Lihat Loker</a>
    <a class="nav-button" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
  <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf</form>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

  @yield('content')
  <p class="text-center text-muted small mt-4">Recruitment &copy; {{ date('Y') }}</p>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
