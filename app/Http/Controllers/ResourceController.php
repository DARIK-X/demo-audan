<?php
namespace App\Http\Controllers;
use App\Models\ResourceLink;
class ResourceController extends Controller {
    public function index() { return view('resources', ['resources' => ResourceLink::where('is_published', true)->orderBy('sort_order')->orderBy('id')->get()]); }
}
