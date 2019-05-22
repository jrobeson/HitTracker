(function($, undefined) {
  function confirmDeletion(event) {
    var needConfirmation = !event.currentTarget.hasAttribute('data-no-confirm');

    if (needConfirmation && !confirm($(event.currentTarget).data('confirm') || 'Are you sure?')) {
      return false;
    }

    return true;
  }

  function buildForm(action, method, csrfToken, params) {
    var form = document.createElement('form');

    form.method = 'POST';
    form.action = action;

    var input = document.createElement('input');

    input.type = 'hidden';
    input.name = '_method';
    input.value = method;

    form.appendChild(input);

    if (csrfToken) {
      var csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_link_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
    }

    if (params) {
      var extraFields = {};
      var parts = params.split('&');
      for (var i = 0, len = parts.length; i < len; i++) {
        var tokens = parts[i].split('=');
        extraFields[tokens[0]] = decodeURIComponent(tokens[1]);
      }
      for (var key in extraFields) {
        var field = document.createElement('input');
        field.type = 'hidden';
        field.name = key;
        field.value = extraFields[key];
        form.appendChild(field);
      }
    }

    return form;
  }

  $(document).ready(function() {
    $('body').delegate('a[data-method]', 'click', function(event) {
      event.preventDefault();

      if (!confirmDeletion(event)) {
        return;
      }

      var csrfToken = $(event.currentTarget).data('csrf-token');
      var action = event.currentTarget.href;
      var method = $(event.currentTarget).data('method');
      var params = $(event.currentTarget).data('params');

      var form = buildForm(action, method, csrfToken, params);
      document.body.appendChild(form);
      form.submit();
    });
  });
})(jQuery);
