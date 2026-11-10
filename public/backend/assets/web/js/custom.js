/* ----------------------------------------------- */
/* Slick Slider */
/* ----------------------------------------------- */

$(document).ready(function () {
    $(".acnoo-dashboard-container .sale-slider .row").slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        arrows: false,
        rtl: false,
        autoplay: true,
        autoplaySpeed: 1500,
        infinite: true,
        pauseOnFocus: false,
        responsive: [
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 576,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                },
            },
            {
                breakpoint: 375,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                },
            },
        ],
    });
});

// tags button related js start
const buttons = document.querySelectorAll(".tag-button");

buttons.forEach((button) => {
    button.addEventListener("click", function () {
        // Create the minus icon element dynamically (improves accessibility)
        const minusIcon = document.createElement("i");
        minusIcon.classList.add("fas", "fa-times", "text-danger"); // Use Font Awesome for minus icon (red color)

        // Check if the minus icon already exists (prevents duplicates)
        const existingIcon = this.querySelector("i.fas.fa-times");

        if (existingIcon) {
            existingIcon.remove(); // Remove existing icon on subsequent clicks
        } else {
            // Add the minus icon after the button text
            this.append(minusIcon);
        }
    });
});

// tags button related js end'

// item-header icon realted js start
$(document).ready(function () {
    $(".item-icon").on("click", function () {
        $(".item-icon")
            .removeClass("blogs-tag-btn-selected")
            .addClass("blogs-tag-btn-unselected");
        $(this)
            .removeClass("blogs-tag-btn-unselected")
            .addClass("blogs-tag-btn-selected");
    });
});

// radio-input related js start
$(document).ready(function () {
    $(".size").on("click", function () {
        $(".size")
            .removeClass("active-radio-selected")
            .addClass("active-radio-unselected");
        $(this)
            .removeClass("active-radio-unselected")
            .addClass("active-radio-selected");
    });
});

// radio-input related js end

// checkbox related js start
// Get all checkboxes
const checkboxes = document.querySelectorAll('input[type="checkbox"]');
// Add event listener to each checkbox
checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
        const parentDiv = this.parentNode.parentNode; // Get the parent div of checkbox
        if (this.checked) {
            parentDiv.classList.add("active-check-selected"); // Add blue background class
        } else {
            parentDiv.classList.remove("active-check-selected"); // Remove blue background class
        }
    });
});

// Get Item Data
$(".landing-category-id").on("click", function (event) {
    event.preventDefault();

    $(".landing-category-id").removeClass("active");
    $(this).addClass("active");

    var route = $(this).data("route");

    $.ajax({
        url: route,
        method: "GET",
        success: function (response) {
            $("#item-container-view").html(response);
        },
    });
});

// view style
$(".items-design").on("click", function (event) {
    event.preventDefault();
    var design = $(this).data("design");
    if (design == "list") {
        $("#list-grid")
            .addClass("products-section")
            .removeClass("products-section-landing");
    } else if (design == "grid") {
        $("#list-grid")
            .removeClass("products-section")
            .addClass("products-section-landing");
    }
});

// attribute item
$(".attribute-btn").on("click", function () {
    $(this).toggleClass("selected");
    var route = $(this).data("route");

    var selected_attributes = [];
    $(".attribute-btn.selected").each(function () {
        selected_attributes.push($(this).data("attribute_id"));
    });

    $.ajax({
        url: route,
        method: "POST",
        data: {
            attribute_ids: selected_attributes,
        },
        success: function (response) {
            $("#item-container-view").html(response);
        },
    });
});

// Soft Manage Highlight Section Start
$(document).ready(function () {

    function resetAll() {
        $(".subtitle-one-icon, .subtitle-two-icon, .subtitle-three-icon")
            .removeClass("highlight-content")
            .addClass("content");

        $(".subtitle-one-img, .subtitle-two-img, .subtitle-three-img")
            .hide();
    }

    // By default subtitle-one active
    resetAll();
    $(".subtitle-one-icon").removeClass("content").addClass("highlight-content");
    $(".subtitle-one-img").show();

    // Subtitle One
    $(".subtitle-one-icon").on("click", function () {
        resetAll();
        $(this).removeClass("content").addClass("highlight-content");
        $(".subtitle-one-img").show();
    });

    // Subtitle Two
    $(".subtitle-two-icon").on("click", function () {
        resetAll();
        $(this).removeClass("content").addClass("highlight-content");
        $(".subtitle-two-img").show();
    });

    // Subtitle Three
    $(".subtitle-three-icon").on("click", function () {
        resetAll();
        $(this).removeClass("content").addClass("highlight-content");
        $(".subtitle-three-img").show();
    });

});

// Soft Manage Highlight Section End

// radio button active > text color and border color change
function updateActiveClass() {
    $(".method-label").removeClass("active-payment");
    $(".payment-text").removeClass("active-payment");

    var selectedMethod = $(".payment-method:checked").closest(".method-label");
    selectedMethod.addClass("active-payment");
    selectedMethod.find(".payment-text").addClass("active-payment");
}

$(".payment-method").on("change", function () {
    updateActiveClass();
});

/** Tag Start*/
$(document).on("click", ".tags-btn", function () {
    // Highlight selected tag
    $(".tags-btn")
        .removeClass("blogs-tag-btn-selected")
        .addClass("blogs-tag-btn-unselected");
    $(this)
        .removeClass("blogs-tag-btn-unselected")
        .addClass("blogs-tag-btn-selected");

    // Fetch tag and route
    let selectedTag = $(this).data("tag");
    let routeUrl = $(this).data("route");

    // Fetch blogs dynamically
    $.ajax({
        url: routeUrl,
        type: "GET",
        data: { tag: selectedTag },
        success: function (response) {
            // Update blog list
            $("#blogs-container").empty();
            response.blogs.data.forEach((blog) => {
                const createdAt = new Date(blog.created_at).toLocaleString(
                    "en-US",
                    {
                        month: "long", //month name (January)
                        day: "2-digit", // Day with leading zero
                        year: "numeric", // Full year
                    }
                );

                // Limit description by characters
                let description =
                    blog.descriptions.length > 80
                        ? blog.descriptions.substring(0, 80) + "..."
                        : blog.descriptions;

                $("#blogs-container").append(`
        <div class="col-lg-6 pb-4 blog-item">
            <div class="blog-shadow rounded">
                <div class="text-center blog-image p-3">
                    <img src="${blog.image}" class="w-100 h-100 object-fit-cover rounded-1" alt="${blog.title}" />
                </div>
                <div class="p-3 pt-0">
                    <div class="d-flex align-items-center mb-2">
                        <img src="/frontend/assets/images/icons/clock.svg" alt="" />
                        <p class="ms-1 mb-0">${createdAt}</p>
                    </div>
                    <h6>${blog.title}</h6>
                    <p>${description}</p>
                    <a href="/blogs/${blog.slug}" class="custom-clr-primary">Read More <span class="font-monospace">></span></a>
                </div>
            </div>
        </div>
    `);
            });
        },
        error: function (error) {
            console.error(error);
        },
    });
});
/** Tag End*/

const accordionItems = document.querySelectorAll(".custom-accordion-item");

accordionItems.forEach((item) => {
    const header = item.querySelector(".custom-accordion-header");
    const content = item.querySelector(".custom-accordion-content");

    if (item.classList.contains("active")) {
        content.style.maxHeight = content.scrollHeight + "px";
    }

    header.addEventListener("click", () => {
        const isActive = item.classList.contains("active");

        accordionItems.forEach((i) => {
            i.classList.remove("active");
            const c = i.querySelector(".custom-accordion-content");
            c.style.maxHeight = null;
        });

        if (!isActive) {
            item.classList.add("active");
            content.style.maxHeight = content.scrollHeight + "px";
        }
    });
});

window.addEventListener("scroll", function () {
    var header = document.querySelector(".header-section.home-header");
    var scrollPosition = window.scrollY;

    if (scrollPosition > 5) {
        header.classList.add("bg-0d0d16");
    } else {
        header.classList.remove("bg-0d0d16");
    }
});

document.addEventListener("DOMContentLoaded", function () {
    new Swiper(".mySwiper", {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            0: {
                slidesPerView: 1,
            },
            480: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 4,
            },
        },
    });
});

document.addEventListener("DOMContentLoaded", function () {
    new Swiper(".testimonialSwiper", {
        slidesPerView: 3,
        centeredSlides: true,
        spaceBetween: 2,
        grabCursor: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        breakpoints: {
            320: {
                slidesPerView: 1,
                spaceBetween: 2,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 2,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 2,
            },
        },
    });
});

// client slider ----------->
const prevBtn = document.querySelector(".client-testimonial-prev");
const nextBtn = document.querySelector(".client-testimonial-next");

const swiper = new Swiper(".client-testimonial-swiper", {
    slidesPerView: 2,
    spaceBetween: 30,
    loop: true,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: ".client-testimonial-next",
        prevEl: ".client-testimonial-prev",
    },
    breakpoints: {
        0: { slidesPerView: 1 },
        1024: { slidesPerView: 2 },
    },
    on: {
        slideChange: function () {
            nextBtn.classList.add("client-testimonial-active");
            prevBtn.classList.remove("client-testimonial-active");
        },
    },
});

nextBtn.addEventListener("click", () => {
    nextBtn.classList.add("client-testimonial-active");
    prevBtn.classList.remove("client-testimonial-active");
});

prevBtn.addEventListener("click", () => {
    prevBtn.classList.add("client-testimonial-active");
    nextBtn.classList.remove("client-testimonial-active");
});
