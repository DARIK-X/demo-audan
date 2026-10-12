<?php
namespace App\Services;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
class ContentFiles
{
    /** Store files first, roll them back on DB failure, remove old files only after successful commit. */
    public function save(Model $model, Request $request, array $data, array $fields): void {
        $newFiles = []; $oldFiles = [];
        try {
            foreach ($fields as $field => $options) {
                [$disk, $column, $directory] = $options;
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store($directory, $disk);
                    if (!$path) throw new \RuntimeException('File could not be stored.');
                    $newFiles[] = [$disk, $path]; $data[$column] = $path;
                    if ($model->{$column}) $oldFiles[] = [$disk, $model->{$column}];
                    if ($field === 'attachment') $data['attachment_name'] = Str::slug(pathinfo($request->file($field)->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'document';
                    if ($field === 'attachment') $data['attachment_name'] = Str::limit($data['attachment_name'], 170, '').'.pdf';
                } elseif ($request->boolean('remove_'.$field)) {
                    if ($model->{$column}) $oldFiles[] = [$disk, $model->{$column}];
                    $data[$column] = null;
                    if ($field === 'attachment') $data['attachment_name'] = null;
                }
            }
            DB::transaction(fn () => $model->fill($data)->save());
        } catch (Throwable $exception) {
            foreach ($newFiles as [$disk, $path]) $this->discard($disk, $path);
            throw $exception;
        }
        foreach ($oldFiles as [$disk, $path]) $this->discard($disk, $path);
    }
    private function discard(string $disk, string $path): void {
        try { Storage::disk($disk)->delete($path); } catch (Throwable $exception) { report($exception); }
    }
    public function delete(Model $model, array $fields): void {
        $files = [];
        foreach ($fields as [$disk, $column]) if ($model->{$column}) $files[] = [$disk, $model->{$column}];
        DB::transaction(fn () => $model->delete());
        foreach ($files as [$disk, $path]) $this->discard($disk, $path);
    }
}
