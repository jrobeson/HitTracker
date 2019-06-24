export {};
(function($) {
  function confirmDeletion(event: any) {
    const needConfirmation = !event.currentTarget.hasAttribute('data-no-confirm');

    if (needConfirmation && !confirm($(event.currentTarget).data('confirm') || 'Are you sure?')) {
      return false;
    }

    return true;
  }

  function buildForm(action: string, method: string, csrfToken: string, params: string) {
    const form = document.createElement('form');

    form.method = 'POST';
    form.action = action;

    const input = document.createElement('input');

    input.type = 'hidden';
    input.name = '_method';
    input.value = method;

    form.appendChild(input);

    if (csrfToken) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_link_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
    }

    if (params) {
      const extraFields: Record<string, string> = {};
      const parts = params.split('&');
      for (let i = 0, len = parts.length; i < len; i++) {
        const tokens = parts[i].split('=');
        extraFields[tokens[0]] = decodeURIComponent(tokens[1]);
      }
      for (const key in extraFields) {
        const field = document.createElement('input');
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

      const csrfToken = $(event.currentTarget).data('csrf-token');
      const action = event.currentTarget.href;
      const method = $(event.currentTarget).data('method');
      const params = $(event.currentTarget).data('params');

      const form = buildForm(action, method, csrfToken, params);
      document.body.appendChild(form);
      form.submit();
    });
  });
})(jQuery);
