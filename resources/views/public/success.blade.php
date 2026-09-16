@extends('layouts.public')
@section('title', 'Lamaran Terkirim')
@section('content')
<div class="text-center py-4">
  <div style="font-size:56px;color:#198754"><i class="bi bi-check-circle-fill"></i></div>
  <h4 class="font-weight-bold mt-2">Lamaran Berhasil Dikirim!</h4>
  <p class="text-muted">Terima kasih <strong>{{ $applicant->nama_lengkap }}</strong> ({{ $applicant->nama_panggilan }}) telah melamar <strong>{{ $applicant->position->full_title }}</strong>.</p>
  <table class="table table-sm text-left">
    <tr><th width="40%">No KTP</th><td>{{ $applicant->no_ktp }}</td></tr>
    <tr><th>No HP</th><td>{{ $applicant->no_hp }}</td></tr>
    <tr><th>Berkas</th><td>{{ $applicant->file_original }}</td></tr>
    <tr><th>Tanggal</th><td>{{ $applicant->created_at->format('d M Y H:i') }}</td></tr>
  </table>
  <a href="{{ route('lamaran.form') }}" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Kembali ke Form</a>
</div>
@endsection
