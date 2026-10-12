<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SettingsController extends Controller
{
    public function edit() { return view('admin.settings', ['settings' => Setting::pluck('value', 'key')->all()]); }
    public function update(Request $request) {
        $data = $request->validate(['site_title' => ['required', 'string', 'max:120'], 'contact_address' => ['nullable', 'string', 'max:400'], 'contact_phone' => ['nullable', 'string', 'max:80'], 'contact_email' => ['nullable', 'email', 'max:180'], 'office_hours' => ['nullable', 'string', 'max:400'], 'contact_note' => ['nullable', 'string', 'max:2000']]);
        DB::transaction(function () use ($data) { foreach ($data as $key => $value) Setting::updateOrCreate(['key' => $key], ['value' => $value]); });
        return back()->with('status', 'Баптаулар сақталды.');
    }
}
