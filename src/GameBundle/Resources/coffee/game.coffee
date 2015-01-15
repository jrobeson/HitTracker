$ ->
  $('select[name="hittracker_game[reload_players]"]').change ->
    game_id = $(this).val()
    text = $(this).children(':selected').text()
    teams = text.replace(' vs. ', '|').split('|')
    request = $.ajax({
      url: window.location.origin + '/games/' + game_id,
      headers: {
        Accept: "application/json",
      }
    })
    request.done (game) ->
      team_players = []
      for player in game.players
        pt = player.team
        team_players[pt] = [] if not team_players[pt]?
        team_players[pt].push(player.name)
      $('.new-game-teams').each ->
        team = teams.shift()
        $(this).find('.team-no input').val(team)
        players = team_players[team]
        $(this).find('input[id$="_name"]').each ->
          $(this).val(players.shift())

  $('form[name="hittracker_game"]').submit ->
    $('.new-game-teams').each ->
      team = $(this).find('.team-no input').val().trim()
      $(this).find('input[id$="_team"]').each ->
        $(this).val(team)

  if $('body').hasClass('hittracker-game-active') or $('body').hasClass('hittracker-game-scoreboard')
    source = new EventSource '/events/game'
    $(window).on 'unload', (source) ->
      source.close

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


    countdown_ref = $('#game-time-countdown')
    game_end = countdown_ref.data('game-end-time')
    client_time = (new Date()).getTime()
    server_time = new Date(parseInt(countdown_ref.data('server-time'))).getTime()
    offset = server_time - client_time
    game_end = game_end - offset
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

$('#print-scores').click (event) ->
  event.preventDefault()

  url = $(this).attr('href')
  copies = $('tr[id^="player-"]').length
  printScores url, copies

$('#hit-simulator select[name="radioId"]').change ->
  $(this).trigger 'focusout'

pushHit = (selector, zone) ->
  value = parseInt($(selector).text()) + 1
  $(selector).animate {color: '#a50b00'}, 500,  ->
    $(this).text value
  .animate {color: '#000'}, 500

printScores = (url, copies) ->

  frame = document.createElement('iframe')
  frame.setAttribute('id', 'print-frame')
  frame.setAttribute('name', 'print-frame')
  frame.setAttribute('type', 'content')
  frame.setAttribute('collapsed', 'true')
  document.documentElement.appendChild(frame)

  frame.addEventListener 'load', (event) ->
    doc = event.originalTarget
    #if (doc.defaultView.frameElement)
    #  return

    jsPrintSetup.clearSilentPrint()
    jsPrintSetup.setOption 'numCopies', copies
    jsPrintSetup.setOption 'orientation', jsPrintSetup.kLandscapeOrientation
    jsPrintSetup.setOption 'headerStrLeft', ''
    jsPrintSetup.setOption 'headerStrCenter', ''
    jsPrintSetup.setOption 'headerStrRight', ''
    jsPrintSetup.setOption 'footerStrLeft', ''
    jsPrintSetup.setOption 'footerStrCenter', ''
    jsPrintSetup.setOption 'footerStrRight', ''
    jsPrintSetup.printWindow(frame.contentWindow)
    setTimeout ->
      frame = document.getElementById('print-frame')
      frame.destroy()
    , 10
  , true

  frame.contentDocument.location.href = url

