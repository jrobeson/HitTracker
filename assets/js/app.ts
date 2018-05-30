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
import 'bootstrap';
import 'jquery-countdown';
import './jquery-ujs.js';
import './jquery.color.js';

import { alertDismiss, printScores } from './ui-util';

const toggleVest = (address: string, value: number) => {
  const request = $.ajax({
    url: `${window.location.origin}/blegateway/unit/${address}/${value}`,
    type: 'POST',
    headers: {
      Accept: 'application/json',
    },
  });
  request.done(() => true);
};

$(document).ready(() => {
  alertDismiss();

  $('.unit-turn-on').click(function(e) {
    const address = $(this).data('unit-address');
    toggleVest(address, 1);
    e.preventDefault();
  });
  $('.unit-turn-off').click(function(e) {
    const address = $(this).data('unit-address');
    toggleVest(address, 0);
    e.preventDefault();
  });

  $('form[id="game_form"] select[id$="_unit"]').focusout(function(e) {
    const address = $(this)
      .children('option:selected')
      .data('unit-address');
    if (address) {
      toggleVest(address, 1);
    }
  });
  $('form[id="game_form"] select[id="game_reload_players"]').change(function() {
    const gameId = $(this).val();
    const request = $.ajax({
      url: `${window.location.origin}/games/${gameId}`,
      headers: {
        Accept: 'application/json',
      },
    });
    request.done(game => {
      const teamPlayers = {} as any;
      for (const player of game.players) {
        const pt = player.team;
        if (!teamPlayers.hasOwnProperty(pt)) {
          teamPlayers[pt] = {};
        }

        teamPlayers[pt][player.unit.id] = player;
      }

      const teams = Object.keys(teamPlayers);

      $('.new-game-teams').each(function() {
        const team = teams.shift() || '';
        $(this)
          .find('.team-no input')
          .val(team);
        const players = teamPlayers[team];
        const playerList = Object.values(players) as any[];
        $(this)
          .find('.player')
          .each(function() {
            const thisPlayer = playerList.shift() as any;
            let name = '';
            let unitId = '';
            if (thisPlayer) {
              name = thisPlayer.name;
              unitId = thisPlayer.unit.id;
            }
            $(this)
              .find('input[id$="_name"]')
              .val(name);
            const unitSelector = $(this).find('select[id$="_unit"]');
            unitSelector.val(unitId);
            unitSelector.trigger('focusout');
          });
      });
    });
  });

  $('form[name="game"]').submit(function() {
    $('.new-game-teams').each(function() {
      const team = $(this)
        .find('.team-no input')
        .val() as string;
      $(this)
        .find('input[id$="_team"]')
        .each(function() {
          $(this).val(team.trim());
        });
    });
  });

  if ($('body').hasClass('hittracker-game-active') || $('body').hasClass('hittracker-game-scoreboard')) {
    const source = new EventSource('/events/game');
    $(window).on('unload', () => {
      source.close();
    });

    if ($('body').hasClass('hittracker-game-scoreboard')) {
      source.addEventListener('game.start', (e: any) => {
        window.location.reload(true);
      });
    }

    source.addEventListener('game.end', (e: any) => {
      queueActivity('<li>Game Ends</li>');
    });

    source.addEventListener('game.hit', (e: any) => {
      const eventData = $.parseJSON(e.data);
      const hit = eventData.content;
      const targetPlayer = hit.target_player;

      // queueActivity(`<li>${player.name} hit $[targetPlayer.name} in Zone ${targetPlayer.zone}</li>`);
      if (targetPlayer.zone_hits) {
        pushHit(`.player-${targetPlayer.id} .zone-${targetPlayer.zone}`, parseInt(targetPlayer.zone_hits, 10));
      }
      if (targetPlayer.hit_points) {
        $(`.player-${targetPlayer.id} .player-hit-points`).text(targetPlayer.hit_points);
        const team = targetPlayer.team.replace(' ', '-').toLowerCase();
        $(`.${team} .team-total-hp`).text(hit.target_team_hit_points);
      }
      if (targetPlayer.score) {
        $(`.player-${targetPlayer.id} .player-score`).text(targetPlayer.score);
        const team = targetPlayer.team.replace(' ', '-').toLowerCase();
        $(`.${team} .team-total-score`).text(hit.target_team_score);
      }
    });

    initCountdown($('#game-time-countdown'));
  }

  $('#print-scores').click(function(event) {
    event.preventDefault();
    const scoreElement = $(this) as any;
    printScores(scoreElement.attr('href'));
  });
});

function queueActivity(content: any) {
  const gameActivitySelector = $('.game-activity ul li') as any;
  if (gameActivitySelector.size() > 10) {
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
function initCountdown(selector: any) {
  let gameEnd = selector.data('game-end-time');
  const clientTime = new Date().getTime();
  const serverTime = new Date(parseInt(selector.data('server-time'), 10)).getTime();
  const offset = serverTime - clientTime;
  gameEnd = gameEnd - offset;

  const formatDate = (event: any) => {
    let format = '%M:%S';
    if (event.offset.hours > 0) {
      format = `%-H:${format}`;
    }
    return event.strftime(format);
  };
  selector
    .countdown(gameEnd)
    .on('update.countdown', function(event: any) {
      $(event.target).text(formatDate(event));
    })
    .on('finish.countdown', function(event: any) {
      $(event.target).text(formatDate(event));
      const musicSelector = $('#active-game-music') as any;
      if (musicSelector.length) {
        musicSelector.get(0).pause();
      }
    });
}

function pushHit(selector: any, zoneHits: number) {
  const value = $(selector).text();
  $(selector)
    .animate({ color: '#a50b00' }, 500, function() {
      $(this).text(zoneHits);
    })
    .animate({ color: '#000' }, 500);
}
