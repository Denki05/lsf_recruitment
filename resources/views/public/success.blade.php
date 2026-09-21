@extends('layouts.public')
@section('title', 'Lamaran Terkirim | LSF')
@section('hero_title', 'Lamaran Terkirim')
@section('hero_sub', 'Terima kasih telah melamar di LSF.')
@section('content')
<style>
.sukses-wrap{max-width:560px;margin:0 auto;padding:18px 0 8px;}
.sukses-icon{width:84px;height:84px;margin:0 auto 12px;border-radius:50%;background:#e9f7ef;color:#198754;display:flex;align-items:center;justify-content:center;font-size:46px;animation:suksesPop .45s ease-out;}
@keyframes suksesPop{0%{transform:scale(.4);opacity:0}70%{transform:scale(1.08)}100%{transform:scale(1);opacity:1}}
.sukses-ringkas{border:1px solid #e4e6ea;border-radius:12px;background:#f8f9fa;padding:4px 14px;margin:16px 0 20px;}
.sukses-row{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid #e9ecef;font-size:14px;}
.sukses-row:last-child{border-bottom:0;}
.sukses-row .lbl{flex:0 0 90px;color:#6c757d;}
.sukses-row .val{flex:1;font-weight:600;min-width:0;}
.langkah{list-style:none;margin:0;padding:0;text-align:left;}
.langkah li{display:flex;gap:12px;position:relative;padding-bottom:14px;}
.langkah li:last-child{padding-bottom:0;}
.langkah li:not(:last-child):before{content:"";position:absolute;left:13px;top:28px;bottom:0;width:2px;background:#dee2e6;}
.langkah .dot{flex:0 0 28px;height:28px;border-radius:50%;background:#dee2e6;color:#6c757d;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;}
.langkah .done .dot{background:#198754;color:#fff;}
.langkah .now .dot{background:var(--brand);color:#fff;}
.langkah .ttl{font-weight:700;font-size:14px;}
.langkah .sub{font-size:12.5px;color:#6c757d;}
</style>

<div class="sukses-wrap text-center">
  <div class="sukses-icon"><i class="bi bi-check-lg"></i></div>
  <h4 class="font-weight-bold mb-1">Lamaran Berhasil Dikirim!</h4>
  <p class="text-muted mb-0">Terima kasih, <strong>{{ $applicant->nama_lengkap }}</strong>.<br>Lamaran Anda telah kami terima.</p>

  <div class="sukses-ringkas text-left">
    <div class="sukses-row"><span class="lbl">Posisi</span><span class="val">{{ $applicant->position->full_title }}</span></div>
    <div class="sukses-row"><span class="lbl">No HP</span><span class="val">{{ substr($applicant->no_hp, 0, 4) }}••••{{ substr($applicant->no_hp, -3) }}</span></div>
    <div class="sukses-row"><span class="lbl">Berkas</span><span class="val text-break">{{ $applicant->file_original }}</span></div>
    <div class="sukses-row"><span class="lbl">Dikirim</span><span class="val">{{ $applicant->created_at->format('d M Y, H:i') }}</span></div>
  </div>

  <h6 class="text-left font-weight-bold mb-3">Apa selanjutnya?</h6>
  <ul class="langkah mb-3">
    <li class="done">
      <span class="dot"><i class="bi bi-check-lg"></i></span>
      <div><div class="ttl">Lamaran diterima</div><div class="sub">Data dan CV Anda sudah masuk ke sistem kami.</div></div>
    </li>
    <li class="now">
      <span class="dot">2</span>
      <div><div class="ttl">Seleksi berkas oleh tim HRD</div><div class="sub">Tim kami meninjau kesesuaian profil Anda dengan posisi yang dilamar.</div></div>
    </li>
    <li>
      <span class="dot">3</span>
      <div><div class="ttl">Anda dihubungi</div><div class="sub">Jika lolos, HRD akan menghubungi melalui nomor HP di atas.</div></div>
    </li>
  </ul>

  <div class="alert alert-info small text-left py-2">
    <i class="bi bi-info-circle"></i> Pastikan nomor HP Anda aktif. Anda tidak perlu mengirim ulang lamaran untuk posisi yang sama.
  </div>

  <a href="{{ route('jobs.index') }}" class="btn btn-primary btn-block"><i class="bi bi-search"></i> Lihat Loker Lain</a>
</div>
@endsection