@extends('layouts.public')

@php
$companyName = $position->branch ? $position->branch->name : 'LSF Recruitment';
$metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags($position->description ?: implode(' ', $position->requirement_list))), 160);
$jobUrl = route('jobs.show', $position);
$descLines = collect(preg_split('/\r\n|\r|\n/', (string) $position->description))
    ->map(function ($line) { return trim(preg_replace('/^[-•*]\s*/u', '', $line)); })
    ->filter()
    ->values();
$skills = collect($position->skill_list)->pluck('tag')->map(function ($tag) { return ucwords($tag); })->unique()->values();
$benefits = collect($position->benefit_list);
$requirementsText = mb_strtolower(implode(' ', $position->requirement_list));
$showEducation = $position->pendidikan_minimal && !preg_match('/\bpendidikan\b/', $requirementsText);
$showAge = ($position->usia_min || $position->usia_maks) && !preg_match('/\b(usia|umur)\b/', $requirementsText);
$showSim = count($position->sim_list) > 0 && !preg_match('/\bsim\b/', $requirementsText);
$showOvertime = $position->butuh_lembur && !preg_match('/\blembur\b/', $requirementsText);
$hasQualifications = $showEducation || $showAge || $showSim || $showOvertime || count($position->requirement_list) > 0;
$salaryLabel = $position->gaji ? 'Rp ' . number_format($position->gaji, 0, ',', '.') : 'Kompetitif';
$postedLabel = $position->created_at ? $position->created_at->locale('id')->diffForHumans() : 'Baru dibuka';
@endphp

@section('title', e($position->title) . ' — ' . e($companyName) . ' | LSF')

@push('meta')
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:title" content="{{ $position->title }} — {{ $companyName }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $jobUrl }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="LSF Recruitment">
@endpush

@section('page')
<style>
  .job-page {
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --brand: #0f4c81;
    --brand-deep: #0b3a63;
    background: #eef2f7;
    min-height: 100vh;
    padding-bottom: 1px;
  }
  .job-page * { box-sizing: border-box; }

  /* Hero */
  .job-hero {
    position: relative;
    overflow: hidden;
    padding: 24px 0 62px;
    background: linear-gradient(120deg, #092d4f 0%, #0b3a63 42%, #0f4c81 74%, #16659b 100%);
    color: #fff;
  }
  .job-hero::before {
    content: "";
    position: absolute;
    width: 520px;
    height: 520px;
    right: -150px;
    top: -260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(52, 211, 153, .2) 0%, rgba(52, 211, 153, 0) 68%);
    pointer-events: none;
  }
  .job-hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(255, 255, 255, .3) 1px, transparent 1px);
    background-size: 26px 26px;
    opacity: .14;
    pointer-events: none;
  }
  .job-hero-inner {
    position: relative;
    z-index: 1;
    max-width: 992px;
    margin: 0 auto;
    padding: 0 24px;
  }
  .job-title-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px 20px;
  }
  .job-title-row h1 { flex: 0 1 auto; }
  .job-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(52, 211, 153, .3);
    background: rgba(16, 185, 129, .14);
    color: #a7f3d0;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
  }
  .job-status .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, .16);
  }
  .job-hero h1 {
    margin: 0 0 10px;
    color: #fff;
    font-size: clamp(26px, 3.4vw, 36px);
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -.025em;
  }
  .job-company-line {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 12px;
  }
  .company-mark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, .18);
    background: rgba(255, 255, 255, .12);
    color: #fff;
    font-size: 15px;
    font-weight: 800;
  }
  .company-copy { min-width: 0; }
  .company-copy strong {
    display: block;
    color: #fff;
    font-size: 13.5px;
    line-height: 1.35;
  }
  .company-copy span {
    display: block;
    margin-top: 1px;
    color: rgba(255, 255, 255, .65);
    font-size: 12px;
  }
  .verified {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
    padding: 3px 8px;
    border-radius: 999px;
    border: 1px solid rgba(186, 230, 253, .24);
    background: rgba(186, 230, 253, .1);
    color: #bae6fd;
    font-size: 10px;
    font-weight: 700;
  }
  .job-hero-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 0;
  }
  .job-hero-chips span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, .14);
    background: rgba(255, 255, 255, .09);
    color: rgba(255, 255, 255, .9);
    font-size: 11.5px;
    font-weight: 600;
  }
  .job-hero-chips span.pay {
    border-color: rgba(52, 211, 153, .3);
    background: rgba(52, 211, 153, .14);
    color: #d1fae5;
  }
  .btn-hero-apply {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 12px 22px;
    border-radius: 9px;
    background: #fff;
    color: var(--brand-deep) !important;
    font-size: 13.5px;
    font-weight: 800;
    text-decoration: none;
    box-shadow: 0 10px 24px rgba(0, 0, 0, .16);
    transition: transform .18s, box-shadow .18s, background .18s;
  }
  .btn-hero-apply:hover {
    background: #f0f7ff;
    color: var(--brand-deep) !important;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, .22);
  }

  /* Shell */
  .job-shell {
    position: relative;
    z-index: 2;
    max-width: 992px;
    margin: -44px auto 0;
    padding: 0 24px 30px;
  }
  .job-panel {
    margin-bottom: 12px;
    padding: 16px 22px 18px;
    border-radius: 14px;
    border: 1px solid rgba(226, 232, 240, .9);
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03), 0 18px 40px -30px rgba(15, 23, 42, .25);
  }
  .job-panel:last-child { margin-bottom: 0; }
  .job-panel h2 {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 10px;
    color: var(--ink);
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -.015em;
  }
  .panel-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    border-radius: 8px;
    background: #edf4fb;
    color: var(--brand);
    font-size: 13px;
  }
  .job-list-clean {
    display: grid;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .job-list-clean li {
    position: relative;
    padding-left: 24px;
    color: #3f4c63;
    font-size: 13.5px;
    line-height: 1.55;
  }
  .job-list-clean li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 3px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 1px solid #bbf7d0;
    background: #ecfdf5;
  }
  .job-list-clean li::after {
    content: "";
    position: absolute;
    left: 4px;
    top: 6.5px;
    width: 6px;
    height: 3.5px;
    border-left: 1.6px solid #059669;
    border-bottom: 1.6px solid #059669;
    transform: rotate(-45deg);
  }
  .skill-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  .skill-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: #f8fafc;
    color: #42526b;
    font-size: 11.5px;
    font-weight: 600;
    transition: background .18s, border-color .18s, color .18s, transform .18s;
  }
  .skill-pill i { color: var(--brand); font-size: 10px; }
  .benefit-list { grid-template-columns: 1fr; }
  @media (min-width: 1200px) {
    .benefit-list { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; }
  }
  .skill-pill:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: var(--brand);
    transform: translateY(-1px);
  }
  .job-footnote {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 14px 0 0;
    color: #94a3b8;
    font-size: 11px;
    text-align: center;
  }

  /* Mobile CTA */
  .mobile-apply-bar { display: none; }

  @media (max-width: 991.98px) {
    .job-hero { padding: 18px 0 56px; }
    .job-hero-inner { padding: 0 18px; }
    .job-hero .btn-hero-apply { display: none; }
    .job-shell { margin-top: -38px; padding: 0 16px 86px; }
    .job-panel { margin-bottom: 10px; padding: 16px; }
    .job-footnote { margin-top: 12px; }
    .mobile-apply-bar {
      position: fixed;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 1040;
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 16px calc(8px + env(safe-area-inset-bottom));
      border-top: 1px solid var(--line);
      background: rgba(255, 255, 255, .97);
      box-shadow: 0 -10px 30px rgba(15, 23, 42, .08);
      backdrop-filter: blur(12px);
    }
    .mobile-apply-meta { flex: 1; min-width: 0; line-height: 1.2; }
    .mobile-apply-meta span {
      display: block;
      margin-bottom: 1px;
      color: #94a3b8;
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
    }
    .mobile-apply-meta strong {
      display: block;
      overflow: hidden;
      color: var(--ink);
      font-size: 13px;
      font-weight: 800;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .btn-mobile-apply {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      flex: 0 0 auto;
      padding: 10px 16px;
      border-radius: 9px;
      background: var(--brand);
      box-shadow: 0 8px 18px -8px rgba(15, 76, 129, .55);
      color: #fff !important;
      font-size: 12.5px;
      font-weight: 700;
      text-decoration: none !important;
    }
    .mobile-apply-bar.is-single { justify-content: center; }
    .mobile-apply-bar.is-single .btn-mobile-apply { flex: 1 1 auto; justify-content: center; }
  }

  @media (max-width: 575.98px) {
    .job-hero { padding: 16px 0 52px; }
    .job-hero h1 { margin-bottom: 10px; font-size: 24px; line-height: 1.15; }
    .job-status { padding: 3px 9px; font-size: 9.5px; }
    .job-company-line { gap: 9px; margin-bottom: 10px; }
    .company-mark { width: 34px; height: 34px; flex-basis: 34px; border-radius: 9px; font-size: 14px; }
    .company-copy strong { font-size: 12.5px; }
    .company-copy span { font-size: 11px; }
    .verified { padding: 3px 7px; font-size: 9.5px; }
    .job-hero-chips { gap: 5px; }
    .job-hero-chips span { padding: 5px 8px; font-size: 10.5px; gap: 4px; }
    .job-shell { padding: 0 12px 80px; }
    .job-panel { padding: 14px; margin-bottom: 9px; border-radius: 12px; }
    .job-panel h2 { gap: 8px; margin-bottom: 9px; font-size: 16px; }
    .panel-icon { width: 26px; height: 26px; flex-basis: 26px; border-radius: 7px; font-size: 12.5px; }
    .job-list-clean { gap: 4px; }
    .job-list-clean li { padding-left: 23px; font-size: 12.8px; line-height: 1.55; }
    .job-list-clean li::before { top: 3px; width: 14px; height: 14px; }
    .job-list-clean li::after { top: 6.5px; left: 4px; width: 5.5px; height: 3px; }
    .skill-cloud { gap: 5px; }
    .skill-pill { padding: 4px 8px; font-size: 11px; gap: 4px; }
    .btn-mobile-apply { padding: 10px 14px; font-size: 12px; }
    .job-footnote { margin-top: 10px; font-size: 10px; }
  }
</style>

<div class="job-page">
  <section class="job-hero">
    <div class="job-hero-inner">
      <div class="job-title-row">
        <h1>{{ $position->title }}</h1>
        <a class="btn-hero-apply" href="{{ route('lamaran.form', $position) }}">
          Lamar Sekarang <i class="bi bi-arrow-up-right"></i>
        </a>
      </div>

      <div class="job-company-line">
        <span class="company-mark">{{ mb_strtoupper(mb_substr($companyName, 0, 1)) }}</span>
        <div class="company-copy">
          <strong>{{ $companyName }}</strong>
          <span>
            <i class="bi bi-geo-alt"></i> {{ $position->location }}@if($position->branch && $position->branch->location && $position->branch->location !== $position->location), {{ $position->branch->location }}@endif
          </span>
        </div>
        <span class="job-status"><span class="dot"></span> Lowongan aktif</span>
        <span class="verified"><i class="bi bi-patch-check-fill"></i> Terverifikasi</span>
      </div>

      <div class="job-hero-chips">
        <span><i class="bi bi-briefcase"></i> Penuh waktu</span>
        @if($position->pendidikan_minimal)
          <span><i class="bi bi-mortarboard"></i> Min. {{ $position->pendidikan_minimal }}</span>
        @endif
        @if(count($position->sim_list))
          <span><i class="bi bi-card-checklist"></i> SIM {{ implode('/', $position->sim_list) }}</span>
        @endif
        @if($position->show_salary)
          <span class="pay"><i class="bi bi-cash-stack"></i> {{ $salaryLabel }}@if($position->gaji) / bln @endif</span>
        @endif
        <span><i class="bi bi-clock-history"></i> Diposting {{ $postedLabel }}</span>
      </div>
    </div>
  </section>

  <main class="job-shell">
    @if($skills->count())
    <section class="job-panel">
      <h2><span class="panel-icon"><i class="bi bi-stars"></i></span> Keahlian yang Dicari</h2>
      <div class="skill-cloud">
        @foreach($skills as $skill)
          <span class="skill-pill"><i class="bi bi-check2-circle"></i> {{ $skill }}</span>
        @endforeach
      </div>
    </section>
    @endif

    @if($position->show_benefits && $benefits->count())
    <section class="job-panel">
      <h2><span class="panel-icon"><i class="bi bi-gift"></i></span> Benefit yang Anda Dapat</h2>
      <ul class="job-list-clean benefit-list">
        @foreach($benefits as $benefit)
          <li>{{ $benefit }}</li>
        @endforeach
      </ul>
    </section>
    @endif

    @if($hasQualifications)
    <section class="job-panel">
      <h2><span class="panel-icon"><i class="bi bi-ui-checks"></i></span> Kualifikasi</h2>
      <ul class="job-list-clean">
        @if($showEducation)
          <li>Pendidikan minimal <strong>{{ $position->pendidikan_minimal }}</strong>.</li>
        @endif
        @if($showAge)
          <li>Rentang usia <strong>{{ $position->usia_min ?: '…' }} – {{ $position->usia_maks ?: '…' }} tahun</strong>.</li>
        @endif
        @if($showSim)
          <li>Memiliki SIM <strong>{{ implode(' / ', $position->sim_list) }}</strong> yang masih berlaku.</li>
        @endif
        @if($showOvertime)
          <li>Bersedia bekerja lembur saat dibutuhkan.</li>
        @endif
        @foreach($position->requirement_list as $requirement)
          <li>{{ $requirement }}</li>
        @endforeach
      </ul>
    </section>
    @endif

    @if($descLines->count())
    <section class="job-panel">
      <h2><span class="panel-icon"><i class="bi bi-file-text"></i></span> Deskripsi Pekerjaan</h2>
      <ul class="job-list-clean">
        @foreach($descLines as $line)
          <li>{{ $line }}</li>
        @endforeach
      </ul>
    </section>
    @endif

    <p class="job-footnote"><i class="bi bi-shield-check"></i> Data hanya untuk rekrutmen · Pengisian ±2 menit · Pastikan data lamaran sudah benar sebelum dikirim.</p>
  </main>

  <div class="mobile-apply-bar{{ $position->show_salary ? '' : ' is-single' }}">
    @if($position->show_salary)
      <div class="mobile-apply-meta">
        <span>Gaji</span>
        <strong>{{ $salaryLabel }}</strong>
      </div>
    @endif
    <a class="btn-mobile-apply" href="{{ route('lamaran.form', $position) }}">Lamar Sekarang <i class="bi bi-arrow-right"></i></a>
  </div>
</div>
@endsection
