"use strict";

$(document).ready(function () {
    let tabs = $(".wizard-tab-form");
    const steps = $(".step");
    let currentTab = 0;

    function refreshTabs() {
        const selectedCategory = $("#categoryDropdown").val();

        $(".wizard-tab-form").hide();

        tabs = $(".wizard-tab-form").filter(function () {
            const step = $(this).data("step");

            if ($(this).hasClass("unit-wizard")) {
                return selectedCategory == "6";
            }

            // Hide normal step 3 if category is 6
            if (step == 3 && selectedCategory == "6") {
                return false;
            }

            return true;
        });
    }

    function displayTab(index) {
        refreshTabs();

        tabs.hide();
        tabs.eq(index).show();

        $("#prevBtn").toggle(index > 0);
        $("#nextBtn").text(index === tabs.length - 1 ? window.trans.submit_btn_text : window.trans.continue_btn_text);

        updateSteps(index);
        $(document).trigger('tabDisplayed', [index]);
    }

    function validateForm() {
        let isValid = true;
        let hasShownError = false;

        const fields = tabs
            .eq(currentTab)
            .find(
                "input:enabled:visible[required], select:enabled:visible[required], textarea:enabled:visible[required]"
            );

        fields.each(function () {
            const field = $(this);
            if (!field.val()) {
                field.addClass("error");
                isValid = false;

                if (!hasShownError) {
                    Notify(
                        "error",
                        null,
                        "All required fields must be filled out."
                    );
                    hasShownError = true;
                }
            } else {
                field.removeClass("error");
            }
        });

        if (isValid) {
            steps.eq(currentTab).addClass("finish");
        }

        return isValid;
    }

    function changeTab(step) {
        if (step === 1 && !validateForm()) return;

        if (currentTab === tabs.length - 1 && step === 1) {
            $("#regForm").submit();
            return;
        }

        currentTab += step;

        if (currentTab < 0) currentTab = 0;
        if (currentTab >= tabs.length) currentTab = tabs.length - 1;

        displayTab(currentTab);
    }

    function updateSteps(index) {
        steps.removeClass("active finish");

        steps.each(function (i) {
            if (i < index) {
                $(this).addClass("finish");
            }
        });

        steps.eq(index).addClass("active");

        steps.each(function (i) {
            $(this)
                .find(".numbering")
                .text(i + 1);
        });
    }

    $("#prevBtn").on("click", () => changeTab(-1));
    $("#nextBtn").on("click", () => changeTab(1));

    $("#categoryDropdown").on("change", function () {
        // reset current tab if category changes mid-wizard
        currentTab = 0;
        displayTab(currentTab);
    });

    displayTab(currentTab);
});

$(document).on('change', 'input[name="type"]', toggleSaleRent);
$(document).on('change', '#categoryDropdown', toggleSaleRent);
$(document).on('tabDisplayed', toggleSaleRent);
toggleSaleRent();

function toggleSaleRent() {
    const type = $('input[name="type"]:checked').val() || 'rent';
    const selectedCategory = $("#categoryDropdown").val();
    const isSale = type === 'sale';

    // Hide all first
    $('.sale-fields, .rent-fields, .unit-sale, .unit-rent').addClass('d-none').hide();
    $('.sale-fields :input, .rent-fields :input, .unit-sale :input, .unit-rent :input').prop('disabled', true);

    if (selectedCategory == 6) {
        // Category 6 → show only unit-based forms
        if (isSale) {
            $('.unit-sale').removeClass('d-none').show();
            $('.unit-sale :input').prop('disabled', false);
        } else {
            $('.unit-rent').removeClass('d-none').show();
            $('.unit-rent :input').prop('disabled', false);
        }
    } else {
        // Other categories → show only basic sale/rent fields
        if (isSale) {
            $('.sale-fields').removeClass('d-none').show();
            $('.sale-fields :input').prop('disabled', false);
        } else {
            $('.rent-fields').removeClass('d-none').show();
            $('.rent-fields :input').prop('disabled', false);
        }
    }
}



/** property next step end **/

// get data by selected category
function toggleFieldsBasedOnCategory(categoryId) {
    $(".tenant_preference_div").addClass("d-none");
    $(".tenant_preference").addClass("d-none");

    if (categoryId == "1") {
        $(".building_name_div").show();
        $(".building_name").prop("disabled", false);

        $(".property_size_div").show();
        $(".property_size").prop("disabled", false);

        $(".lot_number_div").show();
        $(".lot_number").prop("disabled", false);

        $(".unit_number_div").show();
        $(".unit_number").prop("disabled", false);

        $(".parking_lot_div").show();
        $(".parking_lot").prop("disabled", false);

        $(".condition_div").show();
        $(".condition").prop("disabled", false);

        $(".facilities_div").show();
        $(".facilities").prop("disabled", false);

        $(".bedroom_div").show();
        $(".bedroom").prop("disabled", false);

        $(".bathroom_div").show();
        $(".bathroom").prop("disabled", false);

        $(".kitchen_div").show();
        $(".kitchen").prop("disabled", false);

        $(".balcony_div").show();
        $(".balcony").prop("disabled", false);

        $(".furnishing_div").show();
        $(".furnishing").prop("disabled", false);

        $(".floor_range_div").show();
        $(".floor_range").prop("disabled", false);

        $(".residential_type_div").show();
        $(".residential_type").prop("disabled", false);

        //hide fields
        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".land_property_type_div").hide();
        $(".land_property_type").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".land_size_div").hide();
        $(".land_size").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);
    } else if (categoryId == "2") {
        $(".property_size_div").show();
        $(".property_size").prop("disabled", false);

        $(".house_type_div").show();
        $(".house_type").prop("disabled", false);

        $(".lot_number_div").show();
        $(".lot_number").prop("disabled", false);

        $(".parking_lot_div").show();
        $(".parking_lot").prop("disabled", false);

        $(".condition_div").show();
        $(".condition").prop("disabled", false);

        $(".facilities_div").show();
        $(".facilities").prop("disabled", false);

        $(".bedroom_div").show();
        $(".bedroom").prop("disabled", false);

        $(".bathroom_div").show();
        $(".bathroom").prop("disabled", false);

        $(".kitchen_div").show();
        $(".kitchen").prop("disabled", false);

        $(".balcony_div").show();
        $(".balcony").prop("disabled", false);

        $(".furnishing_div").show();
        $(".furnishing").prop("disabled", false);

        //hide fields
        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".land_property_type_div").hide();
        $(".land_property_type").prop("disabled", true);

        $(".land_size_div").hide();
        $(".land_size").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".unit_number_div").hide();
        $(".unit_number").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);

    } else if (categoryId == "3") {
        $(".property_type_div").show();
        $(".property_type").prop("disabled", false);

        $(".property_size_div").show();
        $(".property_size").prop("disabled", false);

        $(".lot_number_div").show();
        $(".lot_number").prop("disabled", false);

        $(".unit_number_div").show();
        $(".unit_number").prop("disabled", false);

        $(".parking_lot_div").show();
        $(".parking_lot").prop("disabled", false);

        $(".condition_div").show();
        $(".condition").prop("disabled", false);

        $(".facilities_div").show();
        $(".facilities").prop("disabled", false);

        //hide fields
        $(".land_property_type_div").hide();
        $(".land_property_type").prop("disabled", true);

        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".bedroom_div").hide();
        $(".bedroom").prop("disabled", true);

        $(".bathroom_div").hide();
        $(".bathroom").prop("disabled", true);

        $(".kitchen_div").hide();
        $(".kitchen").prop("disabled", true);

        $(".balcony_div").hide();
        $(".balcony").prop("disabled", true);

        $(".land_size_div").hide();
        $(".land_size").prop("disabled", true);

        $(".furnishing_div").hide();
        $(".furnishing").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);
    } else if (categoryId == "4") {
        $(".land_property_type_div").show();
        $(".land_property_type").prop("disabled", false);

        $(".lot_number_div").show();
        $(".lot_number").prop("disabled", false);

        $(".land_size_div").show();
        $(".land_size").prop("disabled", false);

        //hide fields
        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".bedroom_div").hide();
        $(".bedroom").prop("disabled", true);

        $(".bathroom_div").hide();
        $(".bathroom").prop("disabled", true);

        $(".kitchen_div").hide();
        $(".kitchen").prop("disabled", true);

        $(".balcony_div").hide();
        $(".balcony").prop("disabled", true);

        $(".condition_div").hide();
        $(".condition").prop("disabled", true);

        $(".facilities_div").hide();
        $(".facilities").prop("disabled", true);

        $(".property_size_div").hide();
        $(".property_size").prop("disabled", true);

        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".furnishing_div").hide();
        $(".furnishing").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".parking_lot_div").hide();
        $(".parking_lot").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".unit_number_div").hide();
        $(".unit_number").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);

        $(".utility_deposit_div").hide();
        $(".utility_deposit").prop("disabled", true);
    } else if (categoryId == "5") {
        $(".tenant_preference_div").removeClass("d-none");
        $(".tenant_preference").removeClass("d-none");

        $(".room_residential_type_div").show();
        $(".room_residential_type").prop("disabled", false);

        $(".condition_div").show();
        $(".condition").prop("disabled", false);

        $(".parking_lot_div").show();
        $(".parking_lot").prop("disabled", false);

        $(".room_size_div").show();
        $(".room_size").prop("disabled", false);

        $(".balcony_div").show();
        $(".balcony").prop("disabled", false);

        $(".furnishing_div").show();
        $(".furnishing").prop("disabled", false);

        $(".facilities_div").show();
        $(".facilities").prop("disabled", false);

        //hide fields
        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".kitchen_div").hide();
        $(".kitchen").prop("disabled", true);

        $(".bedroom_div").hide();
        $(".bedroom").prop("disabled", true);

        $(".bathroom_div").hide();
        $(".bathroom").prop("disabled", true);

        $(".property_size_div").hide();
        $(".property_size").prop("disabled", true);

        $(".lot_number_div").hide();
        $(".lot_number").prop("disabled", true);

        $(".land_size_div").hide();
        $(".land_size").prop("disabled", true);

        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".unit_number_div").hide();
        $(".unit_number").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".land_property_type_div").hide();
        $(".land_property_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);

        $(".utility_deposit_div").hide();
        $(".utility_deposit").prop("disabled", true);
    } else if(categoryId == "7") {
        $(".banglow_property_type_div").show();
        $(".banglow_property_type").prop("disabled", false);

        $(".property_size_div").show();
        $(".property_size").prop("disabled", false);

        $(".bedroom_div").show();
        $(".bedroom").prop("disabled", false);

        $(".bathroom_div").show();
        $(".bathroom").prop("disabled", false);

        $(".condition_div").show();
        $(".condition").prop("disabled", false);

        $(".parking_lot_div").show();
        $(".parking_lot").prop("disabled", false);

        $(".facilities_div").show();
        $(".facilities").prop("disabled", false);

        //hide fields
        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".kitchen_div").hide();
        $(".kitchen").prop("disabled", true);

        $(".balcony_div").hide();
        $(".balcony").prop("disabled", true);

        $(".lot_number_div").hide();
        $(".lot_number").prop("disabled", true);

        $(".land_size_div").hide();
        $(".land_size").prop("disabled", true);

        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".furnishing_div").hide();
        $(".furnishing").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".unit_number_div").hide();
        $(".unit_number").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".land_property_type_div").hide();
        $(".land_property_type").prop("disabled", true);

        $(".utility_deposit_div").hide();
        $(".utility_deposit").prop("disabled", true);
    } else if (categoryId == "8") {
        $(".land_property_type_div").show();
        $(".land_property_type").prop("disabled", false);

        $(".lot_number_div").show();
        $(".lot_number").prop("disabled", false);

        $(".land_size_div").show();
        $(".land_size").prop("disabled", false);

        //hide fields
        $(".building_name_div").hide();
        $(".building_name").prop("disabled", true);

        $(".house_type_div").hide();
        $(".house_type").prop("disabled", true);

        $(".bedroom_div").hide();
        $(".bedroom").prop("disabled", true);

        $(".bathroom_div").hide();
        $(".bathroom").prop("disabled", true);

        $(".kitchen_div").hide();
        $(".kitchen").prop("disabled", true);

        $(".balcony_div").hide();
        $(".balcony").prop("disabled", true);

        $(".condition_div").hide();
        $(".condition").prop("disabled", true);

        $(".property_size_div").hide();
        $(".property_size").prop("disabled", true);

        $(".property_type_div").hide();
        $(".property_type").prop("disabled", true);

        $(".furnishing_div").hide();
        $(".furnishing").prop("disabled", true);

        $(".floor_range_div").hide();
        $(".floor_range").prop("disabled", true);

        $(".parking_lot_div").hide();
        $(".parking_lot").prop("disabled", true);

        $(".room_size_div").hide();
        $(".room_size").prop("disabled", true);

        $(".facilities_div").hide();
        $(".facilities").prop("disabled", true);

        $(".room_residential_type_div").hide();
        $(".room_residential_type").prop("disabled", true);

        $(".unit_number_div").hide();
        $(".unit_number").prop("disabled", true);

        $(".residential_type_div").hide();
        $(".residential_type").prop("disabled", true);

        $(".banglow_property_type_div").hide();
        $(".banglow_property_type").prop("disabled", true);

        $(".utility_deposit_div").hide();
        $(".utility_deposit").prop("disabled", true);
    }
}

$(".nextButton").on("click", function (e) {
    e.preventDefault();

    var selectedCategory = $("#categoryDropdown").val();

    if (selectedCategory) {
        toggleFieldsBasedOnCategory(selectedCategory);

        var categoryName = $("#categoryDropdown option:selected").data("category-name");

        if (categoryName) {
            $("#step-text").text(categoryName + " Details");
        }
    }
});


function propertyTriggerFile(id) {
    document.getElementById(id).click();
}

function propertyPreviewImage(input, id) {
    if (input.files && input.files[0]) {
        const box = input.closest(".property-upload-box");

        // Hide icon and label
        const icon = box.querySelector("svg, img.property-icon");
        const label = box.querySelector("p");
        if (icon) icon.style.display = "none";
        if (label) label.style.display = "none";

        // Remove existing preview if any
        box.querySelectorAll(".preview").forEach(p => p.remove());
        box.querySelectorAll(".property-remove-btn").forEach(p => p.remove());

        // Add preview image
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement("img");
            img.src = e.target.result;
            img.alt = "Preview";
            img.className = "preview";
            box.appendChild(img);

            // Add remove button
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "property-remove-btn";
            btn.dataset.id = id;
            btn.innerText = "×";
            btn.onclick = function (event) {
                event.stopPropagation();
                input.value = "";
                box.querySelectorAll(".preview").forEach(p => p.remove());
                btn.remove();
                const icon = box.querySelector("svg, img.property-icon");
                const label = box.querySelector("p");
                if (icon) icon.style.display = "";
                if (label) label.style.display = "";
            };
            box.appendChild(btn);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function propertyPreviewMoreImage(input) {
    if (input.files && input.files.length > 0) {
        const box = input.closest(".property-upload-box");

        // Remove existing preview if any
        box.querySelectorAll(".preview").forEach(p => p.remove());
        box.querySelectorAll(".property-remove-btn").forEach(p => p.remove());

        // Hide icon and label when image(s) added
        const icon = box.querySelector("svg, img.property-icon");
        const label = box.querySelector("p");
        if (icon) icon.style.display = "none";
        if (label) label.style.display = "none";

        // Add preview for each selected file
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.alt = "Preview";
                img.className = "preview mb-2";
                box.appendChild(img);
            };
            reader.readAsDataURL(file);
        });

        // Add remove button
        if (!box.querySelector(".property-remove-btn")) {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "property-remove-btn";
            btn.dataset.id = input.id;
            btn.innerText = "×";

            // Stop propagation on click
            btn.onclick = function(event) {
            event.stopPropagation();
            const colDiv = box.closest(".property-upload-more");
            if (colDiv) colDiv.remove();
        }
            box.appendChild(btn);
        }

        // Add new empty upload box dynamically
        const uploadRow = document.getElementById("property-image-upload-row");
        const newId = "upload-more-" + (document.querySelectorAll(".property-upload-more").length + 1);
        const newCol = document.createElement("div");
        newCol.className = "col-md-3 property-upload-more";
        newCol.innerHTML = `
            <div class="property-upload-box text-center" onclick="propertyTriggerFile('${newId}')">
                <svg class="mb-2" width="55" height="55" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0268 4.01025H35.375V9.1665H32.5625C29.7666 9.1665 27.5 11.475 27.5 14.3227C27.5 17.1705 29.7666 19.479 32.5625 19.479H35.375V22.3436C35.375 25.1912 37.6416 27.4998 40.4375 27.4998C43.2333 27.4998 45.5 25.1912 45.5 22.3436V19.479H50.5625V44.278C50.5625 47.9846 47.6123 50.9894 43.9731 50.9894H11.0268C7.38763 50.9894 4.4375 47.9846 4.4375 44.278V10.7215C4.4375 7.01501 7.38763 4.01025 11.0268 4.01025ZM20.1875 25.2127C21.268 25.2127 22.3043 25.6498 23.0682 26.428L32 35.5252L36.4318 31.0113C37.1957 30.2331 38.232 29.7961 39.3125 29.7961C40.3929 29.7961 41.4293 30.2331 42.1932 31.0113L46.1696 35.0614V44.278C46.1696 45.5137 45.1863 46.5152 43.9731 46.5152H11.0268C9.81376 46.5152 8.83038 45.5137 8.83038 44.278V35.0614L17.3068 26.428C18.0708 25.6498 19.107 25.2127 20.1875 25.2127Z" fill="#B3B3B3"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M40.4375 4.01025C41.6802 4.01025 42.6875 5.03627 42.6875 6.30191V12.0311H48.3125C49.5552 12.0311 50.5625 13.0571 50.5625 14.3227C50.5625 15.5884 49.5552 16.6144 48.3125 16.6144H42.6875V22.3436C42.6875 23.6093 41.6802 24.6352 40.4375 24.6352C39.1948 24.6352 38.1875 23.6093 38.1875 22.3436V16.6144H32.5625C31.3198 16.6144 30.3125 15.5884 30.3125 14.3227C30.3125 13.0571 31.3198 12.0311 32.5625 12.0311H38.1875V6.30191C38.1875 5.03627 39.1948 4.01025 40.4375 4.01025Z" fill="#B3B3B3"/>
                </svg>
                <p class="mb-0 fw-semibold">Upload More Image</p>
                <input type="file" name="cover_image[]" accept="image/*" id="${newId}" hidden onchange="propertyPreviewMoreImage(this)" multiple>
            </div>
        `;
        uploadRow.appendChild(newCol);
    }
}

// Remove image or upload box
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("property-remove-btn")) {
        const box = e.target.closest(".property-upload-box");
        const col = box.closest(".col-md-3");
        const input = box.querySelector('input[type="file"]');

        // Reset input value
        if (input) input.value = "";

        // If it's a dynamically added upload-more box → remove entire column
        if (col.classList.contains("property-upload-more")) {
            col.remove();
        } else {
            // Restore default view for cover/floorplan
            const icon = box.querySelector("svg, img.property-icon");
            const label = box.querySelector("p");
            if (icon) icon.style.display = "";
            if (label) label.style.display = "";

            box.querySelectorAll(".preview").forEach(p => p.remove());
            e.target.remove();
        }
    }
});

//for edit cover image removed
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("image-removed-index-wise")) {
        const box = e.target.closest(".property-upload-box");
        const existingPath = e.target.dataset.path;
        const isExisting = e.target.dataset.existing === "1";

        // If it's an existing image, track it for deletion
        if (isExisting && existingPath) {
                const removeInput = document.createElement("input");
                removeInput.type = "hidden";
                removeInput.name = "cover_removed_images[]";
                removeInput.value = existingPath;
                document.querySelector("form").appendChild(removeInput);
            }

        box.remove();
    }
});


$(document).ready(function () {
    const $container = $("#propertyAccordionContainer");
    const $addMoreBtn = $("#propertyAddMore");
    const $unitSelect = $(".unitDropdown");
    const $unitAccordionContainer = $(".unit-accordion-container");

    const plusSVG = `
        <svg class="property-icon-plus" width="24" height="24" viewBox="0 0 24 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
            <path d="M12 22C17.5228 22 22 17.5228 22 12
            C22 6.47715 17.5228 2 12 2C6.47715 2
            2 6.47715 2 12C2 17.5228 6.47715
            22 12 22Z" stroke="#666666"
            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 8V16" stroke="#666666" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 12H16" stroke="#666666" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;

    const minusSVG = `
        <svg class="property-icon-minus" width="24" height="24" viewBox="0 0 24 24"
        fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 22C17.5228 22 22 17.5228
            22 12C22 6.47715 17.5228 2 12 2C6.47715
            2 2 6.47715 2 12C2 17.5228 6.47715
            22 12 22Z" stroke="#525252" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" />
            <path d="M8 12H16" stroke="#525252" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round" />
        </svg>`;

    // Accordion toggle / delete
    $container.on("click", ".property-accordion-header", function (e) {
        if ($(e.target).closest(".property-delete").length) return;

        const $accordion = $(this).closest(".property-accordion");
        $accordion.toggleClass("open");

        const $toggleBtn = $accordion.find(".property-toggle");
        $toggleBtn.html($accordion.hasClass("open") ? minusSVG : plusSVG);
    });

    $container.on("click", ".property-delete", function () {
        $(this).closest(".property-accordion").remove();
        updateLabels();
        updateAccordionIndexes();
        reindexUnitImageInputs();
        updateUnits();
    });

    // Add New Accordion
    $addMoreBtn.on("click", function () {
        const $lastAccordion = $container.find(".property-accordion").last();
        const $clone = $lastAccordion.clone();

        $clone.find('input[name*="[id]"]').remove();

        $clone.find("input, textarea, select").each(function() {
            if ($(this).is(":checkbox") || $(this).is(":radio")) {
                $(this).prop("checked", false);
            } else {
                $(this).val("");
            }
        });

        $clone.removeClass("open");
        $clone.find(".property-toggle").html(plusSVG);

        $container.append($clone);
        updateLabels();
        updateAccordionIndexes();
        reindexUnitImageInputs();
        updateUnits();
    });

    // Update Accordion Labels (#1, #2)
    function updateLabels() {
        const $accordions = $container.find(".property-accordion");
        $accordions.each(function (index) {
            $(this).find("strong").text(`#${index + 1}`);
            $(this).find(".property-delete").show();
        });
        // Hide delete for first one only (optional)
        if ($accordions.length === 1) {
            $accordions.find(".property-delete").hide();
        } else {
            $accordions.last().find(".property-delete").show();
        }
    }

    // Create or Update Units in Next Step
    function updateUnits() {
        const $unitNameInputs = $container.find('.unitName');
        const $select = $unitSelect;
        const $accordionTarget = $unitAccordionContainer;
        const type = $('input[name="type"]:checked').val();

        $select.empty().append('<option selected disabled>Select a Unit</option>');
        $accordionTarget.empty();

        $unitNameInputs.each(function (i) {
            const name = $(this).val().trim() || `Unit ${i + 1}`;
            $select.append(`<option value="${name}">${name}</option>`);

            // Fetch existing data (for edit mode)
            const data = (window.propertyUnits && window.propertyUnits[i]) || {};

            let accordionContent = "";

            if (type === "sale") {
                accordionContent = `
                    <div class="unit-accordion-item open">
                        <div class="unit-accordion-header">
                            <h3>Unit - ${name}</h3>
                            <button class="unit-accordion-toggle" aria-expanded="false">${plusSVG}</button>
                        </div>
                        <div class="unit-accordion-content">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label>Sale Amount</label>
                                    <input type="number" class="form-control" name="units[${i}][sale_amount]"
                                        value="${data.sale_amount ?? ''}" placeholder="Ex: $10000">
                                </div>
                            </div>
                        </div>
                    </div>`;
            } else {
                accordionContent = `
                    <div class="unit-accordion-item open">
                        <div class="unit-accordion-header">
                            <h3>Unit - ${name}</h3>
                            <button class="unit-accordion-toggle" aria-expanded="false">${plusSVG}</button>
                        </div>
                        <div class="unit-accordion-content">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label>Rent Amount</label>
                                    <input type="number" class="form-control"
                                        name="units[${i}][monthly_rent]" value="${data.monthly_rent ?? ''}"
                                        placeholder="Ex: $5000">
                                </div>
                                <div class="col-md-4">
                                    <label>Minimum Rental Period</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            name="units[${i}][min_rental_value]"
                                            value="${data.min_rental_value ?? ''}" placeholder="Ex: 1">
                                        <select class="form-select rental-select" name="units[${i}][min_rental_unit]">
                                            <option ${data.min_rental_unit === 'days' ? 'selected' : ''} value="days">Days</option>
                                            <option ${data.min_rental_unit === 'weeks' ? 'selected' : ''} value="weeks">Weeks</option>
                                            <option ${data.min_rental_unit === 'months' ? 'selected' : ''} value="months">Months</option>
                                            <option ${data.min_rental_unit === 'years' ? 'selected' : ''} value="years">Years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Security Deposit</label>
                                    <input type="number" class="form-control"
                                        name="units[${i}][security_deposit]"
                                        value="${data.security_deposit ?? ''}" placeholder="Ex: $200">
                                </div>
                                <div class="col-md-4">
                                    <label>Security Deposit Period</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                        name="units[${i}][security_deposit_value]" value="${data.security_deposit_value ?? ''}" placeholder="Ex: 1">
                                        <select class="form-select rental-select reset_val" name="units[${i}][security_deposit_unit]">
                                            <option ${data.security_deposit_unit === 'days' ? 'selected' : ''} value="days">Days</option>
                                            <option ${data.security_deposit_unit === 'weeks' ? 'selected' : ''} value="weeks" >Weeks</option>
                                            <option ${data.security_deposit_unit === 'months' ? 'selected' : ''} value="months">Months</option>
                                            <option ${data.security_deposit_unit === 'years' ? 'selected' : ''} value="years">Years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Utility Deposit</label>
                                    <input type="number" class="form-control"
                                        name="units[${i}][utility_deposit]"
                                        value="${data.utility_deposit ?? ''}" placeholder="Ex: $100">
                                </div>
                                <div class="col-md-4">
                                    <label>Late Fee</label>
                                    <input type="number" class="form-control"
                                        name="units[${i}][late_fee]"
                                        value="${data.late_fee ?? ''}" placeholder="Ex: $50">
                                </div>
                                <div class="col-md-4">
                                    <label>Late Fee After Days</label>
                                    <input type="number" class="form-control"
                                        name="units[${i}][late_fee_after_days]"
                                        value="${data.late_fee_after_days ?? ''}" placeholder="Ex: $50">
                                </div>
                            </div>
                        </div>
                    </div>`;
            }

            $accordionTarget.append(accordionContent);
        });
    }


    function updateAccordionIndexes() {
        $container.find(".property-accordion").each(function(unitIndex) {
            $(this).find("input, textarea, select").each(function() {
                let name = $(this).attr("name");
                if (!name) return;

                // Check if this is a multi-value field (arrays)
                const arrayFields = ["facilities", "amenities", "tenant_preference", "images"];

                // Extract the base name without [] if present
                const baseName = name.replace(/\[\]$/, '');

                if (arrayFields.includes(baseName)) {
                    // Multi-value fields get units[index][field][]
                    $(this).attr("name", `units[${unitIndex}][${baseName}][]`);
                } else {
                    // Single-value fields get units[index][field]
                    const newName = name.replace(/units\[\d*\]/, `units[${unitIndex}]`);
                    $(this).attr("name", newName);
                }
            });
        });
    }

    // Sync Unit Names Live
    $container.on("input", '.unitName', function () {
        updateUnits();
    });

    $('input[name="type"]').on('change', function () {
        updateUnits();
    });

    // Initial Load
    updateLabels();
    updateAccordionIndexes();
    reindexUnitImageInputs();
    updateUnits();
});

//unit multi image handle start
function getUnitIndexFromElement(el) {
    const accordion = el.closest('.property-accordion');
    if (!accordion) return 0;
    const container = document.getElementById('propertyAccordionContainer');
    return Array.from(container.querySelectorAll('.property-accordion')).indexOf(accordion);
  }

  //Ensure each file input inside each unit has the correct name (call after clone/delete)
  function reindexUnitImageInputs() {
    const container = document.getElementById('propertyAccordionContainer');
    if (!container) return;
    const accordions = Array.from(container.querySelectorAll('.property-accordion'));

    accordions.forEach((accordion, unitIndex) => {
      // use zero-based index for input names
      accordion.querySelectorAll('.custom-image-upload-row input[type="file"]').forEach((inp) => {
        inp.name = `units[${unitIndex}][images][]`;
        inp.dataset.unitIndex = unitIndex;
      });

      // also handle remove_images inputs
      accordion.querySelectorAll('input[type="hidden"][name*="remove_images"]').forEach((hi) => {
        hi.name = `units[${unitIndex}][remove_images][]`;
      });
    });
  }


 //Create and return a fresh "add upload" column element

  function createAddUploadCol() {
    const col = document.createElement('div');
    col.className = 'col-md-3 custom-upload-item custom-upload-add';
    col.innerHTML = `
      <div class="custom-upload-box text-center">
        <svg class="mb-2" width="55" height="55" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0268 4.01025H35.375V9.1665H32.5625C29.7666 9.1665 27.5 11.475 27.5 14.3227C27.5 17.1705 29.7666 19.479 32.5625 19.479H35.375V22.3436C35.375 25.1912 37.6416 27.4998 40.4375 27.4998C43.2333 27.4998 45.5 25.1912 45.5 22.3436V19.479H50.5625V44.278C50.5625 47.9846 47.6123 50.9894 43.9731 50.9894H11.0268C7.38763 50.9894 4.4375 47.9846 4.4375 44.278V10.7215C4.4375 7.01501 7.38763 4.01025 11.0268 4.01025Z" fill="#B3B3B3"/>
          <path fill-rule="evenodd" clip-rule="evenodd" d="M40.4375 4.01025C41.6802 4.01025 42.6875 5.03627 42.6875 6.30191V12.0311H48.3125C49.5552 12.0311 50.5625 13.0571 50.5625 14.3227C50.5625 15.5884 49.5552 16.6144 48.3125 16.6144H42.6875V22.3436C42.6875 23.6093 41.6802 24.6352 40.4375 24.6352C39.1948 24.6352 38.1875 23.6093 38.1875 22.3436V16.6144H32.5625C31.3198 16.6144 30.3125 15.5884 30.3125 14.3227C30.3125 13.0571 31.3198 12.0311 32.5625 12.0311H38.1875V6.30191C38.1875 5.03627 39.1948 4.01025 40.4375 4.01025Z" fill="#B3B3B3"/>
        </svg>
        <p class="mb-0 fw-semibold">Upload More Image</p>
        <input type="file" accept="image/*" multiple hidden>
      </div>
    `;
    return col;
  }


 //Open file dialog when upload box clicked (ignore clicks on remove buttons)

  document.addEventListener('click', (e) => {
    if (e.target.closest('.custom-remove-btn') || e.target.closest('.custom-remove-existing')) return;
    const box = e.target.closest('.custom-upload-box');
    if (!box) return;
    const input = box.querySelector("input[type='file']");
    if (input) input.click();
  });


  document.addEventListener('change', (e) => {
    if (!e.target.matches("input[type='file']")) return;
    const originInput = e.target;
    const originCol = originInput.closest('.custom-upload-item');
    const row = originCol.closest('.custom-image-upload-row');
    const files = Array.from(originInput.files || []);
    if (!files.length) return;

    files.forEach(file => {
      // prepare a single-file DataTransfer and a new input
      const dt = new DataTransfer();
      dt.items.add(file);
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = originInput.accept || 'image/*';
      fileInput.hidden = true;

      try {
        fileInput.files = dt.files;
      } catch (err) {
        // Some browsers deny setting .files — as fallback we keep originInput (less ideal)
        console.warn('Could not set files on new input:', err);
      }

      // build preview column
      const col = document.createElement('div');
      col.className = 'col-md-3 custom-upload-item';
      const box = document.createElement('div');
      box.className = 'custom-upload-box text-center position-relative';
      const img = document.createElement('img');
      img.className = 'img-fluid rounded mb-2 preview';
      img.alt = file.name;
      const reader = new FileReader();
      reader.onload = (ev) => { img.src = ev.target.result; };
      reader.readAsDataURL(file);

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'custom-remove-btn';
      removeBtn.innerText = '×';

      box.appendChild(img);
      box.appendChild(removeBtn);
      box.appendChild(fileInput);
      col.appendChild(box);

      // insert preview before the add box (if exists) otherwise append
      const addEl = row.querySelector('.custom-upload-add');
      if (addEl) row.insertBefore(col, addEl);
      else row.appendChild(col);
    });

    // clear origin input so same files can be re-selected later
    originInput.value = '';

    // ensure one add box exists
    if (!row.querySelector('.custom-upload-add')) {
      row.appendChild(createAddUploadCol());
    }

    // reindex names (important if this unit has been cloned)
    reindexUnitImageInputs();
  });


  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('custom-remove-btn')) {
      const col = e.target.closest('.custom-upload-item');
      if (col) col.remove();
      reindexUnitImageInputs();
      return;
    }

    if (e.target.classList.contains('custom-remove-existing')) {
      const btn = e.target;
      const path = btn.dataset.path; // server path
      const col = btn.closest('.custom-upload-item');
      const unitIndex = getUnitIndexFromElement(btn);
      const form = document.querySelector('form');

      if (form && path != null) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `units[${unitIndex}][remove_images][]`;
        hidden.value = path;
        form.appendChild(hidden);
      }

      if (col) col.remove();
      reindexUnitImageInputs();
      return;
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    reindexUnitImageInputs();
  });


// unit accordian ------------->
$(document).on("click", ".unit-accordion-header", function () {
    const item = $(this).closest(".unit-accordion-item");
    const toggleBtn = $(this).find(".unit-accordion-toggle");

    // Toggle the current one
    item.toggleClass("unit-active");

    const isExpanded = item.hasClass("unit-active");
    toggleBtn.attr("aria-expanded", isExpanded);

    // Close all others
    $(".unit-accordion-item").not(item).removeClass("unit-active")
        .find(".unit-accordion-toggle").attr("aria-expanded", "false");
});


window.addEventListener("DOMContentLoaded", () => {
    const firstItem = document.querySelector(".unit-accordion-item");
    if (firstItem) {
        firstItem.classList.add("unit-active");
        const firstToggle = firstItem.querySelector(".unit-accordion-toggle");
        if (firstToggle) firstToggle.setAttribute("aria-expanded", "true");
    }
});


$(document).ready(function() {
    // Reusable loader
    function loadFacilities(landlordId, selectedFacilities = [], targetContainer) {
        if (!landlordId) {
            $(targetContainer).html('');
            return;
        }

        $.ajax({
            url: '/admin/properties/get-facilities/' + landlordId,
            type: 'GET',
            success: function(response) {
                let html = '';
                response.forEach(function(facility) {
                    const checked = selectedFacilities.includes(facility.id) ? 'checked' : '';
                    html += `
                        <li>
                            <label class="tag-btn check-btn">
                                <input type="checkbox" class="facilities reset_val" name="facilities[]" value="${facility.id}" ${checked}>
                                <span class="checkmark">${facility.name}</span>
                            </label>
                        </li>`;
                });
                $(targetContainer).html(html);
                updateAccordionIndexes();
                autoCheckFacilityAll();
            }
        });
    }

    // Normal/global facilities
    const globalLandlordId = $('.selectedLandlord').val();
    const globalFacilities = JSON.parse($('.selectedGlobalFacilities').val() || '[]');
    if (globalLandlordId) {
        loadFacilities(globalLandlordId, globalFacilities, '.facility_list');
    }

    // Unit-specific facilities
    const unitLandlordId = $('.selectedUnitLandlord').val();
    const unitFacilities = JSON.parse($('.selectedUnitFacilities').val() || '[]');
    if (unitLandlordId) {
        loadFacilities(unitLandlordId, unitFacilities, '.unit_facility_list');
    }

    // Optional: if you have landlord dropdowns, handle change separately
    $('#landlordSelect').on('change', function() {
        loadFacilities($(this).val(), [], '.facility_list');
    });

    $('#landlordSelect').on('change', function() {
        loadFacilities($(this).val(), [], '.unit_facility_list');
    });

    function updateAccordionIndexes() {
        const $container = $("#propertyAccordionContainer");
        $container.find(".property-accordion").each(function(unitIndex) {
            $(this).find("input, textarea, select").each(function() {
                let name = $(this).attr("name");
                if (!name) return;

                // Check if this is a multi-value field (arrays)
                const arrayFields = ["facilities", "amenities", "tenant_preference", "images"];

                // Extract the base name without [] if present
                const baseName = name.replace(/\[\]$/, '');

                if (arrayFields.includes(baseName)) {
                    // Multi-value fields get units[index][field][]
                    $(this).attr("name", `units[${unitIndex}][${baseName}][]`);
                } else {
                    // Single-value fields get units[index][field]
                    const newName = name.replace(/units\[\d*\]/, `units[${unitIndex}]`);
                    $(this).attr("name", newName);
                }
            });
        });
    }


    $(document).on("click", ".select-all-facilities, .select-all-facilities + label", function (e) {
        e.preventDefault();
        const $accordion = $(this).closest(".property-accordion");

        const $selectAll = $accordion.find(".select-all-facilities");
        const newState = !$selectAll.prop("checked");

        $selectAll.prop("checked", newState);
        $accordion.find(".facilities").prop("checked", newState);
    });

    $(document).on("change", ".facilities", function () {
        const $accordion = $(this).closest(".property-accordion");
        const total = $accordion.find(".facilities").length;
        const checked = $accordion.find(".facilities:checked").length;

        $accordion.find(".select-all-facilities").prop("checked", total === checked);
    });

    function autoCheckFacilityAll() {
        $(".property-accordion").each(function () {
            const $accordion = $(this);
            const $facilities = $accordion.find(".facilities");
            const $selectAll = $accordion.find(".select-all-facilities");

            if ($facilities.length && $facilities.length === $facilities.filter(":checked").length) {
                $selectAll.prop("checked", true);
            } else {
                $selectAll.prop("checked", false);
            }
        });
    }


    autoCheckFacilityAll();
});


$(document).ready(function() {
    // Reusable loader
    function loadAmenities(landlordId, selectedAmenities = [], targetContainer) {
        if (!landlordId) {
            $(targetContainer).html('');
            return;
        }

        $.ajax({
            url: '/admin/properties/get-amenities/' + landlordId,
            type: 'GET',
            success: function(response) {
                let html = '';
                response.forEach(function(amenity) {
                    const checked = selectedAmenities.includes(amenity.id) ? 'checked' : '';
                    html += `
                        <li>
                            <label class="tag-btn check-btn">
                                <input type="checkbox" class="amenities reset_val" name="amenities[]" value="${amenity.id}" ${checked}>
                                <span class="checkmark">${amenity.name}</span>
                            </label>
                        </li>`;
                });
                $(targetContainer).html(html);
                updateAccordionIndexes();
                autoCheckAmenityAll();
            }
        });
    }

    // Normal/global amenities
    const globalLandlordId = $('.selectedLandlord').val();
    const globalAmenities = JSON.parse($('.selectedGlobalAmenities').val() || '[]');
    if (globalLandlordId) {
        loadAmenities(globalLandlordId, globalAmenities, '.amenity_list');
    }

    // Unit-specific amenities
    const unitLandlordId = $('.selectedUnitLandlord').val();
    const unitAmenities = JSON.parse($('.selectedUnitAmenities').val() || '[]');
    if (unitLandlordId) {
        loadAmenities(unitLandlordId, unitAmenities, '.unit_amenity_list');
    }

    // Optional: if you have landlord dropdowns
    $('#landlordSelect').on('change', function() {
        loadAmenities($(this).val(), [], '.amenity_list');
    });

    $('#landlordSelect').on('change', function() {
        loadAmenities($(this).val(), [], '.unit_amenity_list');
    });

    function updateAccordionIndexes() {
        const $container = $("#propertyAccordionContainer");
        $container.find(".property-accordion").each(function(unitIndex) {
            $(this).find("input, textarea, select").each(function() {
                let name = $(this).attr("name");
                if (!name) return;

                const arrayFields = ["facilities", "amenities", "tenant_preference", "images"];
                const baseName = name.replace(/\[\]$/, '');

                if (arrayFields.includes(baseName)) {
                    $(this).attr("name", `units[${unitIndex}][${baseName}][]`);
                } else {
                    const newName = name.replace(/units\[\d*\]/, `units[${unitIndex}]`);
                    $(this).attr("name", newName);
                }
            });
        });
    }

    // Select all amenities toggle
    $(document).on("click", ".select-all-amenities, .select-all-amenities + label", function (e) {
        e.preventDefault();
        const $accordion = $(this).closest(".property-accordion");

        const $selectAll = $accordion.find(".select-all-amenities");
        const newState = !$selectAll.prop("checked");

        $selectAll.prop("checked", newState);
        $accordion.find(".amenities").prop("checked", newState);
    });

    // Update select all state on change
    $(document).on("change", ".amenities", function () {
        const $accordion = $(this).closest(".property-accordion");
        const total = $accordion.find(".amenities").length;
        const checked = $accordion.find(".amenities:checked").length;

        $accordion.find(".select-all-amenities").prop("checked", total === checked);
    });

    // Auto check on page load
    function autoCheckAmenityAll() {
        $(".property-accordion").each(function () {
            const $accordion = $(this);
            const $amenities = $accordion.find(".amenities");
            const $selectAll = $accordion.find(".select-all-amenities");

            if ($amenities.length && $amenities.length === $amenities.filter(":checked").length) {
                $selectAll.prop("checked", true);
            } else {
                $selectAll.prop("checked", false);
            }
        });
    }

    autoCheckAmenityAll();
});
