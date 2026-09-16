@extends('layouts.public')
@section('title', 'Form Lamaran Kerja')

@section('content')
<form method="POST" action="{{ route('lamaran.store') }}" enctype="multipart/form-data" id="lamaranForm">
@csrf
<div class="stepper">
  <div class="step-item active" data-step="1"><span class="num">1</span>Data Diri</div>
  <div class="step-item" data-step="2"><span class="num">2</span>Alamat & Kontak</div>
  <div class="step-item" data-step="3"><span class="num">3</span>Lamaran & Berkas</div>
</div>

{{-- STEP 1 --}}
<div class="step-pane active" data-step="1">
  <h5 class="sect font-weight-bold">1. Data Diri</h5>
  <p class="step-desc">Isi data diri singkat — dipakai HRD untuk verifikasi awal.</p>
  <div class="form-row">
    <div class="form-group col-md-6"><label class="required">Nama Lengkap</label><input name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required maxlength="100"></div>
    <div class="form-group col-md-6"><label class="required">Jenis Kelamin</label><select name="jenis_kelamin" class="form-control" required><option value="">-- pilih --</option><option {{ old('jenis_kelamin')=='Laki-laki'?'selected':'' }}>Laki-laki</option><option {{ old('jenis_kelamin')=='Perempuan'?'selected':'' }}>Perempuan</option></select></div>
  </div>
  <div class="form-row">
    <div class="form-group col-md-6"><label>Status</label><select name="status_pernikahan" class="form-control"><option value="">-- pilih (opsional) --</option>@foreach(['Belum Menikah','Menikah','Cerai'] as $s)<option {{ old('status_pernikahan')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="form-group col-md-6"><label>Agama</label><select name="agama" class="form-control"><option value="">-- pilih (opsional) --</option>@foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Khonghucu','Lainnya'] as $a)<option {{ old('agama')==$a?'selected':'' }}>{{ $a }}</option>@endforeach</select></div>
  </div>
  <div class="text-right"><button type="button" class="btn btn-primary next-btn">Lanjut <i class="bi bi-arrow-right"></i></button></div>
</div>

{{-- STEP 2 --}}
<div class="step-pane" data-step="2">
  <h5 class="sect font-weight-bold">2. Alamat & Kontak</h5>
  <p class="step-desc">Alamat tinggal, HP/WA aktif, dan sosmed agar HRD mudah menghubungimu.</p>
  <div class="form-group"><label class="required">Alamat</label><textarea name="alamat_ktp" class="form-control" rows="2" required placeholder="Alamat tinggal saat ini">{{ old('alamat_ktp') }}</textarea></div>
  <div class="form-row">
    <div class="form-group col-md-6"><label class="required">No HP / WA</label><input name="no_hp" class="form-control" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx"></div>
    <div class="form-group col-md-6"><label>Social Media</label><input name="sosmed" class="form-control" value="{{ old('sosmed') }}" maxlength="255" placeholder="IG: @..., TikTok: @..."></div>
  </div>
  <div class="d-flex justify-content-between"><button type="button" class="btn btn-light border prev-btn"><i class="bi bi-arrow-left"></i> Kembali</button><button type="button" class="btn btn-primary next-btn">Lanjut <i class="bi bi-arrow-right"></i></button></div>
</div>

{{-- STEP 3 --}}
<div class="step-pane" data-step="3">
  <h5 class="sect font-weight-bold">3. Lamaran & Berkas</h5>
  <p class="step-desc">Pilih posisi, isi kendaraan & SIM, gabung semua dokumen jadi <strong>1 file</strong> (maks 5 MB).</p>
  <div class="form-row">
    <div class="form-group col-md-4"><label class="required">Posisi Dilamar</label><select name="position_id" class="form-control" required><option value="">-- pilih --</option>@foreach($positions as $p)<option value="{{ $p->id }}" {{ old('position_id')==$p->id?'selected':'' }}>{{ $p->full_title }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label class="required">Kendaraan</label><select name="kendaraan" class="form-control" required><option value="">-- pilih --</option>@foreach(['Motor','Mobil','Motor dan Mobil','Tidak Ada'] as $k)<option {{ old('kendaraan')==$k?'selected':'' }}>{{ $k }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label class="required">SIM</label><select name="sim" class="form-control" required><option value="">-- pilih --</option>@foreach(['A','B','C','A dan C','B dan C','Tidak Punya'] as $s)<option {{ old('sim')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
  </div>
  <div class="form-group"><label class="required">Upload Berkas (1 file: PDF/DOC/DOCX/ZIP, maks 5 MB)</label>
    <div class="dropzone" id="dropzone">
      <div class="dz-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
      <div class="dz-text">Seret & letakkan file di sini, atau <u>klik untuk pilih file</u></div>
      <div class="dz-sub" id="fileInfo">Nama file saat diunduh HRD: NamaPelamar-Posisi-Tanggal.ext</div>
    </div>
    <input type="file" name="berkas" id="berkas" accept=".pdf,.doc,.docx,.zip" required hidden>
  </div>
  <div class="d-flex justify-content-between"><button type="button" class="btn btn-light border prev-btn"><i class="bi bi-arrow-left"></i> Kembali</button><button type="submit" class="btn btn-success"><i class="bi bi-send"></i> Kirim Lamaran</button></div>
  <p class="text-muted text-center mt-2 mb-0" style="font-size:11px">Data KTP hanya dipakai untuk proses rekrutmen.</p>
</div>
</form>

@push('scripts')
<script>
let step = 1;
function showStep(n){
  step = n;
  document.querySelectorAll('.step-pane').forEach(p => p.classList.toggle('active', p.dataset.step == n));
  document.querySelectorAll('.step-item').forEach(el => {
    const s = parseInt(el.dataset.step);
    el.classList.toggle('active', s === n);
    el.classList.toggle('done', s < n);
  });
  window.scrollTo(0,0);
}
document.querySelectorAll('.next-btn').forEach(b => b.addEventListener('click', () => {
  const pane = document.querySelector('.step-pane[data-step="'+step+'"]');
  let valid = true;
  pane.querySelectorAll('[required]').forEach(el => {
    if(!el.value || (el.pattern && !(new RegExp('^'+el.pattern+'$').test(el.value)))){ el.classList.add('is-invalid'); valid = false; } else el.classList.remove('is-invalid');
  });
  if(!valid){ alert('Lengkapi field bertanda * pada langkah ini.'); return; }
  showStep(Math.min(3, step+1));
}));
document.querySelectorAll('.prev-btn').forEach(b => b.addEventListener('click', () => showStep(Math.max(1, step-1))));
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
  if(!berkasInput.files.length){ e.preventDefault(); alert('Silakan upload berkas dulu (klik atau seret file ke kotak upload).'); showStep(3); return; }
});
@if($errors->any()) showStep(3); @endif
</script>
@endpush
@endsection
