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
    event => {
      jsPrintSetup.clearSilentPrint();
      jsPrintSetup.setPaperSizeUnit(jsPrintSetup.kPaperSizeInches);
      const paperSizeId = 200;
      jsPrintSetup.definePaperSize(
        paperSizeId,
        paperSizeId,
        'lazerball_scorecard',
        'lazerball_scorecard_8.5x5.5in',
        'LazerBall Scorecard',
        8.5,
        5.5,
        jsPrintSetup.kPaperSizeInches
      );
      jsPrintSetup.setPaperSizeData(1);

      jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
      jsPrintSetup.setOption('shrinkToFit', true);
      jsPrintSetup.setOption('marginTop', 0);
      jsPrintSetup.setOption('marginBottom', 0);
      jsPrintSetup.setOption('marginLeft', 0);
      jsPrintSetup.setOption('marginRight', 0);
      jsPrintSetup.setOption('headerStrLeft', '');
      jsPrintSetup.setOption('headerStrCenter', '');
      jsPrintSetup.setOption('headerStrRight', '');
      jsPrintSetup.setOption('footerStrLeft', '');
      jsPrintSetup.setOption('footerStrCenter', '');
      jsPrintSetup.setOption('footerStrRight', '');
      jsPrintSetup.printWindow(frame.contentWindow);

      setTimeout(() => {
        if (frame) {
          frame.remove();
        }
      }, 10);
    },
    true
  );

  frame.contentDocument.location.href = url;
};
