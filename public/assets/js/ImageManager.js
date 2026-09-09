/**
 * ImageManager - Enhanced image handling with compression
 *
 * Shared by listing upload UIs:
 * - Guidings (multi-step form)
 * - Camps, Trips, Accommodations, Rental boats, Special offers
 *
 * Keep API changes backward-compatible. Form-specific upload timeouts / spinners
 * live outside this file (e.g. guiding-form-loading.js for guidings only).
 * 
 * Features:
 * - Image cropping with Cropper.js
 * - Client-side image compression
 * - Automatic format detection (JPEG/PNG)
 * - File size validation
 * - Compression statistics
 * 
 * Compression Settings:
 * - maxWidth: Maximum image width (default: 1920px)
 * - maxHeight: Maximum image height (default: 1080px)
 * - quality: JPEG quality 0-1 (default: 0.8)
 * - maxFileSizeMB: Maximum file size in MB (default: 2MB)
 * 
 * Usage Examples:
 * 
 * // Initialize with custom compression settings
 * const imageManager = new ImageManager('#container', '#fileInput');
 * imageManager.updateCompressionSettings({
 *     maxWidth: 1200,
 *     maxHeight: 800,
 *     quality: 0.7,
 *     maxFileSizeMB: 1
 * });
 * 
 * // Get compressed images for upload
 * const compressedImages = imageManager.getCroppedImages(); // Already compressed
 * const compressedFiles = imageManager.getCompressedFiles(); // As File objects
 * const validatedFiles = imageManager.getValidatedCompressedFiles(); // Size-checked
 * 
 * // View compression statistics
 * imageManager.logCompressionStats();
 * 
 * // Demo compression features
 * imageManager.demonstrateCompression();
 * 
 * // Test different compression settings
 * imageManager.testCompression();
 * 
 * // Show compression progress
 * imageManager.showCompressionProgress((results) => {
 *     console.log('Compression complete:', results);
 * });
 * 
 * Integration with existing form submission:
 * The compression is automatically applied when calling getCroppedImages().
 * Your existing form submission code will work without changes:
 * 
 * const croppedImages = imageManager.getCroppedImages();
 * croppedImages.forEach((imgObj, idx) => {
 *     const blob = dataURLtoBlob(imgObj.dataUrl); // Already compressed
 *     formData.append('title_image[]', blob, imgObj.filename);
 * });
 * 
 * Troubleshooting:
 * - If images are still too large, reduce quality or max dimensions
 * - If quality is too low, increase quality setting
 * - Check console for compression statistics and warnings
 * - Use getValidatedCompressedFiles() to ensure files meet size requirements
 */

// Guard against double-including this file on the same page
if (typeof window !== 'undefined' && !window.ImageManager) {

class ImageManager {
    /**
     * @param {string} containerSelector
     * @param {string} fileInputSelector
     * @param {object} [options]
     * @param {string|null} [options.storagePrefix] - e.g. 'guidings-images' for new uploads; omit for bare filenames
     * @param {RegExp|null} [options.pathStripPattern] - strip model-specific URL segments when loading existing images
     */
    constructor(containerSelector, fileInputSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        this.fileInput = document.querySelector(fileInputSelector);

        if (!this.container) {
            console.error('Container not found:', containerSelector);
            return;
        }
        if (!this.fileInput) {
            console.error('File input not found:', fileInputSelector);
            return;
        }

        this.storagePrefix = options.storagePrefix || null;
        this.pathStripPattern = options.pathStripPattern || null;

        this.croppers = [];
        this.imageFiles = [];
        this.imageIndex = 0;
        this.primaryImageIndex = 0;
        this.image_list = [];

        // Track whether we've attempted any HEIC/HEIF conversions
        this.heicConversionSupported = typeof window.heic2any === 'function';

        // Compression settings
        this.compressionSettings = {
            maxWidth: 1920,
            maxHeight: 1080,
            quality: 0.8,
            maxFileSizeMB: 2 // Maximum file size in MB
        };

        this.initEventListeners();
        this.preventFormSubmission();
    }

    initEventListeners() {
        this.fileInput.addEventListener('change', this.debounce((e) => this.handleFileSelect(e.target.files), 300));
    }

    debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    /**
     * Normalize image paths (strip URL origin, leading slashes).
     * Paths that already include a directory are kept as-is.
     * Bare filenames get storagePrefix only when configured (guidings forms).
     */
    resolveImageUrl(path) {
        if (!path || typeof path !== 'string') {
            return '';
        }

        if (path.startsWith('data:') || path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//')) {
            return path;
        }

        const normalized = this.normalizeImagePath(path);
        if (!normalized) {
            return '';
        }

        if (normalized.startsWith('assets/') || normalized.startsWith('images/')) {
            return `${window.location.origin}/${normalized}`;
        }

        const cdnBase = (typeof window.mediaCdnBase === 'string' && window.mediaCdnBase) ? window.mediaCdnBase.replace(/\/$/, '') : '';
        const envPrefix = (typeof window.mediaEnvPrefix === 'string' && window.mediaEnvPrefix) ? window.mediaEnvPrefix.replace(/\/$/, '') : '';

        if (cdnBase) {
            return envPrefix ? `${cdnBase}/${envPrefix}/${normalized}` : `${cdnBase}/${normalized}`;
        }

        const localBase = (typeof window.mediaLocalBase === 'string' && window.mediaLocalBase)
            ? window.mediaLocalBase.replace(/\/$/, '')
            : window.location.origin;

        return `${localBase}/${normalized}`;
    }

    normalizeImagePath(path) {
        if (!path || typeof path !== 'string') {
            return null;
        }

        try {
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//')) {
                const url = new URL(path, window.location.origin);
                path = url.pathname;
            }
        } catch (e) {
            // keep path as-is
        }

        if (this.pathStripPattern) {
            path = path.replace(this.pathStripPattern, '');
        }

        path = path.replace(/^\/+/, '');

        if (!path) {
            return null;
        }

        if (path.includes('/')) {
            return path;
        }

        if (this.storagePrefix) {
            return this.storagePrefix + '/' + path;
        }

        return path;
    }

    /**
     * Rebuild image_list from current preview wrappers and sync hidden input.
     */
    syncImageListFromDom() {
        const wrappers = document.querySelectorAll('.image-preview-wrapper');
        this.image_list = Array.from(wrappers).map((wrapper) => {
            if (wrapper.dataset.storagePath) {
                return this.normalizeImagePath(wrapper.dataset.storagePath);
            }
            if (wrapper.dataset.filename) {
                return this.normalizeImagePath(wrapper.dataset.filename);
            }
            const img = wrapper.querySelector('img');
            if (img && img.src) {
                return this.normalizeImagePath(img.src);
            }
            return null;
        }).filter(Boolean);

        const imageListElement = document.getElementById('image_list');
        if (imageListElement) {
            imageListElement.value = JSON.stringify(this.image_list);
        }
    }

    /**
     * Update client-side gallery snapshot after a successful server save.
     */
    setGallerySnapshot(galleryImages, thumbnailPath) {
        const normalized = (Array.isArray(galleryImages) ? galleryImages : [])
            .map((path) => this.normalizeImagePath(path))
            .filter(Boolean);

        this.image_list = normalized;

        const imageListElement = document.getElementById('image_list');
        if (imageListElement) {
            imageListElement.value = JSON.stringify(normalized);
        }

        const existingImagesElement = document.getElementById('existing_images');
        if (existingImagesElement) {
            existingImagesElement.value = JSON.stringify(normalized);
        }

        const thumbnailElement = document.getElementById('thumbnail_path');
        if (thumbnailElement && thumbnailPath) {
            thumbnailElement.value = thumbnailPath;
        }

        // Keep preview wrappers aligned with server paths so later saves don't drop images.
        const wrappers = Array.from(this.container.querySelectorAll('.image-preview-wrapper'));
        normalized.forEach((path, index) => {
            const wrapper = wrappers[index];
            if (!wrapper || !path) {
                return;
            }
            wrapper.dataset.storagePath = path;
            wrapper.dataset.filename = path.split('/').pop();
            wrapper.dataset.unsaved = 'false';
        });
    }

    /**
     * Main entry point for newly selected files.
     * - Detects unsupported/HEIC images and converts them when possible
     * - Avoids duplicate previews
     * - Updates hidden image_list for backend ordering
     */
    async handleFileSelect(files) {
        try {
            if (!files || files.length === 0) {
                return;
            }

            const fileArray = Array.from(files);

            // Create a Set to track unique files based on name and size
            const uniqueFiles = new Set();
            const existingFiles = new Set(
                Array.from(this.imageFiles).map(file => `${file.name}-${file.size}`)
            );

            for (const originalFile of fileArray) {
                // Basic client-side security: only accept images
                const mimeType = (originalFile.type || '').toLowerCase();
                const extension = (originalFile.name || '').toLowerCase().split('.').pop();

                const isProbablyImage = mimeType.startsWith('image/')
                    || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'heic', 'heif'].includes(extension);

                if (!isProbablyImage) {
                    console.warn('Skipped non-image file for security reasons:', originalFile.name);
                    continue;
                }

                let file = originalFile;

                // Handle HEIC/HEIF by converting to JPEG when browser/library supports it
                const isHeicLike =
                    mimeType === 'image/heic' ||
                    mimeType === 'image/heif' ||
                    ['heic', 'heif'].includes(extension);

                if (isHeicLike) {
                    if (this.heicConversionSupported) {
                        try {
                            const convertedBlob = await window.heic2any({
                                blob: originalFile,
                                toType: 'image/jpeg',
                                quality: 0.8
                            });

                            const newName = originalFile.name.replace(/\.[^/.]+$/, '') + '.jpg';
                            file = new File([convertedBlob], newName, { type: 'image/jpeg' });
                        } catch (conversionError) {
                            console.error('HEIC/HEIF conversion failed, skipping file:', originalFile.name, conversionError);
                            alert('One of your HEIC/HEIF images could not be converted. Please export it as JPG/PNG and upload again.');
                            continue;
                        }
                    } else {
                        console.warn('HEIC/HEIF detected but heic2any is not available. Skipping file:', originalFile.name);
                        alert('Your device saved a photo as HEIC/HEIF. Please switch your camera to “Most compatible (JPEG)” or export the photo as JPG/PNG before uploading.');
                        continue;
                    }
                }

                const fileKey = `${file.name}-${file.size}`;

                // Skip if we've already processed this exact file in this session
                if (uniqueFiles.has(fileKey) || existingFiles.has(fileKey)) {
                    continue;
                }

                uniqueFiles.add(fileKey);
                this.imageFiles.push(file);

                const assignedIndex = this.imageIndex;
                this.imageIndex++;

                const reader = new FileReader();
                reader.onload = (e) => {
                    // Check if this image preview already exists
                    const existingPreview = document.querySelector(`.image-preview-wrapper[data-filename="${file.name}"]`);
                    if (existingPreview) {
                        return;
                    }

                    const wrapper = this.createImagePreview(e.target.result, assignedIndex);
                    wrapper.dataset.filename = file.name;
                    wrapper.dataset.storagePath = this.normalizeImagePath(file.name);
                    wrapper.dataset.unsaved = 'true';
                    this.container.appendChild(wrapper);
                    this.initCropper(wrapper.querySelector('img'));
                    this.syncImageListFromDom();

                    if (assignedIndex === 0 && !document.querySelector('.image-preview-wrapper.primary')) {
                        this.setPrimaryImage(0);
                    }
                };
                reader.readAsDataURL(file);
            }

            // Add validation for maximum number of images
            if (this.imageFiles.length > 20) {
                alert('Maximum 20 images allowed');
                // Remove excess images
                this.imageFiles = this.imageFiles.slice(0, 20);
                this.updateFileInput();
                return;
            }

            this.updateFileInput();
            // Allow selecting the same file again on the next pick
            this.fileInput.value = '';
        } catch (error) {
            console.error('Error in handleFileSelect:', error);
        }
    }

    imageExists(file) {
        // Check both the imageFiles array and the DOM for existing previews
        const fileKey = `${file.name}-${file.size}`;
        const existingInArray = this.imageFiles.some(existingFile =>
            `${existingFile.name}-${existingFile.size}` === fileKey
        );
        const existingInDOM = document.querySelector(`.image-preview-wrapper[data-filename="${file.name}"]`);

        if (existingInArray || existingInDOM) {
            return true;
        }
        return false;
    }

    createImagePreview(src, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'image-preview-wrapper';
        wrapper.dataset.index = index;
        wrapper.style.border = '1px solid red'; // Add this line
        wrapper.style.margin = '10px'; // Add this line

        const img = document.createElement('img');
        img.src = src;
        img.className = 'image-preview';
        img.style.maxWidth = '100%'; // Add this line
        wrapper.appendChild(img);

        const controls = this.createImageControls(index);
        wrapper.appendChild(controls);

        return wrapper;
    }

    createImageControls() {
        const controls = document.createElement('div');
        controls.className = 'image-controls';

        const deleteBtn = this.createButton('<i class="fas fa-trash"></i>', (e) => {
            e.preventDefault();
            const wrapper = e.target.closest('.image-preview-wrapper');
            const index = parseInt(wrapper.dataset.index);
            this.deleteImage(index);
        }, 'Delete');

        const zoomInBtn = this.createButton('<i class="fas fa-search-plus"></i>', (e) => {
            e.preventDefault();
            const wrapper = e.target.closest('.image-preview-wrapper');
            const index = parseInt(wrapper.dataset.index);
            this.croppers[index].zoom(0.1);
        }, 'Zoom In');

        const zoomOutBtn = this.createButton('<i class="fas fa-search-minus"></i>', (e) => {
            e.preventDefault();
            const wrapper = e.target.closest('.image-preview-wrapper');
            const index = parseInt(wrapper.dataset.index);
            this.croppers[index].zoom(-0.1);
        }, 'Zoom Out');

        const rotateBtn = this.createButton('<i class="fas fa-redo"></i>', (e) => {
            e.preventDefault();
            const wrapper = e.target.closest('.image-preview-wrapper');
            const index = parseInt(wrapper.dataset.index);
            this.croppers[index].rotate(90);
        }, 'Rotate');

        const setPrimaryBtn = this.createButton('<i class="fas fa-star"></i>', (e) => {
            e.preventDefault();
            const wrapper = e.target.closest('.image-preview-wrapper');
            const index = parseInt(wrapper.dataset.index);
            this.setPrimaryImage(index);
        }, 'Set as Title Image');

        controls.appendChild(zoomInBtn);
        controls.appendChild(zoomOutBtn);
        controls.appendChild(rotateBtn);
        controls.appendChild(deleteBtn);
        controls.appendChild(setPrimaryBtn);

        return controls;
    }

    createButton(innerHTML, onClick, title) {
        const button = document.createElement('button');
        button.type = 'button'; // Explicitly set type to "button"
        button.className = 'image-control-btn';
        button.innerHTML = innerHTML;
        button.title = title;
        button.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            onClick(e);
        };
        return button;
    }

    initCropper(img) {
        if (!img) {
            console.error('Image element not found');
            return;
        }
        const wrapper = img.closest('.image-preview-wrapper');
        const cropper = new Cropper(img, {
            aspectRatio: 6 / 4,
            viewMode: 3,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: false,
            center: false,
            highlight: false,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            minCropBoxWidth: img.parentElement.offsetWidth,
            minCropBoxHeight: img.parentElement.offsetHeight,
            ready: function() {
                const cropper = this.cropper;
                const imageData = cropper.getImageData();
                const containerData = cropper.getContainerData();

                const scale = Math.max(
                    containerData.width / imageData.naturalWidth,
                    containerData.height / imageData.naturalHeight
                );

                cropper.zoomTo(scale);

                const scaledWidth = imageData.naturalWidth * scale;
                const scaledHeight = imageData.naturalHeight * scale;
                const left = (containerData.width - scaledWidth) / 2;
                const top = (containerData.height - scaledHeight) / 2;

                cropper.setCanvasData({
                    left: left,
                    top: top,
                    width: scaledWidth,
                    height: scaledHeight
                });
            }
        });
        this.croppers.push(cropper);
        if (wrapper) {
            wrapper.cropperInstance = cropper;
        }
    }

    deleteImage(index) {
        const wrapper = (this.container || document).querySelector(`.image-preview-wrapper[data-index="${index}"]`);
        if (wrapper) {
            const isPrimaryImage = wrapper.classList.contains('primary');
            if (wrapper.cropperInstance && typeof wrapper.cropperInstance.destroy === 'function') {
                wrapper.cropperInstance.destroy();
            }
            wrapper.cropperInstance = null;
            wrapper.remove();

            // Update arrays
            this.croppers.splice(index, 1);
            this.imageFiles.splice(index, 1);

            // Update hidden inputs
            this.updateFileInput();
            this.syncImageListFromDom();

            // Update indices and croppers array
            const wrappers = document.querySelectorAll('.image-preview-wrapper');
            wrappers.forEach((w, newIndex) => {
                w.dataset.index = newIndex;
                if (this.croppers[newIndex]) {
                    this.croppers[newIndex].wrapper = w;
                }
            });

            // Update imageIndex
            this.imageIndex = wrappers.length;

            // Handle primary image
            if (isPrimaryImage && this.imageFiles.length > 0) {
                this.setPrimaryImage(0);
            }
        }
    }

    setPrimaryImage(index) {
        document.querySelectorAll('.image-preview-wrapper').forEach(wrapper => {
            wrapper.classList.remove('primary');
        });
        console.log("setPrimaryImage", index);

        const wrapper = document.querySelector(`.image-preview-wrapper[data-index="${index}"]`);
        if (wrapper) {
            wrapper.classList.add('primary');
            this.primaryImageIndex = index;
            document.getElementById('primaryImageInput').value = index;

            // Update the thumbnail_path hidden input
            const img = wrapper.querySelector('img');
            if (img) {
                document.getElementById('thumbnail_path').value = img.src;
            }
        } else {
            console.error('No image wrapper found for index:', index);
        }
    }

    updateFileInput() {
        const dataTransfer = new DataTransfer();
        this.imageFiles.forEach(file => dataTransfer.items.add(file));
        this.fileInput.files = dataTransfer.files;
    }

    markAllImagesAsPersisted() {
        document.querySelectorAll('.image-preview-wrapper[data-unsaved="true"]').forEach((wrapper) => {
            wrapper.dataset.unsaved = 'false';
        });
    }

    hasUnsavedImages() {
        return document.querySelectorAll('.image-preview-wrapper[data-unsaved="true"]').length > 0;
    }

    getCroppedImages(onlyUnsaved = false) {
        const scope = this.container || document;
        const wrappers = scope.querySelectorAll('.image-preview-wrapper');
        if (!wrappers.length) {
            return [];
        }

        return Array.from(wrappers).map((wrapper, position) => {
            if (onlyUnsaved && wrapper.dataset.unsaved !== 'true') {
                return null;
            }

            const index = Number.parseInt(wrapper.dataset.index, 10);
            const cropper = wrapper.cropperInstance
                || (Number.isInteger(index) ? this.croppers[index] : null);

            if (!cropper || typeof cropper.getCroppedCanvas !== 'function') {
                return null;
            }

            try {
                let filename = wrapper.dataset.filename || null;

                if (!filename) {
                    const img = wrapper.querySelector('img');
                    if (img && img.src) {
                        const urlParts = img.src.split('/');
                        filename = urlParts[urlParts.length - 1];
                    }
                }

                // Cap canvas export size for all listing forms (guidings, camps, trips,
                // accommodations, rental boats, special offers). Matches compressionSettings
                // already used by compressImage(); avoids freezing on large phone photos.
                const maxWidth = Number(this.compressionSettings.maxWidth) || 1920;
                const maxHeight = Number(this.compressionSettings.maxHeight) || 1080;
                const canvas = cropper.getCroppedCanvas({
                    maxWidth,
                    maxHeight,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'medium',
                });
                if (!canvas) {
                    console.error(`Cropper returned no canvas for image ${index}`);
                    return null;
                }
                const compressedDataUrl = this.compressImage(canvas, filename);

                return {
                    dataUrl: compressedDataUrl,
                    index: Number.isInteger(index) ? index : position,
                    filename: filename
                };
            } catch (error) {
                console.error(`Error getting cropped canvas for image ${index}:`, error);
                return null;
            }
        }).filter(Boolean);
    }

    /**
     * Compress image to reduce file size
     * @param {HTMLCanvasElement} canvas - The canvas element to compress
     * @param {string} filename - Original filename for format detection
     * @param {number} maxWidth - Maximum width (default: from settings)
     * @param {number} maxHeight - Maximum height (default: from settings)
     * @param {number} quality - JPEG quality (0-1, default: from settings)
     * @returns {string} Compressed image as data URL
     */
    compressImage(canvas, filename, maxWidth = null, maxHeight = null, quality = null) {
        try {
            const ctx = canvas.getContext('2d');
            let { width, height } = canvas;
            
            // Use settings if not provided
            maxWidth = maxWidth || this.compressionSettings.maxWidth;
            maxHeight = maxHeight || this.compressionSettings.maxHeight;
            quality = quality || this.compressionSettings.quality;
            
            // Calculate new dimensions while maintaining aspect ratio
            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height);
                width = Math.round(width * ratio);
                height = Math.round(height * ratio);
            }
            
            // Create a new canvas with the target dimensions
            const compressedCanvas = document.createElement('canvas');
            compressedCanvas.width = width;
            compressedCanvas.height = height;
            const compressedCtx = compressedCanvas.getContext('2d');
            
            // Draw the resized image
            compressedCtx.drawImage(canvas, 0, 0, width, height);
            
            // Determine the best format based on filename and content
            const isTransparent = this.hasTransparency(canvas);
            const format = isTransparent ? 'image/png' : 'image/jpeg';
            
            // Convert to data URL with compression
            if (format === 'image/jpeg') {
                return compressedCanvas.toDataURL('image/jpeg', quality);
            } else {
                // For PNG, we can't control quality, but we can try to reduce size
                return compressedCanvas.toDataURL('image/png');
            }
        } catch (error) {
            console.error('Error compressing image:', error);
            // Fallback to original canvas
            return canvas.toDataURL('image/jpeg', quality || this.compressionSettings.quality);
        }
    }

    /**
     * Check if an image has transparency
     * @param {HTMLCanvasElement} canvas - The canvas to check
     * @returns {boolean} True if the image has transparency
     */
    hasTransparency(canvas) {
        try {
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;
            
            // Sample pixels for performance (check every 10th pixel)
            const sampleRate = 10;
            for (let i = 3; i < data.length; i += sampleRate * 4) {
                if (data[i] < 255) {
                    return true;
                }
            }
            return false;
        } catch (error) {
            console.error('Error checking transparency:', error);
            return false;
        }
    }

    /**
     * Get file size in MB from data URL
     * @param {string} dataUrl - The data URL to check
     * @returns {number} File size in MB
     */
    getFileSizeFromDataUrl(dataUrl) {
        try {
            // Remove the data URL prefix to get the base64 string
            const base64 = dataUrl.split(',')[1];
            // Calculate size: base64 is 4/3 times the actual size
            const sizeInBytes = (base64.length * 3) / 4;
            return sizeInBytes / (1024 * 1024); // Convert to MB
        } catch (error) {
            console.error('Error calculating file size:', error);
            return 0;
        }
    }

    getPrimaryImageIndex() {
        return this.primaryImageIndex;
    }

    // Add this method to handle preview of existing images
    previewExistingImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            this.createImagePreview(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    addExistingImage(imagePath, isPrimary = false) {
        const normalizedPath = this.normalizeImagePath(imagePath);
        const correctedImagePath = this.resolveImageUrl(imagePath);

        // Check if image already exists
        const existingWrapper = Array.from(document.querySelectorAll('.image-preview-wrapper'))
            .find(wrapper => {
                const stored = wrapper.dataset.storagePath;
                if (stored && normalizedPath && stored === normalizedPath) {
                    return true;
                }
                const img = wrapper.querySelector('img');
                return img && img.src === correctedImagePath;
            });

        if (existingWrapper) {
            if (isPrimary) {
                this.setPrimaryImage(parseInt(existingWrapper.dataset.index));
            }
            return;
        }

        const wrapper = this.createImagePreview(correctedImagePath, this.imageIndex);
        if (normalizedPath) {
            wrapper.dataset.storagePath = normalizedPath;
            wrapper.dataset.filename = normalizedPath.split('/').pop();
        }
        wrapper.dataset.unsaved = 'false';
        this.container.appendChild(wrapper);
        this.initCropper(wrapper.querySelector('img'));
        if (isPrimary) {
            this.setPrimaryImage(this.imageIndex);
        }
        this.imageIndex++;
        this.syncImageListFromDom();

        // Update the file input using DataTransfer
        this.updateFileInput();
    }

    loadExistingImages(existingImages, thumbnailPath) {
        if (typeof existingImages === 'string') {
            try {
                existingImages = JSON.parse(existingImages);
            } catch (e) {
                console.error('Error parsing existing images:', e);
                return;
            }
        }

        if (Array.isArray(existingImages)) {
            existingImages.forEach((imagePath) => {
                let correctedPath = imagePath;
                if (this.pathStripPattern) {
                    correctedPath = imagePath.replace(this.pathStripPattern, '/');
                }
                this.addExistingImage(correctedPath, correctedPath === thumbnailPath);
            });

            // If no primary image was set, set the first image as primary
            if (!document.querySelector('.image-preview-wrapper.primary')) {
                this.setPrimaryImage(0);
            }
        } else {
            console.error('Existing images data is not an array:', existingImages);
        }
    }

    preventFormSubmission() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('click', (e) => {
                if (e.target.closest('.image-controls')) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        }
    }

    // Add new method to check image count
    getImageCount() {
        return this.imageFiles.length;
    }

    cleanupDuplicates() {
        const uniqueFiles = new Set();
        const cleanedFiles = [];
        const seenPreviews = new Set();

        document.querySelectorAll('.image-preview-wrapper').forEach(wrapper => {
            const filename = wrapper.dataset.filename;
            if (filename) {
                seenPreviews.add(filename);
            }
        });

        this.imageFiles.forEach(file => {
            const fileKey = `${file.name}-${file.size}`;
            if (!uniqueFiles.has(fileKey) && seenPreviews.has(file.name)) {
                uniqueFiles.add(fileKey);
                cleanedFiles.push(file);
            }
        });

        this.imageFiles = cleanedFiles;
        this.updateFileInput();
    }

    /**
     * Convert data URL to File object for upload
     * @param {string} dataUrl - The data URL to convert
     * @param {string} filename - The filename for the file
     * @param {string} mimeType - The MIME type (default: image/jpeg)
     * @returns {File} File object ready for upload
     */
    dataURLtoFile(dataUrl, filename, mimeType = 'image/jpeg') {
        try {
            const arr = dataUrl.split(',');
            const mime = mimeType || arr[0].match(/:(.*?);/)[1];
            const bstr = atob(arr[1]);
            let n = bstr.length;
            const u8arr = new Uint8Array(n);
            
            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }
            
            return new File([u8arr], filename, { type: mime });
        } catch (error) {
            console.error('Error converting data URL to file:', error);
            return null;
        }
    }

    /**
     * Get compressed files ready for upload
     * @returns {Array} Array of File objects
     */
    getCompressedFiles() {
        const croppedImages = this.getCroppedImages();
        const files = [];
        
        croppedImages.forEach((imageData, index) => {
            if (imageData && imageData.dataUrl) {
                // Determine file extension based on data URL
                const isJPEG = imageData.dataUrl.includes('image/jpeg');
                const extension = isJPEG ? 'jpg' : 'png';
                const mimeType = isJPEG ? 'image/jpeg' : 'image/png';
                
                // Generate filename
                const filename = imageData.filename ? 
                    imageData.filename.replace(/\.[^/.]+$/, '') + '_compressed.' + extension :
                    `image_${index}_compressed.${extension}`;
                
                // Convert to file
                const file = this.dataURLtoFile(imageData.dataUrl, filename, mimeType);
                if (file) {
                    files.push(file);
                }
            }
        });
        
        return files;
    }

    /**
     * Update compression settings
     * @param {Object} settings - New compression settings
     */
    updateCompressionSettings(settings) {
        this.compressionSettings = { ...this.compressionSettings, ...settings };
    }

    /**
     * Get compression statistics for all images
     * @returns {Object} Compression statistics
     */
    getCompressionStats() {
        const croppedImages = this.getCroppedImages();
        let totalOriginalSize = 0;
        let totalCompressedSize = 0;
        let totalImages = croppedImages.length;
        
        croppedImages.forEach(imageData => {
            if (imageData && imageData.dataUrl) {
                const compressedSize = this.getFileSizeFromDataUrl(imageData.dataUrl);
                totalCompressedSize += compressedSize;
                
                // Estimate original size (this is approximate)
                const originalSize = compressedSize / this.compressionSettings.quality;
                totalOriginalSize += originalSize;
            }
        });
        
        const compressionRatio = totalOriginalSize > 0 ? 
            ((totalOriginalSize - totalCompressedSize) / totalOriginalSize * 100) : 0;
        
        return {
            totalImages,
            totalOriginalSizeMB: totalOriginalSize.toFixed(2),
            totalCompressedSizeMB: totalCompressedSize.toFixed(2),
            compressionRatio: compressionRatio.toFixed(1),
            averageSizeMB: totalImages > 0 ? (totalCompressedSize / totalImages).toFixed(2) : 0
        };
    }

    /**
     * Log compression statistics to console
     */
    logCompressionStats() {
        const stats = this.getCompressionStats();
        console.log('Image Compression Statistics:', stats);
        return stats;
    }

    /**
     * Example usage of compression features
     * Call this method to see compression in action
     */
    demonstrateCompression() {
        console.log('=== Image Compression Demo ===');
        
        // Show current settings
        console.log('Current compression settings:', this.compressionSettings);
        
        // Get compression stats
        const stats = this.logCompressionStats();
        
        // Example: Update settings for more aggressive compression
        this.updateCompressionSettings({
            maxWidth: 1200,
            maxHeight: 800,
            quality: 0.6,
            maxFileSizeMB: 1
        });
        
        console.log('Updated settings for aggressive compression:', this.compressionSettings);
        
        // Get new stats with updated settings
        const newStats = this.logCompressionStats();
        
        // Show improvement
        const improvement = ((parseFloat(stats.averageSizeMB) - parseFloat(newStats.averageSizeMB)) / parseFloat(stats.averageSizeMB) * 100).toFixed(1);
        console.log(`Compression improvement: ${improvement}% smaller files`);
        
        return {
            original: stats,
            compressed: newStats,
            improvement: improvement
        };
    }

    /**
     * Get compressed files with size validation
     * @returns {Array} Array of File objects that meet size requirements
     */
    getValidatedCompressedFiles() {
        const files = this.getCompressedFiles();
        const validFiles = [];
        
        files.forEach((file, index) => {
            const fileSizeMB = file.size / (1024 * 1024);
            
            if (fileSizeMB <= this.compressionSettings.maxFileSizeMB) {
                validFiles.push(file);
                console.log(`File ${index + 1}: ${file.name} - ${fileSizeMB.toFixed(2)}MB ✓`);
            } else {
                console.warn(`File ${index + 1}: ${file.name} - ${fileSizeMB.toFixed(2)}MB (too large, max: ${this.compressionSettings.maxFileSizeMB}MB)`);
            }
        });
        
        return validFiles;
    }

    /**
     * Show compression progress and results
     * @param {Function} callback - Optional callback function
     */
    showCompressionProgress(callback = null) {
        const totalImages = this.croppers.length;
        if (totalImages === 0) {
            console.log('No images to compress');
            if (callback) callback();
            return;
        }

        console.log(`Starting compression of ${totalImages} images...`);
        
        let processed = 0;
        const results = [];

        this.croppers.forEach((cropper, index) => {
            try {
                const canvas = cropper.getCroppedCanvas();
                const originalSize = this.getFileSizeFromDataUrl(canvas.toDataURL('image/png'));
                
                const compressedDataUrl = this.compressImage(canvas, `image_${index}`);
                const compressedSize = this.getFileSizeFromDataUrl(compressedDataUrl);
                
                const compressionRatio = ((originalSize - compressedSize) / originalSize * 100).toFixed(1);
                
                results.push({
                    index,
                    originalSize: originalSize.toFixed(2),
                    compressedSize: compressedSize.toFixed(2),
                    compressionRatio: compressionRatio + '%'
                });
                
                processed++;
                console.log(`✓ Image ${index + 1}/${totalImages} compressed: ${originalSize.toFixed(2)}MB → ${compressedSize.toFixed(2)}MB (${compressionRatio}% reduction)`);
                
                if (processed === totalImages && callback) {
                    console.log('=== Compression Complete ===');
                    console.table(results);
                    callback(results);
                }
            } catch (error) {
                console.error(`Error compressing image ${index}:`, error);
                processed++;
                if (processed === totalImages && callback) {
                    callback(results);
                }
            }
        });
    }

    /**
     * Test compression with sample settings
     * @returns {Object} Test results
     */
    testCompression() {
        console.log('=== Testing Image Compression ===');
        
        const testSettings = [
            { name: 'Default', maxWidth: 1920, maxHeight: 1080, quality: 0.8 },
            { name: 'Medium', maxWidth: 1200, maxHeight: 800, quality: 0.7 },
            { name: 'High', maxWidth: 800, maxHeight: 600, quality: 0.6 },
            { name: 'Maximum', maxWidth: 600, maxHeight: 400, quality: 0.5 }
        ];
        
        const results = [];
        
        testSettings.forEach(setting => {
            this.updateCompressionSettings(setting);
            const stats = this.getCompressionStats();
            results.push({
                setting: setting.name,
                ...stats
            });
        });
        
        console.table(results);
        return results;
    }
}

// Expose to global scope once
window.ImageManager = ImageManager;

} // end guard for double inclusion