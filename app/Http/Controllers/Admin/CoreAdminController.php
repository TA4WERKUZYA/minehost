<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Это правильный путь!
use App\Models\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CoreAdminController extends Controller
{
    /**
     * Display a listing of the cores.
     */
    public function index()
    {
        $cores = Core::orderBy('game_type')
                    ->orderBy('name')
                    ->orderBy('version', 'desc')
                    ->paginate(20);
        
        return view('admin.cores.index', [
            'cores' => $cores,
            'title' => 'Управление ядрами'
        ]);
    }

    /**
     * Show the form for creating a new core.
     */
    public function create()
    {
        return view('admin.cores.create', [
            'title' => 'Добавление ядра'
        ]);
    }

    /**
     * Store a newly created core in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'game_type' => 'required|in:java,bedrock',
            'name' => 'required|string|max:50',
            'version' => 'required|string|max:20',
            'file' => 'required|file|mimes:jar|max:102400', // 100MB max
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'changelog' => 'nullable|string'
        ]);

        // Сохраняем файл
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = Str::slug($validated['name']) . '-' . $validated['version'] . '.jar';
            $filePath = 'cores/' . $validated['game_type'] . '/' . $validated['name'] . '/' . $validated['version'] . '/' . $fileName;
            
            // Сохраняем файл
            Storage::putFileAs('cores/' . $validated['game_type'] . '/' . $validated['name'] . '/' . $validated['version'], $file, $fileName);
            
            // Создаем запись в базе
            $core = Core::create([
                'game_type' => $validated['game_type'],
                'name' => $validated['name'],
                'version' => $validated['version'],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'checksum' => md5_file($file->getRealPath()),
                'is_default' => $request->boolean('is_default', false),
                'is_active' => $request->boolean('is_active', true),
                'changelog' => $validated['changelog'] ?? null
            ]);

            // Если это дефолтное ядро, снимаем флаг с других ядер этого типа
            if ($core->is_default) {
                Core::where('game_type', $core->game_type)
                    ->where('id', '!=', $core->id)
                    ->update(['is_default' => false]);
            }

            return redirect()->route('admin.cores.index')
                ->with('success', 'Ядро успешно добавлено!');
        }

        return back()->with('error', 'Ошибка при загрузке файла');
    }

    /**
     * Show the form for editing the specified core.
     */
    public function edit(Core $core)
    {
        return view('admin.cores.edit', [
            'core' => $core,
            'title' => 'Редактирование ядра'
        ]);
    }

    /**
     * Update the specified core in storage.
     */
    public function update(Request $request, Core $core)
    {
        $validated = $request->validate([
            'game_type' => 'required|in:java,bedrock',
            'name' => 'required|string|max:50',
            'version' => 'required|string|max:20',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'changelog' => 'nullable|string'
        ]);

        $core->update([
            'game_type' => $validated['game_type'],
            'name' => $validated['name'],
            'version' => $validated['version'],
            'is_default' => $request->boolean('is_default', false),
            'is_active' => $request->boolean('is_active', true),
            'changelog' => $validated['changelog'] ?? null
        ]);

        // Если это дефолтное ядро, снимаем флаг с других ядер этого типа
        if ($core->is_default) {
            Core::where('game_type', $core->game_type)
                ->where('id', '!=', $core->id)
                ->update(['is_default' => false]);
        }

        return redirect()->route('admin.cores.index')
            ->with('success', 'Ядро успешно обновлено!');
    }

    /**
     * Remove the specified core from storage.
     */
    public function destroy(Core $core)
    {
        // Удаляем файл
        if (Storage::exists($core->file_path)) {
            Storage::delete($core->file_path);
        }

        $core->delete();

        return redirect()->route('admin.cores.index')
            ->with('success', 'Ядро успешно удалено!');
    }

    /**
     * Make core as default.
     */
    public function makeDefault(Core $core)
    {
        // Снимаем флаг со всех ядер этого типа
        Core::where('game_type', $core->game_type)
            ->update(['is_default' => false]);
        
        // Устанавливаем флаг на текущее ядро
        $core->update(['is_default' => true]);

        return redirect()->route('admin.cores.index')
            ->with('success', 'Ядро установлено как дефолтное!');
    }

    /**
     * Show cores statistics.
     */
    public function stats()
    {
        $stats = [
            'total_cores' => Core::count(),
            'java_cores' => Core::where('game_type', 'java')->count(),
            'bedrock_cores' => Core::where('game_type', 'bedrock')->count(),
            'active_cores' => Core::where('is_active', true)->count(),
            'default_cores' => Core::where('is_default', true)->count(),
        ];

        return view('admin.cores.stats', [
            'stats' => $stats,
            'title' => 'Статистика ядер'
        ]);
    }
}
