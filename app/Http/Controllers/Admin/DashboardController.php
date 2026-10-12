<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Article;
use App\Models\Page;
class DashboardController extends Controller
{
    public function __invoke() { return view('admin.dashboard', ['schools' => School::count(), 'articles' => Article::count(), 'pages' => Page::count(), 'latest' => Article::latest()->limit(5)->get()]); }
}
