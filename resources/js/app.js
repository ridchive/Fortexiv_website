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
