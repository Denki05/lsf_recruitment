<?php

return [
    // Tampilkan/sembunyikan seluruh UI yang berhubungan dengan AI.
    // Backend (skor tersimpan, route evaluasi) tetap jalan, hanya UI disembunyikan.
    // Nyalakan lagi via .env: AI_ENABLED=true
    'ai_enabled' => env('AI_ENABLED', false),
];
