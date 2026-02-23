<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogController extends Controller
{
    public function show(Request $request)
    {
        $token = env('LOG_DETAILS_TOKEN');
        if ($token && $request->query('token') !== $token) {
            abort(403, 'Invalid token.');
        }

        $path = storage_path('logs/laravel.log');
        $exists = is_file($path);
        $content = $exists ? $this->tailFile($path, 200 * 1024) : '';

        return view('logs.details', [
            'now' => now()->toDateTimeString(),
            'appEnv' => config('app.env'),
            'appUrl' => config('app.url'),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'host' => $request->getHost(),
            'logPath' => $path,
            'logExists' => $exists,
            'logSize' => $exists ? filesize($path) : 0,
            'logContent' => Str::limit($content, 200 * 1024, ''),
        ]);
    }

    public function clear(Request $request)
    {
        $token = env('LOG_DETAILS_TOKEN');
        if ($token && $request->query('token') !== $token) {
            abort(403, 'Invalid token.');
        }

        $path = storage_path('logs/laravel.log');
        if (is_file($path)) {
            file_put_contents($path, '');
        }

        return response()->json(['ok' => true]);
    }

    private function tailFile(string $path, int $maxBytes): string
    {
        $size = filesize($path);
        if ($size === 0) {
            return '';
        }

        $readBytes = min($size, $maxBytes);
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return '';
        }

        fseek($handle, -$readBytes, SEEK_END);
        $data = fread($handle, $readBytes);
        fclose($handle);

        return $data === false ? '' : $data;
    }
}
