alertDismiss = ->
  target = $('.alert')
  timeout = target.data('auto-dismiss')
  return unless timeout
  timeout = parseInt(timeout) * 1000
  setTimeout ->
    target.fadeTo(500, 0).slideUp(500, -> $(this).remove())
  , timeout
$(document).ready ->
  alertDismiss()
