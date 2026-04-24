<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArchiveSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archive_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->where('is_active', true)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get a setting value as boolean.
     */
    public static function getBoolean($key, $default = false)
    {
        $value = self::getValue($key, $default ? 'true' : 'false');
        return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
    }

    /**
     * Get a setting value as integer.
     */
    public static function getInteger($key, $default = 0)
    {
        $value = self::getValue($key, $default);
        return (int) $value;
    }

    /**
     * Set a setting value.
     */
    public static function setValue($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}