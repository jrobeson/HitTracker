if !window.location.origin
  scheme_host = window.location.protocol + '//' + window.location.hostname
  port = ''
  if window.location.port
    port = ':' + window.location.port
  window.location.origin = scheme_host + port

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
