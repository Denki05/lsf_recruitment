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
:root{
  --brand:#0f4c81;
  --brand-dark:#0b3a63;
  --brand-soft:#eef4fa;
  --bg:#f2f5f9;
  --card:#fff;
  --border:#e2e8f0;
  --text:#152238;
  --muted:#64748b;
  --nav-h:56px;
}
html{scroll-padding-top:calc(var(--nav-h) + 16px);}
body{background:var(--bg)!important;color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:13.5px;}
a{color:var(--brand);}
a:hover{color:var(--brand-dark);}

/* ===== Topbar ===== */
.admin-topbar{position:sticky;top:0;z-index:1030;padding:0;background:linear-gradient(115deg,#0f4c81 0%,#0b3a63 58%,#092d4f 100%);box-shadow:0 8px 24px rgba(9,45,79,.16);}
.admin-topbar-inner{display:flex;flex-wrap:wrap;align-items:center;width:100%;max-width:1440px;margin:0 auto;padding:9px 24px;gap:10px;}
.admin-brand{display:flex;align-items:center;gap:10px;color:#fff!important;text-decoration:none!important;flex-shrink:0;margin-right:16px;}
.admin-brand-mark{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);font-size:17px;box-shadow:inset 0 1px 0 rgba(255,255,255,.12);}
.admin-brand-text{font-size:14.5px;font-weight:800;line-height:1.05;letter-spacing:.2px;white-space:nowrap;}
.admin-brand-text small{display:block;margin-top:2px;font-size:9px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.55);}
.admin-nav{gap:2px;align-items:center;}
.admin-nav-link{display:inline-flex;align-items:center;gap:6px;padding:8px 10px;border-radius:7px;color:rgba(255,255,255,.7)!important;font-size:12.5px;font-weight:600;text-decoration:none!important;white-space:nowrap;line-height:1.2;transition:background .15s,color .15s;}
.admin-nav-link i{font-size:14px;opacity:.9;}
.admin-nav-link:hover{color:#fff!important;background:rgba(255,255,255,.09);}
.admin-nav-link.active{color:#fff!important;background:rgba(255,255,255,.15);box-shadow:inset 0 0 0 1px rgba(255,255,255,.18);}
.admin-nav-link:focus-visible,.admin-tool-btn:focus-visible,.admin-user-btn:focus-visible{outline:2px solid rgba(255,255,255,.65);outline-offset:2px;}
.admin-tools{margin-left:auto;display:flex;align-items:center;gap:8px;flex-shrink:0;}

/* Switcher cabang */
.branch-switch{display:flex;align-items:center;gap:5px;height:34px;padding:0 8px;margin:0;border-radius:8px;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);color:rgba(255,255,255,.65);transition:background .15s;}
.branch-switch:hover{background:rgba(255,255,255,.13);}
.branch-switch .branch-ico{font-size:13px;}
.branch-switch select{appearance:none;-webkit-appearance:none;border:0!important;background:transparent!important;color:#fff;font-size:12px;font-weight:600;height:100%;max-width:150px;padding:0 2px;cursor:pointer;outline:none;}
.branch-switch select:focus{box-shadow:none;}
.branch-switch select option{color:#152238;background:#fff;}
.branch-switch .branch-caret{font-size:8px;opacity:.6;margin-left:-2px;pointer-events:none;}
.branch-static{display:inline-flex;align-items:center;gap:5px;height:34px;padding:0 10px;border-radius:8px;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);color:rgba(255,255,255,.8);font-size:11.5px;font-weight:600;white-space:nowrap;}

/* Tombol utilitas & pengguna */
.admin-tool-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:34px;padding:0 10px;border-radius:8px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.06);color:rgba(255,255,255,.82)!important;font-size:12px;font-weight:600;text-decoration:none!important;transition:background .15s,color .15s;}
.admin-tool-btn:hover{background:rgba(255,255,255,.14);color:#fff!important;}
.admin-tool-label{display:none;}
.admin-user{position:relative;}
.admin-user-btn{display:flex;align-items:center;gap:8px;height:38px;padding:3px 7px 3px 4px;border:1px solid transparent;border-radius:9px;background:transparent;color:#fff;transition:background .15s,border-color .15s;}
.admin-user-btn:hover,.admin-user-btn:focus{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.12);outline:none;}
.admin-avatar{width:30px;height:30px;border-radius:50%;background:#fff;color:var(--brand-dark);font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 1px 2px rgba(0,0,0,.12);}
.admin-user-meta{display:none;flex-direction:column;text-align:left;line-height:1.15;max-width:140px;}
.admin-user-meta strong{font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-user-meta small{font-size:9.5px;color:rgba(255,255,255,.58);font-weight:600;white-space:nowrap;}
.admin-user-btn>.bi-chevron-down{font-size:9px;opacity:.6;margin-left:1px;}
.admin-user-menu{border:1px solid var(--border);border-radius:12px;box-shadow:0 16px 40px rgba(15,23,42,.18);padding:6px;margin-top:10px;min-width:240px;}
.admin-user-menu .dropdown-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;font-size:12.5px;font-weight:600;color:#334155;}
.admin-user-menu .dropdown-item:hover{background:#f1f5f9;color:var(--brand);}
.admin-user-menu .dropdown-item.text-danger{color:#dc2626!important;}
.admin-user-menu .dropdown-item.text-danger:hover{background:#fef2f2;}
.admin-menu-head{padding:9px 10px 10px;border-bottom:1px solid var(--border);margin-bottom:5px;}
.admin-menu-head strong{display:block;font-size:13px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-menu-head small{display:block;font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.admin-menu-head .badges{margin-top:6px;display:flex;gap:4px;flex-wrap:wrap;}
.admin-menu-head .badge{font-size:9.5px;font-weight:600;background:#f1f5f9;color:#475569;border:1px solid var(--border);padding:3px 6px;border-radius:5px;}
.admin-toggler{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;margin-left:auto;border:1px solid rgba(255,255,255,.2);border-radius:9px;background:rgba(255,255,255,.08);color:#fff;font-size:19px;cursor:pointer;}
.admin-toggler:focus{outline:none;box-shadow:0 0 0 3px rgba(255,255,255,.12);}
@media(min-width:1400px){
  .admin-tool-label{display:inline;}
  .admin-user-meta{display:flex;}
}
@media(min-width:1200px){
  .admin-brand{margin-right:12px;}
  .admin-nav-link{padding:8px 9px;font-size:12px;}
}
@media(max-width:1199.98px){
  .admin-topbar-inner{padding:8px 16px;}
  .admin-topbar .navbar-collapse{width:100%;flex-basis:100%;padding:10px 0 6px;margin-top:8px;border-top:1px solid rgba(255,255,255,.12);}
  .admin-nav{width:100%;align-items:stretch;gap:2px;}
  .admin-nav-link{display:flex;width:100%;padding:9px 10px;}
  .admin-tools{width:100%;margin-left:0;padding-top:10px;margin-top:8px;border-top:1px dashed rgba(255,255,255,.14);flex-wrap:wrap;}
  .admin-user-menu{min-width:220px;}
  .branch-switch,.branch-static{flex:1;min-width:170px;}
  .branch-switch select{max-width:none;flex:1;}
  .admin-user{margin-left:auto;}
}

/* ===== Kerangka konten ===== */
.admin-shell{width:100%;max-width:1440px;margin:0 auto;padding:22px 24px 8px;}
@media(max-width:767.98px){.admin-shell{padding:16px 16px 8px;}}
.page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px;}
.page-title{font-size:20px;font-weight:800;letter-spacing:-.25px;color:var(--text);margin:0;line-height:1.25;}
.page-sub{font-size:12.5px;color:var(--muted);margin:3px 0 0;}
.page-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.content-card{background:var(--card);border:1px solid var(--border);border-radius:12px;box-shadow:0 1px 2px rgba(15,23,42,.04);}
.content-card h6{font-size:13px;margin-bottom:8px;}
.content-card > .table-responsive:first-child table thead th:first-child{border-top-left-radius:12px;}
.content-card > .table-responsive:first-child table thead th:last-child{border-top-right-radius:12px;}

/* ===== Tabel ===== */
.table{font-size:12.5px;margin-bottom:0;color:#334155;}
.table th{border-top:none;border-bottom:1px solid var(--border);background:#f8fafc;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b;padding:10px 12px;white-space:nowrap;}
.table td{padding:10px 12px;vertical-align:middle;border-top:1px solid #eef2f7;}
.table-hover tbody tr:hover{background:#f8fafc;}
.table-sm th,.table-sm td{padding:8px 10px;}
.badge-status{font-size:10.5px;font-weight:600;padding:3px 7px;border-radius:5px;}
.badge-soft{background:var(--brand-soft);color:var(--brand);font-weight:600;}
.table-actions{display:inline-flex;align-items:center;gap:4px;flex-wrap:nowrap;}
.table-actions .btn{padding:3px 8px;font-size:11.5px;border-radius:6px;}
.empty-state{display:flex;flex-direction:column;align-items:center;gap:2px;padding:34px 12px;text-align:center;color:#94a3b8;}
.empty-state i{font-size:26px;color:#cbd5e1;margin-bottom:4px;}
.empty-state strong{font-size:13px;color:#64748b;}
.empty-state span{font-size:11.5px;}

/* ===== Tombol, form, badge, pagination ===== */
.btn{font-weight:600;border-radius:7px;}
.btn-sm{font-size:12px;}
.btn-primary{background:var(--brand);border-color:var(--brand);}
.btn-primary:hover,.btn-primary:focus,.btn-primary:active{background:var(--brand-dark)!important;border-color:var(--brand-dark)!important;box-shadow:none!important;}
.btn-outline-primary{color:var(--brand);border-color:#b9cee0;}
.btn-outline-primary:hover{background:var(--brand);border-color:var(--brand);}
.badge-primary{background:var(--brand);}
.page-link{color:var(--brand);}
.page-item.active .page-link{background:var(--brand);border-color:var(--brand);}
.pagination{flex-wrap:wrap;justify-content:center;}
label{font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;}
.form-control{font-size:12.5px;border-color:#cbd5e1;border-radius:7px;color:#334155;}
.form-control-sm{font-size:12px;}
.form-control:focus{border-color:var(--brand);box-shadow:0 0 0 .15rem rgba(15,76,129,.12);}
.input-group-text{font-size:12px;background:#f8fafc;border-color:#cbd5e1;color:#64748b;}

/* ===== Flash message ===== */
.admin-flashes{margin-bottom:14px;}
.admin-alert{display:flex;align-items:flex-start;gap:9px;font-size:12.5px;padding:10px 13px;border-radius:9px;border:1px solid transparent;margin-bottom:8px;box-shadow:0 1px 2px rgba(15,23,42,.03);transition:opacity .25s,transform .25s;}
.admin-alert>i{font-size:14px;line-height:1.2;margin-top:1px;}
.admin-alert>span{flex:1;}
.admin-alert.alert-dismissible{padding-right:38px;}
.admin-alert .close{font-size:17px;line-height:1;padding:0;margin-left:6px;opacity:.55;text-shadow:none;}
.admin-alert.alert-success{background:#ecfdf5;border-color:#bbf7d0;color:#166534;}
.admin-alert.alert-danger{background:#fef2f2;border-color:#fecaca;color:#991b1b;}
.admin-alert.is-hiding{opacity:0;transform:translateY(-3px);}
.admin-footer{padding:22px 0 8px;text-align:center;font-size:11.5px;color:#94a3b8;}
</style>
</head>
<body>
@php
  $authUser = Auth::user();
  $myBranches = \App\Services\BranchAccess::accessibleBranches();
  $currentBranch = \App\Services\BranchAccess::currentBranch();
  $branchLabel = $currentBranch ? $currentBranch->name : ($myBranches->count() === 1 ? $myBranches->first()->name : 'Semua cabang');
  $userInitial = mb_strtoupper(mb_substr($authUser->name ?? 'H', 0, 1));
@endphp
<nav class="navbar navbar-expand-xl admin-topbar">
  <div class="admin-topbar-inner">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}">
      <span class="admin-brand-mark"><i class="bi bi-briefcase-fill"></i></span>
      <span class="admin-brand-text">LSF Recruitment<small>HRD Panel</small></span>
    </a>
    <button class="navbar-toggler admin-toggler" type="button" data-toggle="collapse" data-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Buka menu navigasi">
      <i class="bi bi-list"></i>
    </button>

    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav admin-nav">
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}" @if(request()->routeIs('admin.applications.*')) aria-current="page" @endif><i class="bi bi-inbox"></i> Lamaran</a>
        </li>
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}" href="{{ route('admin.positions.index') }}" @if(request()->routeIs('admin.positions.*')) aria-current="page" @endif><i class="bi bi-megaphone"></i> Loker</a>
        </li>
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.shortlinks.*') ? 'active' : '' }}" href="{{ route('admin.shortlinks.index') }}" @if(request()->routeIs('admin.shortlinks.*')) aria-current="page" @endif><i class="bi bi-link-45deg"></i> Link Pendek</a>
        </li>
        @if($authUser && $authUser->isSuperadmin())
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}" href="{{ route('admin.branches.index') }}" @if(request()->routeIs('admin.branches.*')) aria-current="page" @endif><i class="bi bi-diagram-3"></i> Cabang</a>
        </li>
        <li class="nav-item">
          <a class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" @if(request()->routeIs('admin.users.*')) aria-current="page" @endif><i class="bi bi-people"></i> Pengguna</a>
        </li>
        @endif
      </ul>

      <div class="admin-tools">
        @if($myBranches->count() > 1 || ($authUser && $authUser->isSuperadmin()))
          <form method="POST" action="{{ route('admin.branch.switch') }}" class="branch-switch" title="Cabang aktif">
            @csrf
            <i class="bi bi-geo-alt branch-ico"></i>
            <select name="branch_id" onchange="this.form.submit()" aria-label="Pilih cabang aktif">
              <option value="">Semua cabang</option>
              @foreach($myBranches as $b)
                <option value="{{ $b->id }}" {{ (int) session('current_branch_id') === (int) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
            <i class="bi bi-chevron-down branch-caret"></i>
          </form>
        @elseif($myBranches->count() === 1)
          <span class="branch-static" title="Cabang aktif"><i class="bi bi-geo-alt"></i> {{ $myBranches->first()->name }}</span>
        @endif

        <a class="admin-tool-btn" href="{{ route('jobs.index') }}" target="_blank" rel="noopener" title="Buka situs publik di tab baru">
          <i class="bi bi-box-arrow-up-right"></i><span class="admin-tool-label">Lihat Situs</span>
        </a>

        @if($authUser)
        <div class="dropdown admin-user">
          <button class="admin-user-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menu pengguna">
            <span class="admin-avatar">{{ $userInitial }}</span>
            <span class="admin-user-meta">
              <strong>{{ $authUser->name }}</strong>
              <small>{{ $authUser->roleLabel() }}</small>
            </span>
            <i class="bi bi-chevron-down"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-right admin-user-menu">
            <div class="admin-menu-head">
              <strong>{{ $authUser->name }}</strong>
              <small>{{ $authUser->email }}</small>
              <div class="badges">
                <span class="badge">{{ $authUser->roleLabel() }}</span>
                <span class="badge">{{ $branchLabel }}</span>
              </div>
            </div>
            <a class="dropdown-item" href="{{ route('jobs.index') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Lihat situs publik</a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('admin.logout') }}" class="m-0">@csrf
              <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</button>
            </form>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</nav>

<main class="admin-shell">
  @if(session('success') || $errors->any())
  <div class="admin-flashes">
    @if(session('success'))
    <div class="alert alert-success admin-alert alert-dismissible fade show" role="alert" data-auto-dismiss="5000">
      <i class="bi bi-check-circle-fill"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger admin-alert alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span>{{ $errors->first() }}</span>
      <button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
    </div>
    @endif
  </div>
  @endif

  @yield('content')
  <footer class="admin-footer">Recruitment | LSF &copy; {{ date('Y') }} &middot; Panel HRD</footer>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  document.querySelectorAll('[data-auto-dismiss]').forEach(function(el){
    var delay = parseInt(el.getAttribute('data-auto-dismiss'), 10) || 5000;
    window.setTimeout(function(){
      if(!document.body.contains(el)) return;
      el.classList.add('is-hiding');
      window.setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 260);
    }, delay);
  });
})();
</script>
@stack('scripts')
</body>
</html>
