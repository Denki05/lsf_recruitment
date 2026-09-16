@extends('layouts.admin')
@section('title', 'Detail Lamaran')
@section('content')
<div class="mb-2"><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="row">
  <div class="col-md-7 mb-2"><div class="content-card p-2" style="height:100%">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <h5 class="font-weight-bold mb-0" style="font-size:15px">{{ $applicant->nama_lengkap }}</h5>
      <span class="badge badge-primary">{{ $applicant->status }}</span>
    </div>
    <div class="mb-2" style="font-size:12px;color:var(--muted)"><i class="bi bi-briefcase"></i> {{ $applicant->position->full_title ?? '-' }} &nbsp;·&nbsp; <i class="bi bi-clock"></i> {{ $applicant->created_at->format('d M Y H:i') }}</div>
    <div class="row" style="font-size:12.5px">
      <div class="col-6 mb-1"><span style="color:var(--muted)">JK</span><br><strong>{{ $applicant->jenis_kelamin }}</strong></div>
      <div class="col-6 mb-1"><span style="color:var(--muted)">HP / WA</span><br><strong>{{ $applicant->no_hp }}</strong></div>
      <div class="col-6 mb-1"><span style="color:var(--muted)">Domisili</span><br><strong>{{ $applicant->domisili ?: '-' }}</strong></div>
      <div class="col-6 mb-1"><span style="color:var(--muted)">Email</span><br><strong>{{ $applicant->email ?: '-' }}</strong></div>
      <div class="col-6 mb-1"><span style="color:var(--muted)">SIM</span><br><strong>{{ $applicant->sim ?: '-' }}</strong></div>
      <div class="col-6 mb-1"><span style="color:var(--muted)">Sosmed</span><br><strong>{{ $applicant->sosmed ?: '-' }}</strong></div>
    </div>
  </div></div>
  <div class="col-md-5 mb-2"><div class="content-card p-2" style="height:100%">
    <h6 class="font-weight-bold">Berkas</h6>
    <div class="d-flex align-items-center mb-2" style="gap:8px">
      <span style="font-size:26px;color:#c81e3a"><i class="bi bi-file-earmark-text-fill"></i></span>
      <div style="font-size:12.5px;min-width:0"><strong class="d-block text-truncate">{{ $applicant->file_original }}</strong><span style="color:var(--muted)">{{ number_format(($applicant->file_size?:0)/1024,0) }} KB</span></div>
    </div>
    <a href="{{ route('admin.applications.download', $applicant->id) }}" class="btn btn-sm btn-success btn-block"><i class="bi bi-download"></i> Download</a>
    <small style="color:var(--muted)">Tersimpan sebagai:<br>{{ $applicant->download_name }}</small>
    @if(count($zipList))<div class="alert alert-info mt-2 mb-0 py-1" style="font-size:12px"><strong>Isi ZIP:</strong><ul class="mb-0 pl-3">@foreach($zipList as $z)<li>{{ $z }}</li>@endforeach</ul></div>@endif
  </div></div>
</div>
<div class="content-card p-2 mb-2" style="border-left:3px solid var(--brand)">
  <div class="d-flex justify-content-between align-items-center">
    <h6 class="font-weight-bold mb-0">Saran Kecocokan <small style="color:var(--muted)">(bukan keputusan)</small></h6>
    @if($screening['scorable'] && $screening['score'] !== null)<span class="badge badge-{{ $screening['score'] >= 70 ? 'success' : ($screening['score'] >= 40 ? 'warning' : 'danger') }}" style="font-size:13px">{{ $screening['score'] }}%</span>@endif
  </div>
  @if($screening['scorable'] && $screening['score'] !== null)
  <div class="progress mt-1 mb-1" style="height:8px"><div class="progress-bar bg-{{ $screening['score'] >= 70 ? 'success' : ($screening['score'] >= 40 ? 'warning' : 'danger') }}" style="width:{{ $screening['score'] }}%"></div></div>
  @if(count($screening['matched']))<div style="font-size:12px"><span style="color:var(--muted)">Cocok:</span> @foreach($screening['matched'] as $m)<span class="badge badge-success" title="bobot {{ $m['weight'] }}">{{ $m['label'] }} ×{{ $m['weight'] }}</span> @endforeach</div>@endif
  @if(count($screening['missing']))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Hilang:</span> @foreach($screening['missing'] as $m)<span class="badge badge-light border" title="bobot {{ $m['weight'] }}">{{ $m['label'] }} ×{{ $m['weight'] }}</span> @endforeach</div>@endif
  @endif
  @if(!empty($screening['note']))<div style="font-size:12px;color:var(--muted)" class="mt-1"><i class="bi bi-info-circle"></i> {{ $screening['note'] }}</div>@endif
</div>
<div class="content-card p-2 mb-2" style="border-left:3px solid #6f42c1">
  <div class="d-flex justify-content-between align-items-center">
    <h6 class="font-weight-bold mb-0"><i class="bi bi-stars"></i> Evaluasi AI <small style="color:var(--muted)">(OpenRouter, saran — bukan keputusan)</small></h6>
    @if(!is_null($applicant->ai_score))<span class="badge badge-{{ $applicant->ai_score >= 70 ? 'success' : ($applicant->ai_score >= 40 ? 'warning' : 'danger') }}" style="font-size:13px">{{ $applicant->ai_score }}%</span>@endif
  </div>
  @if(!is_null($applicant->ai_score))
  <div style="font-size:12.5px" class="mt-1">{{ $applicant->ai_summary }}</div>
  @php($str = json_decode($applicant->ai_strengths ?: '[]', true) ?: [])
  @php($gaps = json_decode($applicant->ai_gaps ?: '[]', true) ?: [])
  @if(count($str))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Plus:</span> @foreach($str as $s)<span class="badge badge-success">{{ $s }}</span> @endforeach</div>@endif
  @if(count($gaps))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Minus:</span> @foreach($gaps as $g)<span class="badge badge-light border">{{ $g }}</span> @endforeach</div>@endif
  <div style="font-size:11px;color:var(--muted)" class="mt-1">Dievaluasi {{ $applicant->ai_evaluated_at ? $applicant->ai_evaluated_at->format('d M Y H:i') : '-' }}</div>
  @else
  <div style="font-size:12px;color:var(--muted)" class="mt-1">Belum dievaluasi. Sekali klik, hasil tersimpan dan tidak dihitung ulang.</div>
  @endif
  <form method="POST" action="{{ route('admin.applications.ai', $applicant->id) }}" class="mt-1">@csrf<button class="btn btn-sm btn-outline-primary"><i class="bi bi-stars"></i> {{ is_null($applicant->ai_score) ? 'Jalankan Evaluasi AI' : 'Evaluasi Ulang' }}</button></form>
</div>
<div class="content-card p-2 mb-2">
  <form method="POST" action="{{ route('admin.applications.status', $applicant->id) }}">@csrf
  <div class="form-row align-items-end">
    <div class="form-group col-md-3 mb-0"><label>Ubah Status</label><select name="status" class="form-control form-control-sm">@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ $applicant->status==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="form-group col-md-7 mb-0"><label>Catatan Admin</label><input name="catatan_admin" class="form-control form-control-sm" value="{{ $applicant->catatan_admin }}" placeholder="cth. Cocok, panggil interview Senin"></div>
    <div class="form-group col-md-2 mb-0"><button class="btn btn-sm btn-primary btn-block">Simpan</button></div>
  </div>
  </form>
</div>
<div class="content-card p-2">
  <form method="POST" action="{{ route('admin.applications.destroy', $applicant->id) }}" onsubmit="return confirm('Hapus data lamaran ini beserta file-nya?')">@csrf @method('DELETE')
    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus Lamaran</button>
  </form>
</div>
@endsection
