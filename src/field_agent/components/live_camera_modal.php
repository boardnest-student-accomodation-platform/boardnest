<!-- Native HTML5 Live Camera Viewfinder Modal (Hardware Permission + Live Stream) -->
<div id="liveCameraModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div style="background: #FFFFFF; border-radius: 20px; width: 90%; max-width: 480px; padding: 24px; box-sizing: border-box; text-align: center; border: 1.5px solid #E8DDD4; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div style="font-family: 'Outfit', sans-serif; font-weight: 900; font-size: 18px; color: #3B3330;">📷 Hardware Device Camera</div>
            <button type="button" onclick="closeLiveCamera()" style="background: #F8D7DA; border: none; color: #842029; font-weight: 800; border-radius: 50px; width: 32px; height: 32px; cursor: pointer;">✕</button>
        </div>

        <div style="position: relative; border-radius: 12px; overflow: hidden; background: #000000; height: 280px; display: flex; align-items: center; justify-content: center;">
            <video id="liveCameraVideo" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
            <canvas id="cameraCanvas" style="display: none;"></canvas>
            
            <div id="cameraPermissionNotice" style="position: absolute; color: #FFFFFF; font-size: 13px; font-weight: 700; background: rgba(0,0,0,0.7); padding: 12px 20px; border-radius: 8px;">
                ⌛ Requesting hardware camera permission...
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="button" onclick="snapLivePhoto()" style="flex: 1; background: #A4856D; color: #FFFFFF; border: none; padding: 14px; border-radius: 10px; font-weight: 800; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                🔘 Snap & Attach Photo
            </button>
            <button type="button" onclick="closeLiveCamera()" style="background: #FAF7F2; color: #3B3330; border: 1.5px solid #E8DDD4; padding: 14px 20px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer;">
                Cancel
            </button>
        </div>
    </div>
</div>
