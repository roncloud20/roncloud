// Toggling Navigation Bar
var navBtn = document.getElementById("navBtn");
var navPanel = document.getElementsByClassName("navPanel")[0];

navBtn.addEventListener("click",function() {
    navPanel.classList.toggle("active");
});