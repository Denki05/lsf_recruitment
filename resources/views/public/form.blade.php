@extends('layouts.public')
@section('title', 'Form Lamaran | LSF')

@section('content')
<div class="mb-2"><a href="{{ route('jobs.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Semua Lowongan</a></div>
<form method="POST" action="{{ route('lamaran.store') }}" enctype="multipart/form-data" id="lamaranForm">
@csrf
<p class="step-desc">Isi singkat (< 2 menit) + upload CV. Data lengkap diminta lagi bila Anda dipanggil.</p>
<div class="form-row">
  <div class="form-group col-md-6"><label class="required">Nama Lengkap</label><input name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required maxlength="100"></div>
  <div class="form-group col-md-6"><label class="required">Jenis Kelamin</label><select name="jenis_kelamin" class="form-control" required><option value="">-- pilih --</option><option {{ old('jenis_kelamin')=='Laki-laki'?'selected':'' }}>Laki-laki</option><option {{ old('jenis_kelamin')=='Perempuan'?'selected':'' }}>Perempuan</option></select></div>
</div>
<div class="form-row">
  <div class="form-group col-md-6"><label class="required">No HP / WA</label><input name="no_hp" class="form-control" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx"></div>
  <div class="form-group col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="150" placeholder="opsional"></div>
</div>
<div class="form-row">
  <div class="form-group col-md-4"><label class="required">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}" required max="{{ date('Y-m-d') }}"></div>
  <div class="form-group col-md-4"><label class="required">Jenjang Pendidikan</label><select name="education" class="form-control" required><option value="">-- pilih --</option>@foreach($educations as $e)<option value="{{ $e }}" {{ old('education')==$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
  <div class="form-group col-md-4"><label class="required">Permintaan Gaji (Rp)</label><input type="number" name="expected_salary" class="form-control" value="{{ old('expected_salary') }}" required min="0" max="1000000000" placeholder="cth. 5000000"></div>
</div>
<div class="form-group"><div class="form-check"><input type="checkbox" name="willing_overtime" value="1" id="lembur" class="form-check-input" {{ old('willing_overtime') ? 'checked' : '' }}><label class="form-check-label" for="lembur">Bersedia lembur di luar jam kerja</label></div></div>
<div class="form-row">
  <div class="form-group col-md-4"><label class="required">Domisili</label><input name="domisili" class="form-control" value="{{ old('domisili') }}" required maxlength="100" placeholder="cth. Surabaya"></div>
  <div class="form-group col-md-4"><label class="required">Posisi Dilamar</label>
  @if(!empty($selectedPosition))
    <div class="alert alert-info py-1 px-2 mb-0 text-truncate" style="font-size:13px" title="{{ $selectedPosition->full_title }}"><i class="bi bi-briefcase-fill"></i> <strong>{{ $selectedPosition->full_title }}</strong>@if($selectedPosition->branch)<br><small><i class="bi bi-diagram-3"></i> {{ $selectedPosition->branch->name }}{{ $selectedPosition->branch->location ? ' — '.$selectedPosition->branch->location : '' }}</small>@endif</div>
    <input type="hidden" name="position_id" value="{{ $selectedPosition->id }}">
  @else
    <select name="position_id" class="form-control" required><option value="">-- pilih --</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ old('position_id')==$p->id?'selected':'' }}>{{ $p->full_title }}</option>@endforeach</select>
  @endif</div>
  <div class="form-group col-md-4"><label class="required">SIM</label><select name="sim" class="form-control" required><option value="">-- pilih --</option>@foreach(['A','B','C','A dan C','Tidak Punya'] as $s)<option {{ old('sim')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
</div>
<div class="form-group"><label class="required">Upload CV (PDF/DOC/DOCX/ZIP, maks 5 MB)</label>
  <div class="dropzone" id="dropzone">
    <div class="dz-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
    <div><div class="dz-text">Seret & letakkan file di sini, atau <u>klik untuk pilih file</u></div>
    <div class="dz-sub" id="fileInfo">CV: PDF/DOC/ZIP maks 5 MB → terunduh HRD sebagai Nama-Posisi-Tanggal.ext</div></div>
  </div>
  <input type="file" name="berkas" id="berkas" accept=".pdf,.doc,.docx,.zip" required hidden>
</div>
<button type="submit" class="btn btn-success btn-block"><i class="bi bi-send"></i> Kirim Lamaran</button>
<div class="form-check mt-2"><input type="checkbox" name="ai_consent" value="1" id="aiConsent" class="form-check-input" required {{ old('ai_consent') ? 'checked' : '' }}><label class="form-check-label" for="aiConsent" style="font-size:12.5px">Saya menyatakan data di atas benar dan <strong>menyetujui CV diproses</strong> untuk keperluan rekrutmen.</label></div>
</form>

@push('scripts')
<script>
var berkasInput = document.getElementById('berkas');
var dropzone = document.getElementById('dropzone');
function handleFile(f){
  if(!f) return;
  var okExt = /\.(pdf|doc|docx|zip)$/i.test(f.name);
  if(!okExt){ alert('File harus PDF / DOC / DOCX / ZIP.'); berkasInput.value=''; return; }
  if(f.size > 5*1024*1024){ alert('File melebihi 5 MB ('+(f.size/1024/1024).toFixed(2)+' MB). Kecilkan / kompres dulu.'); berkasInput.value=''; return; }
  document.getElementById('fileInfo').textContent = 'Dipilih: '+f.name+' ('+(f.size/1024).toFixed(0)+' KB)';
  berkasInput.classList.remove('is-invalid');
}
dropzone.addEventListener('click', function(){ berkasInput.click(); });
berkasInput.addEventListener('change', function(){ handleFile(this.files[0]); });
['dragenter','dragover'].forEach(function(ev){ dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.classList.add('dragover'); }); });
['dragleave','drop'].forEach(function(ev){ dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.classList.remove('dragover'); }); });
dropzone.addEventListener('drop', function(e){
  if(!e.dataTransfer.files.length) return;
  berkasInput.files = e.dataTransfer.files;
  handleFile(berkasInput.files[0]);
});
document.getElementById('lamaranForm').addEventListener('submit', function(e){
  if(!berkasInput.files.length){ e.preventDefault(); alert('Silakan upload CV dulu (klik atau seret file ke kotak upload).'); return; }
});
</script>
@endpush
@endsection
