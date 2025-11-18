<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SunatResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'cdr_zip',
        'cdr_xml',
        'sunat_code',
        'sunat_message',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
