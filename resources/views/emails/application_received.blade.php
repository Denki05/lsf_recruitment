<h3>Lamaran Baru Masuk</h3>
<p>Nama: <strong>{{ $applicant->nama_lengkap }}</strong> ({{ $applicant->nama_panggilan }})<br>
Posisi: <strong>{{ $applicant->position->full_title }}</strong><br>
KTP: {{ $applicant->no_ktp }} | HP: {{ $applicant->no_hp }}<br>
Tanggal: {{ $applicant->created_at->format('d M Y H:i') }}</p>
<p>Lihat di panel admin: {{ url('/admin/lamaran/' . $applicant->id) }}</p>
