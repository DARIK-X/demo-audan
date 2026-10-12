<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ResourceLink extends Model {
    protected $fillable = ['title', 'description', 'url', 'is_published', 'sort_order'];
    protected function casts(): array { return ['is_published' => 'boolean', 'sort_order' => 'integer']; }
}
