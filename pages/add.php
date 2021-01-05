<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camagru</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/add.css">
</head>
<body>
<?php
require_once 'components/header.php';
?>
<div class="add-photo">
    <div class="flex-center-container">
        <div class="webcam-outer-frame">
            <div class="webcam-photo">
                <div id="container" class="drag-container">
                    <div id="stickers-container" class="drag-container">
                    </div>
                    <canvas style="position: absolute" class="hidden" id="canvas" width="640" height="480"></canvas>
                    <video class="webcam-video" id="video" width="640" height="480" autoplay></video>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-center-container">
        <button id="capture-btn" class="webcam-btn-capture">Capture</button>
        <button id="post-btn" class="hidden webcam-btn-post">Post</button>
        <button id="reset" class="webcam-btn-reset" disabled>RL</button>
    </div>
    <div class="flex-center-container">
        <aside>
            <div>
                <label>
                    <strong>Blur</strong><code><span id="blur-val">0</span>px</code>
                </label>
                <input id="blur" type="range" min="0" max="20" step="1" value="0" />
            </div>
            <div>
                <label><strong>Saturation</strong><code><span id="saturate-val">1</span></code></label>
                <input id="saturate" type="range" min="0" max="5" step="0.1" value="1"/>
            </div>
            <div>
                <label><strong>Hue</strong><code><span id="hue-val">0</span>deg</code></label>
                <input id="hue" type="range" min="0" max="360" step="1" value="0" />
            </div>
            <div>
                <label><strong>Brightness</strong><code><span id="brightness-val">1</span></code></label>
                <input id="brightness" type="range" min="0" max="1" step="0.05" value="1" />
            </div>
            <div>
                <label><strong>Contrast</strong><code><span id="contrast-val">0</span></code></label>
                <input id="contrast" type="range" min="0" max="5" step="0.05" value="1" />
            </div>
            <div>
                <label><strong>Invert</strong><code><span id="invert-val">0</span></code></label>
                <input id="invert" type="range" min="0" max="1" step="0.05" value="0" />
            </div>
            <div>
                <label><strong>Sepia</strong><code><span id="sepia-val">0</span></code></label>
                <input id="sepia" type="range" min="0" max="1" step="0.05" value="0" />
            </div>
        </aside>
    </div>
    <div class="flex-center-container">
        <div class="webcam-addons-container">
            <div class="arrow-block">
                <button class="arrow-left"/>
            </div>
            <div class="stickers-block">
                <button onclick="addSticker(this.firstChild.src)" class="sticker"><img src="../img/overlay1.png"/></button>
            </div>
            <div class="arrow-block">
                <button class="arrow-right"></button>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'components/footer.php';
?>
</body>
</html>
<script>
    initWebcam();
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    const postButton = document.getElementById('post-btn');
    const captureButton = document.getElementById('capture-btn');
    const resetButton = document.getElementById('reset');
    const stickersContainer = document.getElementById('stickers-container');
    const webContainerHeight = 480;
    const webContainerWidth = 640;
    const maxStickerSize = 400;
    const minStickerSize = 50;
    let newSticker = false;
    let backCanvas = document.createElement('canvas');
    backCanvas.width = canvas.width;
    backCanvas.height = canvas.height;
    let backCtx = backCanvas.getContext('2d');
    let dataURL;

    // dragging stickers
    class DragItem {
        id;
        domElement = null;
        currentX = 0;
        currentY = 0;
        initialX = 0;
        initialY = 0;
        xOffset = 0;
        yOffset = 0;
        src = '../img/overlay1.png';

        constructor(id, domElement) {
            this.id = id;
            this.domElement = domElement;
        }
    }

    let stickers = {};
    let activeSticker = new DragItem();
    let container = document.getElementById('container');
    let active = false;
    let startStickerSize;
    let resizeActive = false;
    let resizeActiveBorder = null;
    let touchBeginX = 0;
    let touchBeginY = 0;

    container.addEventListener("touchstart", dragStart, false);
    container.addEventListener("touchend", dragEnd, false);
    container.addEventListener("touchmove", drag, false);
    container.addEventListener("mousedown", dragStart, false);
    container.addEventListener("mouseup", dragEnd, false);
    container.addEventListener("mouseleave", dragEnd, false);
    container.addEventListener("mousemove", drag, false);

    function updateStickersDomElement() {
        for (let key in stickers) {
            if (stickers.hasOwnProperty(key)) {
                stickers[key].domElement = document.getElementById(stickers[key].id);
            }
        }
    }

    function addSticker(src) {
        newSticker = true;
        const id = 'sticker' + Object.keys(stickers).length;
        const templateHTML = "<div class=\"drag-item\" id=\"" + id + "\">\n" +
            "                            <div class=\"drag-item-border-north hidden\"></div>\n" +
            "                            <div class=\"drag-item-border-south hidden\"></div>\n" +
            "                            <div class=\"drag-item-border-west hidden\"></div>\n" +
            "                            <div class=\"drag-item-border-east hidden\"></div>\n" +
            "                            <button class=\"drag-item-remove-btn hidden\">x</button>\n" +
            "                            <img class=\"drag-item-img\" src=\"" + src + "\">\n" +
            "                        </div>"
        stickersContainer.innerHTML += templateHTML;
        let item = new DragItem(id, document.getElementById(id));
        stickers[item.id] = item;
        updateStickersDomElement();
    }

    function markActiveSticker() {
        activeSticker.domElement.getElementsByTagName('button').item(0).classList.remove('hidden');
        for (let el of activeSticker.domElement.getElementsByTagName('div')) {
            el.classList.remove('hidden');
        }
    }

    function unmarkCurrentSticker() {
        if (activeSticker.domElement != null) {
            activeSticker.domElement.getElementsByTagName('button').item(0).classList.add('hidden');
            for (let el of activeSticker.domElement.getElementsByTagName('div')) {
                el.classList.add('hidden');
            }
        }
    }

    function removeActiveSticker() {
        activeSticker.domElement.remove();
        activeSticker.domElement = null;
    }

    function hideAllStickers() {
        for (let key in stickers) {
            if (stickers.hasOwnProperty(key)) {
                stickers[key].domElement.classList.add('hidden');
            }
        }
    }

    function showAllStickers() {
        for (let key in stickers) {
            if (stickers.hasOwnProperty(key)) {
                stickers[key].domElement.classList.remove('hidden');
            }
        }
    }

    function dragStart(e) {
        touchBeginX = e.clientX;
        touchBeginY = e.clientY;
        if (e.target.tagName === 'BUTTON' &&
            e.target.classList.contains('drag-item-remove-btn')) {
            removeActiveSticker();
            return;
        }
        if (e.target.classList.contains('drag-item-border-north') ||
            e.target.classList.contains('drag-item-border-south') ||
            e.target.classList.contains('drag-item-border-east') ||
            e.target.classList.contains('drag-item-border-west')) {
            resizeActive = true;
            resizeActiveBorder = e.target.classList;
            return;
        }
        unmarkCurrentSticker();
        if (e.target.id.includes('sticker')) {
            activeSticker = stickers[e.target.id];
            startStickerSize = parseInt(getComputedStyle(activeSticker.domElement).height);
            markActiveSticker();
            if (e.type === "touchstart") {
                activeSticker.initialX = e.touches[0].clientX - activeSticker.xOffset;
                activeSticker.initialY = e.touches[0].clientY - activeSticker.yOffset;
            } else {
                activeSticker.initialX = e.clientX - activeSticker.xOffset;
                activeSticker.initialY = e.clientY - activeSticker.yOffset;
            }
            if (e.target === activeSticker.domElement) {
                active = true;
            }
        }
    }

    function dragEnd(e) {
        activeSticker.initialX = activeSticker.currentX;
        activeSticker.initialY = activeSticker.currentY;
        active = false;
        resizeActive = false;
        if (resizeActiveBorder != null) {
            let diffX = (touchBeginX - e.clientX) * 2;
            let diffY = (touchBeginY - e.clientY) * 2;
            switch (resizeActiveBorder[0]) {
                case('drag-item-border-north'):
                    startStickerSize = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize);
                    break;
                case('drag-item-border-south'):
                    startStickerSize = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize);
                    break;
                case('drag-item-border-west'):
                    startStickerSize = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize);
                    break;
                case('drag-item-border-east'):
                    startStickerSize = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize);
                    break;
            }
            resizeActiveBorder = null;
        }
    }

    function drag(e) {
        if (resizeActive) {
            let diffX = (touchBeginX - e.clientX) * 2;
            let diffY = (touchBeginY - e.clientY) * 2;
            switch(resizeActiveBorder[0]) {
                case('drag-item-border-north'):
                    activeSticker.domElement.style.height = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.style.width = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[0].style.width = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[1].style.width = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[2].style.height = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[3].style.height = Math.max(Math.min(startStickerSize + diffY, maxStickerSize), minStickerSize) + 'px';
                    break;
                case('drag-item-border-south'):
                    activeSticker.domElement.style.height = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.style.width = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[0].style.width = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[1].style.width = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[2].style.height = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[3].style.height = Math.max(Math.min(startStickerSize - diffY, maxStickerSize), minStickerSize) + 'px';
                    break;
                case('drag-item-border-west'):
                    activeSticker.domElement.style.height = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.style.width = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[0].style.width = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[1].style.width = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[2].style.height = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[3].style.height = Math.max(Math.min(startStickerSize + diffX, maxStickerSize), minStickerSize) + 'px';
                    break;
                case('drag-item-border-east'):
                    activeSticker.domElement.style.height = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.style.width = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[0].style.width = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[1].style.width = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[2].style.height = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    activeSticker.domElement.children[3].style.height = Math.max(Math.min(startStickerSize - diffX, maxStickerSize), minStickerSize) + 'px';
                    break;
            }
        }
        else if (active) {
            e.preventDefault();
            if (e.type === "touchmove") {
                activeSticker.currentX = e.touches[0].clientX - activeSticker.initialX;
                activeSticker.currentY = e.touches[0].clientY - activeSticker.initialY;
            } else {
                activeSticker.currentX = e.clientX - activeSticker.initialX;
                activeSticker.currentY = e.clientY - activeSticker.initialY;
            }
            activeSticker.xOffset = activeSticker.currentX;
            activeSticker.yOffset = activeSticker.currentY;

            setTranslate(activeSticker.currentX, activeSticker.currentY, activeSticker.domElement);
        }
    }

    function setTranslate(xPos, yPos, el) {
        el.style.transform = "translate3d(" + xPos + "px, " + yPos + "px, 0)";
    }

    captureButton.addEventListener("click", function() {
        captureButton.classList.add('hidden');
        video.classList.add('hidden');
        postButton.classList.remove('hidden');
        canvas.classList.remove('hidden');
        mergeWebcamImage();
        dataURL = canvas.toDataURL("image/png");
        resetButton.disabled = false;
    });

    postButton.addEventListener("click", function() {
        if (newSticker) {
            drawStickers();
        }
        if (dataURL == null) {
            console.log('Please, capture an image')
            return;
        }
        fetch('/user/api/v1/image/post', {
            credentials: 'include',
            body: {
                imgBase64: dataURL
            },
            method:"POST"
        }).then(response => console.log(response));

    })

    resetButton.addEventListener("click", function() {
        postButton.classList.add('hidden');
        canvas.classList.add('hidden');
        video.classList.remove('hidden');
        captureButton.classList.remove('hidden');
        resetButton.disabled = true;
        newSticker = true;
        showAllStickers();
    });

    function mergeWebcamImage() {
        unmarkCurrentSticker();
        hideAllStickers();
        drawCanvas(backCanvas);
        drawCanvas(video);
        context.filter = "blur(" + inputBlur.value + "px) " +
            "saturate(" + inputSaturate.value + ") " +
            "hue-rotate(" + inputHue.value + "deg) " +
            "brightness(" + inputBrightness.value + ") " +
            "contrast(" + inputContrast.value + ") " +
            "invert(" + inputInvert.value + ") " +
            "sepia(" + inputSepia.value + ")";
        backCtx.drawImage(video, 0, 0, webContainerWidth, webContainerHeight);

    }

    function initWebcam() {
        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    console.log("Something went wrong!");
                });
        }
    }

    const inputBlur = document.getElementById('blur');
    const inputSaturate = document.getElementById('saturate');
    const inputHue = document.getElementById('hue');
    const inputBrightness = document.getElementById('brightness');
    const inputContrast = document.getElementById('contrast');
    const inputInvert = document.getElementById('invert');
    const inputSepia = document.getElementById('sepia');
    let inputBlurVal = inputBlur.value;
    let inputSaturateVal = inputSaturate.value;
    let inputHueVal = inputHue.value;
    let inputBrightnessVal = inputBrightness.value;
    let inputContrastVal = inputBrightness.value;
    let inputInvertVal = inputInvert.value;
    let inputSepiaVal = inputSepia.value;

    inputBlur.oninput = function() {
        document.getElementById('blur-val').innerHTML = inputBlur.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputSaturate.oninput = function() {
        document.getElementById('saturate-val').innerHTML = inputSaturate.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputHue.oninput = function() {
        document.getElementById('hue-val').innerHTML = inputHue.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputBrightness.oninput = function() {
        document.getElementById('brightness-val').innerHTML = inputBrightness.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputContrast.oninput = function() {
        document.getElementById('contrast-val').innerHTML = inputContrast.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputInvert.oninput = function() {
        document.getElementById('invert-val').innerHTML = inputInvert.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }
    inputSepia.oninput = function() {
        document.getElementById('sepia-val').innerHTML = inputSepia.value;
        setWebKitValues();
        video.setAttribute("style", "-webkit-filter: " + getWebKitValues());
        drawCanvas(backCanvas);
    }

    function setWebKitValues() {
        inputBlurVal = inputBlur.value
        inputSaturateVal = inputSaturate.value;
        inputHueVal = inputHue.value;
        inputBrightnessVal = inputBrightness.value;
        inputContrastVal = inputContrast.value;
        inputInvertVal = inputInvert.value;
        inputSepiaVal = inputSepia.value;
    }

    function getWebKitValues() {
        return "blur(" + inputBlurVal + "px) " +
            "saturate(" + inputSaturateVal + ") " +
            "hue-rotate(" + inputHueVal + "deg) " +
            "brightness(" + inputBrightnessVal + ") " +
            "contrast(" + inputContrastVal + ") " +
            "invert(" + inputInvertVal + ") " +
            "sepia(" + inputSepiaVal + ")";
    }

    function drawCanvas(src) {
        context.filter = getWebKitValues();
        context.drawImage(src, 0, 0, webContainerWidth, webContainerHeight);
        if (newSticker) {
            drawStickers()
        }
    }

    function drawStickers() {
        context.filter = "none";
        for (let key in stickers) {
            if (stickers.hasOwnProperty(key)) {
                let imageObj = new Image();
                imageObj.src = stickers[key].src;
                imageObj.onload = function () {
                    context.drawImage(imageObj,
                        (webContainerWidth / 2) - (parseInt(getComputedStyle(stickers[key].domElement).width) / 2) + stickers[key].currentX,
                        (webContainerHeight / 2) - (parseInt(getComputedStyle(stickers[key].domElement).height) / 2) + stickers[key].currentY,
                        parseInt(getComputedStyle(stickers[key].domElement).width),
                        parseInt(getComputedStyle(stickers[key].domElement).height));
                }
            }
        }
        newSticker = false;
    }
</script>
