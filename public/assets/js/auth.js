function fillup(email, password) {
    $("#email").val(email);
    $("#password").val(password);
}

$(document).ready(function () {
    $(".dynamic-btn").on("click", function () {
        const matches = $(this)
            .attr("onclick")
            .match(/'([^']+)'/g);
        const email = matches[0].replace(/'/g, "");
        const password = matches[1].replace(/'/g, "");

        fillup(email, password);
    });
});

$(document).on('click', '.hide-pass', function () {
    $(this).toggleClass('show-pass');

    var input = $(this).closest('.input-group').find('input');

    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
    } else {
        input.attr('type', 'password');
    }
});

