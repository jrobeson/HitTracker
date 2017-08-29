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
require('jquery-countdown');
require('./jquery.color.js');
require('./jquery-ujs.js');
require('bootstrap-sass');

if (!window.location.origin) {
    let schemeHost = `${window.location.protocol}//${window.location.hostname}`;
    let port = (window.location.port) ? `:${window.location.port}` : '';
    window.location.origin = `${schemeHost}${port}`;
}

alertDismiss = function () {
    let target = $('.alert');
    let timeout = target.data('auto-dismiss');

    if (!timeout) {
        return;
    }
    timeout = parseInt(timeout) * 1000;
    setTimeout(function() {
        target.fadeTo(500, 0).slideUp(500, function() { $(this).remove() })
    }, timeout);
};

$(document).ready(function () {
    'use strict';
    alertDismiss();

    $('select[name="game[reload_players]"]').change(function () {
        let gameId = $(this).val();
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
                teamPlayers[pt][player.unit.id] = player.name;
            }

            let teams = Object.keys(teamPlayers);

            $('.new-game-teams').each(function () {
                let team = teams.shift();
                $(this).find('.team-no input').val(team);
                let players = teamPlayers[team];
                for (let unitId in players) {
                    $(`select[id$='_unit'] option:selected[value=${unitId}]`).
                        parent().parent().parent().parent().find("input[id$='_name']").
                        val(players[unitId]);
                }

            });
        });
    });

    $('form[name="game"]').submit(function () {
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
            if (source) {
                source.close();
            }
        });

        if ($('body').hasClass('hittracker-game-scoreboard')) {
            source.addEventListener('game.start', (e) => {
                window.location.reload(true);
            });
        }

        source.addEventListener('game.end', (e) => {
            queueActivity('<li>Game Ends</li>');
        });

        source.addEventListener('game.hit', function(e) {
            let eventData = $.parseJSON(e.data);
            let hit = eventData.content;
            let targetPlayer = hit.target_player;

            //queueActivity(`<li>${player.name} hit $[targetPlayer.name} in Zone ${targetPlayer.zone}</li>`);
            if (targetPlayer.zone_hits) {
                pushHit(`.player-${targetPlayer.id} .zone-${targetPlayer.zone}`, targetPlayer.zone_hits);
            }
            if (targetPlayer.hit_points) {
                $(`.player-${targetPlayer.id} .player-hit-points`).text(targetPlayer.hit_points);
                let team = targetPlayer.team.replace(' ', '-').toLowerCase();
                $(`.${team} .team-total-hp`).text(hit.target_team_hit_points);
            }
            if (targetPlayer.score) {
                $(`.player-${targetPlayer.id} .player-score`).text(targetPlayer.score);
                let team = targetPlayer.team.replace(' ', '-').toLowerCase();
                $(`.${team} .team-total-score`).text(hit.target_team_score);
            }

        });

        initCountdown($('#game-time-countdown'));
    }

    $('#print-scores').click(function (event) {
        event.preventDefault();
        printScores($(this).attr('href'));
    });

    $('#hit-simulator select[name="radioId"]').change(function () {
        $(this).trigger('focusout');
    });
});

function queueActivity(content) {
    if ($('.game-activity ul li').size() > 10) {
        $('.game-activity ul li:first').remove();
    }
    $('.game-activity ul').append(content);
}

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
            musicSelector = $('#active-game-music');
            if (musicSelector.length) {
			    musicSelector.get(0).pause();
		    }
        });
}

function pushHit(selector, zoneHits) {
    let value = $(selector).text();
    $(selector).animate({color: '#a50b00'}, 500, function () {
        $(this).text(zoneHits)
    }).animate({color: '#000'}, 500);
}

function printScores(url) {
    let frame = document.createElement('iframe');
    frame.setAttribute('id', 'print-frame');
    frame.setAttribute('name', 'print-frame');
    frame.setAttribute('type', 'content');
    frame.setAttribute('collapsed', 'true');
    document.documentElement.appendChild(frame);

    frame.addEventListener('load', function (event) {
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

        setTimeout(function () {
            let frame = document.getElementById('print-frame');
            frame.remove();
        }, 10);
    }, true);

    frame.contentDocument.location.href = url;
};
