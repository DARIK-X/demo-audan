<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ResourceLink;
use Illuminate\Http\Request;
class ResourceLinkController extends Controller {
    public function index() { return view('admin.links.index', ['links' => ResourceLink::orderBy('sort_order')->orderBy('id')->get()]); }
    public function create() { return view('admin.links.form', ['link' => new ResourceLink]); }
    public function edit(ResourceLink $link) { return view('admin.links.form', compact('link')); }
    public function store(Request $request) { return $this->save($request, new ResourceLink); }
    public function update(Request $request, ResourceLink $link) { return $this->save($request, $link); }
    private function save(Request $request, ResourceLink $link) {
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:400'], 'url' => ['required', 'url:https', 'max:2048'], 'sort_order' => ['required', 'integer', 'min:0', 'max:10000'], 'is_published' => ['nullable', 'boolean']]);
        $data['is_published'] = $request->boolean('is_published');
        $link->fill($data)->save();
        return redirect()->route('admin.links.index')->with('status', 'Сілтеме сақталды.');
    }
    public function destroy(ResourceLink $link) { $link->delete(); return redirect()->route('admin.links.index')->with('status', 'Сілтеме жойылды.'); }
}
