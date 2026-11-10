(function ($) {
    "use strict";

    function sideManu() {
        $(".woodland-sidebar-open, .sidebar-close-btn").on(
            "click ",
            function () {
                $(".woodland-sidebar, .woodland-main-section").toggleClass(
                    "sidebar-active"
                );
            }
        );

        $("li>ul").toggleClass("dropdown-menu");

        let animationSpeed = 300;

        let subMenuSelector = ".dropdown-menu";

        $(".woodland-sidebar-menu > ul").on(
            "click",
            ".dropdown a",
            function (e) {
                let $this = $(this);
                let checkElement = $this.next();

                if (
                    checkElement.is(subMenuSelector) &&
                    checkElement.is(":visible")
                ) {
                    checkElement.slideUp(animationSpeed, function () {
                        checkElement.removeClass("menu-open");
                    });
                    checkElement.parent("li").removeClass("active");
                }

                //If the menu is not visible
                else if (
                    checkElement.is(subMenuSelector) &&
                    !checkElement.is(":visible")
                ) {
                    //Get the parent menu
                    let parent = $this.parents("ul").first();
                    //Close all open menus within the parent
                    let ul = parent.find("ul:visible").slideUp(animationSpeed);
                    //Remove the menu-open class from the parent
                    ul.removeClass("menu-open");
                    //Get the parent li
                    let parent_li = $this.parent("li");

                    //Open the target menu and add the menu-open class
                    checkElement.slideDown(animationSpeed, function () {
                        //Add the class active to the parent li
                        checkElement.addClass("menu-open");
                        parent.find("li.active").removeClass("active");
                        parent_li.addClass("active");
                    });
                }
                //if this isn't a link, prevent the page from being redirected
                if (checkElement.is(subMenuSelector)) {
                    e.preventDefault();
                }
            }
        );
    }

    sideManu();

    $(".select-dropdown").niceSelect();

    // counterup
    $(".counter").counterUp({
        delay: 10,
        time: 3000,
    });

    //Use the id of the form instead of #change
    $(".select-dropdown").change(function () {
        var title = $(this).val();
        if (title == "reject") {
            $("#reject-modal").modal("show");
        } else if (title == "approved") {
            $("#approved-modal").modal("show");
        }
    });

    $(document).ready(function () {
        $("#summernote").summernote();
    });

    $(".input-images").imageUploader({
        imagesInputName: "image",
        preloadedInputName: "old_images",
        maxSize: 1024 * 10240,
        maxFiles: 4,
    });

    // photo upload preview
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(".image-preview").attr("src", e.target.result);
                $(".image-preview").hide();
                $(".image-preview").fadeIn(650);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#file-input-upload").change(function () {
        readURL(this);
        $(".image-preview-icon").addClass("d-none");
    });

    // Click on .add-photo-items will trigger the hidden file input
    $(document).on("click", ".add-photo-items", function (e) {
        // Don't trigger file input if already uploaded
        const previewImage = $(this).find(".image-preview");

        // If click on input or label, allow normal behavior
        if ($(e.target).is("input") || $(e.target).is("label")) return;

        // If image has 'uploaded' class, don't open file dialog again
        if (previewImage.hasClass("uploaded")) return;

        $(this).find(".file-input-upload").trigger("click");
    });

    // When a file is selected, show preview and remove button
    $(document).on("change", ".file-input-upload", function () {
        const fileInput = $(this);
        const container = fileInput.closest(".add-photo-items");
        const previewImage = container.find(".image-preview");
        const removeBtn = container.find(".remove-img-btn");
        const defaultImage = container.find(".default-img"); // নতুন লাইন

        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Hide default image
                defaultImage.addClass("d-none");

                // Show uploaded preview image
                previewImage.attr("src", e.target.result).hide().fadeIn(650);

                // Show remove button
                removeBtn.removeClass("d-none");
            };
            reader.readAsDataURL(this.files[0]);
        }
    });


    // Remove image and hide button
    $(document).on("click", ".remove-img-btn", function (e) {
        e.stopPropagation(); // Prevent event bubbling

        const container = $(this).closest(".add-photo-items");
        const previewImage = container.find(".image-preview");
        const defaultImage = container.find(".default-img");

        // Hide uploaded preview image
        previewImage.attr("src", "").hide();

        // Clear the file input
        container.find(".file-input-upload").val("");

        // Hide remove button
        $(this).addClass("d-none");

        // Show the default image
        defaultImage.removeClass("d-none").fadeIn(300);
    });

    //  image upload tow js
    jQuery(document).ready(function () {
        ImgUpload();
    });

    function ImgUpload() {
        var imgWrap = "";
        var imgArray = [];

        $(".upload__inputfile").each(function () {
            $(this).on("change", function (e) {
                imgWrap = $(this)
                    .closest(".upload__box")
                    .find(".upload__img-wrap");
                var maxLength = $(this).attr("data-max_length");
                var files = e.target.files;
                var filesArr = Array.prototype.slice.call(files);
                var iterator = 0;
                filesArr.forEach(function (f, index) {
                    if (!f.type.match("image.*")) {
                        return;
                    }

                    if (imgArray.length > maxLength) {
                        return false;
                    } else {
                        var len = 0;
                        for (var i = 0; i < imgArray.length; i++) {
                            if (imgArray[i] !== undefined) {
                                len++;
                            }
                        }
                        if (len > maxLength) {
                            return false;
                        } else {
                            imgArray.push(f);

                            var reader = new FileReader();
                            reader.onload = function (e) {
                                var html =
                                    "<div class='upload__img-box'><div style='background-image: url(" +
                                    e.target.result +
                                    ")' data-number='" +
                                    $(".upload__img-close").length +
                                    "' data-file='" +
                                    f.name +
                                    "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                                imgWrap.append(html);
                                imgWrap.addClass("img-active");
                                iterator++;
                            };
                            reader.readAsDataURL(f);
                        }
                    }
                });
            });
        });

        $("body").on("click", ".upload__img-close", function (e) {
            var file = $(this).parent().data("file");
            for (var i = 0; i < imgArray.length; i++) {
                if (imgArray[i].name === file) {
                    imgArray.splice(i, 1);
                    break;
                }
            }
            $(this).parent().parent().remove();
        });
    }
})(jQuery);

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".print-window").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            window.print();
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll(".image-input");

    inputs.forEach(function (input) {
        input.addEventListener("change", function () {
            const previewId = this.dataset.previewId;
            const preview = document.getElementById(previewId);

            if (this.files && this.files[0] && preview) {
                preview.src = URL.createObjectURL(this.files[0]);
            }
        });
    });
});

function exportReportToExcel() {
    let table = document.getElementsByTagName("table"); // you can use document.getElementById('tableId') as well by providing id to the table tag
    TableToExcel.convert(table[0], {
        name: `export.xlsx`,
        sheet: {
            name: "Sheet 1",
        },
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const items = document.querySelectorAll(".pro-accordion-item");

    const plusIcon = `
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g clip-path="url(#clip0_40000020_41447)">
        <path d="M9.99984 18.3333C14.6022 18.3333 18.3332 14.6023 18.3332 9.99996C18.3332 5.39759 14.6022 1.66663 9.99984 1.66663C5.39746 1.66663 1.6665 5.39759 1.6665 9.99996C1.6665 14.6023 5.39746 18.3333 9.99984 18.3333Z" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6.6665 10H13.3332" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M10.0049 6.67163L10.0049 13.3383" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </g>
      <defs>
        <clipPath id="clip0_40000020_41447">
          <rect width="20" height="20" fill="white"/>
        </clipPath>
      </defs>
    </svg>
  `;

    const minusIcon = `
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g clip-path="url(#clip0_40000020_41263)">
        <path d="M9.99984 18.3333C14.6022 18.3333 18.3332 14.6023 18.3332 9.99996C18.3332 5.39759 14.6022 1.66663 9.99984 1.66663C5.39746 1.66663 1.6665 5.39759 1.6665 9.99996C1.6665 14.6023 5.39746 18.3333 9.99984 18.3333Z" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6.6665 10H13.3332" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </g>
      <defs>
        <clipPath id="clip0_40000020_41263">
          <rect width="20" height="20" fill="white"/>
        </clipPath>
      </defs>
    </svg>
  `;

    items.forEach((item) => {
        const header = item.querySelector(".pro-accordion-header");
        const icon = item.querySelector(".pro-accordion-icon");
        const isActive = item.classList.contains("pro-active");

        header.setAttribute("aria-expanded", isActive ? "true" : "false");
        if (icon) icon.innerHTML = isActive ? minusIcon : plusIcon;
    });

    items.forEach((item) => {
        const header = item.querySelector(".pro-accordion-header");
        const icon = item.querySelector(".pro-accordion-icon");

        const toggleItem = () => {
            const isCurrentlyActive = item.classList.contains("pro-active");

            if (isCurrentlyActive) {
                item.classList.remove("pro-active");
                if (header) header.setAttribute("aria-expanded", "false");
                if (icon) icon.innerHTML = plusIcon;
            } else {
                items.forEach((i) => {
                    const h = i.querySelector(".pro-accordion-header");
                    const ic = i.querySelector(".pro-accordion-icon");
                    i.classList.remove("pro-active");
                    if (h) h.setAttribute("aria-expanded", "false");
                    if (ic) ic.innerHTML = plusIcon;
                });

                item.classList.add("pro-active");
                if (header) header.setAttribute("aria-expanded", "true");
                if (icon) icon.innerHTML = minusIcon;
            }
        };

        header.addEventListener("click", toggleItem);
        header.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                toggleItem();
            }
        });
    });
});

