
$('.property-changing-status').on('change', function() {
    var checkbox = $(this);
    var status = parseInt(checkbox.val(), 10);
    var url = checkbox.data('url');
    console.log(url)

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            status: status
        },
        success: function(response) {
            var statusClass;
            var message;
            switch(status) {
                case 0:
                    message = 'Pending';
                    statusClass = 'pending-status';
                    break;
                case 1:
                    message = 'Approved';
                    statusClass = 'approved-status';
                    break;
                case 2:
                    message = 'Inactive';
                    statusClass = 'inactive-status';
                    break;
                case 3:
                    message = 'Rejected';
                    statusClass = 'reject-status';
                    break;
                default:
                    message = 'unknown';
                    statusClass = 'status-unknown';
            }
            checkbox.closest('li').removeClass('reject-status pending-status approved-status inactive-status leave-status-unknown');

            checkbox.closest('li').addClass(statusClass);

            toastr.success(response.message + ' ' + message);

            if (response.redirect) {
                window.location.href = response.redirect;
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Something Went Wrong');
        }
    });
});
