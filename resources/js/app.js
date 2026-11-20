const qrCanvases = document.querySelectorAll('[data-qr-value]');

if (qrCanvases.length > 0) {
    import('qrcode')
        .then(({ default: QRCode }) => Promise.all(Array.from(qrCanvases, (canvas) => (
            QRCode.toCanvas(canvas, canvas.dataset.qrValue, { width: 180, margin: 1 })
        ))))
        .then(() => {
            document.querySelectorAll('[data-qr-download]').forEach((button) => {
                button.disabled = false;
            });
        })
        .catch(() => {
            qrCanvases.forEach((canvas) => {
                canvas.insertAdjacentText('afterend', 'QR tidak dapat ditampilkan. Gunakan token di bawah QR.');
            });
        });
}

document.querySelectorAll('[data-qr-download]').forEach((button) => {
    button.addEventListener('click', () => {
        const canvas = button.closest('[data-qr-card]').querySelector('canvas');
        const link = document.createElement('a');

        link.href = canvas.toDataURL('image/png');
        link.download = `${canvas.dataset.qrValue}-qr.png`;
        link.click();
    });
});

document.querySelectorAll('[data-token-download]').forEach((button) => {
    button.addEventListener('click', () => {
        const token = button.dataset.tokenDownload;
        const content = [
            'FORTEXIV - QR KELAS',
            `Order: ${button.dataset.orderCode}`,
            `Kelas: ${button.dataset.className}`,
            `Token: ${token}`,
            '',
        ].join('\n');
        const url = URL.createObjectURL(new Blob([content], { type: 'text/plain;charset=utf-8' }));
        const link = document.createElement('a');

        link.href = url;
        link.download = `${token}.txt`;
        link.click();
        URL.revokeObjectURL(url);
    });
});

const scannerPage = document.querySelector('[data-qr-scanner]');

if (scannerPage) {
    const reader = document.getElementById('seller-qr-reader');
    const message = scannerPage.querySelector('[data-qr-message]');
    const manualForm = scannerPage.querySelector('[data-qr-manual-form]');
    const tokenInput = manualForm.querySelector('[name="token"]');
    const preview = scannerPage.querySelector('[data-qr-preview]');
    const confirmButton = preview.querySelector('[data-qr-confirm]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let activeType = null;
    let activeToken = null;
    let scanner = null;
    let scannerRunning = false;
    let processing = false;

    const setMessage = (text, type = 'info') => {
        message.className = `note ${type}`;
        message.textContent = text;
    };

    const responseError = (payload) => {
        const validationMessage = payload.errors ? Object.values(payload.errors).flat()[0] : null;

        return validationMessage ?? payload.message ?? 'QR tidak dapat diproses.';
    };

    const post = async (url, values) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(values),
        });
        const payload = await response.json();

        if (!response.ok) {
            throw new Error(responseError(payload));
        }

        return payload;
    };

    const stopScanner = async () => {
        if (!scannerRunning || !scanner) {
            return;
        }

        await scanner.stop();
        scanner.clear();
        scanner = null;
        scannerRunning = false;
    };

    const detectQrType = (token) => {
        if (!token) return activeType || 'class';
        const clean = token.trim().toUpperCase();
        if (clean.startsWith('FTXP-')) return 'product';
        if (clean.startsWith('FTXC-')) return 'class';
        return activeType || 'class';
    };

    const showPreview = (data) => {
        if (preview.querySelector('[data-qr-order]')) {
            preview.querySelector('[data-qr-order]').textContent = `Order ID: ${data.order_code}`;
        }
        if (preview.querySelector('[data-qr-buyer]')) {
            preview.querySelector('[data-qr-buyer]').textContent = data.buyer_name || '—';
        }
        if (preview.querySelector('[data-qr-phone]')) {
            preview.querySelector('[data-qr-phone]').textContent = data.buyer_phone || '—';
        }
        if (preview.querySelector('[data-qr-class]')) {
            preview.querySelector('[data-qr-class]').textContent = data.class_name;
        }
        if (preview.querySelector('[data-qr-pending]')) {
            preview.querySelector('[data-qr-pending]').textContent = `${data.pending_count} unit`;
        }

        const products = preview.querySelector('[data-qr-products]');
        if (products) {
            products.replaceChildren();
            data.products.forEach((product) => {
                const item = document.createElement('li');
                item.style.marginBottom = '6px';
                item.innerHTML = `<strong>${product.name}</strong> × ${product.quantity} unit &middot; <span class="badge ${product.pending_count === 0 ? 'good' : 'warn'}">${product.delivered_count} Delivered, ${product.pending_count} belum diserahkan</span>`;
                products.append(item);
            });
        }

        preview.hidden = false;
        confirmButton.disabled = false;
    };

    const previewToken = async (token) => {
        activeToken = token.trim();
        tokenInput.value = activeToken;
        activeType = detectQrType(activeToken);
        preview.hidden = true;
        setMessage('Memeriksa QR…');

        try {
            let payload;
            try {
                payload = await post(scannerPage.dataset.previewUrl, {
                    type: activeType,
                    token: activeToken,
                });
            } catch (firstErr) {
                const altType = activeType === 'class' ? 'product' : 'class';
                payload = await post(scannerPage.dataset.previewUrl, {
                    type: altType,
                    token: activeToken,
                });
                activeType = altType;
            }

            showPreview(payload.data);
            setMessage('Periksa detail produk, lalu konfirmasikan penyerahan.', 'info');
        } catch (error) {
            setMessage(error.message, 'warn');
        }
    };

    const startScanner = async (type) => {
        try {
            await stopScanner();
            activeType = type;
            activeToken = null;
            preview.hidden = true;
            setMessage(type === 'class' ? 'Arahkan kamera ke QR kelas.' : 'Arahkan kamera ke QR produk.');

            const { Html5Qrcode } = await import('html5-qrcode');
            scanner = new Html5Qrcode(reader.id);
            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: (width, height) => {
                        const size = Math.floor(Math.min(width, height) * 0.75);

                        return { width: size, height: size };
                    },
                },
                async (decodedText) => {
                    if (processing) {
                        return;
                    }

                    processing = true;
                    try {
                        await stopScanner();
                        await previewToken(decodedText);
                    } catch (error) {
                        setMessage(`Kamera tidak dapat dihentikan: ${error.message}`, 'warn');
                    } finally {
                        processing = false;
                    }
                },
                () => {},
            );
            scannerRunning = true;
        } catch (error) {
            if (scanner && !scannerRunning) {
                scanner.clear();
                scanner = null;
            }

            setMessage(`Kamera tidak dapat dibuka: ${error.message}`, 'warn');
        }
    };

    scannerPage.querySelectorAll('[data-qr-mode]').forEach((button) => {
        button.addEventListener('click', () => startScanner(button.dataset.qrMode));
    });

    manualForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const val = tokenInput.value.trim();
        if (!val) {
            setMessage('Masukkan kode token terlebih dahulu.', 'warn');
            return;
        }

        activeType = detectQrType(val);
        await previewToken(val);
    });

    confirmButton.addEventListener('click', async () => {
        if (!activeType || !activeToken) {
            return;
        }

        confirmButton.disabled = true;
        setMessage('Menyimpan konfirmasi…');

        try {
            const result = await post(scannerPage.dataset.confirmUrl, {
                type: activeType,
                token: activeToken,
            });

            preview.hidden = true;
            tokenInput.value = '';
            activeToken = null;
            setMessage(result.message, 'info');
        } catch (error) {
            confirmButton.disabled = false;
            setMessage(error.message, 'warn');
        }
    });
}

const squareImageInputs = document.querySelectorAll('[data-product-image-input], [data-project-image-input]');

squareImageInputs.forEach((imageInput) => {
    const field = imageInput.closest('.field');
    const preview = field?.querySelector('[data-product-image-preview], [data-project-image-preview]');
    const generatedPreview = field?.querySelector('[data-product-generated-preview], [data-project-generated-preview]');
    const openButton = field?.querySelector('[data-open-square-crop]');

    if (!field || !preview || !openButton) {
        return;
    }

    const savedImageUrl = preview.hidden ? null : preview.getAttribute('src');
    let activeUrl = null;
    let activeFile = null;

    const showPreview = (url, altText) => {
        if (activeUrl) {
            URL.revokeObjectURL(activeUrl);
        }

        activeUrl = url;
        preview.src = url;
        preview.alt = altText;
        preview.hidden = false;
        if (generatedPreview) {
            generatedPreview.hidden = true;
        }
        openButton.hidden = false;
    };

    const openCropper = (sourceUrl, sourceFile = null) => {
        const backdrop = document.createElement('div');
        backdrop.className = 'image-cropper-backdrop';
        backdrop.innerHTML = `
            <div class="image-cropper-dialog" role="dialog" aria-modal="true" aria-labelledby="square-crop-title">
                <h2 id="square-crop-title">Atur posisi foto</h2>
                <p>Geser foto dan gunakan zoom agar komposisi persegi 1:1 sesuai.</p>
                <div class="image-crop-stage">
                    <img alt="">
                </div>
                <div class="image-crop-controls">
                    <span>Zoom</span>
                    <input type="range" min="1" max="3" step="0.01" value="1" aria-label="Zoom foto">
                    <span data-zoom-label>100%</span>
                </div>
                <div class="image-crop-buttons">
                    <button type="button" class="btn btn-outline" data-crop-cancel>Batal</button>
                    <button type="button" class="btn" data-crop-apply>Simpan posisi</button>
                </div>
            </div>
        `;
        document.body.append(backdrop);

        const stage = backdrop.querySelector('.image-crop-stage');
        const image = backdrop.querySelector('img');
        const zoomInput = backdrop.querySelector('input[type="range"]');
        const zoomLabel = backdrop.querySelector('[data-zoom-label]');
        const cancelButton = backdrop.querySelector('[data-crop-cancel]');
        const applyButton = backdrop.querySelector('[data-crop-apply]');
        const state = { zoom: 1, offsetX: 0, offsetY: 0, dragging: false, pointerX: 0, pointerY: 0, baseScale: 1 };

        const close = () => {
            backdrop.remove();
        };

        const constrain = () => {
            const stageSize = stage.clientWidth;
            const displayWidth = image.naturalWidth * state.baseScale * state.zoom;
            const displayHeight = image.naturalHeight * state.baseScale * state.zoom;
            const maxX = Math.max(0, (displayWidth - stageSize) / 2);
            const maxY = Math.max(0, (displayHeight - stageSize) / 2);
            state.offsetX = Math.min(maxX, Math.max(-maxX, state.offsetX));
            state.offsetY = Math.min(maxY, Math.max(-maxY, state.offsetY));
        };

        const render = () => {
            if (!image.naturalWidth) {
                return;
            }

            constrain();
            const stageSize = stage.clientWidth;
            const displayWidth = image.naturalWidth * state.baseScale * state.zoom;
            const displayHeight = image.naturalHeight * state.baseScale * state.zoom;
            const left = (stageSize - displayWidth) / 2 + state.offsetX;
            const top = (stageSize - displayHeight) / 2 + state.offsetY;
            image.style.width = `${displayWidth}px`;
            image.style.height = `${displayHeight}px`;
            image.style.left = `${left}px`;
            image.style.top = `${top}px`;
            zoomLabel.textContent = `${Math.round(state.zoom * 100)}%`;
        };

        image.onload = () => {
            const stageSize = stage.clientWidth;
            state.baseScale = Math.max(stageSize / image.naturalWidth, stageSize / image.naturalHeight);
            render();
        };
        image.src = sourceUrl;

        zoomInput.addEventListener('input', () => {
            state.zoom = Number(zoomInput.value);
            render();
        });

        stage.addEventListener('pointerdown', (event) => {
            state.dragging = true;
            state.pointerX = event.clientX;
            state.pointerY = event.clientY;
            stage.classList.add('dragging');
            stage.setPointerCapture(event.pointerId);
        });
        stage.addEventListener('pointermove', (event) => {
            if (!state.dragging) {
                return;
            }
            state.offsetX += event.clientX - state.pointerX;
            state.offsetY += event.clientY - state.pointerY;
            state.pointerX = event.clientX;
            state.pointerY = event.clientY;
            render();
        });
        stage.addEventListener('pointerup', (event) => {
            state.dragging = false;
            stage.classList.remove('dragging');
            stage.releasePointerCapture(event.pointerId);
        });

        cancelButton.addEventListener('click', close);
        backdrop.addEventListener('click', (event) => {
            if (event.target === backdrop) {
                close();
            }
        });

        applyButton.addEventListener('click', () => {
            const stageSize = stage.clientWidth;
            const outputSize = 1080;
            const displayScale = state.baseScale * state.zoom;
            const displayWidth = image.naturalWidth * displayScale;
            const displayHeight = image.naturalHeight * displayScale;
            const left = (stageSize - displayWidth) / 2 + state.offsetX;
            const top = (stageSize - displayHeight) / 2 + state.offsetY;
            const sourceX = (0 - left) / displayScale;
            const sourceY = (0 - top) / displayScale;
            const sourceSize = stageSize / displayScale;
            const canvas = document.createElement('canvas');
            canvas.width = outputSize;
            canvas.height = outputSize;
            const context = canvas.getContext('2d');
            context.drawImage(image, sourceX, sourceY, sourceSize, sourceSize, 0, 0, outputSize, outputSize);

            const originalType = sourceFile?.type || 'image/jpeg';
            const outputType = ['image/jpeg', 'image/png', 'image/webp'].includes(originalType) ? originalType : 'image/jpeg';
            canvas.toBlob((blob) => {
                if (!blob) {
                    close();
                    return;
                }

                const extension = outputType === 'image/png' ? 'png' : outputType === 'image/webp' ? 'webp' : 'jpg';
                const croppedFile = new File([blob], `cropped-${Date.now()}.${extension}`, {
                    type: outputType,
                });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                imageInput.files = transfer.files;
                activeFile = croppedFile;

                if (activeUrl) {
                    URL.revokeObjectURL(activeUrl);
                }
                const croppedUrl = URL.createObjectURL(croppedFile);
                showPreview(croppedUrl, 'Pratinjau foto 1:1');
                close();
            }, outputType, 0.92);
        });
    };

    imageInput.addEventListener('change', () => {
        const selectedFile = imageInput.files?.[0];
        if (!selectedFile?.type.startsWith('image/')) {
            return;
        }

        activeFile = selectedFile;
        const selectedUrl = URL.createObjectURL(selectedFile);
        showPreview(selectedUrl, 'Pratinjau foto 1:1');
        openCropper(selectedUrl, selectedFile);
    });

    openButton.addEventListener('click', () => {
        const sourceFile = activeFile || imageInput.files?.[0];
        const sourceUrl = activeUrl || savedImageUrl;
        if (sourceUrl) {
            openCropper(sourceUrl, sourceFile);
        }
    });

    if (savedImageUrl) {
        openButton.hidden = false;
    }
});
