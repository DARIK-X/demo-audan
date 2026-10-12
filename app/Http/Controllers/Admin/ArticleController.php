<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Services\ContentFiles;
use Illuminate\Http\Request;
class ArticleController extends Controller
{
    public function index(Request $request) {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:30']]);
        $articles = Article::query()->when($filters['q'] ?? null, fn ($q, $term) => $q->where('title', 'like', '%'.$term.'%'))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))->latest()->paginate(15)->withQueryString();
        return view('admin.articles.index', compact('articles'));
    }
    public function create() { return view('admin.articles.form', ['article' => new Article]); }
    public function edit(Article $article) { return view('admin.articles.form', compact('article')); }
    public function store(ArticleRequest $request, ContentFiles $files) { return $this->save($request, new Article, $files); }
    public function update(ArticleRequest $request, Article $article, ContentFiles $files) { return $this->save($request, $article, $files); }
    private function save(ArticleRequest $request, Article $article, ContentFiles $files) {
        $data = $request->safe()->except(['image', 'attachment', 'remove_image', 'remove_attachment']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['published_at'] ?? ($data['is_published'] ? now() : null);
        $files->save($article, $request, $data, ['image' => ['public', 'image_path', 'articles'], 'attachment' => ['local', 'attachment_path', 'documents']]);
        return redirect()->route('admin.articles.index')->with('status', 'Материал сақталды.');
    }
    public function destroy(Article $article, ContentFiles $files) {
        $files->delete($article, [['public', 'image_path'], ['local', 'attachment_path']]);
        return redirect()->route('admin.articles.index')->with('status', 'Материал жойылды.');
    }
}
