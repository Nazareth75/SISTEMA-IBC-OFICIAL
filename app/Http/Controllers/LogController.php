<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Routing\Controllers\HasMiddleware;

class LogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (auth()->user() && !auth()->user()->esSuperadministrador()) {
                    abort(403, 'No tienes permiso para acceder a este módulo.');
                }
                return $next($request);
            }
        ];
    }

    public function index(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        
        $entries = [];
        $fileSize = 0;
        
        if (File::exists($logPath)) {
            $fileSize = File::size($logPath);
            
            // Leer y procesar el archivo de log línea por línea para evitar consumir memoria excesiva
            $file = fopen($logPath, 'r');
            if ($file) {
                $currentEntry = null;
                while (($line = fgets($file)) !== false) {
                    // Buscar patrón de inicio de entrada de log: [YYYY-MM-DD HH:MM:SS] env.LEVEL: mensaje
                    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\S+)\.([A-Z]+): (.*)$/', $line, $matches)) {
                        if ($currentEntry) {
                            $lines = explode("\n", trim($currentEntry['fullMessage']));
                            $currentEntry['message'] = trim($lines[0]);
                            $currentEntry['stack'] = count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : '';
                            unset($currentEntry['fullMessage']);
                            $entries[] = $currentEntry;
                        }
                        $currentEntry = [
                            'date' => $matches[1],
                            'env' => $matches[2],
                            'level' => $matches[3],
                            'fullMessage' => $matches[4],
                        ];
                    } else {
                        if ($currentEntry) {
                            $currentEntry['fullMessage'] .= "\n" . rtrim($line, "\r\n");
                        }
                    }
                }
                if ($currentEntry) {
                    $lines = explode("\n", trim($currentEntry['fullMessage']));
                    $currentEntry['message'] = trim($lines[0]);
                    $currentEntry['stack'] = count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : '';
                    unset($currentEntry['fullMessage']);
                    $entries[] = $currentEntry;
                }
                fclose($file);
            }
            
            // Revertir el orden para mostrar los logs más recientes primero
            $entries = array_reverse($entries);
        }
        
        $collection = collect($entries);
        
        // Filtro por Nivel
        $levelFilter = strtoupper($request->get('level', ''));
        if ($levelFilter && $levelFilter !== 'ALL') {
            $collection = $collection->filter(function($entry) use ($levelFilter) {
                return $entry['level'] === $levelFilter;
            });
        }
        
        // Filtro por texto de búsqueda
        $search = $request->get('search', '');
        if ($search) {
            $collection = $collection->filter(function($entry) use ($search) {
                return stripos($entry['message'], $search) !== false || stripos($entry['stack'], $search) !== false;
            });
        }
        
        $totalCount = $collection->count();
        
        // Paginación manual de 10 registros
        $perPage = 10;
        $page = (int) $request->get('page', 1);
        if ($page < 1) $page = 1;
        
        $sliced = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        
        $paginated = new LengthAwarePaginator(
            $sliced,
            $totalCount,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('admin.logs.index', compact('paginated', 'fileSize', 'totalCount'));
    }

    public function download()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (File::exists($logPath)) {
            return response()->download($logPath, 'laravel_' . date('Y-m-d_H-i-s') . '.log');
        }
        
        return back()->with('error', 'EL ARCHIVO DE LOG NO EXISTE.');
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        
        try {
            if (File::exists($logPath)) {
                File::put($logPath, '');
                return redirect()->route('admin.logs.index')->with('success', 'EL ARCHIVO DE LOGS HA SIDO VACIADO CORRECTAMENTE.');
            }
            return redirect()->route('admin.logs.index')->with('error', 'EL ARCHIVO DE LOGS NO EXISTE.');
        } catch (\Exception $e) {
            return redirect()->route('admin.logs.index')->with('error', 'ERROR AL VACIAR EL ARCHIVO: ' . strtoupper($e->getMessage()));
        }
    }
}
