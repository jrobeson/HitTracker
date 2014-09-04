$ ->
  if $('body').hasClass('hittracker-game-active') or $('body').hasClass('hittracker-game-scoreboard')
    source = new EventSource '/events/game'

    if $('body').hasClass('hittracker-game-scoreboard')
      source.addEventListener 'game.start', (e) ->
        window.location.reload(true)

    source.addEventListener 'game.hit', (e) ->
      event_data = $.parseJSON e.data
      hit = event_data.content
      pushHit "#player-#{hit.player_id} .zone-#{hit.zone}", hit.zone
      $("#player-#{hit.player_id} .player-score").text hit.life_credits

      # TODO: convert to event!, use team table names, make a real function
      $('.scores').each ->
        team_total = 0
        $(this).find('.player-score').each ->
          team_total += parseInt($(this).text())

        $(this).find('.team-total').text team_total

    $(window).on 'unload', (source) ->
      source.close

    countdown_ref = $('#game-time-countdown')
    game_end = countdown_ref.data('game-end-time')
    countdown_ref.countdown(game_end)
      .on('update.countdown', (event) ->
        format = '%M:%S'
        if event.offset.hours > 0
          format = '%-H:' + format
        $(this).text(event.strftime(format))
      ).on('finish.countdown', (event) ->
        format = '%M:%S'
        if event.offset.hours > 0
          format = '%-H:' + format
        $(this).text(event.strftime(format))
      )

$('#print-scores').click ->
  copies = $('tr[id^="player-"]').length
  printScores copies

$('#hit-simulator select[name="esn"]').change ->
  $(this).trigger 'focusout'

pushHit = (selector, zone) ->
  value = parseInt($(selector).text()) + 1
  $(selector).animate {color: '#a50b00'}, 500,  ->
    $(this).text value
  .animate {color: '#000'}, 500

printScores = (copies) ->
  jsPrintSetup.clearSilentPrint()
  jsPrintSetup.setOption 'numCopies', copies
  jsPrintSetup.setOption 'orientation', jsPrintSetup.kLandscapeOrientation
  jsPrintSetup.setOption 'headerStrLeft', ''
  jsPrintSetup.setOption 'headerStrCenter', ''
  jsPrintSetup.setOption 'headerStrRight', ''
  jsPrintSetup.setOption 'footerStrLeft', ''
  jsPrintSetup.setOption 'footerStrCenter', ''
  jsPrintSetup.setOption 'footerStrRight', ''
  jsPrintSetup.print()
