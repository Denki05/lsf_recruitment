@extends('layouts.admin')
@section('title', ($user->exists ? 'Edit' : 'Tambah') . ' Login')
@section('content')
<div class="mb-2"><a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a></div>
<div class="content-card p-2" style="max-width:600px">
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user->id) : route('admin.users.store') }}">@csrf @if($user->exists) @method('PUT') @endif
  <div class="form-row">
    <div class="form-group col-md-6"><label>Nama *</label><input name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required maxlength="100"></div>
    <div class="form-group col-md-6"><label>Email *</label><input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $user->email) }}" required maxlength="150"></div>
  </div>
  <div class="form-group"><label>Password {{ $user->exists ? '(kosongkan bila tidak diubah)' : '*' }}</label><input type="password" name="password" class="form-control form-control-sm" {{ $user->exists ? '' : 'required' }} minlength="6" placeholder="min. 6 karakter"></div>
  <div class="form-check mb-2"><input type="checkbox" name="is_superadmin" value="1" class="form-check-input" id="sup" {{ old('is_superadmin', $user->is_superadmin) ? 'checked' : '' }}><label class="form-check-label" for="sup">Superadmin / Developer (akses semua cabang + kelola cabang & login)</label></div>
  <div id="cabangBox">
    <label>Akses cabang (bisa pilih lebih dari 1)</label>
    <div class="border rounded p-2" style="max-height:180px;overflow:auto">
      @forelse($branches as $b)
      <div class="form-check"><input type="checkbox" name="branch_ids[]" value="{{ $b->id }}" class="form-check-input" id="cb{{ $b->id }}" {{ in_array($b->id, old('branch_ids', $selected)) ? 'checked' : '' }}><label class="form-check-label" for="cb{{ $b->id }}">{{ $b->name }}{{ $b->location ? ' — '.$b->location : '' }}</label></div>
      @empty
      <small class="text-muted">Belum ada cabang. Buat dulu di menu Cabang.</small>
      @endforelse
    </div>
  </div>
  <div class="mt-2"><button class="btn btn-sm btn-primary"><i class="bi bi-save"></i> Simpan</button>
  <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border">Batal</a></div>
</form>
</div>
@push('scripts')
<script>
(function(){
  var sup = document.getElementById('sup'), box = document.getElementById('cabangBox');
  function sync(){ box.style.opacity = sup.checked ? '.4' : '1'; box.style.pointerEvents = sup.checked ? 'none' : 'auto'; }
  sup.addEventListener('change', sync); sync();
})();
</script>
@endpush
@endsection
