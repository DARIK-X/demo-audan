<?php
namespace App\Http\Controllers;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function home() { return view('home', ['schools' => School::where('is_published', true)->orderByDesc('is_featured')->orderByDesc('id')->limit(6)->get(), 'resources' => \App\Models\ResourceLink::where('is_published', true)->orderBy('sort_order')->orderBy('id')->limit(6)->get(), 'news' => \App\Models\Article::published()->where('category', 'news')->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get(), 'count' => School::where('is_published', true)->count()]); }
    public function index(Request $request) {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', \Illuminate\Validation\Rule::in(config('portal.school_types'))], 'sort' => ['nullable', \Illuminate\Validation\Rule::in(['newest', 'name'])]]);
        $schools = School::query()->where('is_published', true)->orderByDesc('is_featured')
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->search($q))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when(($filters['sort'] ?? 'newest') === 'name', fn ($query) => $query->orderBy('name')->orderBy('id'), fn ($query) => $query->orderByDesc('id'))->paginate(12)->withQueryString();
        return view('schools.index', compact('schools'));
    }
    public function show(School $school) {
        abort_unless($school->is_published, 404);
        return view('schools.show', compact('school'));
    }
}
