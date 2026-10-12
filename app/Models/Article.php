<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Article extends Model
{
    protected $fillable = ['title', 'category', 'excerpt', 'body', 'image_path', 'attachment_path', 'attachment_name', 'is_published', 'published_at'];
    protected function casts(): array { return ['is_published' => 'boolean', 'published_at' => 'datetime']; }
    public function scopePublished(Builder $query): Builder {
        return $query->where('is_published', true)->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
