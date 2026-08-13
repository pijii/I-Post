document.addEventListener('DOMContentLoaded', function () {
    // Dropdown toggle handling
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('[data-bs-toggle="dropdown"]');
        if (toggleBtn) {
            e.stopPropagation();
            if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(toggleBtn);
                dropdownInstance.toggle();
            }
        }
    });

    // Close open dropdowns when a modal opens
    document.addEventListener('show.bs.modal', function () {
        const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
        openDropdowns.forEach(function (menu) {
            menu.classList.remove('show');
        });
    });

    // File Sync & Preview Handler between Create Post Card and Modal
    const outerFileInput = document.getElementById('createPostFileInput');
    const modalFileInput = document.getElementById('modalPostFileInput');
    const modalPreviewImg = document.getElementById('modalImagePreview');

    function displayPreview(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                if (modalPreviewImg) {
                    modalPreviewImg.src = e.target.result;
                    modalPreviewImg.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    }

    if (outerFileInput && modalFileInput) {
        outerFileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                modalFileInput.files = this.files;
                displayPreview(this.files[0]);

                const modalElement = document.getElementById('uploadImageModal');
                if (modalElement && typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modalInstance.show();
                }
            }
        });

        modalFileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                displayPreview(this.files[0]);
            } else if (modalPreviewImg) {
                modalPreviewImg.src = '';
                modalPreviewImg.style.display = 'none';
            }
        });
    }

    const createFileInput = document.getElementById('createPostFileInput');
    const previewImg = document.getElementById('modalImagePreview');
    const uploadModalElem = document.getElementById('uploadImageModal');

    if (createFileInput) {
        createFileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                if (modalFileInput) modalFileInput.files = dataTransfer.files;

                const reader = new FileReader();
                reader.onload = function (event) {
                    if (previewImg) {
                        previewImg.src = event.target.result;
                        previewImg.style.display = 'block';
                    }

                    if (uploadModalElem && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(uploadModalElem);
                        modalInstance.show();
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (uploadModalElem) {
        uploadModalElem.addEventListener('hidden.bs.modal', function () {
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
            if (createFileInput) createFileInput.value = '';
            if (modalFileInput) modalFileInput.value = '';
        });
    }
});
