// resources/js/components/qr-code-modal.js
document.addEventListener('alpine:init', () => {
    Alpine.data('qrCodeModal', (routeUrl) => ({
        show: false,
        surveyUrl: '',
        currentAccesskey: '',
        routeUrl: routeUrl, // Store the route URL parameter

        init() {
            this.$watch('show', (value) => {
                if (!value) {
                    // Reset state when modal is closed
                    this.surveyUrl = '';
                    this.currentAccesskey = '';
                }
            });

            // Listen for the open-qr-modal event
            window.addEventListener('open-qr-modal', (event) => {
                this.handleOpenModal(event.detail.accesskey);
            });
        },

        handleOpenModal(accesskey) {
            this.show = true;
            this.currentAccesskey = accesskey;

            this.$nextTick(() => {
                this.generateQrCode(accesskey);
            });
        },

        generateQrCode(accesskey) {
            // Use the stored route URL
            const baseUrl = this.routeUrl || '';

            // Create URL with accesskey token
            var url = new URL(baseUrl);
            url.searchParams.append('token', accesskey);
            this.surveyUrl = url.toString();

            if (typeof window.QRCode !== 'undefined') {
                try {
                    const canvas = document.getElementById('qrcode-canvas');
                    const loadingEl = document.getElementById('qrcode-loading');
                    const errorEl = document.getElementById('qrcode-error');

                    if (loadingEl) loadingEl.style.display = 'flex';
                    if (errorEl) errorEl.style.display = 'none';

                    window.QRCode.toCanvas(canvas, this.surveyUrl, {
                        width: 200,
                        margin: 1
                    }, function(error) {
                        if (loadingEl) loadingEl.style.display = 'none';

                        if (error) {
                            if (errorEl) errorEl.style.display = 'block';
                            console.error('QR code error:', error);
                        }
                    });
                } catch(e) {
                    const loadingEl = document.getElementById('qrcode-loading');
                    const errorEl = document.getElementById('qrcode-error');

                    if (loadingEl) loadingEl.style.display = 'none';
                    if (errorEl) errorEl.style.display = 'block';

                    console.error('QR code generation failed:', e);
                }
            } else {
                const loadingEl = document.getElementById('qrcode-loading');
                const errorEl = document.getElementById('qrcode-error');

                if (loadingEl) loadingEl.style.display = 'none';
                if (errorEl) errorEl.style.display = 'block';

                console.error('QRCode library not loaded');
            }
        }
    }));
});