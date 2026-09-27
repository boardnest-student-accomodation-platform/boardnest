<!-- Native HTML5 Live Camera Viewfinder Modal (Hardware Permission + Live Stream) -->
<div id="liveCameraModal" class="fa-modal-overlay">
    <div class="fa-modal-container">
        <div class="fa-modal-header">
            <div class="fa-modal-title">📷 Hardware Device Camera</div>
            <button type="button" onclick="closeLiveCamera()" class="fa-btn-close">✕</button>
        </div>

        <div class="fa-camera-viewfinder">
            <video id="liveCameraVideo" autoplay playsinline class="fa-camera-video"></video>
            <canvas id="cameraCanvas" style="display: none;"></canvas>
            
            <div id="cameraPermissionNotice" class="fa-camera-notice">
                ⌛ Requesting hardware camera permission...
            </div>
        </div>

        <div class="fa-btn-group">
            <button type="button" onclick="snapLivePhoto()" class="fa-btn-primary">
                🔘 Snap & Attach Photo
            </button>
            <button type="button" onclick="closeLiveCamera()" class="fa-btn-secondary">
                Cancel
            </button>
        </div>
    </div>
</div>
