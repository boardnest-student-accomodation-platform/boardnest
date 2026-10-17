<!-- Native HTML5 Live Camera Viewfinder Modal (Hardware Permission + Live Stream) -->
<div id="liveCameraModal" class="fa-modal-overlay">
    <div class="fa-modal-container">
        <div class="fa-modal-header">
            <div class="fa-modal-title" style="display:flex;align-items:center;gap:8px;">
                <?php echo function_exists('fa_icon') ? fa_icon('camera', '', 18) : ''; ?>
                <span>Hardware Device Camera</span>
            </div>
            <button type="button" onclick="closeLiveCamera()" class="fa-btn-close">&times;</button>
        </div>

        <div class="fa-camera-viewfinder">
            <video id="liveCameraVideo" autoplay playsinline class="fa-camera-video"></video>
            <canvas id="cameraCanvas" style="display: none;"></canvas>
            
            <div id="cameraPermissionNotice" class="fa-camera-notice" style="display:flex;align-items:center;gap:8px;">
                <?php echo function_exists('fa_icon') ? fa_icon('clock', '', 14, '#FFF') : ''; ?>
                <span>Requesting hardware camera permission...</span>
            </div>
        </div>

        <div class="fa-btn-group">
            <button type="button" onclick="snapLivePhoto()" class="fa-btn-primary">
                <?php echo function_exists('fa_icon') ? fa_icon('camera', '', 16, '#FFF') : ''; ?>
                <span>Snap &amp; Attach Photo</span>
            </button>
            <button type="button" onclick="closeLiveCamera()" class="fa-btn-secondary">
                Cancel
            </button>
        </div>
    </div>
</div>
