/**
 * This file is part of HitTracker.
 *
 * HitTracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2014 <johnny@localmomentum.net>
 * @license AGPL-3
 */
$(document).ready(function () {
    $('select[name="hittracker_game[reload_players]"]').change(function () {
        let gameId = $(this).val();
        let text = $(this).children(':selected').text();
        text = text.replace(/[ ]\(Game #: .+?\)/, '');
        let teams = text.replace(' vs. ', '|').split('|');
        let request = $.ajax({
            url: `${window.location.origin}/games/${gameId}`,
            headers: {
                Accept: 'application/json'
            }
        });
        request.done(function (game) {
            let teamPlayers = [];
            for (let player of game.players) {
                let pt = player.team;
                if (!teamPlayers[pt]) {
                    teamPlayers[pt] = {};
                }
                teamPlayers[pt][player.vest.id] = player.name;
            }

            $('.new-game-teams').each(function () {
                let team = teams.shift();
                $(this).find('.team-no input').val(team);
                let players = teamPlayers[team];
                for (let vestId in players) {
                    console.log([players[vestId], vestId]);
                    $(`select[id$='_vest'] option:selected[value=${vestId}]`).
                        parent().parent().parent().parent().find("input[id$='_name']").
                        val(players[vestId]);
                }

            });
        });
    });

    $('form[name="hittracker_game"]').submit(function () {
        $('.new-game-teams').each(function () {
            let team = $(this).find('.team-no input').val().trim();
            $(this).find('input[id$="_team"]').each(function () {
                $(this).val(team);
            });
        });
    });

     if ($('body').hasClass('hittracker-game-active') || $('body').hasClass('hittracker-game-scoreboard')) {
         let source = new EventSource('/events/game');
         $(window).on('unload', (source) => {
             source.close();
         });

        if ($('body').hasClass('hittracker-game-scoreboard')) {
            source.addEventListener('game.start', (e) => {
                window.location.reload(true);
            });
        }

        source.addEventListener('game.hit', function(e) {
            let eventData = $.parseJSON(e.data);
            let hit = eventData.content;

            if ($('.game-activity ul li').size() > 10) {
                $('.game-activity ul li:first').remove();
            }
            //$('.game-activity ul').append(`<li>${hit.player_name} hit Player 2 in Zone ${hit.zone}</li>`);
            pushHit(`#player-${hit.player_id} .zone-${hit.zone}`, hit.zone_hits);
            $(`#player-${hit.player_id} .player-life-credits`).text(hit.life_credits);

            let team = hit.team.replace(' ', '-').toLowerCase();
            $(`.${team} .team-total`).text(hit.team_life_credits);
        });

        initCountdown($('#game-time-countdown'));

    }

    $('#print-scores').click(function (event) {
        event.preventDefault();
        printScores($(this).attr('href'), $('tr[id^="player-"]').length);
    });

    $('#hit-simulator select[name="radioId"]').change(function () {
        $(this).trigger('focusout');
    });
});


/**
 * Setup the game timer countdown
 *
 * The target selector must have game-end-time, and server-time
 * data attributes to initialize the countdown.
 *
 * @param Object selector a jquery selector for the target element
 */
function initCountdown(selector) {
    let gameEnd = selector.data('game-end-time');
    let clientTime = (new Date()).getTime();
    let serverTime = new Date(parseInt(selector.data('server-time'))).getTime();
    let offset = serverTime - clientTime;
    gameEnd = gameEnd - offset;

    formatDate = function(event) {
        let format = '%M:%S';
        if (event.offset.hours > 0) {
            format = `%-H:${format}`;
        }
        return event.strftime(format);
    };
    selector.countdown(gameEnd)
        .on('update.countdown', function (event) {
            $(this).text(formatDate(event));
        }).on('finish.countdown', function(event) {
            $(this).text(formatDate(event));
        });
}

pushHit = function (selector, zoneHits) {
    let value = $(selector).text();
    $(selector).animate({color: '#a50b00'}, 500, function () {
        $(this).text(zoneHits)
    }).animate({color: '#000'}, 500);
};

printScores = function (url, copies) {
    let frame = document.createElement('iframe');
    frame.setAttribute('id', 'print-frame');
    frame.setAttribute('name', 'print-frame');
    frame.setAttribute('type', 'content');
    frame.setAttribute('collapsed', 'true');
    document.documentElement.appendChild(frame);

    frame.addEventListener('load', function (event) {
        jsPrintSetup.clearSilentPrint();
        jsPrintSetup.setOption('numCopies', copies);
        jsPrintSetup.setOption('orientation', jsPrintSetup.kLandscapeOrientation);
        jsPrintSetup.setOption('headerStrLeft', '');
        jsPrintSetup.setOption('headerStrCenter', '');
        jsPrintSetup.setOption('headerStrRight', '');
        jsPrintSetup.setOption('footerStrLeft', '');
        jsPrintSetup.setOption('footerStrCenter', '');
        jsPrintSetup.setOption('footerStrRight', '');
        jsPrintSetup.printWindow(frame.contentWindow);

        setTimeout(function () {
            let frame = document.getElementById('print-frame');
            frame.destroy();
        }, 10);
    }, true);

    frame.contentDocument.location.href = url;
};
