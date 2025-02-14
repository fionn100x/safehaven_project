document.addEventListener("DOMContentLoaded", function () {
    const audio = document.getElementById("backgroundMusic");
    const playPauseBtn = document.getElementById("playPauseBtn");
    const icon = playPauseBtn.querySelector("i");

    if (!audio) {
        console.error("Audio element not found!");
        return;
    }

    // Restore saved progress & play state
    let savedTime = localStorage.getItem("audioTime");
    let savedState = localStorage.getItem("audioState");

    if (savedTime) {
        audio.currentTime = parseFloat(savedTime);
    }

    if (savedState === "playing") {
        audio.play().catch(err => console.log("Autoplay blocked:", err));
        icon.classList.replace("fa-play", "fa-pause");
    }

    // Play/Pause button functionality
    playPauseBtn.addEventListener("click", function () {
        if (audio.paused) {
            audio.play();
            localStorage.setItem("audioState", "playing");
            icon.classList.replace("fa-play", "fa-pause");
        } else {
            audio.pause();
            localStorage.setItem("audioState", "paused");
            icon.classList.replace("fa-pause", "fa-play");
        }
    });

    // Save progress before leaving the page
    window.addEventListener("beforeunload", function () {
        localStorage.setItem("audioTime", audio.currentTime);
    });
});