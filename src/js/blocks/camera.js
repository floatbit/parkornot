export default class Camera {
    constructor(el) {
        this.el = el;
        this.initCamera();
    }

    async initCamera() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        const captureButton = document.getElementById('capture-button');
        const submitButton = document.getElementById('submit-button');
        const retakeButton = document.getElementById('retake-button');
        const imageForm = document.getElementById('image-form');
        const imageDataInput = document.getElementById('image-data');
        const switchCameraButton = document.getElementById('switch-camera');
        //const zoomSlider = document.getElementById('zoom-slider');
        let currentStream = null;
        let currentDeviceIndex = 0;
        let devices = [];
        let currentZoom = 1;

        async function getDevices() {
            devices = await navigator.mediaDevices.enumerateDevices();
            devices = devices.filter(device => device.kind === 'videoinput');
        }

        async function startStream(deviceId) {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
            const constraints = {
                video: {
                    deviceId: deviceId ? { exact: deviceId } : undefined,
                    width: { ideal: 480 },
                    height: { ideal: 640 },
                    facingMode: { ideal: 'environment' } // try to use the back camera
                }
            };
            currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = currentStream;
            video.onloadedmetadata = () => {
                const aspectRatio = video.videoWidth / video.videoHeight;
                canvas.width = 480;
                canvas.height = 640;

                // Set zoom slider max based on capabilities
                const videoTrack = currentStream.getVideoTracks()[0];
                if (videoTrack.getCapabilities) {
                    const capabilities = videoTrack.getCapabilities();
                    if (capabilities.zoom) {
                        //zoomSlider.max = capabilities.zoom.max;
                    }
                }
            };
        }

        function switchCamera() {
            currentDeviceIndex = (currentDeviceIndex + 1) % devices.length;
            startStream(devices[currentDeviceIndex].deviceId);
        }

        async function applyZoom(zoomLevel) {
            const videoTrack = currentStream.getVideoTracks()[0];
            if (videoTrack.getCapabilities) {
                const capabilities = videoTrack.getCapabilities();
                if (capabilities.zoom) {
                    const constraints = {
                        advanced: [{ zoom: zoomLevel }]
                    };
                    await videoTrack.applyConstraints(constraints);
                    currentZoom = zoomLevel;
                }
            }
        }

        // zoomSlider.addEventListener('input', (event) => {
        //     const zoomLevel = parseFloat(event.target.value);
        //     applyZoom(zoomLevel);
        // });

        captureButton.addEventListener('click', () => {
            const videoAspectRatio = video.videoWidth / video.videoHeight;
            const containerAspectRatio = canvas.width / canvas.height;

            let sx, sy, sWidth, sHeight;

            if (videoAspectRatio > containerAspectRatio) {
                sHeight = video.videoHeight;
                sWidth = video.videoHeight * containerAspectRatio;
                sx = (video.videoWidth - sWidth) / 2;
                sy = 0;
            } else {
                sWidth = video.videoWidth;
                sHeight = video.videoWidth / containerAspectRatio;
                sx = 0;
                sy = (video.videoHeight - sHeight) / 2;
            }

            context.drawImage(video, sx, sy, sWidth, sHeight, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/png');
            imageDataInput.value = dataUrl;

            const now = new Date();
            document.getElementById('current-date').value = now.toLocaleDateString();
            document.getElementById('current-time').value = now.toTimeString().split(' ')[0];
            document.getElementById('current-day').value = now.toLocaleDateString('en-US', { weekday: 'long' });

            submitButton.style.display = 'inline-block';
            retakeButton.style.display = 'inline-block';
            video.style.display = 'none';
            captureButton.style.display = 'none';
            switchCameraButton.style.display = 'none';
            //zoomSlider.style.display = 'none';
        });

        retakeButton.addEventListener('click', () => {
            context.clearRect(0, 0, canvas.width, canvas.height);
            imageDataInput.value = '';

            submitButton.style.display = 'none';
            retakeButton.style.display = 'none';
            video.style.display = 'block';
            captureButton.style.display = 'inline-block';
            switchCameraButton.style.display = 'inline-block';
            //zoomSlider.style.display = 'inline-block';
        });

        switchCameraButton.addEventListener('click', switchCamera);

        submitButton.addEventListener('click', () => {
            captureButton.style.display = 'none';
            switchCameraButton.style.display = 'none';
            retakeButton.style.display = 'none';
            submitButton.style.display = 'none';
            document.getElementById('loading-container').classList.remove('hidden');
        });

        (async () => {
            await getDevices();
            const backCamera = devices.find(device => device.label.toLowerCase().includes('back')) || devices[0];
            currentDeviceIndex = devices.indexOf(backCamera);
            await startStream(backCamera.deviceId);
            applyZoom(currentZoom); // Initial zoom level
        })();
    }
}