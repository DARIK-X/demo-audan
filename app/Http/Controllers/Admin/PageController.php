<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
class PageController extends Controller
{
    public function index() { return view('admin.pages.index', ['pages' => Page::orderBy('id')->get()]); }
    public function edit(Page $page) { return view('admin.pages.form', compact('page')); }
    public function update(Request $request, Page $page) {
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['required', 'string', 'max:30000'], 'is_published' => ['nullable', 'boolean']]);
        $data['is_published'] = $request->boolean('is_published');
        $page->update($data);
        return redirect()->route('admin.pages.index')->with('status', 'Бет жаңартылды.');
    }
}
