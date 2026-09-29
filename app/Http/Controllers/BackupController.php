<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Routing\Controllers\HasMiddleware;

class BackupController extends Controller implements HasMiddleware
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

    public function index()
    {
        $backupDir = storage_path('app/backups');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = [];
        $rawFiles = File::files($backupDir);
        
        foreach ($rawFiles as $file) {
            if ($file->getExtension() === 'sql') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatSize($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                ];
            }
        }

        // Ordenar por fecha descendente
        usort($files, function($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return view('admin.backups.index', compact('files'));
    }

    public function store()
    {
        $backupDir = storage_path('app/backups');
        
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_sistema_ibc_' . date('Y-m-d_H-i-s') . '.sql';
        $backupPath = $backupDir . '/' . $filename;

        $dbConfig = config('database.connections.mysql');
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $database = $dbConfig['database'] ?? '';
        $username = $dbConfig['username'] ?? '';
        $password = $dbConfig['password'] ?? '';

        $success = false;

        // Intentar con mysqldump si está disponible
        try {
            $passwordOption = $password !== '' ? '--password="' . addcslashes($password, '"\\$') . '"' : '';
            $command = sprintf(
                'mysqldump --host="%s" --port="%s" --user="%s" %s "%s" > "%s"',
                $host,
                $port,
                $username,
                $passwordOption,
                $database,
                $backupPath
            );

            // Ejecutar comando
            $output = [];
            $returnVar = -1;
            exec($command, $output, $returnVar);

            if ($returnVar === 0 && File::exists($backupPath) && File::size($backupPath) > 100) {
                $success = true;
            }
        } catch (\Exception $e) {
            \Log::warning("mysqldump falló, usando fallback PHP: " . $e->getMessage());
        }

        // Fallback PHP si mysqldump no funcionó
        if (!$success) {
            try {
                $this->runPhpBackup($backupPath, $database);
                $success = true;
            } catch (\Exception $e) {
                \Log::error("Fallo total de backup: " . $e->getMessage());
                if (File::exists($backupPath)) {
                    File::delete($backupPath);
                }
                return redirect()->route('admin.backups.index')->with('error', 'NO SE PUDO GENERAR LA COPIA DE SEGURIDAD: ' . strtoupper($e->getMessage()));
            }
        }

        return redirect()->route('admin.backups.index')->with('success', 'COPIA DE SEGURIDAD GENERADA CORRECTAMENTE: ' . $filename);
    }

    public function download($filename)
    {
        // Seguridad: Evitar Directory Traversal
        $filename = basename($filename);
        $backupPath = storage_path('app/backups/' . $filename);

        if (File::exists($backupPath)) {
            return response()->download($backupPath);
        }

        return redirect()->route('admin.backups.index')->with('error', 'EL ARCHIVO DE RESPALDO NO EXISTE.');
    }

    public function destroy($filename)
    {
        $filename = basename($filename);
        $backupPath = storage_path('app/backups/' . $filename);

        if (File::exists($backupPath)) {
            File::delete($backupPath);
            return redirect()->route('admin.backups.index')->with('success', 'COPIA DE SEGURIDAD ELIMINADA CORRECTAMENTE.');
        }

        return redirect()->route('admin.backups.index')->with('error', 'EL ARCHIVO NO SE PUDO ENCONTRAR.');
    }

    public function restore($filename)
    {
        $filename = basename($filename);
        $backupPath = storage_path('app/backups/' . $filename);

        if (!File::exists($backupPath)) {
            return redirect()->route('admin.backups.index')->with('error', 'EL ARCHIVO DE RESPALDO NO EXISTE.');
        }

        try {
            // Leer el archivo SQL
            $sql = File::get($backupPath);

            // Desactivar restricciones de llaves foráneas temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Ejecutar el SQL completo en una sola transacción raw
            DB::unprepared($sql);
            
            // Reactivar restricciones de llaves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('admin.backups.index')->with('success', 'BASE DE DATOS RESTAURADA CON ÉXITO DESDE: ' . $filename);
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            \Log::error("Error de restauración: " . $e->getMessage());
            return redirect()->route('admin.backups.index')->with('error', 'ERROR AL RESTAURAR LA BASE DE DATOS: ' . strtoupper($e->getMessage()));
        }
    }

    private function runPhpBackup($backupPath, $database)
    {
        $tables = \Illuminate\Support\Facades\Schema::getTableListing();

        $sql = "-- SISTEMA IBC DATABASE BACKUP\n";
        $sql .= "-- Generado por PHP Fallback\n";
        $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Obtener estructura de la tabla
            $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
            $createTableSql = $createTableResult[0]->{'Create Table'};

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableSql . ";\n\n";

            // Obtener datos de la tabla
            $rows = DB::select("SELECT * FROM `{$table}`");
            if (count($rows) > 0) {
                $sql .= "INSERT INTO `{$table}` VALUES \n";
                $valueStrings = [];
                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array)$row as $val) {
                        if (is_null($val)) {
                            $values[] = "NULL";
                        } else {
                            // Escapar string manualmente para evitar inyecciones y errores de sintaxis
                            $escaped = str_replace(
                                ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
                                ["\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z"],
                                $val
                            );
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $valueStrings[] = "(" . implode(",", $values) . ")";
                }
                $sql .= implode(",\n", $valueStrings) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($backupPath, $sql);
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
