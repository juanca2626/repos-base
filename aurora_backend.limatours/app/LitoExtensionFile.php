<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LitoExtensionFile extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nroref', 'lito_extension_file_type_id', 'link', 'original_name',
    ];

    public function passengers()
    {
        return $this->hasMany('App\LitoExtensionFilePassenger', 'lito_extension_file_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo('App\LitoExtensionFileType', 'lito_extension_file_type_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany('App\LitoExtensionFileLog', 'lito_extension_file_id', 'id');
    }

    public function log()
    {
        return $this->hasOne('App\LitoExtensionFileLog', 'lito_extension_file_id', 'id')
            ->orderBy('id', 'desc');
    }

    public function getLinkAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Separamos los componentes de la URL
        $parts = parse_url($value);

        if (!isset($parts['path'])) {
            return $value;
        }

        // 1. Dividimos el path por carpetas (/)
        $segments = explode('/', $parts['path']);

        // 2. Codificamos cada segmento individualmente para evitar codificar los slashes
        // rawurlencode convierte espacios en %20 y el '+' en %2B
        $encodedSegments = array_map('rawurlencode', $segments);

        // 3. Reconstruimos el path
        $newPath = implode('/', $encodedSegments);

        // 4. Reconstruimos la URL completa
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'https://';
        $host = isset($parts['host']) ? $parts['host'] : '';

        // Si la URL original tenía query params (?...) o fragmentos (#...)
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $newPath . $query . $fragment;
    }
}
