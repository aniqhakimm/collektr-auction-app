<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function index(): View
    {
        $auctions = Auction::latest()->paginate(20);

        return view('admin.auctions.index', compact('auctions'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.auctions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['image_path'] = $this->handleImageUpload($request);

        $auction = Auction::create($data);

        $this->handleGalleryUpload($request, $auction);

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction created.');
    }

    public function edit(Auction $auction): View
    {
        $auction->load('images');
        $categories = Category::orderBy('name')->get();

        return view('admin.auctions.edit', compact('auction', 'categories'));
    }

    public function update(Request $request, Auction $auction): RedirectResponse
    {
        $data = $this->validated($request);

        $newCover = $this->handleImageUpload($request);
        if ($newCover !== null) {
            $data['image_path'] = $newCover;
        }

        $auction->update($data);

        $this->handleGalleryUpload($request, $auction);

        if ($request->filled('delete_images')) {
            AuctionImage::whereIn('id', $request->input('delete_images'))
                ->where('auction_id', $auction->id)
                ->delete();
        }

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction updated.');
    }

    public function destroy(Auction $auction): RedirectResponse
    {
        $auction->delete();

        return redirect()->route('admin.auctions.index')
            ->with('success', 'Auction deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'starting_price' => ['required', 'numeric', 'min:0.01'],
            'auction_end_at' => ['required', 'date', 'after:now'],
            'status'         => ['required', 'in:draft,active,ended'],
            'category_id'    => ['nullable', 'exists:categories,id'],
            'image'          => ['nullable', 'file', 'image', 'max:2048'],
            'gallery.*'      => ['nullable', 'file', 'image', 'max:2048'],
        ]);

        // Remove file fields — handled separately, not written directly to the model
        unset($data['image'], $data['gallery']);

        return $data;
    }

    private function storeFile(\Illuminate\Http\UploadedFile $file, string $dir): string
    {
        $ext  = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'jpg';
        $name = bin2hex(random_bytes(16)) . '.' . ltrim($ext, '.');

        $destDir = storage_path('app/public/' . trim($dir, '/'));
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Use getPathname() — getRealPath() returns false when the temp path
        // contains unresolvable symlinks (common with Laragon/Apache on Windows)
        move_uploaded_file($file->getPathname(), $destDir . '/' . $name);

        return trim($dir, '/') . '/' . $name;
    }

    private function handleImageUpload(Request $request): ?string
    {
        $file = $request->file('image');

        if (! $file instanceof \Illuminate\Http\UploadedFile
            || $file->getError() !== UPLOAD_ERR_OK
            || ! $file->getPathname()) {
            return null;
        }

        return $this->storeFile($file, 'auctions');
    }

    private function handleGalleryUpload(Request $request, Auction $auction): void
    {
        $files = $request->file('gallery');

        if (empty($files)) {
            return;
        }

        $nextOrder = $auction->images()->max('sort_order') + 1;

        foreach ((array) $files as $file) {
            if (! $file instanceof \Illuminate\Http\UploadedFile
                || $file->getError() !== UPLOAD_ERR_OK
                || ! $file->getPathname()) {
                continue;
            }
            $path = $this->storeFile($file, 'auctions/gallery');
            $auction->images()->create([
                'path'       => $path,
                'sort_order' => $nextOrder++,
            ]);
        }
    }
}
