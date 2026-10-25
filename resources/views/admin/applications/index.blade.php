@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
<style>
/* Layout Container */
.admin-container { width: 100%; }
.compact-card { background: #ffffff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }

/* Sidebar Filter */
.screening-card { position: sticky; top: calc(var(--nav-h, 56px) + 12px); }
.screening-head { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-radius: 11px 11px 0 0; }
.screening-head b { font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b; }
.screening-body { padding: 12px 14px; }
.scr-label { font-size: 10.5px; font-weight: 700; color: #64748b; margin-bottom: 3px; display: block; text-transform: uppercase; letter-spacing: 0.3px; }
.form-group-mini { margin-bottom: 8px; }
.form-control-mini { height: 28px; padding: 2px 8px; font-size: 12px; border-radius: 5px; border: 1px solid #cbd5e1; color: #334155; }
.form-control-mini:focus { border-color: #0f4c81; outline: none; }

/* Custom Select2 */
.select2-container .select2-selection--single { height: 28px !important; font-size: 12px; border-color: #cbd5e1; border-radius: 5px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 26px !important; padding-left: 8px; color: #334155; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 26px !important; }

/* Sticky Toolbar Kanan */
.toolbar-card { padding: 8px 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: calc(var(--nav-h, 56px) + 12px); z-index: 60; background: rgba(255,255,255,0.96); backdrop-filter: blur(4px); }
.fbadge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; border-radius: 10px; min-width: 16px; height: 16px; display: inline-flex; align-items: center; justify-content: center; }

/* Buttons */
.btn-xs { font-size: 11.5px; padding: 4px 10px; font-weight: 600; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s; }
.btn-primary-soft { background: #eef6ff; color: #0f4c81; border: 1px solid #bfdbfe; }
.btn-primary-soft:hover { background: #dbeafe; color: #0c3e6a; }
.btn-solid-main { background: #0f4c81; color: #fff; border: none; }
.btn-solid-main:hover { background: #0c3e6a; color: #fff; }

#lamaranList { transition: opacity 0.2s; min-height: 300px; }
#toTop { position: fixed; right: 24px; bottom: 24px; z-index: 70; display: none; width: 38px; height: 38px; border-radius: 50%; border: none; background: #0f4c81; color: #fff; box-shadow: 0 4px 12px rgba(15,76,129,0.25); font-size: 18px; cursor: pointer; }
#toTop:hover { background: #0c3e6a; }

@media(max-width:767px){ .screening-card { position: static; margin-bottom: 16px; } }
</style>

<div class="admin-container">
  <div class="page-head mb-2">
    <div>
      <h1 class="page-title">Data Lamaran</h1>
      <p class="page-sub">Saring kandidat, perbarui status, dan bandingkan pelamar.</p>
    </div>
  </div>

  <div class="row">
    <!-- FILTER SIDEBAR -->
    <div class="col-md-3 pr-md-2">
      <form method="GET" action="{{ route('admin.applications.index') }}" id="filterForm">
        @if(request('sort'))
          <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        
        <div class="compact-card screening-card">
          <div class="screening-head">
            <b><i class="bi bi-sliders"></i> Filter Pelamar</b>
            <a href="{{ route('admin.applications.index') }}" class="text-muted text-decoration-none" style="font-size: 11px; font-weight: 600;">Reset</a>
          </div>
          <div class="screening-body">
            
            <div class="form-group-mini">
              <span class="scr-label">Pencarian</span>
              <input name="q" id="modalQ" class="form-control form-control-mini" placeholder="Cari Nama / No HP..." value="{{ request('q') }}" autocomplete="off">
            </div>

            <div class="form-group-mini">
              <span class="scr-label">Posisi Dilamar</span>
              <select name="position_id" id="modalPos" class="form-control form-control-mini auto-filter">
                <option value="">Semua Posisi</option>
                @foreach($positions as $p)
                  <option value="{{ $p->id }}" {{ (string) request('position_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->full_title }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-row">
              <div class="col-6 form-group-mini">
                <span class="scr-label">Status</span>
                <select name="status" id="modalStatus" class="form-control form-control-mini auto-filter">
                  <option value="">Semua</option>
                  @foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)
                    <option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 form-group-mini">
                <span class="scr-label">Tgl Masuk</span>
                <input type="date" name="tanggal" id="modalTgl" class="form-control form-control-mini auto-filter" value="{{ request('tanggal') }}">
              </div>
            </div>

            <hr class="my-2" style="border-top: 1px dashed #e2e8f0;">

            <!-- Parameter Sekunder -->
            <div class="form-row">
              <div class="col-6 form-group-mini">
                <span class="scr-label">Pendidikan</span>
                <select name="education" class="form-control form-control-mini auto-filter">
                  <option value="">Semua</option>
                  @foreach(['SD','SMP','SMA/SMK','D3','D4/S1','S2','S3'] as $e) <option {{ request('education')==$e?'selected':'' }}>{{ $e }}</option> @endforeach
                </select>
              </div>
              <div class="col-6 form-group-mini">
                <span class="scr-label">SIM</span>
                <select name="sim" class="form-control form-control-mini auto-filter">
                  <option value="">Semua</option>
                  @foreach(['A','B','C','A dan C','Tidak Punya'] as $s) <option {{ request('sim')==$s?'selected':'' }}>{{ $s }}</option> @endforeach
                </select>
              </div>
            </div>

            <div class="form-group-mini">
              <span class="scr-label">Umur Pelamar</span>
              <select name="umur_range" id="umurRange" class="form-control form-control-mini" style="width:100%">
                <option value="">Semua Umur</option>
                <option value="15-20" {{ request('umur_range')=='15-20'?'selected':'' }}>≤ 20 thn</option>
                <option value="21-25" {{ request('umur_range')=='21-25'?'selected':'' }}>21 – 25 thn</option>
                <option value="26-30" {{ request('umur_range')=='26-30'?'selected':'' }}>26 – 30 thn</option>
                <option value="31-35" {{ request('umur_range')=='31-35'?'selected':'' }}>31 – 35 thn</option>
                <option value="36-40" {{ request('umur_range')=='36-40'?'selected':'' }}>36 – 40 thn</option>
                <option value="41-70" {{ request('umur_range')=='41-70'?'selected':'' }}>41+ thn</option>
              </select>
            </div>

            <div class="form-group-mini">
              <span class="scr-label">Ekspektasi Gaji</span>
              <select name="gaji_range" id="gajiRange" class="form-control form-control-mini" style="width:100%">
                <option value="">Semua Gaji</option>
                <option value="0-3000000" {{ request('gaji_range')=='0-3000000'?'selected':'' }}>&lt; Rp 3 jt</option>
                <option value="3000000-5000000" {{ request('gaji_range')=='3000000-5000000'?'selected':'' }}>Rp 3 – 5 jt</option>
                <option value="5000000-8000000" {{ request('gaji_range')=='5000000-8000000'?'selected':'' }}>Rp 5 – 8 jt</option>
                <option value="8000000-12000000" {{ request('gaji_range')=='8000000-12000000'?'selected':'' }}>Rp 8 – 12 jt</option>
                <option value="12000000-1000000000" {{ request('gaji_range')=='12000000-1000000000'?'selected':'' }}>&gt; Rp 12 jt</option>
              </select>
            </div>

            <div class="form-group-mini mb-3">
              <span class="scr-label">Ketersediaan Lembur</span>
              <select name="lembur" class="form-control form-control-mini auto-filter">
                <option value="">Semua</option>
                <option value="1" {{ request('lembur')==='1'?'selected':'' }}>Bersedia Lembur</option>
                <option value="0" {{ request('lembur')==='0'?'selected':'' }}>Tidak Lembur</option>
              </select>
            </div>

            <!-- Aksi Massal -->
            <div class="bg-light rounded p-2 border mb-2">
              <span class="scr-label text-dark mb-1"><i class="bi bi-lightning-charge-fill text-warning"></i> Aksi Massal</span>
              <select id="targetStatus" class="form-control form-control-mini">
                <option value="">Ubah Status Semua Hasil...</option>
                @foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s) <option>{{ $s }}</option> @endforeach
              </select>
            </div>
            
            <a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-xs btn-outline-success w-100 justify-content-center export-link" data-base="{{ route('admin.applications.export') }}">
              <i class="bi bi-file-earmark-excel"></i> Export CSV
            </a>
          </div>
        </div>
      </form>
    </div>

    <!-- LIST DATA PELAMAR -->
    <div class="col-md-9 pl-md-1">
      <form method="POST" action="{{ route('admin.applications.bulk') }}" id="bulkForm">@csrf
      
      <div class="compact-card toolbar-card">
        <div class="d-flex align-items-center">
          <span class="text-muted font-weight-bold" style="font-size: 11.5px;"><i class="bi bi-check2-square text-primary mr-1"></i> Pilih pelamar untuk dibanding</span>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
          <span class="fbadge" id="filterBadge" style="display:none">0</span>
          
          @if(request('sort') === 'skor')
            <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-xs btn-light border sort-link" data-sort=""><i class="bi bi-arrow-down-up"></i> Normal</a>
          @else
            <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'skor'])) }}" class="btn btn-xs btn-primary-soft sort-link" data-sort="skor"><i class="bi bi-trophy-fill text-warning"></i> Urut Skor</a>
          @endif
          
          @if(config('recruitment.ai_enabled'))
            @if(request('sort') === 'ai')
              <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}" class="btn btn-xs btn-light border sort-link" data-sort=""><i class="bi bi-arrow-down-up"></i> Normal</a>
            @else
              <a href="{{ route('admin.applications.index', array_merge(request()->query(), ['sort' => 'ai'])) }}" class="btn btn-xs btn-primary-soft sort-link" data-sort="ai"><i class="bi bi-stars text-primary"></i> Skor AI</a>
            @endif
          @endif
          
          <button type="button" class="btn btn-xs btn-solid-main" id="compareBtn"><i class="bi bi-columns-gap"></i> Bandingkan</button>
        </div>
      </div>

      <div id="lamaranList">
        @include('admin.applications.partials.list')
      </div>
      
      </form>
    </div>
  </div>
</div>
<button type="button" id="toTop" title="Kembali ke atas"><i class="bi bi-arrow-up-short"></i></button>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  if(!window.Swal){
    window.Swal = { mixin: function(){ return { fire: function(o){ alert(o.title || ''); } }; }, fire: function(o){ o = o || {}; if(o.showCancelButton){ return Promise.resolve({ isConfirmed: confirm((o.title || '') + '\n' + (o.text || '')) }); } alert((o.title || '') + '\n' + (o.text || '')); return Promise.resolve({ isConfirmed: true }); } };
  }
  var Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2600, timerProgressBar: true });
  
  if(window.jQuery && jQuery.fn.select2){
    jQuery('#umurRange, #gajiRange, #modalPos').select2({ width: '100%' });
  }

  var form = document.getElementById('filterForm'), box = document.getElementById('lamaranList'), badge = document.getElementById('filterBadge'), expBase = document.querySelector('.export-link').dataset.base, filterKeys = ['q','position_id','status','tanggal','umur_range','gaji_range','sim','education','lembur'];
  
  function queryString(){ return new URLSearchParams(new FormData(form)).toString(); }
  function refreshBadge(){ var p = new URLSearchParams(new FormData(form)), n = 0; filterKeys.forEach(function(k){ if((p.get(k) || '').trim() !== '') n++; }); badge.textContent = n; badge.style.display = n ? 'inline-flex' : 'none'; }
  function syncLinks(search){ document.querySelectorAll('.export-link').forEach(function(a){ a.href = expBase + search; }); document.querySelectorAll('.sort-link').forEach(function(a){ var s = a.dataset.sort, p = new URLSearchParams(new FormData(form)); p.delete('page'); if(s) p.set('sort', s); else p.delete('sort'); a.href = form.action + '?' + p.toString(); }); }
  
  function fetchList(){
    var qs = queryString();
    box.style.opacity = '0.5';
    fetch(form.action + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){ box.innerHTML = html; box.style.opacity = '1'; history.replaceState(null, '', form.action + '?' + qs); syncLinks('?' + qs); refreshBadge(); })
      .catch(function(){ window.location.href = form.action + '?' + qs; });
  }

  var qt;
  jQuery(form).on('input', function(e){ refreshBadge(); clearTimeout(qt); qt = setTimeout(fetchList, e.target.name === 'q' ? 450 : 300); });
  jQuery(form).on('change', function(e){ if(e.target && e.target.id === 'targetStatus') return; refreshBadge(); clearTimeout(qt); fetchList(); });

  box.addEventListener('click', function(e){
    var qa = e.target.closest('.qa-btn');
    if(qa){
      if(qa.disabled) return;
      if(qa.dataset.status === 'Ditolak'){
        Swal.fire({ title: 'Tolak pelamar?', text: qa.dataset.nama + ' akan ditandai Ditolak.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, tolak', cancelButtonText: 'Batal' }).then(function(r){ if(r.isConfirmed) doQa(qa); });
      } else { doQa(qa); }
      return;
    }
    var pg = e.target.closest('.pagination a');
    if(pg){ e.preventDefault(); box.style.opacity='0.5'; fetch(pg.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function(r){ return r.text(); }).then(function(html){ box.innerHTML = html; box.style.opacity='1'; history.replaceState(null, '', pg.href); syncLinks('?' + pg.href.split('?')[1]); }).catch(function(){ window.location.href = pg.href; }); return; }
    
    var chip = e.target.closest('.chip-x');
    if(chip){
      var input = form.querySelector('[name="' + chip.dataset.key + '"]');
      if(input){ if(input.tagName === 'SELECT'){ jQuery(input).val('').trigger('change.select2'); } else { input.value = ''; } }
      refreshBadge(); fetchList();
    }
  });

  document.getElementById('targetStatus').addEventListener('change', function(){
    var st = this.value; if(!st) return;
    var total = document.getElementById('resultTotal'); var n = total ? total.dataset.total : '?'; var sel = this;
    Swal.fire({ title: 'Terapkan ke hasil filter?', html: '<b>' + n + '</b> lamaran akan diubah ke "<b>' + st + '</b>".', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, jalankan', cancelButtonText: 'Batal' }).then(function(r){
      if(!r.isConfirmed){ sel.value = ''; return; }
      fetch('{{ route('admin.applications.bulkFiltered') }}?' + queryString(), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new URLSearchParams({ target_status: st }) })
        .then(function(rr){ if(!rr.ok) throw 0; return rr.json(); })
        .then(function(j){ sel.value = ''; Toast.fire({ icon: 'success', title: j.count + ' lamaran → ' + j.status }); fetchList(); })
        .catch(function(){ sel.value = ''; Toast.fire({ icon: 'error', title: 'Gagal menyimpan.' }); });
    });
  });

  function doQa(qa){
    qa.disabled = true;
    fetch(qa.dataset.url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new URLSearchParams({ status: qa.dataset.status }) })
      .then(function(r){ if(!r.ok) throw 0; return r.json(); })
      .then(function(j){ Toast.fire({ icon: 'success', title: j.nama + ' → ' + j.status }); fetchList(); })
      .catch(function(){ qa.disabled = false; Toast.fire({ icon: 'error', title: 'Gagal menyimpan.' }); });
  }

  document.getElementById('compareBtn').addEventListener('click', function(){
    var ids = Array.prototype.map.call(box.querySelectorAll('.row-check:checked'), function(c){ return c.value; });
    if(ids.length < 2){ Swal.fire({ icon: 'info', title: 'Pilih Kandidat', text: 'Centang 2–4 lamaran untuk dibandingkan.' }); return; }
    if(ids.length > 4){ Swal.fire({ icon: 'info', title: 'Kebanyakan', text: 'Maksimal 4 lamaran.' }); return; }
    window.location.href = '{{ route('admin.applications.compare') }}?' + ids.map(function(id){ return 'ids[]=' + encodeURIComponent(id); }).join('&');
  });

  var toTop = document.getElementById('toTop');
  window.addEventListener('scroll', function(){ toTop.style.display = window.pageYOffset > 300 ? 'block' : 'none'; });
  toTop.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });
})();
</script>
@endpush
@endsection