export const alertDismiss = () => {
    let target = $('.alert');
    let timeout = target.data('auto-dismiss');

    if (!timeout) {
        return;
    }
    timeout = parseInt(timeout) * 1000;
    setTimeout(function() {
        target.fadeTo(500, 0).slideUp(500, function() { $(this).remove() })
    }, timeout);
};
