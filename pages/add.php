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
                <canvas style="position: absolute" class="hidden" id="canvas" width="640" height="480"></canvas>
                <video id="video" width="640" height="480" autoplay></video>
            </div>
        </div>
    </div>
    <div class="flex-center-container">
        <div class="webcam-addons-container">
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
    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let context = canvas.getContext('2d');
    let postButton = document.getElementById('post-btn');
    let captureButton = document.getElementById('capture-btn');
    let resetButton = document.getElementById('reset');
    let dataURL;

    captureButton.addEventListener("click", function() {
        captureButton.classList.add('hidden');
        video.classList.add('hidden');
        postButton.classList.remove('hidden');
        canvas.classList.remove('hidden');
        mergeWebcamImage();
        dataURL = canvas.toDataURL("image/png");
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
    });

    function mergeWebcamImage() {
        let imageObj2 = new Image();
        context.drawImage(video, 0, 0, 640, 480);
        imageObj2.src = '../img/overlay1.png';
        imageObj2.onload = function() {
            context.drawImage(imageObj2, 0, 0, 640, 480);
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