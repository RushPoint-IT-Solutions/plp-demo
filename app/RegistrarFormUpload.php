<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RegistrarFormUpload extends Model
{
    protected $fillable = [
        'form_key',
        'form_label',
        'version_number',
        'is_current',
        'uploaded_by_user_id',
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size_bytes',
        'content_hash',
        'notes',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_current' => 'boolean',
        'uploaded_by_user_id' => 'integer',
        'size_bytes' => 'integer',
    ];

    public static function definitions(): array
    {
        $definitions = config('registrar_forms', []);

        return is_array($definitions) ? $definitions : [];
    }

    public static function currentForFormKeys(array $formKeys): Collection
    {
        if (!Schema::hasTable('registrar_form_uploads')) {
            return collect();
        }

        $normalizedKeys = collect($formKeys)
            ->map(function ($formKey) {
                return trim((string) $formKey);
            })
            ->filter(function ($formKey) {
                return $formKey !== '';
            })
            ->values()
            ->all();

        if (!count($normalizedKeys)) {
            return collect();
        }

        return static::query()
            ->whereIn('form_key', $normalizedKeys)
            ->where('is_current', true)
            ->orderBy('form_key')
            ->orderByDesc('version_number')
            ->get()
            ->keyBy('form_key');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeForFormKey($query, string $formKey)
    {
        return $query->where('form_key', trim($formKey));
    }
}