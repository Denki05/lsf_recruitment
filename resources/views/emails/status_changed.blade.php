<h3>Update Status Lamaran</h3>
<p>Halo <strong>{{ $applicant->nama_lengkap }}</strong>,</p>
<p>Status lamaran Anda untuk <strong>{{ $applicant->position->full_title }}</strong> saat ini: <strong>{{ $applicant->status }}</strong>.</p>
@if($applicant->catatan_admin)<p>Catatan: {{ $applicant->catatan_admin }}</p>@endif
<p>Terima kasih.</p>
