@extends('layouts.admin')
@section('title', 'Banding Kandidat')
@section('content')
<div class="mb-2"><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="content-card p-2">
  <h6 class="font-weight-bold">Banding {{ count($data) }} Kandidat <small style="color:var(--muted)">(urut skor keyword)</small></h6>
  <div class="table-responsive"><table class="table table-sm table-hover">
    <tr><th></th>@foreach($data as $d)<th><a href="{{ route('admin.applications.show', $d['applicant']->id) }}">{{ $d['applicant']->nama_lengkap }}</a></th>@endforeach</tr>
    <tr><th>Posisi</th>@foreach($data as $d)<td><small>{{ $d['applicant']->position->full_title ?? '-' }}</small></td>@endforeach</tr>
    <tr><th>Status</th>@foreach($data as $d)<td><span class="badge badge-info badge-status">{{ $d['applicant']->status }}</span></td>@endforeach</tr>
    <tr><th>Skor keyword</th>@foreach($data as $d)<td><strong>{{ is_null($d['skor']) ? '-' : $d['skor'] . '%' }}</strong></td>@endforeach</tr>
    @if(config('recruitment.ai_enabled'))<tr><th>Skor AI</th>@foreach($data as $d)<td><strong>{{ is_null($d['applicant']->ai_score) ? '-' : $d['applicant']->ai_score . '%' }}</strong></td>@endforeach</tr>@endif
    <tr><th>HP</th>@foreach($data as $d)<td><small>{{ $d['applicant']->no_hp }}</small></td>@endforeach</tr>
    <tr><th>Domisili</th>@foreach($data as $d)<td><small>{{ $d['applicant']->domisili ?: '-' }}</small></td>@endforeach</tr>
    <tr><th>SIM</th>@foreach($data as $d)<td><small>{{ $d['applicant']->sim ?: '-' }}</small></td>@endforeach</tr>
    <tr><th>Pengalaman</th>@foreach($data as $d)<td><small>{{ \Illuminate\Support\Str::limit($d['applicant']->pengalaman_kerja ?: '-', 160) }}</small></td>@endforeach</tr>
    <tr><th>Masuk</th>@foreach($data as $d)<td><small>{{ $d['applicant']->created_at->format('d/m/Y') }}</small></td>@endforeach</tr>
  </table></div>
</div>
@endsection
