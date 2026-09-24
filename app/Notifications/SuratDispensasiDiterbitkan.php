<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Notifications\Notification;

class SuratDispensasiDiterbitkan extends Notification
{
    public function __construct(private readonly Dispensasi $dispensasi)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'judul' => 'Surat E-Dispensasi Diterbitkan',
            'pesan' => "Surat e-dispensasi {$this->dispensasi->nomor_surat_dispensasi} untuk "
                . "{$this->dispensasi->pegawai->nama_pegawai} telah diterbitkan oleh Admin Departemen.",
            'url'   => route('sdm.arsip-e-dispensasi.show', $this->dispensasi),
        ];
    }
}