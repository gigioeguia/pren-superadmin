const CSRF_TOKEN = $('meta[name="csrf-token"]').attr("content");
$.ajaxSetup({ headers: { "X-CSRF-TOKEN": CSRF_TOKEN } });
let $savingLoader =
    '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
    $ajaxform = $(".ajaxform");
$ajaxform.initFormValidation(),
    $(document).on("submit", ".ajaxform", function (e) {
        e.preventDefault();
        let t = $(this).find(".submit-btn"),
            a = t.html();
        $ajaxform.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    t.html($savingLoader).attr("disabled", !0);
                },
                success: function (e) {
                    t.html(a).attr("disabled", false);
                    Notify("success", null, e);

                    if (e.redirect) {
                        t.html(a).removeClass("disabled").attr("disabled", !1);
                        window.sessionStorage.hasPreviousMessage = true;
                        window.sessionStorage.previousMessage =
                            e.message ?? null;
                        if (e.secondary_redirect_url) {
                            window.open(e.secondary_redirect_url, "_blank");
                        }
                        location.href = e.redirect;
                    }
                },
                error: function (e) {
                    t.html(a).attr("disabled", !1), Notify("error", e);
                },
            });
    });

let $ajaxform_instant_reload = $(".ajaxform_instant_reload");
$ajaxform_instant_reload.initFormValidation(),
    $(document).on("submit", ".ajaxform_instant_reload", function (e) {
        e.preventDefault();
        let t = $(this).find(".submit-btn"),
            a = t.html();
        $ajaxform_instant_reload.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    t.html($savingLoader)
                        .addClass("disabled")
                        .attr("disabled", !0);
                },
                success: function (e) {
                    t.html(a).removeClass("disabled").attr("disabled", !1),
                        (window.sessionStorage.hasPreviousMessage = !0),
                        (window.sessionStorage.previousMessage =
                            e.message ?? null),
                        e.redirect && (location.href = e.redirect);
                },
                error: function (e) {
                    t.html(a).removeClass("disabled").attr("disabled", !1),
                        showInputErrors(e.responseJSON),
                        Notify("error", e);
                },
            });
    });

function fetchMessage(route) {
    $.ajax({
        type: "GET",
        url: route,
        success: function (response) {
            $("#restore-message").val("");
            $("#message-container").html(response);
        },
    });
}

let $ajaxform_reset_form = $(".ajaxform_reset_form");
function notification(e, t) {
    let a;
    (a =
        "success" == e
            ? "fa fa-check-circle"
            : "error" == e
                ? "fa fa-times-circle"
                : "fa fa-info-circle"),
        Lobibox.notify(e, {
            pauseDelayOnHover: !0,
            continueDelayOnInactiveTab: !1,
            icon: a,
            sound: !1,
            position: "top right",
            showClass: "zoomIn",
            hideClass: "zoomOut",
            size: "mini",
            rounded: !0,
            width: 250,
            height: "auto",
            delay: 2e3,
            msg: t,
        });
}
function ajaxSuccess(e, t) {
    e.redirect
        ? (e.message &&
            ((window.sessionStorage.hasPreviousMessage = !0),
                (window.sessionStorage.previousMessage = e.message ?? null)),
            (location.href = e.redirect))
        : e.message && Notify("success", e);
}
function clean(e) {
    return (e = (e = e.replace(/ /g, "-")).replace(
        /[^A-Za-z0-9\-]/,
        ""
    )).toLowerCase();
}
$ajaxform_reset_form.initFormValidation(),
    $(document).on("submit", ".ajaxform_reset_form", function (e) {
        e.preventDefault();
        let t = $(this),
            a = t.find(".submit-button"),
            s = a.html();
        $ajaxform_reset_form.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    a.html($savingLoader).attr("disabled", !0);
                },
                success: function (e) {
                    a.html(s).attr("disabled", !1),
                        t.trigger("reset"),
                        Notify("success", e);
                },
                error: function (e) {
                    a.html(s).attr("disabled", !1),
                        showInputErrors(e.responseJSON),
                        Notify("error", e);
                },
            });
    }),
    $(".init_form_validation").initFormValidation(),
    $(document).on("click", ".action-confirm", function (e) {
        e.preventDefault();
        let t = $(this),
            a = t.data("title") ?? "Heads Up!",
            s = t.data("type") ?? "DELETE",
            n = t.data("icon") ?? "warning",
            r = t.data("content") ?? "Are you sure to delete?",
            o = t.attr("href") ?? t.data("action"),
            i = t.html();
        swal({ title: a, text: r, icon: n, buttons: !0, dangerMode: !0 }).then(
            (e) => {
                if (!e) return 0;
                $.ajax({
                    url: o,
                    data: { _token: CSRF_TOKEN },
                    type: s,
                    beforeSend: function () {
                        t.html($savingLoader)
                            .addClass("disabled")
                            .attr("disabled", !0);
                    },
                    success: function (e) {
                        t.html(i).removeClass("disabled").attr("disabled", !1),
                            ajaxSuccess(e, t);
                    },
                    error: function (e) {
                        t.html(i).removeClass("disabled").attr("disabled", !1),
                            Notify(e);
                    },
                });
            }
        );
    }),
    $(document).on("click", ".order-action", function (e) {
        e.preventDefault();
        let t = $(this),
            a = t.data("id"),
            s = t.data("status"),
            n = t.data("content") ?? "Are you sure?",
            r = t.attr("href") ?? t.data("action"),
            o = t.html();
        swal({
            title: "Are you sure?",
            text: n,
            icon: "warning",
            buttons: !0,
            dangerMode: !0,
        }).then((e) => {
            if (!e) return 0;
            $.ajax({
                url: r,
                data: { id: a, status: s, _token: CSRF_TOKEN },
                type: "GET",
                beforeSend: function () {
                    t.html($savingLoader)
                        .addClass("disabled")
                        .attr("disabled", !0);
                },
                success: function (e) {
                    t.html(o).removeClass("disabled").attr("disabled", !1),
                        ajaxSuccess(e, t);
                },
                error: function (e) {
                    t.html(o).removeClass("disabled").attr("disabled", !1),
                        Notify(e);
                },
            });
        });
    });

/**  filter all from start */
$(".filter-form").on("input", function (e) {
    e.preventDefault();

    var table = $(this).attr("table");
    $.ajax({
        type: "POST",
        url: $(this).attr("action"),
        data: new FormData(this),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        success: function (res) {
            $(table).html(res.data);
        },
    });
});

/**  filter all from  end */


$('.filter-form-lang').on('input', function (e) {
    e.preventDefault();

    let $form = $(this);
    let table = $form.attr('table');
    let method = $form.attr('method') || "GET";
    let url = $form.attr('action') + '?' + $form.serialize();

    $.ajax({
        type: method,
        url: url,
        dataType: "json",
        success: function (res) {
            $(table).html(res.data);
        }
    });
});


$(document).on('input', '.search-input', function () {
    let url = $(this).data('url');
    let search = $(this).val();

    $.ajax({
        type: "GET",
        url: url,
        data: {
            search: search
        },
        success: function (res) {
            $('#translate-data').html(res.data);
        }
    });
});


/**  filter from for expense start **/
$(".filter-form-expense").on("input", function (e) {
    e.preventDefault();

    var table = $(this).attr("table");
    $.ajax({
        type: "POST",
        url: $(this).attr("action"),
        data: new FormData(this),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        success: function (res) {
            // Update the table with the filtered data
            $(table).html(res.data);

            // Update the total expense value
            $("#total_expense").text(res.total_expense);
        },
    });
});
/**  filter from for income end **/

/**  filter from for income start **/
$(".filter-form-income").on("input", function (e) {
    e.preventDefault();

    var table = $(this).attr("table");
    $.ajax({
        type: "POST",
        url: $(this).attr("action"),
        data: new FormData(this),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        success: function (res) {
            // Update the table with the filtered data
            $(table).html(res.data);

            // Update the total income value
            $("#total_income").text(res.total_income);
        },
    });
});
/**  filter from for income end **/

//PREVIEW IMAGE
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            var inputId = $(input).attr("id");

            // Select the image element based on the input's ID
            var imageElement = $("img.product-img").filter(function () {
                return $(this).closest("label").attr("for") === inputId;
            });
            imageElement.attr("src", e.target.result);
            imageElement.hide().fadeIn(650);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Status button Change
$(".change-text").change(function () {
    var $dynamicText = $(this).closest(".form-control").find(".dynamic-text");

    if (this.checked) {
        $dynamicText.text("Active");
    } else {
        $dynamicText.text("Deactive");
    }
});

// Status button Change
$(".cnge-text").change(function () {
    var $test = $(this).closest(".form-control").find(".is-live-text");

    if (this.checked) {
        $test.text("Yes");
    } else {
        $test.text("No");
    }
});

/** STATUS CHANGE */
$(".status").on("change", function () {
    var checkbox = $(this);
    var status = checkbox.prop("checked") ? 1 : 0;
    var url = checkbox.data("url");

    $.ajax({
        url: url,
        type: "POST",
        data: {
            status: status,
        },
        success: function (response) {
            if (status === 1) {
                toastr.success(response.message + " status published");
            } else {
                toastr.success(response.message + " status unpublished");
            }
        },
        error: function (xhr, status, error) {
            console.log(error);
            toastr.error("Something Went Wrong");
        },
    });
});

/** DELETE ACTION */
$(document).on("click", ".delete-confirm", function (e) {
    e.preventDefault();
    let t = $(this),
        o = t.attr("href") ?? t.data("action"),
        i = t.html();

    // Create modal dynamically
    let confirmationModal = `
        <div class="modal fade" id="delete-confirmation-modal" tabindex="-1" aria-labelledby="delete-confirmation-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="text-end">
                        <button type="button" class="btn-close m-3 mb-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="delete-modal">
                            <h5>Are You Sure?</h5>
                            <p>You won't be able to revert this!</p>
                        </div>
                        <div class="button-group">
                            <button class="btn reset-btn" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn theme-btn delete-confirmation-button">Yes, Delete It!</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $("body").append(confirmationModal); // Append modal to the body
    $("#delete-confirmation-modal").modal("show");

    // handle dynamic modal
    $(".delete-confirmation-button").on("click", function () {
        $.ajax({
            url: o,
            data: { _token: CSRF_TOKEN },
            type: "DELETE",
            beforeSend: function () {
                t.html($savingLoader)
                    .addClass("disabled")
                    .attr("disabled", true);
            },
            success: function (e) {
                t.html(i).removeClass("disabled").attr("disabled", false);
                ajaxSuccess(e, t);
            },
            error: function (e) {
                t.html(i).removeClass("disabled").attr("disabled", false);
                Notify(e);
            },
        });

        // Hide and remove modal
        $("#delete-confirmation-modal").modal("hide");
        $("#delete-confirmation-modal").remove();
    });
});

$(document).ready(function () {
    /** CATEGORY VIEW */
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#category_view_" + service).on("click", function () {
            let imageSrc = $("#category_view_" + service).data("image");
            $("#category_view_image").attr("src", imageSrc);
            $("#category_view_name").text(
                $("#category_view_" + service).data("name")
            );
            $("#category_view_status").text(
                $("#category_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    /** SUGGESTION VIEW */
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#suggestion_view_" + service).on("click", function () {
            $("#suggestion_view_suggestion").text(
                $("#suggestion_view_" + service).data("suggestions")
            );
            $("#suggestion_view_category").text(
                $("#suggestion_view_" + service).data("category")
            );
            $("#suggestion_view_status").text(
                $("#suggestion_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    /** fAQ VIEW */
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#faqs_view_" + service).on("click", function () {
            $("#faqs_view_question").text(
                $("#faqs_view_" + service).data("question")
            );
            $("#faqs_view_answer").text(
                $("#faqs_view_" + service).data("answer")
            );
            $("#faqs_view_status").text(
                $("#faqs_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    /** Subscription Plan Start */

    // Listen for input changes and update feature values accordingly
    $("#words_limit").on("input", function () {
        $("#word_feature").val($("#words_limit").val() + " Word Limit");
    });

    $("#images_limit").on("input", function () {
        $("#image_feature").val($("#images_limit").val() + " images Limit");
    });

    //View Modal
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#plan_view_" + service).on("click", function () {
            $("#plan_view_title").text(
                $("#plan_view_" + service).data("title")
            );
            $("#plan_view_subtitle").text(
                $("#plan_view_" + service).data("subtitle")
            );
            $("#plan_view_price").text(
                $("#plan_view_" + service).data("price")
            );
            $("#plan_view_duration").text(
                $("#plan_view_" + service).data("duration")
            );
            $("#plan_view_status").text(
                $("#plan_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    /** Subscription Plan End */

    /** Buy/Earn Credits Start */
    // view modal
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#credits_earning_" + service).on("click", function () {
            $("#credits_earning_view_user").text(
                $("#credits_earning_" + service).data("user")
            );
            $("#credits_earning_view_platform").text(
                $("#credits_earning_" + service).data("platform")
            );
            $("#credits_earning_view_credits").text(
                $("#credits_earning_" + service).data("credits")
            );
            $("#credits_earning_view_price").text(
                $("#credits_earning_" + service).data("price")
            );
            $("#credits_earning_view_created_at").text(
                $("#credits_earning_" + service).data("created_at")
            );
        });
    });
    /** Buy/Earn Credits End */

    /** Users Start */
    //View Modal
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#user_view_" + service).on("click", function () {
            let imageSrc = $("#user_view_" + service).data("image");
            $("#user_view_image").attr("src", imageSrc);
            $("#user_view_created_at").text(
                $("#user_view_" + service).data("created_at")
            );
            $("#user_view_name").text($("#user_view_" + service).data("name"));
            $("#user_view_email").text(
                $("#user_view_" + service).data("email")
            );
            $("#user_view_plan").text($("#user_view_" + service).data("plan"));
            $("#user_view_status").text(
                $("#user_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });
    /** Users End */

    /** Gateway */
    //View Modal
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#gateway_view_" + service).on("click", function () {
            let imageSrc = $("#gateway_view_" + service).data("logo");
            $("#gateway_view_logo").attr("src", imageSrc);

            $("#gateway_view_name").text(
                $("#gateway_view_" + service).data("name")
            );
            $("#gateway_view_client_id").text(
                $("#gateway_view_" + service).data("client_id")
            );
            $("#gateway_view_charge").text(
                $("#gateway_view_" + service).data("charge")
            );
            $("#gateway_view_is_live").text(
                $("#gateway_view_" + service).data("is_live") == 1
                    ? "Yes"
                    : "No"
            );
            $("#gateway_view_status").text(
                $("#gateway_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    /** API KEYS VIEW */
    $(".view-btn").each(function () {
        let container = $(this);
        let service = container.data("id");
        $("#api_key_view_" + service).on("click", function () {
            $("#api_view_key").text($("#api_key_view_" + service).data("key"));
            $("#api_view_title").text(
                $("#api_key_view_" + service).data("title")
            );
            $("#api_view_status").text(
                $("#api_key_view_" + service).data("status") == 1
                    ? "Active"
                    : "Deactive"
            );
        });
    });

    $(document).ready(function () {
        $(".searchInput").on("input", function () {
            const searchText = $(this).val();
            const url = $(this).attr("action");
            $.ajax({
                url: url,
                type: "GET",
                data: { search: searchText },
                success: function (response) {
                    $(".searchResults").html(response.data);
                },
            });
        });
    });
});

/** CHECKBOX FOR DELETE ALL */
$(document).ready(function () {
    // Select all checkboxes when the checkbox in the header is clicked
    $(".selectAllCheckbox").on("click", function () {
        $(".checkbox-item").prop("checked", this.checked);
    });

    // Perform the delete action for selected elements when the delete icon is clicked
    $(".delete-selected").on("click", function (e) {
        var checkedCheckboxes = $(".checkbox-item:checked");
        if (checkedCheckboxes.length === 0) {
            toastr.error(
                "No items selected. Please select at least one item to delete."
            );
        } else {
            $("#multi-delete-modal").modal("show");
        }
    });

    $(".multi-delete-btn").on("click", function () {
        var ids = $(".checkbox-item:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        let submitButton = $(this);
        let originalButtonText = submitButton.html();
        let del_url = $(".checkbox-item").data("url");

        $.ajax({
            type: "POST",
            url: del_url,
            data: {
                ids,
            },
            dataType: "json",
            beforeSend: function () {
                submitButton.html($savingLoader).attr("disabled", true);
            },
            success: function (res) {
                submitButton.html(originalButtonText).attr("disabled", false);
                window.sessionStorage.hasPreviousMessage = true;
                window.sessionStorage.previousMessage = res.message ?? null;
                res.redirect && (location.href = res.redirect);
            },
            error: function (xhr) {
                submitButton.html(originalButtonText).attr("disabled", false);
                Notify("error", xhr);
            },
        });
    });
});

/** system setting start */
// Initial label text
var initialLabelText = $("#mail-driver-type-select option:selected").val();

$("#mail-driver-type-select").on("change", function () {
    var selectedOptionValue = $(this).val();
    $("#mail-driver-label").text(selectedOptionValue);
});

$("#mail-driver-label").text(initialLabelText);

/** system setting end */

$(".reject-btn").on("click", function () {
    var url = $(this).data("url");
    $(".rejectForm").attr("action", url);
});

$(".approve-btn").on("click", function () {
    var url = $(this).data("url");
    $(".approveForm").attr("action", url);
});

/** STATUS CHANGE FOR PROPERTY */

$(document).ready(function () {
    $(".property-status").each(function () {
        var checkbox = $(this);
        var currentStatus = checkbox.data("status");

        if (currentStatus !== 1 && currentStatus !== 2) {
            checkbox.prop("unchecked", true);
        }
    });

    $(".property-status").on("change", function () {
        var checkbox = $(this);
        var currentStatus = checkbox.data("status");
        var status = checkbox.prop("checked") ? 1 : 2;
        var url = checkbox.data("url");

        if (currentStatus === 3) {
            checkbox.prop("checked", false);
            toastr.error("Rejected property status can't be changed.");
            return;
        } else if (currentStatus === 0) {
            checkbox.prop("checked", false);
            toastr.error("Pending property status can't be changed from here.");
            return;
        }

        $.ajax({
            url: url,
            type: "POST",
            data: {
                status: status,
            },
            success: function (response) {
                if (status === 1) {
                    toastr.success(response.message + " status published");
                } else {
                    toastr.success(response.message + " status unpublished");
                }
            },
            error: function (xhr, status, error) {
                console.log(error);
                toastr.error("Something Went Wrong");
            },
        });
    });
});

/** STATUS CHANGE END FOR PROPERTY */

// Add new row
$(document).on("click", ".withdraw-method-field", function () {
    let html = `
    <div class="row row-method">
        <div class="col-5 col-sm-6">
            <label for="">${window.trans.label}</label>
            <input type="text" name="meta[label][]" value="" class="form-control" placeholder="${window.trans.enter_label_name}" required>
        </div>
        <div class="col-5 col-sm-5">
            <label for="">${window.trans.input}</label>
            <input type="text" name="meta[input][]" value="" class="form-control" placeholder="${window.trans.enter_input_name}" required>
        </div>
        <div class="col-2 col-sm-1 align-self-center">
            <button type="button" class="btn text-danger trash remove-withdraw-method"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    `;
    $(".row-for-method").append(html);
});

// Remove a row
$(document).on("click", ".remove-withdraw-method", function () {
    console.log("Remove button clicked");
    $(this).closest(".row-method").remove();
});

//Withdraw Method view Start
$(".withdraw-method-view-btn").on("click", function () {
    $("#payment_method").text($(this).data("payment-method"));
    $("#method_currency").text($(this).data("method-currency"));
    $("#method_min_amount").text($(this).data("payment-min-amount"));
    $("#method_max_amount").text($(this).data("payment-max-amount"));
    $("#method_charge").text($(this).data("payment-charge"));
    $("#method_status").text($(this).data("method-status"));
});
//Withdraw Method view End

//Message view Start
$(".message-view-btn").on("click", function () {
    $("#name").text($(this).data("name"));
    $("#phone").text($(this).data("phone"));
    $("#email").text($(this).data("email"));
    $("#company_name").text($(this).data("company-name"));
    $("#message").text($(this).data("message"));
});
//Message view End

$(".reject-btn").on("click", function () {
    var url = $(this).data("url");
    $(".rejectForm").attr("action", url);
});

$(".approve-btn").on("click", function () {
    var url = $(this).data("url");
    $(".approveForm").attr("action", url);
});

//Application modals start

$(document).ready(function () {
    $(document).on("click", "#application_processing", function (e) {
        e.preventDefault();
        $("#processing-modal").modal("hide");
        $("#landlordAgreementForm").submit();
    });
});

$(".application-rejected-btn").on("click", function () {
    var url = $(this).data("url");
    $(".applicationRejectedForm").attr("action", url);
});

$(".application-cancel-btn").on("click", function () {
    var url = $(this).data("url");
    $(".applicationCancelForm").attr("action", url);
});

$(".application-approved-btn").on("click", function () {
    var url = $(this).data("url");
    $(".applicationApprovedForm").attr("action", url);
});

//Application modals end

// Maintainance modal start

$(".maintainceProcessing").on('click', function () {
    $("#maintenance-processing-modal")
        .modal("hide")
        .on("hidden.bs.modal", function () {
            $("#maintenance-confirm-modal").modal("show");
            $(this).off("hidden.bs.modal");
        });
});

// Maintainance modal end

$(document).ready(function () {
    $(".methodDropdown").on("change", function () {
        const dynamicFieldsContainer = $(".dynamicFields");
        dynamicFieldsContainer.empty(); // Clear previous fields

        const selectedOption = $(this).find("option:selected");
        const labels = selectedOption.data("labels"); // Labels array
        const inputs = selectedOption.data("inputs"); // Input field names array

        // Ensure labels and inputs are arrays
        if (Array.isArray(labels) && Array.isArray(inputs)) {
            labels.forEach(function (label, index) {
                // Check if a corresponding input exists
                if (index < inputs.length) {
                    const inputName = inputs[index];

                    // Create a field wrapper
                    const fieldWrapper = $("<div>", { class: "form-group" });

                    // Create the label
                    const labelElement = $("<label>").text(label);

                    // Create the input
                    const input = $("<input>", {
                        type: "text",
                        name: `infos[${inputName}]`,
                        class: "form-control",
                        placeholder: `Enter ${label}`,
                    });

                    // Append label and input to the wrapper
                    fieldWrapper.append(labelElement).append(input);

                    // Append the wrapper to the dynamic fields container
                    dynamicFieldsContainer.append(fieldWrapper);
                }
            });
        } else {
            console.error("Labels or inputs are not arrays:", {
                labels,
                inputs,
            });
        }
    });
});

// Withdraw Method Edit Start

function generateDynamicFields(labels, inputs, savedInfos = {}, containerSelector) {
    const container = $(containerSelector);
    container.empty();

    if (Array.isArray(labels) && Array.isArray(inputs)) {
        labels.forEach((label, index) => {
            const inputName = inputs[index];
            const inputValue = savedInfos[inputName] ?? '';

            const fieldWrapper = $("<div>", { class: "form-group mb-3" });
            const labelElement = $("<label>").text(label);
            const input = $("<input>", {
                type: "text",
                name: `infos[${inputName}]`,
                class: "form-control",
                placeholder: `Enter ${label}`,
                value: inputValue
            });

            fieldWrapper.append(labelElement).append(input);
            container.append(fieldWrapper);
        });
    }
}

$(document).on("click", ".withdraw-method-edit-btn", function () {
    const method = $(this).data("method");
    const url = $(this).data("url");

    $("#edit-method-form").attr("action", url);
    $("#edit_account_no").val(method.account_no);
    $("#edit_method_id").val(method.method_id).trigger("change");
    $("#edit_id").val(method.id);

    setTimeout(() => {
        const selectedOption = $("#edit_method_id").find("option:selected");
        const labels = selectedOption.data("labels");
        const inputs = selectedOption.data("inputs");

        generateDynamicFields(labels, inputs, method.infos, "#editDynamicFields");
    }, 100);
});

$(document).on("change", "#edit_method_id", function () {
    const selectedOption = $(this).find("option:selected");
    const labels = selectedOption.data("labels");
    const inputs = selectedOption.data("inputs");

    generateDynamicFields(labels, inputs, {}, "#editDynamicFields");
});

// Withdraw Method Edit End


$(".payout-request-btn").on("click", function () {
    var url = $(this).data("url");
    var user_method_id = $(this).data("id");

    $("#method_min_amount").text($(this).data("method-min-amount"));
    $("#bank_name").text($(this).data("bank-name"));
    $("#charge").text($(this).data("charge"));

    var fullAccountNumber = String($(this).data("bank-acc-no") || "");
    var lastFourDigits = fullAccountNumber.slice(-4);
    var maskedAccountNumber = "**** **** **** " + lastFourDigits;
    $("#bank_acc_no").text(maskedAccountNumber);
    $("#user_method_id").val(user_method_id);

    $(".payoutRequestForm").attr("action", url);
});

$payout_form = $(".payout_request_form");
$payout_form.initFormValidation(),
    $(document).on("submit", ".payout_request_form", function (e) {
        e.preventDefault();
        let t = $(this).find(".payout-submit-btn"),
            a = t.html();
        $payout_form.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    t.html($savingLoader).attr("disabled", !0);
                },
                success: function (e) {
                    t.html(a).attr("disabled", false);

                    var title = $("#payment_success_title_hidden").val();
                    var message = $("#payment_success_message_hidden").val();

                    $("#payment_success_title").text(title);
                    $("#payment_success_message").text(message);

                    if (e.redirect) {
                        window.sessionStorage.hasPreviousMessage = true;
                        window.sessionStorage.previousMessage = e.message ?? null;

                        if (e.secondary_redirect_url) {
                            window.open(e.secondary_redirect_url, "_blank");
                        }
                        location.href = e.redirect;
                    }

                    $("#payoutRequestModal").modal("hide");
                    $("#thankYouModal").modal("show");

                },
                error: function (e) {
                    t.html(a).attr("disabled", !1), Notify("error", e);
                },
            });
    });

$(".reason-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var reason = $(this).data("reason");

    $("#reason").val(reason);
    $(".refundReasonForm").attr("action", url);
});

//admin Landlord view Start
$(".landlord-view-btn").on("click", function () {
    $("#landlord_id").text($(this).data("landlord-id"));
    $("#landlord_name").text($(this).data("landlord-name"));
    $("#number_of_property").text($(this).data("number-of-property"));
    $("#number_of_tenant").text($(this).data("number-of-tenant"));
    $("#number_of_rent").text($(this).data("number-of-rent"));
    $("#subscription_type").text($(this).data("subscription-type"));
    $("#duration_date").text($(this).data("duration-date"));
    $("#landlord_status").text($(this).data("landlord-status"));
    $("#landlord_balance").text($(this).data("landlord-balance"));
});
//admin Landlord view End

// Labor edit start
$(".admin-labor-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var labor_name = $(this).data("labor-name");
    var email = $(this).data("email");
    var phone = $(this).data("phone");
    var tenant_phone_code = $(this).data("tenant_phone_code");
    var gender = $(this).data("gender");
    var salary = $(this).data("salary");

    $("#labor_name").val(labor_name);
    $("#email").val(email);
    $("#gender").val(gender);
    $("#phone").val(phone);
    $("#tenant_phone_codes").val(tenant_phone_code);
    $("#salary").val(salary);
    $(".editAdminLaborForm").attr("action", url);
});

// Labor edit End

//Admin Labor view Start
$(".admin-labor-view-btn").on("click", function () {
    $("#labor_view_name").text($(this).data("labor-view-name"));
    $("#labor_email").text($(this).data("labor-email"));
    $("#labor_phone").text($(this).data("labor-phone"));
    $("#labor_gender").text($(this).data("labor-gender"));
    $("#labor_salary").text($(this).data("labor-salary"));
});
//Admin Labor view End

// Message edit start
$(".message-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var message_title = $(this).data("message-title");
    var message_payment_method = $(this).data("message-payment-method");

    // Set values in modal fields
    $("#message_title").val(message_title);
    $("#message_payment_method").val(message_payment_method);
    $(".editMessageForm").attr("action", url);
});
// Message edit End

//Notification view Start
$(".notification-view-btn").on("click", function () {
    $("#notification_message").text($(this).data("notification-message"));
    $("#notification_created_at").text($(this).data("notification-created-at"));
    $("#notification_read_at").text($(this).data("notification-read-at"));
});
//Notification view End

$(".rent-payment-approve-btn").on("click", function () {
    var url = $(this).data("url");
    var payment_receipt = $(this).data("payment-receipt");

    $("#payment_receipt").attr("href", payment_receipt);
    $(".rentPaymentForm").attr("action", url);
});

$(".rent-payment-reject-btn").on("click", function () {
    var url = $(this).data("url");

    $(".rentPaymentRejectForm").attr("action", url);
});

$(".security-deposits-approve-btn").on("click", function () {
    var url = $(this).data("url");
    var payment_receipt = $(this).data("payment-receipt");

    $("#security_payment_receipt").attr("href", payment_receipt);
    $(".securityDepositApproveForm").attr("action", url);
});

$(".security-deposits-reject-btn").on("click", function () {
    var url = $(this).data("url");
    $(".securityDepositRejectForm").attr("action", url);
});

$(".subscription-payment-approve-btn").on("click", function () {
    var url = $(this).data("url");
    var payment_receipt = $(this).data("payment-receipt");

    $("#payment_receipt").attr("href", payment_receipt);
    $(".rentPaymentForm").attr("action", url);
});

const $loginForm = $(".login_form");
$loginForm.initFormValidation();

$(document).on("submit", ".login_form", function (e) {
    e.preventDefault();

    const $submitButton = $(this).find(".submit-btn");
    const originalButtonText = $submitButton.html();

    if (!$loginForm.valid()) return;

    $.ajax({
        type: "POST",
        url: this.action,
        data: new FormData(this),
        dataType: "json",
        contentType: false,
        cache: false,
        processData: false,
        positionClass: "toast-top-left",
        beforeSend: function () {
            $submitButton
                .html($savingLoader)
                .addClass("disabled")
                .attr("disabled", true);
        },
        success: function (response) {
            $submitButton
                .html(originalButtonText)
                .removeClass("disabled")
                .attr("disabled", false);

            window.sessionStorage.hasPreviousMessage = true;
            window.sessionStorage.previousMessage = response.message || null;

            if (response.redirect) {
                location.href = response.redirect;
            }
        },
        error: function (error) {
            let response = error.responseJSON;

            console.log(error);

            $submitButton
                .html(originalButtonText)
                .removeClass("disabled")
                .attr("disabled", false);

            if (response.redirect) {
                window.sessionStorage.hasPreviousMessage = true;
                window.sessionStorage.notifyType = "warning";
                window.sessionStorage.previousMessage =
                    response.message || null;
                location.href = response.redirect;
            } else {
                showInputErrors(error.responseJSON);
                Notify("error", error);
            }
        },
    });
});

//Notification view Start
$(".admin-transaction-view-btn").on("click", function () {
    $("#admin_invoice_number").text($(this).data("admin-invoice-number"));
    $("#admin_transaction_date").text($(this).data("admin-transaction-date"));
    $("#admin_transaction_name").text($(this).data("admin-transaction-name"));
    $("#admin_transaction_payment_method").text(
        $(this).data("admin-transaction-payment")
    );
    $("#admin_transaction_amount").text(
        $(this).data("admin-transaction-amount")
    );
    $("#admin_transaction_type").text($(this).data("admin-transaction-type"));
});
//Notification view End

// subscription Reject
$(".modal-reject").on("click", function () {
    var url = $(this).data("url");
    $(".modalRejectForm").attr("action", url);
});

// subscription Reject End

// subscription Approved
$(".modal-approve").on("click", function () {
    var url = $(this).data("url");
    var subs_receipt = $(this).data("subs-receipt");
    console.log(subs_receipt);

    $("#subs_payment_receipt").attr("href", subs_receipt);
    $(".modalApproveForm").attr("action", url);
});
// subscription Approved end

//plans feature
$("#feature-btn").on('click', function (e) {
    e.preventDefault();

    let value = $(".add-feature").val();
    let featureCount = $(".feature-list").children().length;

    if (value !== "") {
        $(".feature-list").append(`
            <div class="col-lg-6 remove-list">
                <div class="feature-wrp">
                    <div class="form-control d-flex justify-content-between align-items-center">
                        <input name="features[${featureCount}][name]" class="feature-name" type="text" value="${value}">
                        <div>
                            <label class="switch m-0 check-height custom-checkbox">
                                <input type="checkbox" class="feature-status" value="1" name="features[${featureCount}][status]" checked>
                                <span class="slider round checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <button type="button" class="remove-one d-none"><i class="fal fa-times"></i></button>
                </div>
            </div>
        `);
        $(".add-feature").val("");
    }
});

// House Type edit Start
$(document).on("click", ".house-type-edit-btn", function () {
    var url = $(this).data("url");
    var house_type_name = $(this).data("house-type-name");

    $("#house_type_name").val(house_type_name);
    $(".houseTypeUpdateForm").attr("action", url);
});
// House Type edit End

// Facility edit Start
$(document).on("click", ".facility-edit-btn", function () {
    var url = $(this).data("url");
    var landlordId = $(this).data("landlord-id");
    var facility_name = $(this).data("facility-name");

    $("#landlord_id").val(landlordId);
    $("#facility_name").val(facility_name);
    $(".facilityUpdateForm").attr("action", url);
});
// Facility edit End

// Amenity edit Start
$(document).on("click", ".amenity-edit-btn", function () {
    var url = $(this).data("url");
    var landlordId = $(this).data("landlord-id");
    var amenity_name = $(this).data("amenity-name");

    $("#landlord_id").val(landlordId);
    $("#amenity_name").val(amenity_name);
    $(".amenityUpdateForm").attr("action", url);
});
// Amenity edit End

$chatFormSubmit = $(".chatFormSubmit");
$chatFormSubmit.initFormValidation(),
    $(document).on("submit", ".chatFormSubmit", function (e) {
        e.preventDefault();
        var route = $(this).data("route");
        let t = $(this).find(".chat-submit-btn"),
            a = t.html();
        $chatFormSubmit.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    t.html($savingLoader).attr("disabled", !0);
                },
                success: function (e) {
                    t.html(a).attr("disabled", false);
                    // Notify("success", null, e)
                    fetchMessage(route);
                },
                error: function (e) {
                    t.html(a).attr("disabled", !1), Notify("error", e);
                },
            });
    });

function fetchMessage(route) {
    $.ajax({
        type: "GET",
        url: route,
        success: function (response) {
            $(".restore-message").val("");
            $(".restore-file").val("");
            $("#message-container").html(response);
        },
    });
}

$(document).ready(function () {
    $(".user-chat-list").on("click", function () {
        let url = $(this).data("route");
        var userName = $(this).data("user-name");
        var userImage = $(this).data("user-img");
        var user_id = $(this).data("user-id");

        // Update chat header
        $(".chat-header .user-name").text(userName);
        $("#url").val(url);
        $(".chat-header .user-img").attr("src", userImage);

        $("#receiver_id").val(user_id);
        $("#parent_id").val("");

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (data) {
                let chatContainer = $(".chats-container");
                chatContainer.html(""); // Clear previous messages

                if (data.messages.length > 0) {
                    $("#parent_id").val(data.messages[0].id); // assumes first message is root
                }

                $.each(data.messages, function (index, msg) {
                    let fileContent = "";

                    if (msg.file) {
                        let fileExt = msg.ext;

                        if (["jpg", "jpeg", "png", "gif", "svg", "webp",].includes(fileExt)) {
                            fileContent = `<img src="${msg.file}" class="chat-image" target="_blank" alt="Image" >`;
                        } else if (fileExt === "pdf") {
                            fileContent = `<a href="${msg.file}" target="_blank" >
                                  <div class="download-document-container ">
                        <div class="download-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="40" height="40" rx="20" fill="#636364"/>
                                <path d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                        </div>
                        <div class="download-content d-flex align-items-center gap-2 mt-2">
                            <div class="">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                            </div>
                            <div class="">
                                <h6>House rent document for</h6>
                                <span>View PDF</span>
                            </div>
                        </div>
                    </div>
                                </a>`;
                        } else if (["doc", "docx"].includes(fileExt)) {
                            fileContent = `<a href="${msg.file}" target="_blank" >
                                  <div class="download-document-container ">
                        <div class="download-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="40" height="40" rx="20" fill="#636364"/>
                                <path d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                        </div>
                        <div class="download-content d-flex align-items-center gap-2 mt-2">
                            <div class="">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                            </div>
                            <div class="">
                                <h6>House rent document for</h6>
                                <span>Download Document</span>
                            </div>
                        </div>
                    </div>
                                </a>`;
                        } else {
                            fileContent = `<a href="${msg.file}" target="_blank" >
                                  <div class="download-document-container ">
                        <div class="download-icon">
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="40" height="40" rx="20" fill="#636364"/>
                                <path d="M20 22.0833V13.75M20 22.0833C19.4165 22.0833 18.3262 20.4214 17.9166 20M20 22.0833C20.5835 22.0833 21.6737 20.4214 22.0833 20" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M26.6667 23.75C26.6667 25.8183 26.235 26.25 24.1667 26.25H15.8334C13.765 26.25 13.3334 25.8183 13.3334 23.75" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                        </div>
                        <div class="download-content d-flex align-items-center gap-2 mt-2">
                            <div class="">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8 17H16" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 13H12" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13 2.5V3C13 5.8284 13 7.2426 13.8787 8.1213C14.7574 9 16.1716 9 19 9H19.5M20 10.6569V14C20 17.7712 20 19.6568 18.8284 20.8284C17.6569 22 15.7712 22 12 22C8.22877 22 6.34315 22 5.17157 20.8284C4 19.6569 4 17.7712 4 14V9.4558C4 6.2108 4 4.5883 4.88607 3.4893C5.06508 3.2673 5.26731 3.0651 5.48933 2.8861C6.58831 2 8.21082 2 11.4558 2C12.1614 2 12.5141 2 12.8372 2.114C12.9044 2.1377 12.9702 2.165 13.0345 2.1957C13.3436 2.3436 13.593 2.593 14.0919 3.0919L18.8284 7.8284C19.4065 8.4065 19.6955 8.6955 19.8478 9.0631C20 9.4306 20 9.8394 20 10.6569Z" stroke="#7B787B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                            </div>
                            <div class="">
                                <h6>House rent document for</h6>
                                <span>Download file</span>
                            </div>
                        </div>
                    </div>
                                </a>`;
                        }
                    }

                    let chatBubble = `
                            <div class="${msg.sender_id === data.auth_id
                            ? "my-msg"
                            : "opposite-msg"
                        }">
                                <div class="d-flex ${msg.sender_id === data.auth_id
                            ? "align-items-start flex-column"
                            : "justify-content-end align-items-end flex-column"
                        }">
                                ${fileContent}
                                ${msg.message ? `<p>${msg.message}</p>` : ""}
                                    <span>${msg.time}</span>
                                </div>
                            </div>
                        `;
                    chatContainer.append(chatBubble);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error fetching messages:", error);
            },
        });
    });
});

$(document).ready(function () {
    $(".user-chat-list").on("click", function () {
        $(".user-chat-list").removeClass("present-chat-bg"); // Remove from all
        $(this).addClass("present-chat-bg"); // Add to clicked one
    });
});

$adminChatFormSubmit = $(".adminChatFormSubmit");
$adminChatFormSubmit.initFormValidation(),
    $(document).on("submit", ".adminChatFormSubmit", function (e) {
        e.preventDefault();
        var route = $("#url").val();
        let t = $(this).find(".submit-btn"),
            a = t.html();
        $adminChatFormSubmit.valid() &&
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: !1,
                cache: !1,
                processData: !1,
                beforeSend: function () {
                    t.html($savingLoader).attr("disabled", !0);
                },
                success: function (e) {
                    t.html(a).attr("disabled", false);
                    // Notify("success", null, e)
                    adminFetchMessage(route);
                },
                error: function (e) {
                    t.html(a).attr("disabled", !1), Notify("error", e);
                },
            });
    });

function adminFetchMessage(route) {
    $.ajax({
        url: route,
        type: "GET",
        dataType: "json",
        success: function (data) {
            // Check if data corresponds to the right user
            if (data && data.auth_id) {
                $(".admin-message").val("");
                $(".admin-file").val("");
                let chatContainer = $(".chats-container");
                chatContainer.html(""); // Clear previous messages for this container

                // Append only the messages for the correct user
                $.each(data.messages, function (index, msg) {
                    let fileContent = "";

                    if (msg.file) {
                        let fileExt = msg.ext;

                        if (
                            [
                                "jpg",
                                "jpeg",
                                "svg",
                                "png",
                                "gif",
                                "webp",
                            ].includes(fileExt)
                        ) {
                            fileContent = `<img src="${msg.file}" target="_blank" alt="Image" class="chat-image">`;
                        } else if (fileExt === "pdf") {
                            fileContent = `<a href="${msg.file}" target="_blank" class="chat-file">📄 View PDF</a>`;
                        } else if (["doc", "docx"].includes(fileExt)) {
                            fileContent = `<a href="${msg.file}" target="_blank" class="chat-file">📑 Download Document</a>`;
                        } else {
                            fileContent = `<a href="${msg.file}" target="_blank" class="chat-file">📎 Download File</a>`;
                        }
                    }

                    let chatBubble = `
                                <div class="${msg.sender_id === data.auth_id
                            ? "my-msg"
                            : "opposite-msg"
                        }">
                                    <div class="d-flex ${msg.sender_id === data.auth_id
                            ? "align-items-start flex-column"
                            : "justify-content-end align-items-end flex-column"
                        }">
                                    ${fileContent}
                                     ${msg.message
                            ? `<p>${msg.message}</p>`
                            : ""
                        }
                                     <span>${msg.time}</span>
                                    </div>
                                </div>
                            `;
                    chatContainer.append(chatBubble); // Only append messages for this user
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error fetching messages:", error);
        },
    });
}


$(document).ready(function () {
    $('select[id="for-country-flag"]').each(function (index) {
        let $select = $(this);
        let $img = $select.closest('.country-dropdown').find('img[id="country-flag"]');

        let selectedOption = $select.find(':selected');
        let flagUrl = selectedOption.data('flag');
        $img.attr('src', flagUrl);

        $select.on('change', function () {
            let selectedOption = $(this).find(':selected');
            let flagUrl = selectedOption.data('flag');
            $img.attr('src', flagUrl);
        });
    });
});


//Facilities & Amenities Select All functionality start
$(document).ready(function () {
    $("#selectAllFacilities").on("change", function () {
        $(".facilities").prop("checked", $(this).prop("checked"));
    });

    $(".facilities").on("change", function () {
        const allFacilitiesChecked =
            $(".facilities").length === $(".facilities:checked").length;
        $("#selectAllFacilities").prop("checked", allFacilitiesChecked);
    });

    const allFacilitiesChecked =
        $(".facilities").length === $(".facilities:checked").length;
    $("#selectAllFacilities").prop("checked", allFacilitiesChecked);

    $("#selectAllAmenities").on("change", function () {
        $(".amenities").prop("checked", $(this).prop("checked"));
    });

    $(".amenities").on("change", function () {
        const allAmenitiesChecked =
            $(".amenities").length === $(".amenities:checked").length;
        $("#selectAllAmenities").prop("checked", allAmenitiesChecked);
    });

    const allAmenitiesChecked =
        $(".amenities").length === $(".amenities:checked").length;
    $("#selectAllAmenities").prop("checked", allAmenitiesChecked);
});
//Facilities & Amenities Select All functionality end

$(document).on("input", ".chat-filter", function () {
    let form = $(this).closest(".chat-filter");
    let container = "#admin-chat-user-data";

    $.ajax({
        url: form.attr("action"),
        method: "POST",
        data: form.serialize(),
        success: function (res) {
            $(container).html(res.data);
        },
        error: function (xhr) {
            console.error(xhr.responseText);
        },
    });
});

// Hide div start
$('select[name="custom_days"]')
    .on("change", function () {
        if ($(this).val() === "custom_date") {
            $("#custom-date-range").removeClass("d-none");
        } else {
            $("#custom-date-range").addClass("d-none");
        }
    })
    .trigger("change");

// Hide div end

// Expense Category edit Start
$(".expense-category-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var expenese_category_name = $(this).data("expense-category-name");
    var expense_description = $(this).data("expense-category-description");

    $("#expenese_category_name").val(expenese_category_name);
    $("#expense_description").val(expense_description);
    $(".expesneCategoryUpdateForm").attr("action", url);
});

// Expense Category edit end

// Expense edit Start
$(".expense-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var expense_category_id = $(this).data("expense-category-id");
    var expense_amount = $(this).data("expense-amount");
    var payment_type = $(this).data("payment-type");
    var reference_no = $(this).data("reference-no");
    var expense_for = $(this).data("expense-for");
    var expense_date = $(this).data("expense-date");
    var note = $(this).data("expense-note");

    // Set the values in the modal's fields
    $("#expense_category_id").val(expense_category_id);
    $("#expense_amount").val(expense_amount);
    $("#payment_type").val(payment_type);
    $("#reference_no").val(reference_no);
    $("#expense_for").val(expense_for);
    $("#expense_date").val(expense_date);
    $("#note").val(note);
    $(".expenseUpdateForm").attr("action", url);
});

// Expense edit end

// Income Category edit Start
$(".income-category-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var income_category_name = $(this).data("income-category-name");
    var income_description = $(this).data("income-category-description");

    $("#income_category_name").val(income_category_name);
    $("#income_description").val(income_description);
    $(".incomeCategoryUpdateForm").attr("action", url);
});

// Floor edit Start
$(".floor-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var floor_no = $(this).data("floor-no");

    $("#floor_no").val(floor_no);
    $(".floorUpdateForm").attr("action", url);
});
// Floor edit end

// Income edit Start
$(".income-edit-btn").on("click", function () {
    var url = $(this).data("url");
    var income_category_id = $(this).data("income-category-id");
    var income_amount = $(this).data("income-amount");
    var payment_type = $(this).data("payment-type");
    var reference_no = $(this).data("reference-no");
    var income_for = $(this).data("income-for");
    var income_date = $(this).data("income-date");
    var note = $(this).data("income-note");

    // Set the values in the modal's fields
    $("#income_category_id").val(income_category_id);
    $("#income_amount").val(income_amount);
    $("#payment_type").val(payment_type);
    $("#reference_no").val(reference_no);
    $("#income_for").val(income_for);
    $("#income_date").val(income_date);
    $("#note").val(note);
    $(".incomeUpdateForm").attr("action", url);

    $('select[name="custom_days"]').trigger("change");
});
// Income edit end

// Get Plan id and name
$(document).on('click', '.subscribe-plan', function () {
    const planId = $(this).data('plan-id');
    const planName = $(this).data('plan-name');

    $('#plan_id').val(planId);

    if (planName) {
        $('#subscription_name').text(planName);
    } else {
        $('#subscription_name').text('New');
    }
});

// SignUp By Plan Start
$(document).ready(function () {
    // sign up form
    let $sign_up_form = $(".sign_up_form");
    $sign_up_form.initFormValidation();

    $(document).on("submit", ".sign_up_form", function (e) {
        e.preventDefault();

        let t = $(this).find(".submit-btn"),
            a = t.html();
            console.log(a)

        if ($sign_up_form.valid()) {
            $.ajax({
                type: "POST",
                url: this.action,
                data: new FormData(this),
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    t.html($savingLoader) // Show loader
                        .addClass("disabled")
                        .attr("disabled", true);
                },

                success: function (response) {
                    $("#registration-modal").modal("hide");
                    if (response.openModal === true) {
                        $("#dynamicEmail").text(response.email);
                        $("#verifymodal").modal("show");
                        startCountdown(response.otp_expiration);
                        return;
                    }

                    if (response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }

                    if (response.message) {
                        Notify("success", response.message);
                    }
                },

                error: function (e) {
                    // Handle error response
                    showInputErrors(e.responseJSON);
                    Notify("error", e);
                },
                complete: function () {
                    t.html(a).removeClass("disabled").attr("disabled", false);
                },
            });
        }
    });

    // OTP input field--------------------->
    const pinInputs = document.querySelectorAll(".pin-input");

    pinInputs.forEach((inputField, index) => {
        inputField.addEventListener("input", () => {
            inputField.value = inputField.value.replace(/[^0-9]/g, "").slice(0, 1);

            if (inputField.value && index < pinInputs.length - 1) {
                pinInputs[index + 1].focus();
            }
        });

        inputField.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && !inputField.value && index > 0) {
                pinInputs[index - 1].focus();
            }
        });

        inputField.addEventListener("paste", (e) => {
            e.preventDefault();
        });
    });

    function showInputErrors(e) {
        if (e.errors !== undefined) {
            $.each(e.errors, function (field, message) {
                $("#" + field + "-error").remove();

                let errorLabel = `
                    <label id="${field}-error" class="error" for="${field}">${message}</label>
                `;

                $("#" + field)
                    .parents()
                    .hasClass("form-check")
                    ? $("#" + field)
                        .parents()
                        .find(".form-check")
                        .append(errorLabel)
                    : $("#" + field)
                        .addClass("error")
                        .after(errorLabel);
            });
        }
    }

    function ajaxSuccess(response, Notify) {
        if (response.redirect) {
            if (response.message) {
                window.sessionStorage.hasPreviousMessage = true;
                window.sessionStorage.previousMessage = response.message ?? null;
            }

            location.href = response.redirect;
        } else if (response.message) {
            Notify("success", response);
        }
    }

    // Verify OTP submission
    let $verify_form = $(".verify_form");
    $verify_form.initFormValidation();

    $(document).on("submit", ".verify_form", function (e) {
        e.preventDefault();

        let t = $(this).find(".submit-btn"),
            a = t.html();

        const email = $("#dynamicEmail").text();

        // Get the OTP input values from the form
        const otpInputs = $(this).find(".otp-input");
        let otpValues = otpInputs
            .map(function () {
                return $(this).val();
            })
            .get()
            .join("");

        // Validate OTP and form before submitting
        if ($verify_form.valid()) {
            let formData = new FormData(this);
            formData.append("email", email);
            formData.append("otp", otpValues);

            $.ajax({
                type: "POST",
                url: this.action,
                data: formData,
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    t.html($savingLoader)
                        .addClass("disabled")
                        .attr("disabled", true);
                },
                success: function (response) {
                    t.html(a).removeClass("disabled").attr("disabled", false);

                    // Check for successful OTP submission
                    if (response.redirect ?? false) {
                        window.sessionStorage.hasPreviousMessage = true;
                        window.sessionStorage.previousMessage =
                            response.message || "Operation successfully.";
                        location.href = response.redirect;
                    }
                },
                error: function (response) {
                    t.html(a).removeClass("disabled").attr("disabled", false);
                    toastr.error(
                        response.responseJSON.error
                    );
                },
            });
        } else {
            toastr.error("Please enter all OTP digits.");
        }
    });

    // OTP countdown------------------->
    let countdownInterval;
    function startCountdown(timeLeft) {
        const countdownElement = $("#countdown");
        const resendButton = $("#otp-resend");

        // Function to format time as MM:SS
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${String(minutes).padStart(2, "0")}:${String(
                remainingSeconds
            ).padStart(2, "0")}`;
        }

        // Clear any existing countdown interval
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        // Initialize countdown display
        countdownElement.text(formatTime(timeLeft));
        resendButton.addClass("disabled").attr("disabled", true); // Disable the button during countdown

        // Start the new countdown interval
        countdownInterval = setInterval(() => {
            timeLeft--;

            // Update the countdown text
            countdownElement.text(formatTime(timeLeft));

            // Stop the countdown when timeLeft reaches zero
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                countdownElement.text("00:00");

                // Enable the resend button
                resendButton.removeClass("disabled").removeAttr("disabled");
            }
        }, 1000);
    }

    // Resend OTP
    $("#otp-resend").on("click", function () {
        const resendButton = $(this);

        // Prevent action if the button is disabled
        if (resendButton.hasClass("disabled")) {
            return;
        }

        const route = resendButton.data("route");
        const originalText = resendButton.text();
        const email = $("#dynamicEmail").text();

        // Ensure email is available
        if (!email) {
            Notify("error", "Email is missing. Please try again.");
            return;
        }

        // Temporarily disable the button during the request
        resendButton.text("Sending...").addClass("disabled").attr("disabled", true);

        $.ajax({
            type: "POST",
            url: route,
            data: { email: email },
            dataType: "json",
            success: function (response) {
                resendButton
                    .text(originalText)
                    .addClass("disabled")
                    .attr("disabled", true);
                startCountdown(response.otp_expiration);
            },
            error: function (e) {
                resendButton
                    .text(originalText)
                    .removeClass("disabled")
                    .removeAttr("disabled");
            },
        });
    });
});
// SignUp By Plan End
