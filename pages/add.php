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
                    <div class="drag-item" id="sticker1"">
                        <button class="drag-item-remove-btn hidden">x</button>
                        <img class="drag-item-img" src="../img/overlay1.png" >
                    </div>
                    <div class="drag-item" id="sticker2"">
                        <button class="drag-item-remove-btn hidden">x</button>
                        <img class="drag-item-img" src="../img/overlay1.png" >
                    </div>
                    <canvas style="position: absolute" class="hidden" id="canvas" width="640" height="480"></canvas>
                    <video style="z-index: 1; position: absolute" id="video" width="640" height="480" autoplay></video>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-center-container">
        <div class="webcam-addons-container">
            <button class="arrow"></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="sticker"><img src="../img/overlay1.png" height="80" width="80"/></button>
            <button class="arrow"></button>
        </div>
    </div>
    <div class="flex-center-container">
        <button id="capture-btn" class="webcam-btn-capture">Capture</button>
        <button id="post-btn" class="hidden webcam-btn-post">Post</button>
        <button id="reset" class="webcam-btn-reset">RL</button>
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

        constructor(id, domElement, initialX, initialY) {
            this.id = id;
            this.domElement = domElement;
            this.initialX = initialX;
            this.initialY = initialY;
        }
    }

    let item = new DragItem('sticker1', document.getElementById('sticker1'));
    let item2 = new DragItem('sticker2', document.getElementById('sticker2'));
    let stickers = {};
    stickers[item.id] = item;
    stickers[item2.id] = item2;
    let sticker = new DragItem();
    let container = document.getElementById('container');
    let active = false;

    container.addEventListener("touchstart", dragStart, false);
    container.addEventListener("touchend", dragEnd, false);
    container.addEventListener("touchmove", drag, false);
    container.addEventListener("mousedown", dragStart, false);
    container.addEventListener("mouseup", dragEnd, false);
    container.addEventListener("mouseleave", dragEnd, false);
    container.addEventListener("mousemove", drag, false);

    function markActiveSticker() {
        sticker.domElement.getElementsByTagName('button').item(0).classList.remove('hidden');
        sticker.domElement.style.border = "2px solid rgba(152, 255, 140, 0.7)"
    }

    function unmarkCurrentSticker() {
        if (sticker.domElement != null) {
            sticker.domElement.getElementsByTagName('button').item(0).classList.add('hidden');
            sticker.domElement.style.border = "none";
        }
    }

    function removeSticker() {
        sticker.domElement.remove();
        sticker.domElement = null;
    }

    function dragStart(e) {
        if (e.target.tagName === 'BUTTON' &&
            e.target.classList.contains('drag-item-remove-btn')) {
            removeSticker();
            return;
        }
        unmarkCurrentSticker();
        if (e.target.id.includes('sticker')) {
            sticker = stickers[e.target.id];
            markActiveSticker();
            if (e.type === "touchstart") {
                sticker.initialX = e.touches[0].clientX - sticker.xOffset;
                sticker.initialY = e.touches[0].clientY - sticker.yOffset;
            } else {
                sticker.initialX = e.clientX - sticker.xOffset;
                sticker.initialY = e.clientY - sticker.yOffset;
            }

            if (e.target === sticker.domElement) {
                active = true;
            }
        }
    }

    function dragEnd(e) {
        sticker.initialX = sticker.currentX;
        sticker.initialY = sticker.currentY;
        active = false;
    }

    function drag(e) {
        if (active) {
            e.preventDefault();

            if (e.type === "touchmove") {
                sticker.currentX = e.touches[0].clientX - sticker.initialX;
                sticker.currentY = e.touches[0].clientY - sticker.initialY;
            } else {
                sticker.currentX = e.clientX - sticker.initialX;
                sticker.currentY = e.clientY - sticker.initialY;
            }

            sticker.xOffset = sticker.currentX;
            sticker.yOffset = sticker.currentY;

            setTranslate(sticker.currentX, sticker.currentY, sticker.domElement);
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
        //dragItem.classList.add('hidden');
    });

    postButton.addEventListener("click", function() {
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
      //  dragItem.classList.remove('hidden');
    });

    function mergeWebcamImage() {
        context.drawImage(video, 0, 0, 640, 480);
        for (let key in stickers) {
            if (stickers.hasOwnProperty(key)) {
                let imageObj = new Image();
                imageObj.src = stickers[key].src;
                imageObj.onload = function() {
                    context.drawImage(imageObj, 270 + stickers[key].currentX, 190 + stickers[key].currentY, 100, 100);
                }
            }
        }
    }

    function initWebcam() {
        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;
                })
                .catch(function (err0r) {
                    console.log("Something went wrong!");
                });
        }
    }
</script>
