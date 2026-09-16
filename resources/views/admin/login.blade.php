<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin — Recruitment</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body{background:#f0f2f5;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
.login-card{background:#fff;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.12);width:100%;max-width:400px;overflow:hidden;border:1px solid #e4e6ea;}
.login-body{padding:28px 32px;}
</style>
</head>
<body>
<div class="login-card">
  <div class="text-center pt-4"><span style="display:inline-flex;width:52px;height:52px;background:#0d6efd;border-radius:14px;align-items:center;justify-content:center;color:#fff;font-size:26px"><i class="bi bi-shield-lock-fill"></i></span>
  <h5 class="font-weight-bold mt-2 mb-0">HRD Login</h5><p class="text-muted small">Recruitment Panel</p></div>
  <div class="login-body">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login.submit') }}">@csrf
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" id="pw" class="form-control" required><small><a href="#" id="togglePw">tampilkan</a></small></div>
      <div class="form-check mb-3"><input type="checkbox" name="remember" class="form-check-input" id="rm"><label class="form-check-label" for="rm">Ingat saya</label></div>
      <button class="btn btn-primary btn-block font-weight-bold"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
    </form>
    <p class="text-muted small text-center mt-3">Default: admin@recruitment.local / admin123 — segera ganti!</p>
  </div>
</div>
<script>document.getElementById('togglePw').onclick=function(e){e.preventDefault();var i=document.getElementById('pw');i.type=i.type==='password'?'text':'password';};</script>
</body>
</html>
