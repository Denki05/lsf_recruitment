<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Recruitment | LSF')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* Palet Recruitment | LSF: biru pekat korporat + aksen hijau/amber */
:root{ --brand:#0f4c81; --brand-dark:#0b3a63; --brand-light:#e8f0f8; }
body{background:#eef1f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
.top-hero{background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;padding:12px 0 26px;}
.top-hero h3{font-size:22px;margin-bottom:0;display:inline;}
.top-hero p{font-size:13px;margin-bottom:0;display:inline;margin-left:10px;color:#cfe0f1;}
.btn-primary{background-color:var(--brand);border-color:var(--brand);}
.btn-primary:hover,.btn-primary:focus{background-color:var(--brand-dark);border-color:var(--brand-dark);}
.badge-primary{background-color:var(--brand);}
a{color:var(--brand);}
.top-hero h3{font-size:22px;margin-bottom:0;display:inline;}
.top-hero p{font-size:13px;margin-bottom:0;display:inline;margin-left:10px;}
.form-card{margin-top:-10px;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:1px solid #e4e6ea;}
.step-dots{display:flex;gap:6px;justify-content:center;margin:0 0 10px;}
.step-dot{width:28px;height:6px;border-radius:6px;background:#dee2e6;}
.step-dot.active{background:var(--brand);}
.stepper{display:flex;gap:4px;margin-bottom:8px;}
.step-item{flex:1;text-align:center;padding:6px 4px;border-radius:10px;background:#f1f3f5;border:1px solid #e4e6ea;font-size:12px;color:#6c757d;}
.step-item .num{display:inline-flex;width:22px;height:22px;border-radius:50%;background:#adb5bd;color:#fff;font-size:12px;font-weight:700;align-items:center;justify-content:center;margin-right:4px;}
.step-item.active{background:var(--brand-light);border-color:var(--brand);color:var(--brand-dark);font-weight:700;}
.step-item.active .num{background:var(--brand);}
.step-item.done{background:#e9f7ef;border-color:#198754;color:#198754;}
.step-item.done .num{background:#198754;}
.step-desc{font-size:12.5px;color:#6c757d;margin-bottom:8px;}
.dropzone{border:2px dashed #adb5bd;border-radius:10px;padding:8px 12px;text-align:center;cursor:pointer;background:#f8f9fa;transition:border-color .2s,background .2s;display:flex;align-items:center;justify-content:center;gap:10px;}
.dropzone:hover,.dropzone.dragover{border-color:var(--brand);background:var(--brand-light);}
.dropzone .dz-icon{font-size:24px;color:var(--brand);flex-shrink:0;}
.dropzone .dz-text{font-size:13.5px;font-weight:600;text-align:left;}
.dropzone .dz-sub{font-size:11.5px;color:#6c757d;text-align:left;}
.step-pane{display:none;}
.step-pane.active{display:block;}
.form-group{margin-bottom:10px;}
label{font-weight:600;font-size:14px;margin-bottom:4px;}
.form-control{height:calc(1.5em + .6rem + 2px);padding:.3rem .6rem;font-size:14px;}
select.form-control{height:calc(1.5em + .6rem + 2px);}
textarea.form-control{height:auto;}
h5.sect{font-size:16px;margin-bottom:2px;}
.required:after{content:" *";color:#dc3545;}
@media(min-width:992px){.container-narrow{max-width:1100px;}}
</style>
</head>
<body>
<div class="top-hero">
  <div class="container container-narrow text-center">
    <h3 class="font-weight-bold mb-1"><i class="bi bi-briefcase-fill"></i> @yield('hero_title', 'Recruitment')</h3>
    <p class="mb-0">@yield('hero_sub', 'Form cepat (< 2 menit). Siapkan CV (PDF/DOC/ZIP, maks 5 MB).')</p>
  </div>
</div>
<div class="container container-narrow pb-1">
  <div class="card form-card">
    <div class="card-body p-3 pt-2">
      @if($errors->any())
      <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
