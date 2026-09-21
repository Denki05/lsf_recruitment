@extends('layouts.admin')
@section('title', ($branch->exists ? 'Edit' : 'Tambah') . ' Cabang')
@section('content')
<div class="mb-2"><a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="content-card p-2" style="max-width:560px">
<form method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch->id) : route('admin.branches.store') }}">@csrf @if($branch->exists) @method('PUT') @endif
  <div class="form-group"><label>Nama Cabang / Perusahaan *</label><input name="name" class="form-control form-control-sm" value="{{ old('name', $branch->name) }}" required maxlength="100" placeholder="cth: UNIFRA"></div>
  <div class="form-group"><label>Lokasi</label><input name="location" class="form-control form-control-sm" value="{{ old('location', $branch->location) }}" maxlength="150" placeholder="cth: Surabaya"></div>
  <div class="form-group"><label>Alamat</label><input name="address" class="form-control form-control-sm" value="{{ old('address', $branch->address) }}" maxlength="255" placeholder="cth: Jl. ..."></div>
  <div class="form-check mb-2"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="aktif" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="aktif">Aktif (tampil di situs & form)</label></div>
  <button class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan</button>
  <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-light border">Batal</a>
</form>
</div>
@endsection
