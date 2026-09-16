@extends('layouts.admin')
@section('title', 'Detail Lamaran')
@section('content')
<div class="content-card p-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="font-weight-bold mb-0">{{ $applicant->nama_lengkap }} <small class="text-muted">({{ $applicant->nama_panggilan }})</small></h5>
    <span class="badge badge-primary">{{ $applicant->status }}</span>
  </div>
  <p class="text-muted">{{ $applicant->position->full_title ?? '-' }} — masuk {{ $applicant->created_at->format('d M Y H:i') }}</p>
  <div class="row">
    <div class="col-md-6"><table class="table table-sm">
      <tr><th width="35%">TTL</th><td>{{ $applicant->tempat_lahir }}, {{ date('d M Y', strtotime($applicant->tanggal_lahir)) }}</td></tr>
      <tr><th>JK</th><td>{{ $applicant->jenis_kelamin }}</td></tr>
      <tr><th>KTP</th><td>{{ $applicant->no_ktp }}</td></tr>
      <tr><th>Alamat KTP</th><td>{{ $applicant->alamat_ktp }}</td></tr>
      <tr><th>Alamat Now</th><td>{{ $applicant->alamat_sekarang ?: '-' }}</td></tr>
    </table></div>
    <div class="col-md-6"><table class="table table-sm">
      <tr><th width="35%">HP</th><td>{{ $applicant->no_hp }}</td></tr>
      <tr><th>Sosmed</th><td>{{ $applicant->sosmed ?: '-' }}</td></tr>
      <tr><th>Nikah / Agama</th><td>{{ $applicant->status_pernikahan }} / {{ $applicant->agama }}</td></tr>
      <tr><th>Kendaraan / SIM</th><td>{{ $applicant->kendaraan }} / {{ $applicant->sim }}</td></tr>
      <tr><th>Berkas</th><td>{{ $applicant->file_original }} ({{ number_format(($applicant->file_size?:0)/1024,0) }} KB)<br><a href="{{ route('admin.applications.download', $applicant->id) }}" class="btn btn-sm btn-success mt-1"><i class="bi bi-download"></i> Download (nama: {{ $applicant->download_name }})</a></td></tr>
    </table></div>
  </div>
  @if(count($zipList))<div class="alert alert-info"><strong>Isi ZIP:</strong><ul class="mb-0">@foreach($zipList as $z)<li>{{ $z }}</li>@endforeach</ul></div>@endif

  <hr>
  <form method="POST" action="{{ route('admin.applications.status', $applicant->id) }}" class="form-row">@csrf
    <div class="form-group col-md-3"><label>Status</label><select name="status" class="form-control form-control-sm">@foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)<option {{ $applicant->status==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="form-group col-md-7"><label>Catatan Admin</label><input name="catatan_admin" class="form-control form-control-sm" value="{{ $applicant->catatan_admin }}"></div>
    <div class="form-group col-md-2"><label>&nbsp;</label><button class="btn btn-sm btn-primary btn-block">Simpan</button></div>
  </form>
  <div class="d-flex justify-content-between">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border">Kembali</a>
    <form method="POST" action="{{ route('admin.applications.destroy', $applicant->id) }}" onsubmit="return confirm('Hapus data ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form>
  </div>
</div>
@endsection
