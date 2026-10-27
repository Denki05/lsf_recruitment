@extends('layouts.admin')
@section('title', 'Data Lamaran')
@section('content')
@php
$advKeys = ['tanggal', 'education', 'sim', 'umur_range', 'gaji_range', 'lembur'];
$advCount = 0;
foreach ($advKeys as $k) {
    if (request()->filled($k)) { $advCount++; }
}
$advActive = $advCount > 0;
$sortMode = request('sort');
$aiEnabled = config('recruitment.ai_enabled');
@endphp
<style>
/* ===== Filter ===== */
.lam-filter-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 10px; margin-bottom: 12px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
.lam-filter-row { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; }
.lam-search { position: relative; flex: 1 1 260px; min-width: 200px; }
.lam-search > i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #94a3b8; pointer-events: none; }
.lam-search input { width: 100%; height: 36px; padding: 4px 10px 4px 30px; font-size: 12.5px; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
.lam-search input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(15,76,129,.1); }
.lam-select { height: 36px; min-width: 0; padding: 4px 9px; font-size: 12px; font-weight: 600; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
.lam-select:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(15,76,129,.1); }
.lam-select-pos { flex: 0 1 240px; }
.lam-select-status { flex: 0 1 145px; }
.lam-more-btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 11px; font-size: 12px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; white-space: nowrap; }
.lam-more-btn:hover { border-color: #94a3b8; color: var(--brand); }
.lam-more-btn.has-filter { color: var(--brand); border-color: #bfdbfe; background: var(--brand-soft); }
.lam-more-caret { font-size: 9px; opacity: .6; transition: transform .15s; }
.lam-more-btn[aria-expanded="true"] .lam-more-caret { transform: rotate(180deg); }
.lam-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 16px; height: 16px; padding: 0 4px; font-size: 9.5px; font-weight: 800; color: #fff; background: var(--brand); border-radius: 999px; line-height: 1; }
.lam-reset { display: inline-flex; align-items: center; height: 36px; padding: 0 7px; font-size: 11.5px; font-weight: 600; color: #94a3b8; text-decoration: none !important; white-space: nowrap; }
.lam-reset:hover { color: #dc2626; }

/* ===== Filter lanjutan ===== */
.lam-adv { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; align-items: end; padding-top: 10px; margin-top: 10px; border-top: 1px dashed var(--border); }
.lam-field label { display: block; margin-bottom: 3px; font-size: 10px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #64748b; }
.lam-input { width: 100%; height: 34px; padding: 4px 9px; font-size: 12px; color: #334155; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; }
.lam-input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(15,76,129,.1); }
.lam-bulk { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 10px; padding: 9px 11px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; }
.lam-bulk-label { margin: 0; font-size: 11.5px; font-weight: 600; color: #92400e; }
.lam-bulk-label span { font-weight: 500; color: #a16207; }
.lam-bulk select { width: auto; min-width: 190px; height: 32px; padding: 3px 8px; font-size: 12px; font-weight: 600; color: #334155; border: 1px solid #d6b34a; border-radius: 6px; background: #fff; }

/* ===== Bar hasil & sort ===== */
.lam-results { position: sticky; top: calc(var(--nav-h, 56px) + 8px); z-index: 40; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 14px; padding: 8px 12px; margin-bottom: 9px; background: rgba(255,255,255,.97); border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 3px 10px rgba(15,23,42,.05); }
.lam-total { font-size: 12px; color: #64748b; line-height: 1.3; }
.lam-total strong { font-size: 14.5px; font-weight: 800; color: var(--text); }
.lam-selection { margin-top: 1px; font-size: 10.5px; color: #94a3b8; line-height: 1.35; }
.lam-selection.is-warn { color: #b45309; font-weight: 600; }
.lam-results-right { display: flex; align-items: center; gap: 7px; }
.lam-sort { display: inline-flex; gap: 2px; padding: 2px; background: #f1f5f9; border-radius: 7px; }
.lam-sort-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 9px; font-size: 11.5px; font-weight: 600; color: #64748b; text-decoration: none !important; border-radius: 5px; white-space: nowrap; }
.lam-sort-btn:hover { color: var(--brand); }
.lam-sort-btn.active { color: var(--brand); background: #fff; box-shadow: 0 1px 2px rgba(15,23,42,.1); }
.lam-sort-btn i { font-size: 11px; }
.lam-compare { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 32px; padding: 0 12px; font-size: 11.5px; font-weight: 700; color: #fff; background: var(--brand); border: 0; border-radius: 7px; white-space: nowrap; }
.lam-compare:hover:not(:disabled) { background: var(--brand-dark); }
.lam-compare:disabled { color: #94a3b8; background: #e2e8f0; cursor: not-allowed; }
.lam-compare-count { display: inline-flex; align-items: center; justify-content: center; min-width: 16px; height: 16px; padding: 0 4px; font-size: 9.5px; font-weight: 800; color: var(--brand); background: #fff; border-radius: 999px; }

/* ===== Chip filter aktif ===== */
.lam-chip-row { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin-bottom: 7px; }
.lam-chip-label { font-size: 10.5px; font-weight: 600; color: #94a3b8; }
.lam-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; font-size: 10.5px; font-weight: 600; color: #334155; background: #f8fbff; border: 1px solid #dbeafe; border-radius: 999px; line-height: 1.4; cursor: pointer; }
.lam-chip i { font-size: 11px; color: #94a3b8; }
.lam-chip:hover { background: #eef4fa; border-color: #bfdbfe; }
.lam-chip:hover i { color: #ef4444; }
.lam-chip-clear { color: #64748b; background: #fff; border-color: var(--border); }
.lam-chip-clear:hover { color: #dc2626; background: #fef2f2; border-color: #fecaca; }

/* ===== Baris pelamar ===== */
.lam-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; margin-bottom: 7px; background: #fff; border: 1px solid var(--border); border-radius: 10px; transition: border-color .15s, box-shadow .15s, background .15s; }
.lam-row:hover { border-color: #cbd5e1; box-shadow: 0 3px 10px rgba(15,23,42,.05); }
.lam-row.is-selected { background: #f8fbff; border-color: #bfdbfe; box-shadow: 0 0 0 2px rgba(15,76,129,.06); }
.lam-pick { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 18px; margin: 0; cursor: pointer; }
.lam-pick input { width: 15px; height: 15px; margin: 0; accent-color: var(--brand); cursor: pointer; }
.lam-avatar { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 34px; width: 34px; height: 34px; font-size: 11.5px; font-weight: 800; letter-spacing: .3px; color: var(--brand); background: var(--brand-soft); border-radius: 9px; }
.lam-main { flex: 1 1 auto; min-width: 0; }
.lam-head { display: flex; flex-wrap: wrap; align-items: center; gap: 5px 7px; }
.lam-name { font-size: 13.5px; font-weight: 700; color: #1e293b; text-decoration: none !important; }
.lam-name:hover { color: var(--brand); text-decoration: underline !important; }
.lam-row.is-ditolak .lam-name { color: #94a3b8; }
.lam-status { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; font-size: 9.5px; font-weight: 700; letter-spacing: .3px; text-transform: uppercase; border-radius: 999px; line-height: 1.5; white-space: nowrap; }
.lam-status::before { content: ""; width: 5px; height: 5px; background: currentColor; border-radius: 50%; }
.lam-status.is-baru { color: #2563eb; background: #eff6ff; }
.lam-status.is-seleksi { color: #b45309; background: #fef3c7; }
.lam-status.is-interview { color: #0e7490; background: #ecfeff; }
.lam-status.is-diterima { color: #047857; background: #ecfdf5; }
.lam-status.is-ditolak { color: #dc2626; background: #fef2f2; }
.lam-branch { padding: 1px 5px; font-size: 9.5px; font-weight: 600; color: #64748b; background: #f8fafc; border: 1px solid var(--border); border-radius: 4px; white-space: nowrap; }
.lam-date { display: inline-flex; align-items: center; gap: 3px; margin-left: auto; font-size: 10.5px; color: #94a3b8; white-space: nowrap; }
.lam-date i { font-size: 10px; }
.lam-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 2px 12px; margin-top: 3px; font-size: 11.5px; color: #64748b; }
.lam-meta-item { display: inline-flex; align-items: center; gap: 4px; min-width: 0; }
.lam-meta-item i { font-size: 11px; color: #94a3b8; flex-shrink: 0; }
.lam-pos { font-weight: 700; color: #334155; }
.lam-pos i { color: #64748b; }
.lam-salary { font-weight: 700; color: #047857; }
.lam-salary i { color: #059669; }
.lam-score { font-weight: 700; color: #1d4ed8; }
.lam-score i { color: #2563eb; }
.lam-ai { font-weight: 700; color: #6d28d9; }
.lam-ai i { color: #7c3aed; }

/* ===== Aksi baris ===== */
.lam-actions { display: flex; align-items: center; gap: 5px; flex: 0 0 auto; }
.lam-btn-detail { display: inline-flex; align-items: center; padding: 5px 11px; font-size: 11.5px; font-weight: 700; color: #fff !important; background: var(--brand); border: 1px solid var(--brand); border-radius: 6px; text-decoration: none !important; white-space: nowrap; }
.lam-btn-detail:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
.lam-btn-cv { display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; color: #475569 !important; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none !important; white-space: nowrap; }
.lam-btn-cv:hover { color: var(--brand) !important; border-color: #bfdbfe; background: var(--brand-soft); }
.lam-btn-proses { display: inline-flex; align-items: center; gap: 3px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; color: #475569; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; white-space: nowrap; }
.lam-btn-proses:hover, .lam-btn-proses:focus { color: var(--brand); border-color: #bfdbfe; background: var(--brand-soft); outline: none; }
.lam-btn-proses i { font-size: 12px; }
.lam-actions .dropdown-toggle::after { margin-left: 2px; font-size: 8px; vertical-align: 1px; opacity: .6; }
.lam-actions .dropdown-menu { min-width: 190px; padding: 5px; margin-top: 4px; font-size: 12px; border: 1px solid var(--border); border-radius: 9px; box-shadow: 0 12px 30px rgba(15,23,42,.14); }
.lam-actions .dropdown-item { display: flex; align-items: center; gap: 8px; padding: 7px 9px; font-weight: 600; color: #334155; border-radius: 6px; }
.lam-actions .dropdown-item i { font-size: 13px; }
.lam-actions .dropdown-item:hover { background: #f1f5f9; color: var(--brand); }
.lam-actions .dropdown-item.text-danger:hover { color: #dc2626 !important; background: #fef2f2; }

/* ===== Empty, pagination, misc ===== */
.lam-empty { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 40px 16px; text-align: center; color: #64748b; background: #fff; border: 1px dashed #cbd5e1; border-radius: 10px; }
.lam-empty i { margin-bottom: 4px; font-size: 30px; color: #cbd5e1; }
.lam-empty strong { font-size: 13px; color: #334155; }
.lam-empty span { font-size: 11.5px; }
.lam-empty .btn { margin-top: 8px; font-size: 11.5px; }
.lam-pagination { display: flex; justify-content: center; margin-top: 12px; }
.lam-pagination .pagination { margin-bottom: 4px; }
.lam-pagination .page-link { padding: 4px 9px; font-size: 12px; color: var(--brand); border-radius: 5px; margin: 0 1px; }
.lam-pagination .page-item.active .page-link { color: #fff; background: var(--brand); border-color: var(--brand); }
#lamaranList { min-height: 260px; scroll-margin-top: calc(var(--nav-h, 56px) + 66px); transition: opacity .2s; }
#toTop { position: fixed; right: 24px; bottom: 24px; z-index: 70; display: none; width: 38px; height: 38px; font-size: 18px; color: #fff; background: var(--brand); border: none; border-radius: 50%; box-shadow: 0 4px 12px rgba(15,76,129,.25); cursor: pointer; }
#toTop:hover { background: var(--brand-dark); }

@media (max-width: 991.98px) {
  .lam-results { position: static; }
}
@media (max-width: 1199.98px) {
  .lam-adv { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
  .lam-filter-card { padding: 8px; }
  .lam-search { flex: 1 1 100%; }
  .lam-select-pos { flex: 1 1 100%; }
  .lam-select-status { flex: 1 1 calc(50% - 4px); }
  .lam-more-btn { flex: 1 1 auto; justify-content: center; }
  .lam-reset { flex: 0 0 auto; }
  .lam-adv { grid-template-columns: 1fr 1fr; gap: 8px; }
  .lam-bulk select { width: 100%; min-width: 0; }
  .lam-results { flex-direction: column; align-items: stretch; gap: 6px; }
  .lam-results-right { flex-wrap: nowrap; }
  .lam-sort { flex: 1 1 auto; min-width: 0; }
  .lam-sort-btn { flex: 1 1 auto; justify-content: center; padding: 5px 6px; }
  .lam-compare { flex: 0 0 auto; height: 32px; padding: 0 10px; }
  .lam-row { flex-wrap: wrap; align-items: flex-start; padding: 10px; }
  .lam-avatar { width: 30px; height: 30px; flex-basis: 30px; }
  .lam-main { flex: 1 1 calc(100% - 75px); }
  .lam-date { margin-left: 0; }
  .lam-actions { width: 100%; padding-top: 2px; }
  .lam-actions .dropdown { flex: 1 1 auto; }
  .lam-btn-detail, .lam-btn-cv { flex: 1 1 0; justify-content: center; padding: 7px 8px; }
  .lam-btn-proses { width: 100%; justify-content: center; padding: 7px 8px; }
  .lam-pagination .page-link { padding: 4px 8px; }
  #lamaranList { scroll-margin-top: calc(var(--nav-h, 56px) + 104px); }
}
</style>

<div class="page-head">
  <div>
    <h1 class="page-title">Data Lamaran</h1>
    <p class="page-sub">Saring pelamar, proses status, dan bandingkan kandidat.</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.applications.export', request()->query()) }}" class="btn btn-sm btn-outline-secondary export-link" data-base="{{ route('admin.applications.export') }}" title="Unduh daftar sesuai filter aktif">
      <i class="bi bi-file-earmark-arrow-down"></i> Export CSV
    </a>
  </div>
</div>

<form method="GET" action="{{ route('admin.applications.index') }}" id="filterForm" class="lam-filter-card">
  <input type="hidden" name="sort" value="{{ $sortMode }}">

  <div class="lam-filter-row">
    <div class="lam-search">
      <i class="bi bi-search"></i>
      <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor HP..." aria-label="Cari nama atau nomor HP" autocomplete="off">
    </div>

    <select name="position_id" class="lam-select lam-select-pos" aria-label="Filter posisi yang dilamar">
      <option value="">Semua posisi</option>
      @foreach($positions as $p)
        <option value="{{ $p->id }}" {{ (string) request('position_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->full_title }}</option>
      @endforeach
    </select>

    <select name="status" class="lam-select lam-select-status" aria-label="Filter status lamaran">
      <option value="">Semua status</option>
      @foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)
        <option {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
      @endforeach
    </select>

    <button type="button" class="lam-more-btn {{ $advActive ? 'has-filter' : '' }}" data-toggle="collapse" data-target="#lamAdvanced" aria-expanded="{{ $advActive ? 'true' : 'false' }}" aria-controls="lamAdvanced">
      <i class="bi bi-sliders2"></i> Filter lain
      <span class="lam-badge" id="filterBadge" style="{{ $advCount ? '' : 'display:none' }}">{{ $advCount }}</span>
      <i class="bi bi-chevron-down lam-more-caret"></i>
    </button>

    <a href="{{ route('admin.applications.index') }}" class="lam-reset" title="Hapus semua filter">Reset</a>
  </div>

  <div class="collapse {{ $advActive ? 'show' : '' }}" id="lamAdvanced">
    <div class="lam-adv">
      <div class="lam-field">
        <label for="lamTanggal">Tanggal masuk</label>
        <input type="date" name="tanggal" id="lamTanggal" class="lam-input" value="{{ request('tanggal') }}">
      </div>

      <div class="lam-field">
        <label for="lamEducation">Pendidikan</label>
        <select name="education" id="lamEducation" class="lam-input">
          <option value="">Semua</option>
          @foreach(['SD','SMP','SMA/SMK','D3','D4/S1','S2','S3'] as $e)
            <option {{ request('education') == $e ? 'selected' : '' }}>{{ $e }}</option>
          @endforeach
        </select>
      </div>

      <div class="lam-field">
        <label for="lamSim">SIM</label>
        <select name="sim" id="lamSim" class="lam-input">
          <option value="">Semua</option>
          @foreach(['A','B','C','A dan C','Tidak Punya'] as $s)
            <option {{ request('sim') == $s ? 'selected' : '' }}>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div class="lam-field">
        <label for="lamUmur">Umur</label>
        <select name="umur_range" id="lamUmur" class="lam-input">
          <option value="">Semua umur</option>
          <option value="15-20" {{ request('umur_range') == '15-20' ? 'selected' : '' }}>≤ 20 thn</option>
          <option value="21-25" {{ request('umur_range') == '21-25' ? 'selected' : '' }}>21–25 thn</option>
          <option value="26-30" {{ request('umur_range') == '26-30' ? 'selected' : '' }}>26–30 thn</option>
          <option value="31-35" {{ request('umur_range') == '31-35' ? 'selected' : '' }}>31–35 thn</option>
          <option value="36-40" {{ request('umur_range') == '36-40' ? 'selected' : '' }}>36–40 thn</option>
          <option value="41-70" {{ request('umur_range') == '41-70' ? 'selected' : '' }}>41+ thn</option>
        </select>
      </div>

      <div class="lam-field">
        <label for="lamGaji">Ekspektasi gaji</label>
        <select name="gaji_range" id="lamGaji" class="lam-input">
          <option value="">Semua gaji</option>
          <option value="0-3000000" {{ request('gaji_range') == '0-3000000' ? 'selected' : '' }}>&lt; Rp 3 jt</option>
          <option value="3000000-5000000" {{ request('gaji_range') == '3000000-5000000' ? 'selected' : '' }}>Rp 3–5 jt</option>
          <option value="5000000-8000000" {{ request('gaji_range') == '5000000-8000000' ? 'selected' : '' }}>Rp 5–8 jt</option>
          <option value="8000000-12000000" {{ request('gaji_range') == '8000000-12000000' ? 'selected' : '' }}>Rp 8–12 jt</option>
          <option value="12000000-1000000000" {{ request('gaji_range') == '12000000-1000000000' ? 'selected' : '' }}>&gt; Rp 12 jt</option>
        </select>
      </div>

      <div class="lam-field">
        <label for="lamLembur">Lembur</label>
        <select name="lembur" id="lamLembur" class="lam-input">
          <option value="">Semua</option>
          <option value="1" {{ request('lembur') === '1' ? 'selected' : '' }}>Bersedia lembur</option>
          <option value="0" {{ request('lembur') === '0' ? 'selected' : '' }}>Tidak lembur</option>
        </select>
      </div>
    </div>

    <div class="lam-bulk">
      <p class="lam-bulk-label mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Ubah status <strong>semua hasil filter</strong> <span>(bukan hanya yang dicentang)</span></p>
      <select id="targetStatus" aria-label="Ubah status semua hasil filter">
        <option value="">Pilih status baru...</option>
        @foreach(['Baru','Seleksi','Interview','Diterima','Ditolak'] as $s)
          <option>{{ $s }}</option>
        @endforeach
      </select>
    </div>
  </div>
</form>

<div class="lam-results" id="lamResultsBar">
  <div>
    <div class="lam-total"><strong id="toolbarTotal">{{ $applicants->total() }}</strong> pelamar</div>
    <div class="lam-selection" id="selectionHint">Centang 2–4 pelamar untuk dibandingkan.</div>
  </div>
  <div class="lam-results-right">
    <div class="lam-sort" role="group" aria-label="Urutkan pelamar">
      <a href="{{ route('admin.applications.index', \Illuminate\Support\Arr::except(request()->query(), ['sort', 'page'])) }}"
         class="lam-sort-btn {{ !in_array($sortMode, ['skor', 'ai']) ? 'active' : '' }}" data-sort="" title="Urutkan dari lamaran terbaru">
        <i class="bi bi-clock"></i> Terbaru
      </a>
      <a href="{{ route('admin.applications.index', array_merge(\Illuminate\Support\Arr::except(request()->query(), ['page']), ['sort' => 'skor'])) }}"
         class="lam-sort-btn {{ $sortMode === 'skor' ? 'active' : '' }}" data-sort="skor" title="Urutkan berdasarkan skor kecocokan keyword">
        <i class="bi bi-trophy"></i> Skor
      </a>
      @if($aiEnabled)
      <a href="{{ route('admin.applications.index', array_merge(\Illuminate\Support\Arr::except(request()->query(), ['page']), ['sort' => 'ai'])) }}"
         class="lam-sort-btn {{ $sortMode === 'ai' ? 'active' : '' }}" data-sort="ai" title="Urutkan berdasarkan skor evaluasi AI">
        <i class="bi bi-stars"></i> Skor AI
      </a>
      @endif
    </div>
    <button type="button" class="lam-compare" id="compareBtn" disabled>
      <i class="bi bi-columns-gap"></i> Bandingkan
      <span class="lam-compare-count" id="compareCount" style="display:none">0</span>
    </button>
  </div>
</div>

<div id="lamaranList">
  @include('admin.applications.partials.list')
</div>

<button type="button" id="toTop" title="Kembali ke atas"><i class="bi bi-arrow-up-short"></i></button>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  if(!window.Swal){
    window.Swal = { mixin: function(){ return { fire: function(o){ alert(o.title || ''); } }; }, fire: function(o){ o = o || {}; if(o.showCancelButton){ return Promise.resolve({ isConfirmed: confirm((o.title || '') + '\n' + (o.text || '')) }); } alert((o.title || '') + '\n' + (o.text || '')); return Promise.resolve({ isConfirmed: true }); } };
  }
  var Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 2600, timerProgressBar: true });

  var form = document.getElementById('filterForm');
  var box = document.getElementById('lamaranList');
  var badge = document.getElementById('filterBadge');
  var moreBtn = document.querySelector('.lam-more-btn');
  var compareBtn = document.getElementById('compareBtn');
  var compareCount = document.getElementById('compareCount');
  var selectionHint = document.getElementById('selectionHint');
  var toolbarTotal = document.getElementById('toolbarTotal');
  var filterKeys = ['tanggal', 'education', 'sim', 'umur_range', 'gaji_range', 'lembur'];
  var sortLinks = document.querySelectorAll('.lam-sort-btn');
  var qt;
  var reqSeq = 0;

  function baseParams(){
    var p = new URLSearchParams(new FormData(form));
    Array.from(p.keys()).forEach(function(k){ if ((p.get(k) || '') === '') { p.delete(k); } });
    p.delete('page');
    return p;
  }
  function queryString(){ return baseParams().toString(); }

  function setSort(value){
    var input = form.querySelector('input[name="sort"]');
    if(input){ input.value = value || ''; }
    Array.prototype.forEach.call(sortLinks, function(a){
      a.classList.toggle('active', (a.dataset.sort || '') === (value || ''));
    });
  }

  function refreshBadge(){
    var p = new URLSearchParams(new FormData(form));
    var n = 0;
    filterKeys.forEach(function(k){ if ((p.get(k) || '').trim() !== '') { n++; } });
    if(badge){ badge.textContent = n; badge.style.display = n ? 'inline-flex' : 'none'; }
    if(moreBtn){ moreBtn.classList.toggle('has-filter', n > 0); }
  }

  function syncLinks(){
    var qs = queryString();
    Array.prototype.forEach.call(document.querySelectorAll('.export-link'), function(a){
      a.href = a.dataset.base + (qs ? '?' + qs : '');
    });
    Array.prototype.forEach.call(sortLinks, function(a){
      var p = baseParams();
      var s = a.dataset.sort || '';
      if(s){ p.set('sort', s); } else { p.delete('sort'); }
      var str = p.toString();
      a.href = form.action + (str ? '?' + str : '');
    });
  }

  function syncToolbar(){
    var total = document.getElementById('resultTotal');
    if(total && toolbarTotal){ toolbarTotal.textContent = total.dataset.total; }
  }

  function updateSelection(){
    var checked = box.querySelectorAll('.row-check:checked');
    var n = checked.length;
    Array.prototype.forEach.call(box.querySelectorAll('.lam-row'), function(row){
      var c = row.querySelector('.row-check');
      row.classList.toggle('is-selected', !!(c && c.checked));
    });
    if(compareBtn){ compareBtn.disabled = n < 2 || n > 4; }
    if(compareCount){
      compareCount.textContent = n;
      compareCount.style.display = n ? 'inline-flex' : 'none';
    }
    if(selectionHint){
      selectionHint.classList.toggle('is-warn', n > 4);
      selectionHint.textContent = n === 0
        ? 'Centang 2–4 pelamar untuk dibandingkan.'
        : (n === 1
          ? 'Pilih 1 pelamar lagi untuk dibandingkan.'
          : (n > 4 ? 'Maksimal 4 pelamar. Kurangi pilihan.' : n + ' pelamar dipilih untuk dibandingkan.'));
    }
  }

  function renderList(html, url, goTop){
    box.innerHTML = html;
    box.style.opacity = '1';
    if(url){ history.replaceState(null, '', url); }
    syncLinks();
    refreshBadge();
    syncToolbar();
    updateSelection();
    if(goTop && box.scrollIntoView){ box.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  }

  function fetchList(){
    var qs = queryString();
    var url = form.action + (qs ? '?' + qs : '');
    var seq = ++reqSeq;
    box.style.opacity = '0.55';
    box.setAttribute('aria-busy', 'true');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){ if(seq !== reqSeq){ return; } box.removeAttribute('aria-busy'); renderList(html, url, false); })
      .catch(function(){ if(seq !== reqSeq){ return; } window.location.href = url; });
  }

  function loadPage(url){
    var seq = ++reqSeq;
    box.style.opacity = '0.55';
    box.setAttribute('aria-busy', 'true');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function(r){ if(!r.ok) throw 0; return r.text(); })
      .then(function(html){ if(seq !== reqSeq){ return; } box.removeAttribute('aria-busy'); renderList(html, url, true); })
      .catch(function(){ if(seq !== reqSeq){ return; } window.location.href = url; });
  }

  jQuery(form).on('input', function(e){
    if(e.target && e.target.id === 'targetStatus'){ return; }
    refreshBadge();
    clearTimeout(qt);
    qt = setTimeout(fetchList, e.target && e.target.name === 'q' ? 450 : 300);
  });
  jQuery(form).on('change', function(e){
    if(e.target && e.target.id === 'targetStatus'){ return; }
    refreshBadge();
    clearTimeout(qt);
    fetchList();
  });
  form.addEventListener('submit', function(e){ e.preventDefault(); clearTimeout(qt); fetchList(); });

  Array.prototype.forEach.call(sortLinks, function(a){
    a.addEventListener('click', function(e){
      e.preventDefault();
      clearTimeout(qt);
      setSort(this.dataset.sort || '');
      fetchList();
    });
  });

  box.addEventListener('change', function(e){
    if(e.target && e.target.classList.contains('row-check')){ updateSelection(); }
  });

  box.addEventListener('click', function(e){
    var qa = e.target.closest('.qa-btn');
    if(qa){
      if(qa.disabled){ return; }
      if(qa.dataset.status === 'Ditolak'){
        Swal.fire({ title: 'Tolak pelamar?', text: qa.dataset.nama + ' akan ditandai Ditolak.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, tolak', cancelButtonText: 'Batal' }).then(function(r){ if(r.isConfirmed){ doQa(qa); } });
      } else {
        doQa(qa);
      }
      return;
    }

    var pg = e.target.closest('.lam-pagination a');
    if(pg){ e.preventDefault(); loadPage(pg.href); return; }

    var chip = e.target.closest('.chip-x');
    if(chip){
      if(chip.hasAttribute('data-clear-all')){
        Array.prototype.forEach.call(form.querySelectorAll('input, select'), function(el){
          if(el.name !== 'sort'){ el.value = ''; }
        });
        setSort('');
      } else {
        var input = form.querySelector('[name="' + chip.dataset.key + '"]');
        if(input){ input.value = ''; }
      }
      clearTimeout(qt);
      refreshBadge();
      fetchList();
    }
  });

  var targetStatus = document.getElementById('targetStatus');
  if(targetStatus){
    targetStatus.addEventListener('change', function(){
      var st = this.value;
      if(!st){ return; }
      var total = document.getElementById('resultTotal');
      var n = total ? total.dataset.total : '?';
      var sel = this;
      Swal.fire({ title: 'Terapkan ke hasil filter?', html: '<b>' + n + '</b> lamaran akan diubah ke "<b>' + st + '</b>".', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, jalankan', cancelButtonText: 'Batal' }).then(function(r){
        if(!r.isConfirmed){ sel.value = ''; return; }
        fetch('{{ route('admin.applications.bulkFiltered') }}?' + queryString(), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new URLSearchParams({ target_status: st }) })
          .then(function(rr){ if(!rr.ok) throw 0; return rr.json(); })
          .then(function(j){ sel.value = ''; Toast.fire({ icon: 'success', title: j.count + ' lamaran → ' + j.status }); fetchList(); })
          .catch(function(){ sel.value = ''; Toast.fire({ icon: 'error', title: 'Gagal menyimpan.' }); });
      });
    });
  }

  function doQa(qa){
    qa.disabled = true;
    fetch(qa.dataset.url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: new URLSearchParams({ status: qa.dataset.status }) })
      .then(function(r){ if(!r.ok) throw 0; return r.json(); })
      .then(function(j){ Toast.fire({ icon: 'success', title: j.nama + ' → ' + j.status }); fetchList(); })
      .catch(function(){ qa.disabled = false; Toast.fire({ icon: 'error', title: 'Gagal menyimpan.' }); });
  }

  if(compareBtn){
    compareBtn.addEventListener('click', function(){
      var ids = Array.prototype.map.call(box.querySelectorAll('.row-check:checked'), function(c){ return c.value; });
      if(ids.length < 2 || ids.length > 4){ return; }
      window.location.href = '{{ route('admin.applications.compare') }}?' + ids.map(function(id){ return 'ids[]=' + encodeURIComponent(id); }).join('&');
    });
  }

  var toTop = document.getElementById('toTop');
  window.addEventListener('scroll', function(){ toTop.style.display = window.pageYOffset > 300 ? 'block' : 'none'; });
  toTop.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });

  refreshBadge();
  syncLinks();
  updateSelection();
})();
</script>
@endpush
@endsection
