@extends('layouts.public')
@section('title', $position->title . ' | LSF')
@section('hero_title', $position->branch ? $position->branch->name : 'Recruitment')
@section('hero_sub', $position->title . ' — ' . $position->location)

@section('content')
<style>
  .job-container { max-width: 850px; margin: 0 auto; }
  
  /* Hero / Main Card */
  .job-header-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; }
  .job-title { font-size: 24px; font-weight: 800; color: #1e293b; margin-bottom: 6px; line-height: 1.3; }
  .job-company { font-size: 15px; color: #0f4c81; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 6px; }
  .job-company a { color: inherit; text-decoration: none; }
  .job-company a:hover { text-decoration: underline; }
  
  /* Meta Grid */
  .job-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 8px; }
  .meta-item { display: flex; align-items: flex-start; gap: 12px; }
  .meta-icon { width: 36px; height: 36px; border-radius: 8px; background: #eef6ff; color: #0f4c81; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
  .meta-text { line-height: 1.4; }
  .meta-label { font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; display: block; }
  .meta-value { font-size: 13.5px; color: #334155; font-weight: 600; }

  /* Buttons */
  .btn-lamar-utama { background: #0f4c81; border: none; color: #fff; font-weight: 700; padding: 10px 28px; border-radius: 8px; font-size: 15px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(15, 76, 129, 0.2); }
  .btn-lamar-utama:hover { background: #0c3e6a; color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(15, 76, 129, 0.3); }
  
  /* Content Sections */
  .content-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
  .section-title { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
  
  .job-desc-text { font-size: 14.5px; color: #334155; line-height: 1.7; }
  .job-desc-text ul { padding-left: 0; list-style: none; margin-bottom: 16px; }
  .job-desc-text li { position: relative; padding-left: 24px; margin-bottom: 8px; }
  .job-desc-text li::before { content: "\F287"; font-family: "bootstrap-icons"; position: absolute; left: 0; top: 1px; color: #10b981; font-weight: bold; }

  /* Match Chips */
  .skill-chip { display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0; color: #475569; font-size: 12.5px; font-weight: 500; border-radius: 20px; padding: 6px 14px; margin: 0 6px 8px 0; }
  
  /* Other Jobs */
  .other-job-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: block; color: inherit; transition: all 0.2s; }
  .other-job-card:hover { border-color: #0f4c81; background: #f8fafc; text-decoration: none; color: inherit; transform: translateY(-2px); }
</style>

<div class="job-container">
  <div class="mb-3">
    <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left"></i> Kembali ke Daftar Lowongan
    </a>
  </div>

  <!-- HEADER CARD -->
  <div class="job-header-card">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
      <div>
        <h1 class="job-title">{{ $position->title }}</h1>
        <div class="job-company">
          <i class="bi bi-building"></i>
          {{ $position->branch ? $position->branch->name : 'LSF' }}
          <i class="bi bi-patch-check-fill text-primary" title="Perusahaan Terverifikasi"></i>
          @if($position->branch)
            <span class="text-muted mx-1">•</span> 
            <a href="{{ route('jobs.branch', $position->branch->name) }}" class="text-muted font-weight-normal" style="font-size: 13px;">Lihat lowongan cabang ini</a>
          @endif
        </div>
      </div>
      <div class="text-right">
        <span class="badge badge-success mb-2 px-2 py-1"><i class="bi bi-clock-history"></i> Diposting {{ $position->created_at ? $position->created_at->diffForHumans() : 'Baru saja' }}</span>
        @if($position->applicants_count > 0)
          <div style="font-size:12px; color:#64748b;"><i class="bi bi-people-fill"></i> {{ $position->applicants_count > 10 ? 'Banyak pelamar' : $position->applicants_count . ' pelamar' }}</div>
        @endif
      </div>
    </div>

    <!-- META DATA GRID -->
    <div class="job-meta-grid">
      <div class="meta-item">
        <div class="meta-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="meta-text">
          <span class="meta-label">Lokasi Penempatan</span>
          <span class="meta-value">{{ $position->location }}@if($position->branch && $position->branch->location && $position->branch->location !== $position->location), {{ $position->branch->location }}@endif</span>
        </div>
      </div>
      <div class="meta-item">
        <div class="meta-icon"><i class="bi bi-briefcase-fill"></i></div>
        <div class="meta-text">
          <span class="meta-label">Tipe Pekerjaan</span>
          <span class="meta-value">Penuh Waktu (Full-time)</span>
        </div>
      </div>
      @if($position->pendidikan_minimal)
      <div class="meta-item">
        <div class="meta-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="meta-text">
          <span class="meta-label">Pendidikan Min.</span>
          <span class="meta-value">{{ $position->pendidikan_minimal }}</span>
        </div>
      </div>
      @endif
      @if($position->gaji)
      <div class="meta-item">
        <div class="meta-icon bg-success text-white"><i class="bi bi-cash-stack"></i></div>
        <div class="meta-text">
          <span class="meta-label">Gaji Ditawarkan</span>
          <span class="meta-value">Rp {{ number_format($position->gaji, 0, ',', '.') }} <small class="text-muted font-weight-normal">/ bln</small></span>
        </div>
      </div>
      @endif
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
      <a href="{{ route('lamaran.form', $position->id) }}" class="btn btn-lamar-utama">
        <i class="bi bi-send-fill mr-1"></i> Lamar Posisi Ini
      </a>
      <span class="text-muted" style="font-size: 12.5px;"><i class="bi bi-lightning-charge-fill text-warning"></i> Lamaran Cepat (< 2 Menit)</span>
    </div>
  </div>

  @php
  $chips = collect($position->skill_list)->pluck('tag')->map(function ($t) { return ucwords($t); })->merge(collect($position->requirement_list)->map(function ($l) { $l = trim(preg_replace('/^[-•*\d.)\s]+/u', '', $l)); return \Illuminate\Support\Str::limit(ucwords(mb_strtolower(mb_substr($l, 0, 40))), 40); }))->unique()->values();
  @endphp
  
  @if($chips->count())
  <div class="content-card">
    <h6 class="section-title"><i class="bi bi-tags-fill text-secondary mr-2"></i>Keahlian & Syarat Utama</h6>
    <div id="matchChips">
      @foreach($chips as $i => $c)
        <span class="skill-chip" @if($i >= 8) style="display:none" data-extra="1" @endif>{{ $c }}</span>
      @endforeach
    </div>
    @if($chips->count() > 8)
      <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-2" id="matchMore" style="font-weight: 600;">+{{ $chips->count() - 8 }} Tampilkan Semua ▾</button>
    @endif
  </div>
  @endif

  <div class="content-card job-desc-text">
    @if($position->description)
    <h6 class="section-title"><i class="bi bi-file-text-fill text-secondary mr-2"></i>Deskripsi Pekerjaan</h6>
    <ul class="mb-4">
      @foreach(preg_split('/\r\n|\r|\n/', $position->description) as $line)
      @if(trim($line) !== '')<li>{{ trim($line) }}</li>@endif
      @endforeach
    </ul>
    @endif

    <h6 class="section-title"><i class="bi bi-ui-checks-grid text-secondary mr-2"></i>Kualifikasi Khusus</h6>
    <ul>
      @if($position->pendidikan_minimal)<li>Pendidikan minimal <strong>{{ $position->pendidikan_minimal }}</strong>.</li>@endif
      @if($position->usia_min || $position->usia_maks)<li>Rentang usia antara <strong>{{ $position->usia_min ?: '…' }} – {{ $position->usia_maks ?: '…' }} tahun</strong>.</li>@endif
      @if($position->sim_list)<li>Wajib memiliki lisensi mengemudi <strong>SIM {{ implode(' / ', $position->sim_list) }}</strong> yang masih aktif.</li>@endif
      @if($position->butuh_lembur)<li>Bersedia bekerja lembur di luar jam kerja reguler jika dibutuhkan.</li>@endif
      @foreach($position->requirement_list as $r)<li>{{ $r }}</li>@endforeach
    </ul>
  </div>

  <!-- CALL TO ACTION BAWAH -->
  <div class="card bg-primary text-white border-0 shadow text-center p-4 mb-4" style="border-radius: 12px;">
    <h5 class="font-weight-bold mb-1">Apakah Anda kandidat yang kami cari?</h5>
    <p class="mb-3" style="opacity: 0.9; font-size: 14px;">Jangan lewatkan kesempatan ini. Proses rekrutmen kami cepat dan transparan.</p>
    <div>
      <a href="{{ route('lamaran.form', $position->id) }}" class="btn btn-light btn-lg font-weight-bold text-primary px-5" style="border-radius: 8px;">
        Lamar Sekarang <i class="bi bi-arrow-right-short"></i>
      </a>
    </div>
  </div>

  <!-- LOKER LAINNYA -->
  @if($others->count())
  <div class="mt-5 mb-3">
    <h6 class="font-weight-bold" style="font-size: 16px; color:#1e293b;">Lowongan Lain di {{ $position->branch ? $position->branch->name : 'Perusahaan Ini' }}</h6>
  </div>
  <div class="row">
    @foreach($others as $o)
    <div class="col-md-6 mb-3">
      <a href="{{ route('jobs.show', $o->id) }}" class="other-job-card bg-white h-100">
        <h6 class="font-weight-bold text-dark mb-1">{{ $o->title }}</h6>
        <div class="text-muted" style="font-size: 12.5px;"><i class="bi bi-geo-alt"></i> {{ $o->location }}</div>
      </a>
    </div>
    @endforeach
  </div>
  @endif

</div>

@push('scripts')
<script>
(function(){
  var more = document.getElementById('matchMore');
  if(more) {
    more.addEventListener('click', function(){
      var extras = document.querySelectorAll('#matchChips [data-extra]');
      var hidden = Array.prototype.some.call(extras, function(e){ return e.style.display === 'none'; });
      extras.forEach(function(e){ e.style.display = hidden ? 'inline-block' : 'none'; });
      more.textContent = hidden ? 'Sembunyikan ▴' : '+' + extras.length + ' Tampilkan Semua ▾';
    });
  }
})();
</script>
@endpush
@endsection