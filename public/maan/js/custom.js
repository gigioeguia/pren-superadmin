"use strict";

$(".change-lang").on("change", function () {
    location.href = $(this).val();
});

$(".view-item").each(function () {
    var container = $(this);
    var service = container.data("id");
    let property_view = "#property-view-" + service;
    $(property_view).on("click", function () {
        //alert($(lead_view).data('id'));
        $("#view-contact-name").text($(property_view).data("contact-name"));
        $("#view-contact-email").text($(property_view).data("contact-email"));
        $("#view-contact-phone").text($(property_view).data("contact-phone"));
        $("#view-property-title").text($(property_view).data("property-title"));
        $("#view-property-description").text(
            $(property_view).data("property-description")
        );
        $("#view-address").text($(property_view).data("address"));
        $("#view-rent-price").text($(property_view).data("property-rent"));
        $("#view-building-name").text($(property_view).data("building-name"));
        $("#view-postcode").text($(property_view).data("postcode"));
        $("#view-bathroom").text($(property_view).data("bathroom"));
        $("#view-bedroom").text($(property_view).data("bedroom"));
        $("#view-floor-rang").text($(property_view).data("floor-rang"));
        $("#view-property-size").text($(property_view).data("property-size"));
        $("#view-furnishing").text($(property_view).data("furnishing"));
        $("#view-parking-lot").text($(property_view).data("parking-lot"));
        $("#view-rental-deposit").text($(property_view).data("rental-deposit"));
        $("#view-category").text($(property_view).data("category"));
    });
    /**
     * landlord info view
     * */
    let landlord_view = "#landlord-view-" + service;
    $(landlord_view).on("click", function () {
        //alert($(lead_view).data('id'));
        $("#view-name").text($(landlord_view).data("name"));
        $("#view-email").text($(landlord_view).data("email"));
        $("#view-phone").text($(landlord_view).data("phone"));
        $("#view-address").text($(landlord_view).data("address"));
        $("#view-city").text($(landlord_view).data("city"));
        $("#view-state").text($(landlord_view).data("state"));
        $("#view-postal-code").text($(landlord_view).data("postal-code"));
        $("#view-country").text($(landlord_view).data("country"));
        $("#view-gender").text($(landlord_view).data("gender"));
        let images = $(landlord_view).data("images");

        $(images).each(function (index, value) {
            $("#landlord-mykads-image").append(
                '<li><img src="/uploads/' + value.image + '" alt=""></li>'
            );
        });
    });
    /**
     * lead info view
     * */
    let lead_view = "#lead-view-" + service;
    $(lead_view).on("click", function () {
        //alert($(lead_view).data('id'));
        $("#view-first-name").text($(lead_view).data("first-name"));
        $("#view-last-name").text($(lead_view).data("last-name"));
        $("#view-phone").text($(lead_view).data("phone"));
        $("#view-email").text($(lead_view).data("email"));
        $("#view-city").text($(lead_view).data("city"));
        $("#view-address").text($(lead_view).data("address"));
        $("#view-state").text($(lead_view).data("state"));
        $("#view-country").text($(lead_view).data("country"));
        $("#view-postal-code").text($(lead_view).data("postal-code"));
        $("#view-inquires-property").text($(lead_view).data("property-title"));
        $("#view-landlord").text($(lead_view).data("landlord"));
    });
    /**
     * labor info view
     * */
    let labor_view = "#labor-view-" + service;
    $(labor_view).on("click", function () {
        $("#view-name").text($(labor_view).data("name"));
        $("#view-email").text($(labor_view).data("email"));
        $("#view-phone").text($(labor_view).data("phone"));
        $("#view-gender").text($(labor_view).data("gender"));
        $("#view-cost-type").text($(labor_view).data("salary-type"));
        $("#view-cost").text($(labor_view).data("salary"));
    });
    /**
     * tenant info view
     * */
    let tenant_view = "#tenant-view-" + service;
    $(tenant_view).on("click", function () {
        $("#view-id").text($(tenant_view).data("id"));
        $("#view-name").text($(tenant_view).data("name"));
        $("#view-property-name").text($(tenant_view).data("property-name"));
        $("#view-rent-price").text($(tenant_view).data("rent-price"));
        $("#view-lease-id").text($(tenant_view).data("lease-id"));
        $("#view-contact-relation").text(
            $(tenant_view).data("contact-relation")
        );
        $("#view-contact-name").text($(tenant_view).data("contact-name"));
        $("#view-contact-email").text($(tenant_view).data("contact-email"));
        $("#view-contact-phone").text($(tenant_view).data("contact-phone"));
        $("#view-vehicle-type").text($(tenant_view).data("vehicle-type"));
        $("#view-vehicle-registration").text(
            $(tenant_view).data("vehicle-registration")
        );
        $("#view-vehicle-brand").text($(tenant_view).data("vehicle-brand"));
    });
    /**
     * maintenance info view
     * */
    let maintenance_view = "#maintenance-view-" + service;

    $(maintenance_view).on("click", function () {
        $("#view-property").text($(maintenance_view).data("property"));
        $("#view-unit").text($(maintenance_view).data("unit"));
        $("#view-title").text($(maintenance_view).data("title"));
        $("#view-description").text($(maintenance_view).data("description"));
        let images = $(maintenance_view).data("images");

        $(images).each(function (index, value) {
            $("#maintenance-image").append(
                '<li><img src="/uploads/' + value.image + '" alt=""></li>'
            );
        });
    });
    /**
     * package info view
     * */
    let package_view = "#package-view-" + service;

    $(package_view).on("click", function () {
        $("#view-name").text($(package_view).data("name"));
        $("#view-duration").text($(package_view).data("package-time"));
        $("#view-description").text($(package_view).data("description"));
        $(package_view).data("number-property-unlimited") == 1
            ? $("#view-number-property").text("Unlimited")
            : $("#view-number-property").text(
                  $(package_view).data("number-property")
              );
        $(package_view).data("number-lead-unlimited") == 1
            ? $("#view-number-lead").text("Unlimited")
            : $("#view-number-lead").text($(package_view).data("number-lead"));
        $(package_view).data("number-tenant-unlimited") == 1
            ? $("#view-number-tenant").text("Unlimited")
            : $("#view-number-tenant").text(
                  $(package_view).data("number-tenant")
              );
        $(package_view).data("number-lease-unlimited") == 1
            ? $("#view-number-lease").text("Unlimited")
            : $("#view-number-lease").text(
                  $(package_view).data("number-lease")
              );
        $(package_view).data("number-maintenance-unlimited") == 1
            ? $("#view-number-maintenance").text("Unlimited")
            : $("#view-number-maintenance").text(
                  $(package_view).data("number-maintenance")
              );
        $("#view-price").text("RM " + $(package_view).data("price"));
        $("#image-li").remove();
        $("#package-image").append(
            '<li id="image-li"><img src="/uploads/' +
                $(package_view).data("image") +
                '" alt=""></li>'
        );
    });
    let landlord_payment = "#landlord-payment-view-" + service;
    $(landlord_payment).on("click", function () {
        $("#landlord-image").attr("src", "");
        $("#landlord-image").attr(
            "src",
            "/uploads/" + $(landlord_payment).data("landlord-image")
        );
        $("#landlord-assign").text($(landlord_payment).data("package-name"));
        $("#landlord-name").text($(landlord_payment).data("landlord-name"));
        $("#landlord-email").text($(landlord_payment).data("landlord-email"));
        $("#landlord-phone").text($(landlord_payment).data("landlord-phone"));
        $("#landlord-address").text(
            $(landlord_payment).data("landlord-address") +
                ", " +
                $(landlord_payment).data("landlord-city") +
                ", " +
                $(landlord_payment).data("landlord-state") +
                ", " +
                $(landlord_payment).data("landlord-country")
        );
        $("#payment-id").val($(landlord_payment).data("payment-id"));
        $("#payment-type").val($(landlord_payment).data("payment-type"));
        $("#package-name").val($(landlord_payment).data("package-name"));
        $("#tenant-phone").val($(landlord_payment).data("tenant-phone"));
        $("#package-amount").val($(landlord_payment).data("package-amount"));
        $("#started-date").val($(landlord_payment).data("started-date"));
        $("#ended-date").val($(landlord_payment).data("ended-date"));
        $("#payment-paid-status").val(
            $(landlord_payment).data("payment-paid-status")
        );
        $("#payment-transaction-id").val(
            $(landlord_payment).data("payment-transaction-id")
        );
    });
    let tenant_payment = "#tenant-payment-view-" + service;
    $(tenant_payment).on("click", function () {
        $("#landlord-image").attr("src", "");
        $("#landlord-image").attr(
            "src",
            "/uploads/" + $(tenant_payment).data("landlord-image")
        );
        $("#property-title").text($(tenant_payment).data("property-title"));
        $("#landlord-name").text($(tenant_payment).data("landlord-name"));
        $("#landlord-email").text($(tenant_payment).data("landlord-email"));
        $("#landlord-phone").text($(tenant_payment).data("landlord-phone"));
        $("#landlord-address").text(
            $(tenant_payment).data("landlord-address") +
                ", " +
                $(tenant_payment).data("landlord-city") +
                ", " +
                $(tenant_payment).data("landlord-state") +
                ", " +
                $(tenant_payment).data("landlord-country")
        );
        $("#payment-invoice-id").val(
            $(tenant_payment).data("payment-invoice-id")
        );
        $("#payment-bill-type").val(
            $(tenant_payment).data("payment-bill-type")
        );
        $("#tenant-name").val($(tenant_payment).data("tenant-name"));
        $("#tenant-phone").val($(tenant_payment).data("tenant-phone"));
        $("#tenant-amount").val($(tenant_payment).data("tenant-amount"));
        $("#payment-type").val($(tenant_payment).data("payment-type"));
        $("#payment-date").val($(tenant_payment).data("payment-date"));
        $("#payment-paid-status").val(
            $(tenant_payment).data("payment-paid-status")
        );
        $("#payment-transaction-id").val(
            $(tenant_payment).data("payment-transaction-id")
        );
    });
    let tenant_refund = "#tenant-refund-view-" + service;
    $(tenant_refund).on("click", function () {
        let refund_id = $(tenant_refund).data("id");
        $("#landlord-image").attr("src", "");
        $("#landlord-image").attr(
            "src",
            "/uploads/" + $(tenant_refund).data("landlord-image")
        );
        $("#property-title").text($(tenant_refund).data("property-title"));
        $("#landlord-name").text($(tenant_refund).data("landlord-name"));
        $("#landlord-email").text($(tenant_refund).data("landlord-email"));
        $("#landlord-phone").text($(tenant_refund).data("landlord-phone"));
        $("#landlord-address").text(
            $(tenant_refund).data("landlord-address") +
                ", " +
                $(tenant_refund).data("landlord-city") +
                ", " +
                $(tenant_refund).data("landlord-state") +
                ", " +
                $(tenant_refund).data("landlord-country")
        );
        $("#payment-invoice-id").val(
            $(tenant_refund).data("payment-invoice-id")
        );
        $("#payment-bill-type").val($(tenant_refund).data("payment-bill-type"));
        $("#tenant-name").val($(tenant_refund).data("tenant-name"));
        $("#tenant-phone").val($(tenant_refund).data("tenant-phone"));
        $("#tenant-amount").val($(tenant_refund).data("tenant-amount"));
        $("#payment-type").val($(tenant_refund).data("payment-type"));
        $("#payment-date").val($(tenant_refund).data("payment-date"));
        $("#payment-paid-status").val(
            $(tenant_refund).data("payment-paid-status")
        );
        $("#payment-transaction-id").val(
            $(tenant_refund).data("payment-transaction-id")
        );
        $('form[name="refundForm"]').attr("action", "");

        $('form[name="refundForm"]').on("submit", function () {
            $('form[name="refundForm"]').append(
                '<input type="hidden" name="_method" value="PUT">'
            );
            $('form[name="refundForm"]').attr(
                "action",
                "/admin/payment/tenant/refund/approve/" + refund_id
            );
        });
    });
});

// Labor edit start
$(".labor-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var name = $(this).data("name");
    var email = $(this).data("email");
    var phone = $(this).data("phone");
    var gender = $(this).data("gender");
    var salary = $(this).data("salary");

    $("#name").val(name);
    $("#email").val(email);
    $("#gender").val(gender);
    if (phone) {
        $("#phone").val(phone.mobile_no ?? "");
        $("#tenant_phone_codes").val(phone.mobile_code ?? "");
    } else {
        $("#phone").val("");
        $("#tenant_phone_codes").val("");
    }

    $("#salary").val(salary);
    $(".editLaborForm").attr("action", url);
});

// Labor edit End

$(".edit-item").each(function () {
    var container = $(this);
    var service = container.data("id");
    let labor_edit = "#labor-edit-" + service;
    $(labor_edit).on("click", function () {
        let laborId = $(labor_edit).data("id");
        $("#name").val($(labor_edit).data("name"));
        $("#email").val($(labor_edit).data("email"));
        $("#phone").val($(labor_edit).data("phone"));
        $("#gender").val($(labor_edit).data("gender"));
        $(
            "input[name=salary_type][value='" +
                $(labor_edit).data("salary-type") +
                "']"
        ).prop("checked", true);
        $("#salary").val($(labor_edit).data("salary"));
        let laborform = $("#laborForm").attr("action", "");
        var editactionroute = "/landlord/labor/update";
        $("#laborForm").on("submit", function () {
            $("#laborForm").append(
                '<input type="hidden" name="_method" value="PATCH" class="ajaxform_instant_reload">'
            );
            $("#laborForm").attr("action", editactionroute + "/" + laborId);
        });
    });
    //payment gateway edit
    let payment_gateway_edit = "#payment-gateway-" + service;
    $("#image-aria").hide();
    $(payment_gateway_edit).on("click", function () {
        let payment_gateway_id = $(payment_gateway_edit).data("id");
        $("#name").val($(payment_gateway_edit).data("name"));
        $("#url").val($(payment_gateway_edit).data("url"));
        $("#api_key").val($(payment_gateway_edit).data("api-key"));
        $("#collection_id").val($(payment_gateway_edit).data("collection-id"));
        $("#image-aria")
            .show()
            .html(
                '<div className="upload__img-box"><div data-number="0" data-file="1.png" className="img-bg"><img src="/uploads/' +
                    $(payment_gateway_edit).data("image") +
                    '" alt=""></div></div>'
            );
        //$('#upload__img-box').empty().append(html);
        $('form[name="paymentGatewayForm"]').attr("action", "");

        $('form[name="paymentGatewayForm"]').on("submit", function () {
            $('form[name="paymentGatewayForm"]').append(
                '<input type="hidden" name="_method" value="PUT">'
            );
            $('form[name="paymentGatewayForm"]').attr(
                "action",
                "/admin/payment-gateway/update/" + payment_gateway_id
            );
        });
    });
    let withdraw_method_edit = "#withdraw-method-" + service;
    $(withdraw_method_edit).on("click", function () {
        let withdraw_method_id = $(withdraw_method_edit).data("id");
        $("#account_holder").val(
            $(withdraw_method_edit).data("account-holder")
        );
        $("#currency").val($(withdraw_method_edit).data("currency"));
        $("#bank_code").val($(withdraw_method_edit).data("bank-code"));
        $("#bank_name").val($(withdraw_method_edit).data("bank-name"));
        $("#account_no").val($(withdraw_method_edit).data("account-no"));
        $("#status").val($(withdraw_method_edit).data("status"));
        $("#offline_status").prop(
            "checked",
            $(withdraw_method_edit).data("offline-status") == 1 ? true : false
        );

        $('form[name="withdrawMethodForm"]').attr("action", "");

        $('form[name="withdrawMethodForm"]').on("submit", function () {
            $('form[name="withdrawMethodForm"]').append(
                '<input type="hidden" name="_method" value="PUT">'
            );
            $('form[name="withdrawMethodForm"]').attr(
                "action",
                "/landlord/withdraw-method/update/" + withdraw_method_id
            );
        });
    });
    let user_role_edit = "#user-role-" + service;
    $(user_role_edit).on("click", function () {
        let user_role_id = $(user_role_edit).data("id");
        let permission = JSON.stringify($(user_role_edit).data("permission"));
        $("#user_id").val($(user_role_edit).data("user-id"));
        $("#dashboard").prop(
            "checked",
            permission.includes("dashboard") ? true : false
        );
        $("#property").prop(
            "checked",
            permission.includes("property") ? true : false
        );
        $("#all-leads").prop(
            "checked",
            permission.includes("all-leads") ? true : false
        );
        $("#export-leads").prop(
            "checked",
            permission.includes("export-leads") ? true : false
        );
        $("#landlord-list").prop(
            "checked",
            permission.includes("landlord-list") ? true : false
        );
        $("#tenant-list").prop(
            "checked",
            permission.includes("tenant-list") ? true : false
        );
        $("#subscription").prop(
            "checked",
            permission.includes("subscription") ? true : false
        );
        $("#landlords-payment").prop(
            "checked",
            permission.includes("landlords-payment") ? true : false
        );
        $("#tenants-payment").prop(
            "checked",
            permission.includes("tenants-payment") ? true : false
        );
        $("#landlords-withdrawal").prop(
            "checked",
            permission.includes("landlords-withdrawal") ? true : false
        );
        $("#refund-request").prop(
            "checked",
            permission.includes("refund-request") ? true : false
        );
        $("#commission-settings").prop(
            "checked",
            permission.includes("commission-settings") ? true : false
        );
        $("#payment-settings").prop(
            "checked",
            permission.includes("payment-settings") ? true : false
        );
        $("#banner-settings").prop(
            "checked",
            permission.includes("banner-settings") ? true : false
        );
        $("#user-roles").prop(
            "checked",
            permission.includes("user-roles") ? true : false
        );
        //alert($(withdraw_method_edit).data('user-id'))
        $('form[name="userRoleForm"]').attr("action", "");

        $('form[name="userRoleForm"]').on("submit", function () {
            $('form[name="userRoleForm"]').append(
                '<input type="hidden" name="_method" value="PUT">'
            );
            $('form[name="userRoleForm"]').attr(
                "action",
                "/admin/setting/user-role/update/" + user_role_id
            );
        });
        console.log($(user_role_edit).data("permission"));
    });

    /*$('#currency-convert-'+service).on('click',function () {
        var convertid = $('#currency-convert-'+service).data('id');

        $('#edit_currency_id').val($('#currency-convert-'+service).data('currency-id'));
        $('#edit_par_currency').val($('#currency-convert-'+service).data('par-currency'));
        $('#edit_coin').val($('#currency-convert-'+service).data('coin'));

        var faqForm =$('#convertFrom').attr('action','');
        var editactionroute = "/currency-convert/update"
        $('#convertFrom').on('submit',function () {
            /!*$('#convertFrom').append('<input type="hidden" name="_method" value="PUT">')*!/
            $('#convertFrom').attr('action', editactionroute+'/'+convertid);
        })

    })*/
});
$(".delete-item").each(function () {
    var delete_container = $(this);
    var delete_service = delete_container.data("id");
    let delete_item = "#delete-item-" + delete_service;
    $(delete_item).on("click", function () {
        let url = $(this).attr("href");
        $("#deleteForm").on("submit", function () {
            $("#deleteForm").attr("action", url);
        });
    });
});
/**
 *  package  form js
 *  */

/**
 *  custom css
 *  */
$(".image-size-alert").css({
    color: "red",
    "font-size": "10px",
    "margin-left": "5px",
});

$(document).on("click", ".add-new-item", function () {
    let html = `
    <div class="row row-items">
        <div class="col-sm-5">
            <label for="">${window.trans.label}</label>
            <input type="text" name="manual_data[label][]" value="" class="form-control" placeholder="${window.trans.enter_label_name}">
        </div>
        <div class="col-sm-5">
            <label for="">${window.trans.select_required_optional}</label>
            <select class="form-control" required name="manual_data[is_required][]">
                <option value="1">${window.trans.required}</option>
                <option value="0">${window.trans.optional}</option>
            </select>
        </div>
        <div class="col-sm-2 align-self-center mt-3">
            <button type="button" class="btn text-danger trash remove-btn-features"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    `;
    $(".manual-rows").append(html);
});

$(document).on("click", ".remove-btn-features", function () {
    var $row = $(this).closest(".row-items");
    $row.remove();
});

//Dynamic Tags Setting Start
$(document)
    .off("click", ".add-new-tag")
    .on("click", ".add-new-tag", function () {
        let html = `
    <div class="col-md-6">
        <div class="row row-items">
            <div class="col-sm-10">
                <label for="">${window.trans.tags}</label>
                <input type="text" name="tags[]" class="form-control" required
                    placeholder="${window.trans.enter_tags_name}">
            </div>
            <div class="col-sm-2 align-self-center mt-3">
                <button type="button" class="mb-lg-1 btn text-danger trash remove-btn-features"
                    onclick="removeDynamicField(this)"><i
                        class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    `;
        $(".manual-rows .single-tags").append(html);
    });

    $(document).on("click", ".remove-btn-features", function () {
        var $row = $(this).closest(".row-items");
        $row.remove();
});

//Dynamic Tags Setting End

// function previewImage(event, previewId) {
//     const file = event.target.files[0];
//     const preview = document.getElementById(previewId);
//     const uploadBox = preview.parentElement;
//     const placeholder = uploadBox.querySelector(".upload-placeholder");
//     const removeBtn = uploadBox.parentElement.nextElementSibling; // .remove-btn

//     if (file) {
//         const reader = new FileReader();
//         reader.onload = function () {
//             preview.src = reader.result;
//             preview.classList.remove("hidden");
//             placeholder.classList.add("hidden");
//             removeBtn.classList.remove("d-none");
//         };
//         reader.readAsDataURL(file);
//     }
// }

// function removeImage(previewId) {
//     const preview = document.getElementById(previewId);
//     const uploadBox = preview.parentElement;
//     const placeholder = uploadBox.querySelector(".upload-placeholder");
//     const removeBtn = uploadBox.parentElement.nextElementSibling; // .remove-btn
//     const input = uploadBox.parentElement.querySelector('input[type="file"]');

//     preview.src = "";
//     preview.classList.add("hidden");
//     placeholder.classList.remove("hidden");
//     removeBtn.classList.add("d-none");
//     input.value = "";
// }

document.addEventListener("DOMContentLoaded", function () {
    // Preview handler
    document.querySelectorAll(".image-input").forEach(function (input) {
        input.addEventListener("change", function (event) {
            const file = event.target.files[0];
            const previewId = input.dataset.previewId;
            const preview = document.getElementById(previewId);
            const uploadBox = preview.parentElement;
            const placeholder = uploadBox.querySelector(".upload-placeholder");
            const removeBtn = input
                .closest(".upload-field-profile")
                .querySelector(".remove-btn");

            if (file) {
                const reader = new FileReader();
                reader.onload = function () {
                    preview.src = reader.result;
                    preview.classList.remove("hidden");
                    placeholder.classList.add("hidden");
                    removeBtn.classList.remove("d-none");
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Remove handler
    document.querySelectorAll(".remove-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const field = btn.closest(".upload-field-profile");
            const input = field.querySelector(".image-input");
            const previewId = input.dataset.previewId;
            const preview = document.getElementById(previewId);
            const placeholder = field.querySelector(".upload-placeholder");

            preview.src = "";
            preview.classList.add("hidden");
            placeholder.classList.remove("hidden");
            btn.classList.add("d-none");
            input.value = "";
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Preview Image
    document.querySelectorAll(".image-input").forEach(function (input) {
        input.addEventListener("change", function (event) {
            const file = event.target.files[0];
            const previewId = input.dataset.previewId;
            const preview = document.getElementById(previewId);
            const uploadBox = preview.parentElement;
            const placeholder = uploadBox.querySelector(".upload-placeholder");
            const removeBtn = input
                .closest(".upload-field")
                .querySelector(".remove-btn"); // ✅ FIXED

            if (file) {
                const reader = new FileReader();
                reader.onload = function () {
                    preview.src = reader.result;
                    preview.classList.remove("hidden");
                    placeholder.classList.add("hidden");
                    removeBtn.classList.remove("d-none"); // ✅ SHOWS NOW
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Remove Image
    document.querySelectorAll(".remove-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const uploadField = btn.closest(".upload-field");
            const input = uploadField.querySelector(".image-input");
            const previewId = input.dataset.previewId;
            const preview = document.getElementById(previewId);
            const placeholder = uploadField.querySelector(
                ".upload-placeholder"
            );

            preview.src = "";
            preview.classList.add("hidden");
            placeholder.classList.remove("hidden");
            btn.classList.add("d-none");
            input.value = "";
        });
    });
});

$(document).ready(function () {
    let vehicleCounter = 1;

    $(".add-Vehicles-container").on('click', function (e) {
        e.preventDefault();

        let newVehicleFields = `
                <div class="col-lg-4 mt-4">
                    <label>${window.trans.vehicles_type}</label>
                    <select class="form-control" name="vehicles_info[${vehicleCounter}][type]">
                        <option value="">${window.trans.car_motorcycle_etc}</option>
                        <option value="Car">${window.trans.car}</option>
                        <option value="Motorcycles">${window.trans.motorcycles}</option>
                        <option value="Lorry">${window.trans.lorry}</option>
                    </select>
                </div>

                <div class="col-lg-4 mt-4">
                    <label>${window.trans.registration_no}</label>
                    <input type="number" class="form-control" name="vehicles_info[${vehicleCounter}][reg_no]" placeholder="${window.trans.enter_registration_no}">
                </div>

                <div class="col-lg-4 mt-4">
                    <label>${window.trans.vehicles_brand}</label>
                    <input type="text" class="form-control" name="vehicles_info[${vehicleCounter}][brand]" placeholder="${window.trans.enter_vehicles_brand}">
                </div>
        `;

        $("#vehicles-container").append(newVehicleFields);

        vehicleCounter++;
    });
});

// Chat file ----------------------------->
function openFileInput() {
    document.getElementById("landlordFile1").click();
    document.getElementById("landlordFile1").onchange = function (event) {
        const selectedFile = event.target.files[0];
    };
}

function adminFileInput() {
    document.getElementById("admindFile2").click();
    document.getElementById("admindFile2").onchange = function (event) {
        const selectedFile = event.target.files[0];
    };
}

/** Plan form page start */
function handleUnlimitedStatus(checkboxSelector, inputSelector) {
    $(document).on("change", checkboxSelector, function () {
        if ($(this).prop("checked")) {
            $(inputSelector).val("").prop("disabled", true);
        } else {
            $(inputSelector).prop("disabled", false);
        }
    });

    $(document).on("input", inputSelector, function () {
        if ($(this).val() !== "") {
            $(checkboxSelector).prop("checked", false);
        }
    });
}

handleUnlimitedStatus("#property_unlimited_status", "#number_of_property");
handleUnlimitedStatus("#tenant_unlimited_status", "#number_of_tenant");
handleUnlimitedStatus("#labor_unlimited_status", "#number_of_labor");

/** Plan form page end */

// Tab switching logic
const tabs = document.querySelectorAll(".custom-tab");
const tabPanes = document.querySelectorAll(".tab-pane");

tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        tabs.forEach((t) => t.classList.remove("active"));
        tabPanes.forEach((p) => p.classList.remove("show", "active"));
        tab.classList.add("active");
        const target = document.querySelector(
            tab.getAttribute("data-bs-target")
        );
        target.classList.add("show", "active");
    });
});


// Multidelete Start
function updateSelectedCount() {
    var selectedCount = $(".delete-checkbox-item:checked").length;
    $(".selected-count").text(selectedCount);

    if (selectedCount > 0) {
        $(".delete-show").removeClass("d-none");
    } else {
        $(".delete-show").addClass("d-none");
    }
}

$(".select-all-delete").on("click", function () {
    $(".delete-checkbox-item").prop("checked", this.checked);
    updateSelectedCount();
});

$(document).on("change", ".delete-checkbox-item", function () {
    updateSelectedCount();
});

$(".trigger-modal").on("click", function () {
    var dynamicUrl = $(this).data("url");

    $("#dynamic-delete-form").attr("action", dynamicUrl);

    var ids = $(".delete-checkbox-item:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    if (ids.length === 0) {
        alert("Please select at least one item.");
        return;
    }

    var form = $("#dynamic-delete-form");
    form.find("input[name='ids[]']").remove();
    ids.forEach(function (id) {
        form.append('<input type="hidden" name="ids[]" value="' + id + '">');
    });
});

$(".create-all-delete").on("click", function (event) {
    event.preventDefault();

    var form = $("#dynamic-delete-form");
    form.submit();
});
// Multidelete End

// Image preview start
$(document).on("change", ".file-input-change", function () {
    let prevId = $(this).data("id");
    newPreviewImage(this, prevId);
});

function newPreviewImage(input, prevId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $("#" + prevId).attr("src", e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Image preview end

// Add dynamic image start
$(document).ready(function () {
    function addNewImageField() {
        const html = `
            <div class="image-item-wrapper">
                <div class="add-photo-items cat_group_1">
                    <div class="remove-img-btn cross-btn" title="Remove Image">
                        <img class="cross" src="${window.location.origin}/backend/assets/img/icon/cross.svg" alt="Delete">
                    </div>
                    <img class="image-preview mt-4" src="${window.location.origin}/backend/assets/img/property-img/add-photo/camera.png" alt="Preview">
                    <label>
                        <input type="file" class="d-none reset_val file-input-upload cat_group_1" name="cover_image[]" accept="image/*">
                        <div class="images-upload-btn">Upload</div>
                    </label>
                </div>
            </div>
        `;
        $("#dynamic-cover-image-area").append(html);
    }

    $(document).on("change", ".file-input-upload", function () {
        if (this.files && this.files.length > 0) {
            const $wrapper = $(this).closest(".image-item-wrapper");
            const isLast = $wrapper.is(":last-child");

            if (isLast) {
                addNewImageField();
            }
        }
    });

    $(document).on("click", ".cross-btn", function () {
        $(this).closest(".image-item-wrapper").remove();
    });
});

// Add dynamic image end

$(document).ready(function () {
    function validatePercentageValue() {
        const $input = $('input[name="amount"]');
        const $typeSelect = $('select[name="type"]');
        const value = parseFloat($input.val());
        const type = $typeSelect.val();

        if (type === "percentage" && value > 99) {
            $input.val("");
            toastr.error("Percentage cannot be more than 99%");
        }
    }

    // On type change
    $('select[name="type"]').on("change", function () {
        validatePercentageValue();
    });

    // On amount input change
    $('input[name="amount"]').on("input", function () {
        validatePercentageValue();
    });
});

$(document).on("click", ".chat-profile", function () {
    const $this = $(this);
    const route = $this.data("route");

    $.get(route, function (res) {
        $this.find("span").text("0");
    });
});

$(document).ready(function() {
    $('.summernote').summernote({
        height: 300,
    });
});


$(document).ready(function() {
    const $propertySelect = $('#propertySelect');
    const $wrapper = $('#unitContainerWrapper');
    const routeUrl = $('#getUnitData').val();

    $propertySelect.on('change', function() {
        const propertyId = $(this).val();

        // Remove any existing unit container entirely
        $wrapper.empty();

        if (!propertyId) return;

        $.ajax({
            url: routeUrl,
            method: 'GET',
            data: { property_id: propertyId },
            beforeSend: function() {
                // Optional loading message
                $wrapper.html('<p>Loading units...</p>');
            },
            success: function(units) {
                if (units.length > 0) {
                    let html = `
                        <div class="col-lg-12 mt-3">
                            <label class="form-label">Unit</label>
                            <select class="form-control" id="unitSelect" name="unit_id" required>
                                <option value="">Select Unit</option>`;
                    $.each(units, function(index, unit) {
                        html += `<option value="${unit.id}">${unit.unit_name}</option>`;
                    });
                    html += `</select></div>`;

                    $wrapper.html(html);
                } else {
                    $wrapper.empty();
                }
            },
            error: function() {
                $wrapper.empty();
            }
        });
    });
});

// Language Flag Icon Preview Start
$(document).ready(function () {

    $('#addLanguageModal').on('shown.bs.modal', function () {

        let uploadBox = $('#languageUploadBox');
        let fileInput = $('#languageFileInput');
        let previewImage = $('#languagePreviewImage');
        let uploadContent = $('#languageUploadContent');

        if (!uploadBox.length) return;

        uploadBox.off('click').on('click', function () {
            fileInput[0].click();
        });

        fileInput.off('change').on('change', function () {
            if (this.files[0]) preview(this.files[0]);
        });

        // Drag over
        uploadBox.off('dragover').on('dragover', function (e) {
            e.preventDefault();
            uploadBox.addClass('language-dragover');
        });

        // Drag leave
        uploadBox.off('dragleave').on('dragleave', function () {
            uploadBox.removeClass('language-dragover');
        });

        // Drop
        uploadBox.off('drop').on('drop', function (e) {

            e.preventDefault();
            uploadBox.removeClass('language-dragover');

            let file = e.originalEvent.dataTransfer.files[0];
            if (!file) return;

            let dt = new DataTransfer();
            dt.items.add(file);
            fileInput[0].files = dt.files;

            preview(file);
        });

        function preview(file) {

            if (!file.type.startsWith('image/')) return;

            let reader = new FileReader();

            reader.onload = function (e) {
                previewImage.attr('src', e.target.result).show();
                uploadContent.hide();
            };

            reader.readAsDataURL(file);
        }

    });

    // Edit Language Modal
    let editUploadBox = $('#editLanguageUploadBox');
    let editFileInput = $('#editLanguageFileInput');
    let editPreviewImage = $('#editLanguagePreviewImage');
    let editUploadContent = $('#editLanguageUploadContent');

    editUploadBox.on('click', function () {
        editFileInput[0].click();
    });

    editFileInput.on('change', function () {
        if (this.files[0]) previewEditFile(this.files[0]);
    });

    // Drag over
    editUploadBox.on('dragover', function (e) {
        e.preventDefault();
        editUploadBox.addClass('language-dragover');
    });

    // Drag leave
    editUploadBox.on('dragleave', function () {
        editUploadBox.removeClass('language-dragover');
    });

    // Drop
    editUploadBox.on('drop', function (e) {

        e.preventDefault();
        editUploadBox.removeClass('language-dragover');

        let file = e.originalEvent.dataTransfer.files[0];
        if (!file) return;

        let dt = new DataTransfer();
        dt.items.add(file);
        editFileInput[0].files = dt.files;

        previewEditFile(file);
    });

    function previewEditFile(file) {

        if (!file.type.startsWith('image/')) return;

        let reader = new FileReader();

        reader.onload = function (e) {
            editPreviewImage.attr('src', e.target.result).show();
            editUploadContent.hide();
        };

        reader.readAsDataURL(file);
    }

    function initSelect2(el, placeholder) {

        if (el.length === 0) return;

        if (el.hasClass('select2-hidden-accessible')) {
            el.select2('destroy');
        }

        el.select2({
            placeholder: placeholder,
        });
    }
});
// Language Flag Icon Preview End

// Update default language start
$(document).on('change', '.default-lang', function () {

    let checkbox = $(this);
    let url = checkbox.data('url');

    if (!checkbox.is(':checked')) {
        checkbox.prop('checked', true);
        toastr.warning(window.trans.at_least_one_default);
        return;
    }

    $('.default-lang').not(checkbox).prop('checked', false);

    $.ajax({
        url: url,
        type: "POST",
        data: {
            is_default: 1
        },
        success: function (res) {
            toastr.success(res.message);
        },
        error: function () {
            toastr.error('Something went wrong');
        }
    });
});
// Update default language end

// Language edit start
$(document).on("click", ".language-edit-btn", function () {
    var url = $(this).data("url");
    var name = $(this).data("name");
    var code = $(this).data("code");
    var order = $(this).data("order");
    var icon = $(this).data("icon");

    $("#name").val(name);
    $("#code").val(code);
    $("#order").val(order);

    if (icon) {
        $(".lang-icon").attr("src", icon).show();
        $("#editLanguageUploadContent").hide();
    } else {
        $(".lang-icon").hide();
        $("#editLanguageUploadContent").show();
    }

    $(".languageUpdateForm").attr("action", url);
});
// Language edit end

//  LANGUAGE START
const $addNewTranslationForm = $(".add_new_translation");
$addNewTranslationForm.initFormValidation();

$(document).on("submit", ".add_new_translation", function (event) {
    event.preventDefault();

    const $form = $(this);
    const $submitBtn = $form.find(".submit-btn");
    const originalBtnHtml = $submitBtn.html();

    // Stop if form is not valid
    if (!$addNewTranslationForm.valid()) {
        return;
    }

    $.ajax({
        type: "POST",
        url: $form.attr("action"),
        data: new FormData(this),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        beforeSend() {
            $submitBtn
                .html($savingLoader)
                .addClass("disabled")
                .attr("disabled", true);
        },
        success(res) {
            $submitBtn
                .html(originalBtnHtml)
                .removeClass("disabled")
                .attr("disabled", false);
            $("#addTraslationModal").modal("hide");
            Notify("success", null, res.message);

            let firstItem = parseInt($("#datatable").data("first-item")) || 1;
            let translation_rows = firstItem + $("#translation-rows tr").length;

            let key = $(".new_key").val();
            let value = $(".new_value").val();

            let newRow = `<tr>
                            <td>${translation_rows}</td>
                            <td class="text-start">${key}</td>
                            <td><input type="text" class="form-control value-input" name="translations[${key}]" id="value-${translation_rows}" value="${value}"></td>
                            <td>
                                <div class="d-flex align-items-center gap-2 justify-content-center">
                                    <button type="button" class="traslation-save" data-row="${translation_rows}" data-url="${$("#update-url").val()}" data-key="${key}">${window.trans.save}</button>
                                    <a class="traslation-delete" href="">
                                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.8408 14.4032C16.7817 15.3562 16.7348 16.1125 16.6387 16.7165C16.5401 17.336 16.3824 17.8519 16.0667 18.3032C15.778 18.716 15.4063 19.0645 14.9753 19.3263C14.5041 19.6125 13.9779 19.7377 13.3516 19.7975L8.63283 19.7975C8.00586 19.7375 7.47913 19.6121 7.00759 19.3253C6.57623 19.063 6.2044 18.714 5.91579 18.3004C5.6003 17.8484 5.44302 17.3318 5.3452 16.7114C5.24981 16.1064 5.20397 15.3491 5.14619 14.3947L4.64453 6.10938H17.355L16.8408 14.4032ZM9.07393 16.1556C8.75485 16.1556 8.49618 15.9004 8.49618 15.5853V11.0226C8.49618 10.7076 8.75485 10.4522 9.07393 10.4522C9.393 10.4522 9.65167 10.7076 9.65167 11.0226V15.5853C9.65167 15.9004 9.393 16.1556 9.07393 16.1556ZM13.5033 11.0226C13.5033 10.7076 13.2446 10.4522 12.9256 10.4522C12.6065 10.4522 12.3478 10.7076 12.3478 11.0226V15.5853C12.3478 15.9004 12.6065 16.1556 12.9256 16.1556C13.2446 16.1556 13.5033 15.9004 13.5033 15.5853V11.0226Z" fill="white"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12.1179 2.22642C12.5875 2.26236 13.0289 2.38496 13.4079 2.62681C13.6883 2.80567 13.883 3.02461 14.0493 3.26173C14.2035 3.48146 14.3588 3.75541 14.5348 4.06618L14.8895 4.69206H18.4778C18.9368 4.69206 19.3088 5.01038 19.3088 5.40305C19.3088 5.79571 18.9368 6.11403 18.4778 6.11403C13.4912 6.11403 8.50513 6.11403 3.51857 6.11403C3.05959 6.11403 2.6875 5.79571 2.6875 5.40305C2.6875 5.01038 3.05959 4.69206 3.51857 4.69206H7.18337L7.47907 4.13711C7.65073 3.81489 7.80191 3.53111 7.95377 3.3034C8.11758 3.05777 8.3115 2.83047 8.59523 2.64426C8.97894 2.39243 9.4292 2.26476 9.90939 2.22736C10.2709 2.19921 10.6353 2.20308 10.9982 2.20358C11.4227 2.20416 11.8043 2.20243 12.1179 2.22642ZM9.01006 4.69206H13.0423C12.8539 4.35976 12.7334 4.14913 12.6242 3.99352C12.4644 3.76577 12.2729 3.66594 11.9698 3.64274C11.7543 3.62625 11.4735 3.62559 11.0268 3.62559C10.5691 3.62559 10.281 3.62627 10.0601 3.64347C9.74916 3.66768 9.55535 3.77132 9.39745 4.00807C9.29399 4.16321 9.18135 4.37089 9.01006 4.69206Z" fill="white"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>`;

            $("#translation-rows").append(newRow);

            $form[0].reset();
        },
        error(xhr) {
            $submitBtn
                .html(originalBtnHtml)
                .removeClass("disabled")
                .attr("disabled", false);
            showInputErrors(xhr.responseJSON);
            Notify("error", xhr);
        },
    });
});

$(document).on("click", ".traslation-save", function () {
    let btn = $(this);
    let rowId = btn.data("row");
    let key = btn.data("key").trim();
    let value = $("#value-" + rowId)
        .val()
        .trim();
    let route = btn.data("url");

    const originalBtnHtml = btn.html();

    $.ajax({
        type: "PUT",
        url: route,
        data: {
            key: key,
            value: value,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend() {
            btn.html($savingLoader).addClass("disabled").attr("disabled", true);
        },
        success(res) {
            btn.html(originalBtnHtml)
                .removeClass("disabled")
                .attr("disabled", false);
            Notify("success", null, res.message);
        },
        error(xhr) {
            btn.html(originalBtnHtml)
                .removeClass("disabled")
                .attr("disabled", false);
            showInputErrors(xhr.responseJSON);
            Notify("error", xhr);
        },
    });
});

$(document).on("input", ".searchInput", function () {
    let btn = $(this);
    let route = btn.data("url");
    let search = btn.val();

    $.ajax({
        type: "GET",
        url: route,
        data: {
            search: search,
        },
        success(res) {
            $("#translate-data").html(res.data);
        },
        error(xhr) {
            showInputErrors(xhr.responseJSON);
            Notify("error", xhr);
        },
    });
});

$(document).on("click", ".traslation-delete", function (e) {
    const btn = $(this);
    const route = btn.data("url");
    const key = btn.data("key").trim();
    const $row = $(this).closest("tr");

    if (!key) return;

    $.ajax({
        url: route,
        type: "DELETE",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            key: key,
        },
        success: function (res) {
            toastr.success(res.message);
            $row.remove();
        },
        error: function (xhr) {
            Notify("error", xhr);
        },
    });
});

// LANGUAGE END

// Custom image preview start
$(document).ready(function () {
    $('.custom-image-box').each(function () {
        let box = $(this);
        let img = box.find('.custom-preview-image')[0];
        let content = box.find('.custom-image-content');

        function toggleImage() {
            if (!img.src || !img.complete || img.naturalWidth === 0) {
                $(img).addClass('d-none');
                content.removeClass('d-none');
            } else {
                $(img).removeClass('d-none');
                content.addClass('d-none');
            }
        }

        toggleImage();

        $(img).on('load', function () {
            toggleImage();
        });
    });

    $(document).on('change', '.preview-image-input', function () {
        let input = this;
        let previewId = $(this).data('id');
        let box = $(this).closest('.custom-image-box');

        if (input.files && input.files[0]) {
            let reader = new FileReader();

            reader.onload = function (e) {
                $(previewId)
                    .attr('src', e.target.result)
                    .removeClass('d-none');

                box.find('.custom-image-content').addClass('d-none');
            };

            reader.readAsDataURL(input.files[0]);
        }
    });
});
// Custom image preview end

function toggleExpiration() {
    if ($(".otp-status-on").is(":checked")) {
        $(".otp-expiration-field").show();
    } else {
        $(".otp-expiration-field").hide();
    }
}

toggleExpiration();

$('input[name="otp_status"]').on("change", function () {
    toggleExpiration();
});


$(document).on("click", "#openUserSignupTab", function (e) {
    e.preventDefault();
    var $otpTabTrigger = $("#otp-tab");
    if ($otpTabTrigger.length) {
        var tab = new bootstrap.Tab($otpTabTrigger[0]);
        tab.show();
    }
});
