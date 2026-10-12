<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected static function booted(): void {
        static::saving(function (School $school) { $school->search_text = mb_strtolower($school->name.' '.$school->village, 'UTF-8'); });
    }
    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, string $term): \Illuminate\Database\Eloquent\Builder {
        $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(trim($term), 'UTF-8'));
        return $query->whereRaw("search_text LIKE ? ESCAPE '!'", ['%'.$term.'%']);
    }
    protected $fillable = ['name', 'type', 'village', 'address', 'students', 'phone', 'website_url', 'photo_path', 'description', 'is_published', 'is_featured'];
    protected function casts(): array { return ['is_published' => 'boolean', 'is_featured' => 'boolean', 'students' => 'integer']; }
}
