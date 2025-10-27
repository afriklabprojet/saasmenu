<?php

namespace App\Http\Controllers\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    /**
     * Display a listing of media files
     */
    public function index()
    {
        try {
            // Get all images from storage
            $images = Storage::disk('public')->files('images');
            $mediaFiles = [];

            foreach ($images as $image) {
                $mediaFiles[] = [
                    'id' => base64_encode($image),
                    'name' => basename($image),
                    'path' => $image,
                    'url' => Storage::url($image),
                    'size' => Storage::size($image),
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($image))
                ];
            }

            return view('admin.media.index', compact('mediaFiles'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du chargement des médias: ' . $e->getMessage());
        }
    }

    /**
     * Upload a new image
     */
    public function add_image(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fichier invalide. Formats acceptés: jpeg, png, jpg, gif, svg (max 2MB)'
                ], 400);
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('images', $imageName, 'public');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Image uploadée avec succès',
                    'data' => [
                        'id' => base64_encode($path),
                        'name' => $imageName,
                        'path' => $path,
                        'url' => Storage::url($path)
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Aucun fichier sélectionné'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a media file
     */
    public function delete_media($id)
    {
        try {
            $path = base64_decode($id);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return redirect()->back()->with('success', 'Média supprimé avec succès');
            }

            return redirect()->back()->with('error', 'Fichier non trouvé');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Download a media file
     */
    public function download($id)
    {
        try {
            $path = base64_decode($id);

            if (Storage::disk('public')->exists($path)) {
                $file = Storage::disk('public')->get($path);
                $filename = basename($path);

                return Response::make($file, 200, [
                    'Content-Type' => Storage::mimeType($path),
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ]);
            }

            return redirect()->back()->with('error', 'Fichier non trouvé');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }
}
