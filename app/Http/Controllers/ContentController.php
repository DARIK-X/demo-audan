<?php
namespace App\Http\Controllers;
use App\Models\Article;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ContentController extends Controller
{
    public function index(Request $request, string $category) {
        abort_unless(array_key_exists($category, config('portal.categories')), 404);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $articles = Article::published()->where('category', $category)
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', '%'.$term.'%'))
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(9)->withQueryString();
        return view('content.index', ['articles' => $articles, 'category' => $category, 'title' => config('portal.categories.'.$category)]);
    }
    public function show(string $category, Article $article) {
        abort_unless($article->category === $category && Article::published()->whereKey($article->id)->exists(), 404);
        return view('content.show', compact('article', 'category'));
    }
    public function download(Article $article) {
        abort_unless(Article::published()->whereKey($article->id)->exists() && $article->attachment_path, 404);
        abort_unless(Storage::disk('local')->exists($article->attachment_path), 404);
        return Storage::disk('local')->download($article->attachment_path, $article->attachment_name ?: 'document.pdf', ['X-Content-Type-Options' => 'nosniff']);
    }
    public function page(string $slug) {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('content.page', compact('page'));
    }
}
