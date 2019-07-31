import * as moment from 'moment';
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

export const formatGameTotalTime = (totalInSeconds: number) => {
  const duration = moment.duration(totalInSeconds, 'seconds');
  const hours = Math.floor(duration.asHours());
  const minutes = Math.floor(duration.asMinutes()) - hours * 60;
  const seconds = Math.floor(duration.asSeconds()) - hours * 60 * 60 - minutes * 60;

  const hoursDisplay = hours !== 0 ? `${hours.toString(10).padStart(2, '0')}:` : '';
  const minutesDisplay = minutes.toString(10).padStart(2, '0');
  const secondsDisplay = seconds.toString(10).padStart(2, '0');

  const result = `${hoursDisplay}${minutesDisplay}:${secondsDisplay}`;

  return result;
};
