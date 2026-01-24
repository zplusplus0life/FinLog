<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileAccessController extends Controller
{
    /**
     * Serve files based on user role with strict access control
     */
    public function serveFile(Request $request, $role, $filename)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            abort(404, 'File tidak ditemukan.');
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Validate that the requested role matches the user's role
        if ($userRole !== strtolower($role)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Allowed roles
        $allowedRoles = ['admin', 'manajer', 'staff'];
        if (!in_array($userRole, $allowedRoles)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Clean and validate filename to prevent directory traversal
        $cleanFilename = basename($filename);
        if ($cleanFilename !== $filename || empty($cleanFilename)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Construct the file path within the user's role directory
        $filePath = "private/{$userRole}/{$cleanFilename}";

        // Check if file exists in the role-specific directory
        if (!Storage::exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Get file info
        $mimeType = Storage::mimeType($filePath);
        $fileSize = Storage::size($filePath);

        // Stream the file with appropriate headers
        return Storage::response($filePath, $cleanFilename, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
        ]);
    }

    /**
     * List files in user's role directory
     */
    public function listFiles(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Allowed roles
        $allowedRoles = ['admin', 'manajer', 'staff'];
        if (!in_array($userRole, $allowedRoles)) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        // Get files from user's role directory
        $directory = "private/{$userRole}";
        
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $files = Storage::files($directory);
        
        $fileList = array_map(function ($file) use ($userRole) {
            $filename = basename($file);
            return [
                'name' => $filename,
                'size' => Storage::size($file),
                'modified' => Storage::lastModified($file),
                'mime_type' => Storage::mimeType($file),
                'download_url' => route('files.serve', ['role' => $userRole, 'filename' => $filename]),
            ];
        }, $files);

        return response()->json([
            'role' => $userRole,
            'files' => $fileList,
            'total' => count($fileList)
        ]);
    }

    /**
     * Upload file to user's role directory
     */
    public function uploadFile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Allowed roles
        $allowedRoles = ['admin', 'manajer', 'staff'];
        if (!in_array($userRole, $allowedRoles)) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        
        // Generate safe filename
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
        
        // Store in role-specific directory
        $directory = "private/{$userRole}";
        $filePath = $file->storeAs($directory, $filename);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload',
            'file' => [
                'name' => $filename,
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'download_url' => route('files.serve', ['role' => $userRole, 'filename' => $filename]),
            ]
        ]);
    }

    /**
     * Delete file from user's role directory
     */
    public function deleteFile(Request $request, $filename)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? '');

        // Allowed roles
        $allowedRoles = ['admin', 'manajer', 'staff'];
        if (!in_array($userRole, $allowedRoles)) {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        // Clean filename
        $cleanFilename = basename($filename);
        if ($cleanFilename !== $filename || empty($cleanFilename)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        // File path in role directory
        $filePath = "private/{$userRole}/{$cleanFilename}";

        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        Storage::delete($filePath);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil dihapus'
        ]);
    }
}