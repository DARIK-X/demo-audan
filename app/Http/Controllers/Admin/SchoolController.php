<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use App\Services\ContentFiles;
use Illuminate\Http\Request;
class SchoolController extends Controller
{
    public function index(Request $request) {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        return view('admin.index', ['schools' => School::query()->when($filters['q'] ?? null, fn ($q, $term) => $q->search($term))->orderByDesc('id')->paginate(15)->withQueryString(), 'total' => School::count(), 'published' => School::where('is_published', true)->count()]);
    }
    public function create() { return view('admin.schools.form', ['school' => new School]); }
    public function edit(School $school) { return view('admin.schools.form', compact('school')); }
    public function store(SchoolRequest $request, ContentFiles $files) { return $this->save($request, new School, $files); }
    public function update(SchoolRequest $request, School $school, ContentFiles $files) { return $this->save($request, $school, $files); }
    private function save(SchoolRequest $request, School $school, ContentFiles $files) {
        $data = $request->safe()->except(['photo', 'remove_photo']);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->has('is_featured') ? $request->boolean('is_featured') : (bool) $school->is_featured;
        $files->save($school, $request, $data, ['photo' => ['public', 'photo_path', 'schools']]);
        return redirect()->route('admin.schools.index')
            ->with('status', $school->is_published ? 'Мектеп сақталды және қоғамдық тізімде жарияланды.' : 'Мектеп жоба ретінде сақталды. Қоғамдық тізімде көрсету үшін жариялаңыз.')
            ->with('school_id', $school->id)->with('school_published', $school->is_published);
    }
    public function publication(Request $request, School $school) {
        $data = $request->validate(['is_published' => ['required', 'boolean']]);
        $school->update(['is_published' => (bool) $data['is_published']]);
        return redirect()->route('admin.schools.index')
            ->with('status', $school->is_published ? 'Мектеп қоғамдық тізімде жарияланды.' : 'Мектеп сайттан жасырылды. Әкімші тізімінде сақталады.')
            ->with('school_id', $school->id)->with('school_published', $school->is_published);
    }
    public function destroy(School $school, ContentFiles $files) {
        $files->delete($school, [['public', 'photo_path']]);
        return redirect()->route('admin.schools.index')->with('status', 'Мектеп жойылды.');
    }
}
