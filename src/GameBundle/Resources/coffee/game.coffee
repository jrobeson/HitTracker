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

    $('#hit-simulator select[name="esn"]').change ->
      $(this).trigger 'focusout'

    countdown_ref = $('#countdown')
    game_end = new Date(countdown_ref.data('gameEndTime'))
    countdown_ref.countdown(game_end)
      .on('update.countdown', (event) ->
        format = '%M:%S'
        if event.offset.hours > 0
          format = '%-H:' + format

        $(this).text(event.strftime(format))
      )

$('#print-scores').click ->
  copies = $('tr[id^="player-"]').length
  printScores copies

pushHit = (selector, zone) ->
  value = parseInt($(selector).text()) + 1
  $(selector).animate {color: '#a50b00'}, 500,  ->
    $(this).text value
  .animate {color: '#000'}, 500

printScores = (copies) ->
  window.jsPrintSetup.setOption 'numCopies', copies
  window.jsPrintSetup.print()
