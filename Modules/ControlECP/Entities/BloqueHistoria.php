<?php

namespace Modules\ControlECP\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class BloqueHistoria extends Model
{
    use HasFactory;

    protected $table = 'bloques_historia';

    protected $fillable = [
        'titulo',
        'texto',
        'tipo_contenido',
        'ruta_archivo',
        'url_video',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    /**
     * URL pública del archivo si es imagen o audio.
     */
    public function getArchivoUrlAttribute(): ?string
    {
        if (empty($this->ruta_archivo)) {
            return null;
        }

        return Storage::disk('public')->url($this->ruta_archivo);
    }

    /**
     * Convierte una URL de YouTube o Vimeo en su enlace de incrustación (embed).
     */
    public function getVideoEmbedUrlAttribute(): ?string
    {
        if ($this->tipo_contenido !== 'video' || empty($this->url_video)) {
            return null;
        }

        $url = trim($this->url_video);

        // YouTube: formatos youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/..., youtube.com/shorts/...
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/i', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
        }

        // Vimeo: formatos vimeo.com/..., player.vimeo.com/video/...
        if (preg_match('/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]*\/videos\/|album\/\d+\/video\/|video\/|)(\d+))/i', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return $url;
    }

    public function isImagen(): bool
    {
        return $this->tipo_contenido === 'imagen';
    }

    public function isAudio(): bool
    {
        return $this->tipo_contenido === 'audio';
    }

    public function isVideo(): bool
    {
        return $this->tipo_contenido === 'video';
    }
}
