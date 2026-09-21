<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin Recruitment | LSF')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
:root{--brand:#0f4c81;--brand-dark:#0b3a63;--bg:#eef1f5;--card:#fff;--border:#e3e8ef;--text:#1c2733;--muted:#6b7a8d;}
body{background:var(--bg)!important;color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:13.5px;}
.topnav{position:sticky;top:0;z-index:1000;background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15);}
.topnav .container{display:flex;align-items:center;gap:8px;padding-top:8px;padding-bottom:8px;flex-wrap:wrap;}
.brand{font-weight:800;font-size:15px;margin-right:8px;white-space:nowrap;}
.brand small{font-weight:400;opacity:.75;}
.nav-link-btn{color:#d7e5f3;text-decoration:none;font-size:12.5px;font-weight:600;padding:6px 10px;border-radius:8px;white-space:nowrap;}
.nav-link-btn:hover{color:#fff;background:rgba(255,255,255,.15);text-decoration:none;}
.nav-link-btn.active{background:#fff;color:var(--brand-dark);}
.content-card{background:var(--card);border:1px solid var(--border);border-radius:10px;box-shadow:0 1px 3px rgba(16,38,76,.06);}
.content-card h6{font-size:13px;margin-bottom:8px;}
.badge-status{font-size:10.5px;}
.table{font-size:12.5px;margin-bottom:0;}
.table th{border-top:none;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);padding:.45rem .5rem;}
.table td{padding:.45rem .5rem;vertical-align:middle;}
.form-control-sm{font-size:12.5px;}
.btn-sm{font-size:12px;}
.stat{border:1px solid var(--border);border-radius:8px;padding:6px 8px;background:#fff;display:flex;align-items:center;gap:8px;text-align:left;}
.stat .h4{margin-bottom:0;font-size:19px;line-height:1.1;}
.stat small{color:var(--muted);font-size:10.5px;}
.stat-icon{width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.si-total{background:#e8eef5;color:#0f4c81;} .si-baru{background:#e0f0ff;color:#0d6efd;}
.si-proses{background:#fff4d6;color:#b8860b;} .si-terima{background:#dcf5e5;color:#146c43;}
.si-tolak{background:#fbdfe3;color:#c81e3a;}
a.stat-link:hover{text-decoration:none;} a.stat-link:hover .stat{border-color:var(--brand);box-shadow:0 1px 5px rgba(15,76,129,.18);}
.alert{font-size:12.5px;padding:.5rem .75rem;}
@media(min-width:768px){.container{max-width:100%;}}
@media(min-width:992px){.container{max-width:100%;}}
.admin-wrap{padding-left:14px;padding-right:14px;}
</style>
</head>
<body>
<nav class="topnav"><div class="container admin-wrap">
  <span class="brand"><i class="bi bi-briefcase-fill"></i> HRD <small>Recruitment | LSF</small></span>
  <a class="nav-link-btn {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a class="nav-link-btn {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}"><i class="bi bi-inbox"></i> Lamaran</a>
  <a class="nav-link-btn {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}" href="{{ route('admin.positions.index') }}"><i class="bi bi-megaphone"></i> Loker</a>
  @if(Auth::check() && Auth::user()->isSuperadmin())
  <a class="nav-link-btn {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}"><i class="bi bi-diagram-3"></i> Cabang</a>
  <a class="nav-link-btn {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Login</a>
  @endif
  <a class="nav-link-btn" href="{{ route('jobs.index') }}" target="_blank"><i class="bi bi-eye"></i> Situs</a>
  @php($myBranches = \App\Services\BranchAccess::accessibleBranches())
  @if(Auth::check() && $myBranches->count() > 1 || (Auth::check() && Auth::user()->isSuperadmin()))
  <form method="POST" action="{{ route('admin.branch.switch') }}" class="d-inline ml-1">@csrf
    <select name="branch_id" class="form-control form-control-sm d-inline" style="width:auto;display:inline-block;font-size:12px" onchange="this.form.submit()" title="Cabang aktif">
      <option value="">Semua cabang</option>
      @foreach($myBranches as $b)<option value="{{ $b->id }}" {{ (int) session('current_branch_id') === (int) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach
    </select>
  </form>
  @elseif(Auth::check() && $myBranches->count() === 1)
  <span class="badge badge-light ml-1" style="font-size:11px"><i class="bi bi-geo-alt"></i> {{ $myBranches->first()->name }}</span>
  @endif
  <a class="nav-link-btn" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="bi bi-box-arrow-right"></i> Keluar</a>
  <span class="ml-auto d-none d-md-inline" style="font-size:12px;opacity:.85">{{ Auth::user()->name ?? '' }} · {{ Auth::check() ? Auth::user()->roleLabel() : '' }}</span>
</div></nav>
<div class="container admin-wrap py-2">
  <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf</form>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

  @yield('content')
  <p class="text-center small mt-3 mb-1" style="color:var(--muted)">Recruitment | LSF &copy; {{ date('Y') }}</p>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
