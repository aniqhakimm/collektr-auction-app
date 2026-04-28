@php $editing = isset($auction); @endphp

<div class="space-y-5">

    {{-- Title --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
        <input type="text" name="title"
               value="{{ old('title', $auction->title ?? '') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                      {{ $errors->has('title') ? 'border-red-400' : '' }}">
        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="4"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none
                         {{ $errors->has('description') ? 'border-red-400' : '' }}">{{ old('description', $auction->description ?? '') }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Starting Price --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Starting Price (RM) <span class="text-red-500">*</span></label>
        <input type="number" name="starting_price" step="0.01" min="0.01"
               value="{{ old('starting_price', $auction->starting_price ?? '') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                      {{ $errors->has('starting_price') ? 'border-red-400' : '' }}">
        @error('starting_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Auction End At --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Auction End Date/Time <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="auction_end_at"
               value="{{ old('auction_end_at', isset($auction) ? $auction->auction_end_at->format('Y-m-d\TH:i') : '') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                      {{ $errors->has('auction_end_at') ? 'border-red-400' : '' }}">
        @error('auction_end_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Category --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="category_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                       {{ $errors->has('category_id') ? 'border-red-400' : '' }}">
            <option value="">— No category —</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $auction->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Status --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
        <select name="status"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                       {{ $errors->has('status') ? 'border-red-400' : '' }}">
            @foreach(['draft', 'active', 'ended'] as $s)
                <option value="{{ $s }}" {{ old('status', $auction->status ?? 'draft') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Cover Image --}}
    <div
        x-data="{
            preview: '{{ $editing && $auction->image_path ? Storage::url($auction->image_path) : '' }}',
            onChange(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = ev => this.preview = ev.target.result;
                reader.readAsDataURL(file);
            }
        }"
    >
        <label class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>

        {{-- Preview --}}
        <template x-if="preview">
            <div class="mb-3">
                <img :src="preview" alt="Cover preview"
                     class="h-40 w-full object-cover rounded-lg border border-gray-200">
            </div>
        </template>

        {{-- Drop zone / file input --}}
        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-colors">
            <div class="flex flex-col items-center gap-1 text-gray-400 pointer-events-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <span class="text-xs" x-text="preview ? 'Click to replace image' : 'Click to upload cover image'"></span>
            </div>
            <input type="file" name="image" accept="image/*" class="hidden"
                   @change="onChange($event)">
        </label>
        @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Gallery Images --}}
    <div
        x-data="{
            existing: [
                @if($editing)
                    @foreach($auction->images as $img)
                        { id: {{ $img->id }}, url: '{{ Storage::url($img->path) }}', remove: false },
                    @endforeach
                @endif
            ],
            newPreviews: [],
            allFiles: new DataTransfer(),
            onGalleryChange(e) {
                const input = e.target;
                Array.from(input.files).forEach(file => {
                    this.allFiles.items.add(file);
                    const reader = new FileReader();
                    reader.onload = ev => this.newPreviews.push({ src: ev.target.result, name: file.name });
                    reader.readAsDataURL(file);
                });
                // Assign the accumulated FileList back so the form submits all files
                input.files = this.allFiles.files;
            },
            removeNew(index) {
                this.newPreviews.splice(index, 1);
                const fresh = new DataTransfer();
                Array.from(this.allFiles.files)
                    .filter((_, i) => i !== index)
                    .forEach(f => fresh.items.add(f));
                this.allFiles = fresh;
                this.$refs.galleryInput.files = fresh.files;
            }
        }"
    >
        <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>

        {{-- Existing saved images --}}
        <template x-if="existing.length > 0">
            <div class="mb-3">
                <p class="text-xs text-gray-400 mb-2">Saved images — click to mark for removal.</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="img in existing" :key="img.id">
                        <div class="relative cursor-pointer" @click="img.remove = !img.remove">
                            <img :src="img.url" alt="Gallery"
                                 :class="img.remove ? 'opacity-30' : 'opacity-100'"
                                 class="w-20 h-20 object-cover rounded-lg border border-gray-200 transition-opacity">
                            <div x-show="img.remove"
                                 class="absolute inset-0 flex items-center justify-center rounded-lg bg-red-500/20">
                                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <input type="checkbox" name="delete_images[]" :value="img.id" :checked="img.remove" class="hidden">
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- New image previews (accumulated) --}}
        <template x-if="newPreviews.length > 0">
            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="(item, i) in newPreviews" :key="i">
                    <div class="relative group">
                        <img :src="item.src" :alt="item.name"
                             class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                        <button type="button" @click="removeNew(i)"
                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full
                                       flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </template>

        {{-- Drop zone --}}
        <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-colors">
            <div class="flex flex-col items-center gap-1 text-gray-400 pointer-events-none">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <span class="text-xs"
                      x-text="newPreviews.length > 0 ? 'Add more images' : 'Click to upload gallery images'">
                </span>
            </div>
            <input x-ref="galleryInput" type="file" name="gallery[]" accept="image/*" multiple class="hidden"
                   @change="onGalleryChange($event)">
        </label>
        @error('gallery.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

</div>
