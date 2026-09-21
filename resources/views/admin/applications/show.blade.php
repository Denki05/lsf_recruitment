@extends('layouts.admin')
@section('title', 'Detail Lamaran')
@section('content')
@php
    $ext = strtolower(pathinfo($applicant->file_original, PATHINFO_EXTENSION));
    $isPdf = $ext === 'pdf';

    $statuses     = ['Baru', 'Seleksi', 'Interview', 'Diterima', 'Ditolak'];
    $statusColors = ['Baru' => 'secondary', 'Seleksi' => 'warning', 'Interview' => 'info', 'Diterima' => 'success', 'Ditolak' => 'danger'];
    $statusHex    = ['Baru' => '#6c757d', 'Seleksi' => '#ffc107', 'Interview' => '#17a2b8', 'Diterima' => '#28a745', 'Ditolak' => '#dc3545'];
    $statusColor  = $statusColors[$applicant->status] ?? 'secondary';

    $fileIcons = [
        'pdf'  => ['bi-file-earmark-pdf-fill', '#c81e3a'],
        'doc'  => ['bi-file-earmark-word-fill', '#2b6cb0'],
        'docx' => ['bi-file-earmark-word-fill', '#2b6cb0'],
        'zip'  => ['bi-file-earmark-zip-fill', '#d69e2e'],
    ];
    $fileIcon = $fileIcons[$ext] ?? ['bi-file-earmark-fill', '#6b7a8d'];

    // Nomor WhatsApp format internasional (62...)
    $hpDigits = preg_replace('/\D/', '', (string) $applicant->no_hp);
    if (strpos($hpDigits, '62') === 0) {
        $wa = $hpDigits;
    } elseif (strpos($hpDigits, '0') === 0) {
        $wa = '62' . substr($hpDigits, 1);
    } else {
        $wa = '62' . $hpDigits;
    }
@endphp

<style>
.dt-card{padding:10px 14px;}
.dt-avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;flex-shrink:0;}
.dt-chip{display:inline-flex;align-items:center;gap:5px;background:#f1f4f8;border:1px solid var(--border);border-radius:20px;padding:1px 9px;font-size:11.5px;color:var(--text);margin:4px 5px 0 0;max-width:100%;}
.dt-info{display:grid;grid-template-columns:1fr;gap:0;}
.dt-group + .dt-group{border-top:1px solid var(--border);margin-top:10px;padding-top:10px;}
@media(min-width:768px){
  .dt-info{grid-template-columns:repeat(3,minmax(0,1fr));gap:0 22px;}
  .dt-group + .dt-group{border-top:0;margin-top:0;padding-top:0;border-left:1px solid var(--border);padding-left:22px;}
}
.dt-gtitle{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--brand);margin-bottom:2px;}
.dt-row{display:flex;justify-content:space-between;align-items:baseline;gap:12px;padding:6px 0;border-bottom:1px dashed var(--border);font-size:12.5px;}
.dt-row:last-child{border-bottom:0;}
.dt-row .k{color:var(--muted);white-space:nowrap;}
.dt-row .v{font-weight:600;text-align:right;word-break:break-word;min-width:0;}
.dt-copy{border:0;background:none;color:var(--muted);padding:0 2px;margin-left:2px;cursor:pointer;font-size:12px;line-height:1;}
.dt-copy:hover{color:var(--brand);}
.dt-copy.ok{color:#28a745;}
.dt-label{font-size:10.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;white-space:nowrap;}
.dt-label i{margin-right:3px;}
.dt-value{font-size:13px;font-weight:600;word-break:break-word;line-height:1.3;}
.dt-pills{display:flex;flex-wrap:wrap;gap:4px;}
.dt-pills label{margin:0;flex:1 1 auto;position:relative;}
.dt-pills input{position:absolute;opacity:0;pointer-events:none;}
.dt-pills span{display:block;text-align:center;padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--muted);background:#fff;transition:all .12s;}
.dt-pills span:hover{border-color:var(--brand);color:var(--brand);}
.dt-pills input:focus + span{box-shadow:0 0 0 2px rgba(15,76,129,.3);}
.dt-pills input:checked + span{color:#fff;border-color:transparent;}
.dt-pills .s-Baru input:checked + span{background:#6c757d;}
.dt-pills .s-Seleksi input:checked + span{background:#e0a800;}
.dt-pills .s-Interview input:checked + span{background:#17a2b8;}
.dt-pills .s-Diterima input:checked + span{background:#28a745;}
.dt-pills .s-Ditolak input:checked + span{background:#dc3545;}
.dt-tl{list-style:none;margin:0;padding:0 4px 0 4px;max-height:230px;overflow-y:auto;}
.dt-tl li{position:relative;padding:0 0 9px 18px;font-size:12px;}
.dt-tl li:before{content:"";position:absolute;left:4px;top:12px;bottom:-2px;width:2px;background:var(--border);}
.dt-tl li:last-child{padding-bottom:0;}
.dt-tl li:last-child:before{display:none;}
.dt-tl .dot{position:absolute;left:0;top:3px;width:10px;height:10px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px var(--border);}
.dt-sub{color:var(--muted);font-size:11px;}
.dt-frame{position:absolute;top:0;left:0;width:100%;height:100%;border:0;}
#cvModal .modal-content{height:90vh;}
</style>

{{-- Bar atas --}}
<div class="mb-2"><a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-left"></i> Daftar Lamaran</a></div>

{{-- Header pelamar --}}
<div class="content-card dt-card mb-2" style="border-left:4px solid {{ $statusHex[$applicant->status] ?? '#6c757d' }}">
  <div class="d-flex flex-wrap align-items-center" style="gap:12px">
    <div class="dt-avatar">{{ mb_strtoupper(mb_substr($applicant->nama_lengkap, 0, 1)) }}</div>
    <div style="min-width:0;flex:1 1 260px">
      <div class="d-flex flex-wrap align-items-center" style="gap:8px">
        <h4 class="font-weight-bold mb-0" style="font-size:17px">{{ $applicant->nama_lengkap }}</h4>
        <span class="badge badge-{{ $statusColor }}" style="font-size:11.5px">{{ $applicant->status }}</span>
      </div>
      <div>
        <span class="dt-chip"><i class="bi bi-briefcase"></i> {{ $applicant->position->full_title ?? '-' }}</span>
        <span class="dt-chip"><i class="bi bi-clock"></i> Masuk {{ $applicant->created_at->format('d M Y, H:i') }}</span>
        @if($applicant->umur !== null)<span class="dt-chip"><i class="bi bi-person"></i> {{ $applicant->umur }} thn</span>@endif
        <span class="dt-chip" title="{{ $applicant->file_original }}"><i class="bi {{ $fileIcon[0] }}" style="color:{{ $fileIcon[1] }}"></i> <span class="text-truncate" style="max-width:220px">{{ $applicant->file_original }}</span> <span class="dt-sub">· {{ number_format(($applicant->file_size ?: 0) / 1024, 0) }} KB</span></span>
      </div>
    </div>
    <div class="d-flex flex-wrap" style="gap:5px">
      @if($isPdf)
      <button type="button" id="btnPreview" data-url="{{ route('admin.applications.preview', $applicant->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Lihat CV</button>
      @else
      <span title="Preview hanya tersedia untuk PDF" class="d-inline-block"><button type="button" class="btn btn-sm btn-outline-secondary" disabled style="pointer-events:none"><i class="bi bi-eye-slash"></i> Lihat CV</button></span>
      @endif
      <a href="{{ route('admin.applications.download', $applicant->id) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i> Download</a>
      <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success"><i class="bi bi-whatsapp"></i> WhatsApp</a>
    </div>
  </div>
  @if(count($zipList))<div class="alert alert-info mt-2 mb-0 py-1 px-2" style="font-size:12px"><strong>Isi ZIP:</strong> {{ implode(' · ', $zipList) }}</div>@endif
</div>

<div class="row">
  {{-- ===== KOLOM KIRI: data & penilaian ===== --}}
  <div class="col-lg-8 col-xl-9">

    <div class="content-card dt-card mb-2">
      <div class="dt-info">

        <div class="dt-group">
          <div class="dt-gtitle"><i class="bi bi-telephone"></i> Kontak</div>
          <div class="dt-row"><span class="k">HP / WA</span><span class="v">{{ $applicant->no_hp }}<button type="button" class="dt-copy" data-copy="{{ $applicant->no_hp }}" title="Salin nomor"><i class="bi bi-clipboard"></i></button></span></div>
          <div class="dt-row"><span class="k">Email</span><span class="v">@if($applicant->email)<a href="mailto:{{ $applicant->email }}">{{ $applicant->email }}</a><button type="button" class="dt-copy" data-copy="{{ $applicant->email }}" title="Salin email"><i class="bi bi-clipboard"></i></button>@else - @endif</span></div>
          <div class="dt-row"><span class="k">Sosmed</span><span class="v">{{ $applicant->sosmed ?: '-' }}</span></div>
        </div>

        <div class="dt-group">
          <div class="dt-gtitle"><i class="bi bi-person-vcard"></i> Profil</div>
          <div class="dt-row"><span class="k">Jenis Kelamin</span><span class="v">{{ $applicant->jenis_kelamin ?: '-' }}</span></div>
          <div class="dt-row"><span class="k">Tgl Lahir / Umur</span><span class="v">{{ $applicant->tanggal_lahir ? $applicant->tanggal_lahir->format('d M Y') : '-' }}{{ $applicant->umur !== null ? ' ('.$applicant->umur.' thn)' : '' }}</span></div>
          <div class="dt-row"><span class="k">Pendidikan</span><span class="v">{{ $applicant->education ?: '-' }}</span></div>
          <div class="dt-row"><span class="k">Domisili</span><span class="v">{{ $applicant->domisili ?: '-' }}</span></div>
        </div>

        <div class="dt-group">
          <div class="dt-gtitle"><i class="bi bi-sliders"></i> Preferensi Kerja</div>
          <div class="dt-row"><span class="k">Permintaan Gaji</span><span class="v">{{ $applicant->expected_salary !== null ? 'Rp '.number_format($applicant->expected_salary, 0, ',', '.') : '-' }}</span></div>
          <div class="dt-row"><span class="k">Bersedia Lembur</span><span class="v">{{ $applicant->willing_overtime ? 'Ya' : 'Tidak' }}</span></div>
          <div class="dt-row"><span class="k">SIM</span><span class="v">{{ $applicant->sim ?: '-' }}</span></div>
        </div>

      </div>
    </div>

    <div class="content-card dt-card mb-2">
      <h6 class="font-weight-bold mb-1"><i class="bi bi-briefcase"></i> Pengalaman Kerja</h6>
      @if($applicant->pengalaman_kerja)
      <div style="white-space:pre-wrap;word-break:break-word;font-size:13px;line-height:1.5;max-height:260px;overflow-y:auto">{{ $applicant->pengalaman_kerja }}</div>
      @else
      <div class="dt-sub">Belum diisi (lamaran ini masuk sebelum kolom pengalaman kerja ada).</div>
      @endif
    </div>

    <div class="content-card dt-card mb-2" style="border-left:3px solid var(--brand)">
      <div class="d-flex align-items-center" style="gap:10px">
        <h6 class="font-weight-bold mb-0 text-nowrap"><i class="bi bi-bullseye"></i> Saran Kecocokan <small style="color:var(--muted)">(bukan keputusan)</small></h6>
        @if($screening['scorable'] && $screening['score'] !== null)
        <div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-{{ $screening['score'] >= 70 ? 'success' : ($screening['score'] >= 40 ? 'warning' : 'danger') }}" style="width:{{ $screening['score'] }}%"></div></div>
        <span class="badge badge-{{ $screening['score'] >= 70 ? 'success' : ($screening['score'] >= 40 ? 'warning' : 'danger') }}" style="font-size:13px">{{ $screening['score'] }}%</span>
        @endif
      </div>
      @if($screening['scorable'] && $screening['score'] !== null)
      @if(count($screening['matched']))<div style="font-size:12px" class="mt-2"><span style="color:var(--muted)">Cocok:</span> @foreach($screening['matched'] as $m)<span class="badge badge-success" title="bobot {{ $m['weight'] }}">{{ $m['label'] }} ×{{ $m['weight'] }}</span> @endforeach</div>@endif
      @if(count($screening['missing']))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Hilang:</span> @foreach($screening['missing'] as $m)<span class="badge badge-light border" title="bobot {{ $m['weight'] }}">{{ $m['label'] }} ×{{ $m['weight'] }}</span> @endforeach</div>@endif
      @endif
      @if(!empty($screening['note']))<div style="font-size:12px;color:var(--muted)" class="mt-1"><i class="bi bi-info-circle"></i> {{ $screening['note'] }}</div>@endif
    </div>

    @if(config('recruitment.ai_enabled'))
    <div class="content-card dt-card mb-2" style="border-left:3px solid #6f42c1">
      <div class="d-flex justify-content-between align-items-center">
        <h6 class="font-weight-bold mb-0"><i class="bi bi-stars"></i> Evaluasi AI <small style="color:var(--muted)">(OpenRouter, saran — bukan keputusan)</small></h6>
        @if(!is_null($applicant->ai_score))<span class="badge badge-{{ $applicant->ai_score >= 70 ? 'success' : ($applicant->ai_score >= 40 ? 'warning' : 'danger') }}" style="font-size:13px">{{ $applicant->ai_score }}%</span>@endif
      </div>
      @if(!is_null($applicant->ai_score))
      <div style="font-size:12.5px" class="mt-1">{{ $applicant->ai_summary }}</div>
      @php($str = json_decode($applicant->ai_strengths ?: '[]', true) ?: [])
      @php($gaps = json_decode($applicant->ai_gaps ?: '[]', true) ?: [])
      @if(count($str))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Plus:</span> @foreach($str as $s)<span class="badge badge-success">{{ $s }}</span> @endforeach</div>@endif
      @if(count($gaps))<div style="font-size:12px" class="mt-1"><span style="color:var(--muted)">Minus:</span> @foreach($gaps as $g)<span class="badge badge-light border">{{ $g }}</span> @endforeach</div>@endif
      <div style="font-size:11px;color:var(--muted)" class="mt-1">Dievaluasi {{ $applicant->ai_evaluated_at ? $applicant->ai_evaluated_at->format('d M Y H:i') : '-' }}</div>
      @else
      <div style="font-size:12px;color:var(--muted)" class="mt-1">Belum dievaluasi. Sekali klik, hasil tersimpan dan tidak dihitung ulang.</div>
      @endif
      @if(!$applicant->ai_consent)
      <div style="font-size:12px;color:var(--muted)" class="mt-1"><i class="bi bi-shield-lock"></i> Pelamar tidak menyetujui pemrosesan AI — evaluasi terkunci.</div>
      @else
      <form method="POST" action="{{ route('admin.applications.ai', $applicant->id) }}" class="mt-1">@csrf<button class="btn btn-sm btn-outline-primary"><i class="bi bi-stars"></i> {{ is_null($applicant->ai_score) ? 'Jalankan Evaluasi AI' : 'Evaluasi Ulang' }}</button></form>
      @if(is_null($applicant->ai_score))
      <form method="POST" action="{{ route('admin.applications.aiReuse', $applicant->id) }}" class="mt-1">@csrf<button class="btn btn-sm btn-outline-secondary"><i class="bi bi-clock-history"></i> Salin dari riwayat (tanpa AI)</button></form>
      @endif
      @endif
    </div>
    @endif

    @if(count($history))
    <div class="content-card dt-card mb-2">
      <h6 class="font-weight-bold"><i class="bi bi-people"></i> Riwayat Pelamar Ini <small style="color:var(--muted)">(no HP sama)</small></h6>
      <div class="table-responsive"><table class="table table-sm table-hover mb-0">
        <tr><th>#</th><th>Tanggal</th><th>Posisi</th><th>Status</th><th>Skor AI</th><th></th></tr>
        @foreach($history as $h)<tr>
          <td>{{ $h->id }}</td><td>{{ $h->created_at->format('d/m/Y') }}</td>
          <td><small>{{ $h->position->full_title ?? '-' }}</small></td>
          <td><span class="badge badge-info badge-status">{{ $h->status }}</span></td>
          <td>{{ is_null($h->ai_score) ? '-' : $h->ai_score . '%' }}</td>
          <td><a href="{{ route('admin.applications.show', $h->id) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
        </tr>@endforeach
      </table></div>
    </div>
    @endif
  </div>

  {{-- ===== KOLOM KANAN: tindak lanjut ===== --}}
  <div class="col-lg-4 col-xl-3">

    <div class="content-card dt-card mb-2" style="border-top:3px solid var(--brand)">
      <h6 class="font-weight-bold mb-2"><i class="bi bi-flag"></i> Tindak Lanjut</h6>
      <form method="POST" action="{{ route('admin.applications.status', $applicant->id) }}">@csrf
        <div class="dt-label">Status</div>
        <div class="dt-pills mb-2">
          @foreach($statuses as $s)
          <label class="s-{{ $s }}"><input type="radio" name="status" value="{{ $s }}" {{ $applicant->status === $s ? 'checked' : '' }}><span>{{ $s }}</span></label>
          @endforeach
        </div>
        <div class="dt-label">Catatan</div>
        <textarea name="catatan_admin" rows="2" class="form-control form-control-sm" placeholder="cth. Cocok, panggil interview Senin">{{ $applicant->catatan_admin }}</textarea>
        <div class="d-flex justify-content-between align-items-center mt-2">
          <button class="btn btn-sm btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button>
          <span class="dt-sub">Tercatat di riwayat</span>
        </div>
      </form>
      <hr class="my-2">
      <form method="POST" action="{{ route('admin.applications.destroy', $applicant->id) }}" onsubmit="return confirm('Hapus data lamaran ini beserta file-nya? Tindakan ini permanen.')">@csrf @method('DELETE')
        <button class="btn btn-link btn-sm text-danger p-0" style="font-size:12px"><i class="bi bi-trash"></i> Hapus lamaran ini</button>
      </form>
    </div>

    <div class="content-card dt-card mb-2">
      <h6 class="font-weight-bold mb-2"><i class="bi bi-clock-history"></i> Riwayat Status</h6>
      <ul class="dt-tl">
        @foreach($applicant->statusLogs as $log)
        <li>
          <span class="dot" style="background:{{ $statusHex[$log->to_status] ?? '#6c757d' }}"></span>
          <div class="d-flex justify-content-between align-items-center" style="gap:6px">
            <span>
              <span class="badge badge-light border">{{ $log->from_status ?? '-' }}</span>
              <i class="bi bi-arrow-right" style="color:var(--muted)"></i>
              <span class="badge badge-{{ $statusColors[$log->to_status] ?? 'secondary' }}">{{ $log->to_status }}</span>
            </span>
            <span class="dt-sub text-nowrap">{{ $log->created_at->format('d/m H:i') }}</span>
          </div>
          <div class="dt-sub">oleh {{ optional($log->user)->name ?? '-' }}@if($log->note) · <em>“{{ $log->note }}”</em>@endif</div>
        </li>
        @endforeach
        <li>
          <span class="dot" style="background:#adb5bd"></span>
          <div class="d-flex justify-content-between align-items-center">
            <strong>Lamaran masuk</strong>
            <span class="dt-sub text-nowrap">{{ $applicant->created_at->format('d/m H:i') }}</span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>

@if($isPdf)
{{-- Popup preview CV --}}
<div class="modal fade" id="cvModal" tabindex="-1" role="dialog" aria-labelledby="cvModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header py-2 align-items-center">
        <h6 class="modal-title font-weight-bold mb-0 text-truncate" id="cvModalTitle"><i class="bi bi-file-earmark-pdf"></i> CV — {{ $applicant->nama_lengkap }}</h6>
        <div class="ml-auto mr-2 d-flex" style="gap:6px">
          <a href="{{ route('admin.applications.preview', $applicant->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> Tab baru</a>
          <a href="{{ route('admin.applications.download', $applicant->id) }}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Download</a>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body p-0 position-relative">
        <iframe id="cvFrame" class="dt-frame" title="Preview CV"></iframe>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  document.querySelectorAll('.dt-copy').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-copy');
      var done = function () {
        b.classList.add('ok');
        b.innerHTML = '<i class="bi bi-check2"></i>';
        setTimeout(function () { b.classList.remove('ok'); b.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 1200);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(t).then(done);
      } else {
        var ta = document.createElement('textarea');
        ta.value = t; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
      }
    });
  });
})();
</script>
@endpush
@endif
@endsection