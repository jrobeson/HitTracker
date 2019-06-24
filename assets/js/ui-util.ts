export const alertDismiss = () => {
  const target = $('.alert');
  let timeout = target.data('auto-dismiss');

  if (!timeout) {
    return;
  }
  timeout = parseInt(timeout, 10) * 1000;
  setTimeout(() => {
    target.fadeTo(500, 0).slideUp(500, function() {
      $(this).remove();
    });
  }, timeout);
};

export const printScores = (url: string) => {
  const frame = document.createElement('iframe');
  frame.setAttribute('id', 'print-frame');
  frame.setAttribute('name', 'print-frame');
  frame.setAttribute('type', 'content');
  frame.setAttribute('collapsed', 'true');
  document.documentElement.appendChild(frame);

  frame.addEventListener(
    'load',
    () => {
      if (!frame.contentWindow) {
        return;
      }
      frame.contentWindow.focus();
      frame.contentWindow.print();
      setTimeout(() => {
        if (frame) {
          frame.remove();
        }
      }, 10);
    },
    true
  );

  frame.contentDocument!.location.href = url;
};
