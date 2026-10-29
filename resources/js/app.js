import { BrowserMultiFormatReader } from '@zxing/browser';
import { BarcodeFormat, DecodeHintType } from '@zxing/library';

const startButton = document.getElementById('start-qr-scan');

if (startButton) {
    const stopButton = document.getElementById('stop-qr-scan');
    const status = document.getElementById('qr-scan-status');
    const panel = document.getElementById('qr-scanner-panel');
    const video = document.getElementById('qr-camera');
    const imageInput = document.getElementById('qr-image-upload');
    const verifyUrl = startButton.dataset.verifyUrl;
    const hints = new Map([[DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.QR_CODE]]]);
    const reader = new BrowserMultiFormatReader(hints);
    let controls = null;
    let scanning = false;

    function setStatus(message) {
        status.textContent = message;
    }

    function stopCamera() {
        scanning = false;
        if (controls) {
            controls.stop();
            controls = null;
        }
        reader.reset();
        video.srcObject = null;
        panel.hidden = true;
        startButton.hidden = false;
        stopButton.hidden = true;
    }

    function openVerification(scannedValue) {
        const destination = new URL(verifyUrl, window.location.origin);
        destination.searchParams.set('code', scannedValue);
        window.location.assign(destination.toString());
    }

    startButton.addEventListener('click', async function () {
        if (!window.isSecureContext) {
            setStatus('The browser blocks camera access on this connection. Open the permit QR with your phone camera, or use the code entry field below.');
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Camera access is not available in this browser. Upload a QR image or enter the code below.');
            return;
        }

        try {
            panel.hidden = false;
            startButton.hidden = true;
            stopButton.hidden = false;
            scanning = true;
            setStatus('Starting camera. Allow camera access when prompted.');
            controls = await reader.decodeFromVideoDevice(undefined, video, function (result) {
                if (!result || !scanning) return;
                const scannedValue = result.getText();
                stopCamera();
                setStatus('QR code detected. Opening the permit verification result…');
                openVerification(scannedValue);
            });
            if (scanning) {
                setStatus('Camera is on. Hold the permit QR code in front of the camera.');
            } else {
                controls.stop();
            }
        } catch (error) {
            stopCamera();
            setStatus('Could not start the camera. Check browser permission and connection security, or upload a QR image or enter the code below.');
            console.error('Unable to start QR camera scanner:', error);
        }
    });

    imageInput.addEventListener('change', async function () {
        const file = imageInput.files && imageInput.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            setStatus('Choose an image file containing a permit QR code.');
            imageInput.value = '';
            return;
        }

        const imageUrl = URL.createObjectURL(file);
        try {
            setStatus('Reading the QR code from your image…');
            const result = await reader.decodeFromImageUrl(imageUrl);
            if (!result.getText()) {
                setStatus('No QR code was found. Choose a clearer image or enter the code below.');
                return;
            }

            setStatus('QR code detected. Opening the permit verification result…');
            openVerification(result.getText());
        } catch (error) {
            setStatus('Could not find a readable QR code in this image. Try a clearer image or enter the code below.');
        } finally {
            URL.revokeObjectURL(imageUrl);
            reader.reset();
            imageInput.value = '';
        }
    });

    stopButton.addEventListener('click', function () {
        stopCamera();
        setStatus('Camera stopped. Start it again, upload a QR image, or enter the code below.');
    });

    window.addEventListener('pagehide', stopCamera);
}
