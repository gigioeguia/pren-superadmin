document.addEventListener("DOMContentLoaded", function () {
    const images = document.querySelectorAll(".img-clickable");
    const modalImage = document.getElementById("modalImage");

    images.forEach((img) => {
        img.addEventListener("click", function () {
            modalImage.src = this.src;
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    let imageModal = document.getElementById("imageModal");
    imageModal.addEventListener("show.bs.modal", function (event) {
        let button = event.relatedTarget;
        let imageUrl = button.getAttribute("data-bs-whatever");
        let modalImg = imageModal.querySelector("#modalImage");
        modalImg.src = imageUrl;
    });
});
