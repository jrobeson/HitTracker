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
import './jquery-ujs.ts';
import './jquery.color.js';

import './i18n.ts';
import './transitionary.tsx';

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

$(() => {
  alertDismiss();

  $('.new-game-teams').each(function() {
    const teamColor = $(this)
      .find('.team-color')
      .val();
    $(this)
      .find('.unit')
      .find(`option[data-unit-color][data-unit-color!="${teamColor}"]`)
      .remove();
  });
  $('.unit-turn-on').on('click', function(e) {
    const address = $(this).data('unit-address');
    toggleVest(address, 1);
    e.preventDefault();
  });
  $('.unit-turn-off').on('click', function(e) {
    const address = $(this).data('unit-address');
    toggleVest(address, 0);
    e.preventDefault();
  });

  $('form[id="game_form"] select[id$="_unit"]').on('focusout', function() {
    const address = $(this)
      .children('option:selected')
      .data('unit-address');
    if (address) {
      toggleVest(address, 1);
    }
  });
  $('form[id="game_form"] select[id="game_reload_players"]').on('change', function() {
    const gameId = $(this).val();
    const request = $.ajax({
      url: `${window.location.origin}/games/${gameId}`,
      headers: {
        Accept: 'application/json',
      },
    });
    request.done(game => {
      const teamPlayers = {} as any;
      for (const team of game.teams) {
        const pt = team.name;
        if (!teamPlayers.hasOwnProperty(pt)) {
          teamPlayers[pt] = {};
        }
        for (const player of team.players) {
          teamPlayers[pt][player.unit.id] = player;
        }
      }

      const teams = Object.keys(teamPlayers);

      $('.new-game-teams').each(function() {
        const team = teams.shift() || '';
        const players = teamPlayers[team];
        // tslint:disable-next-line: no-unnecessary-type-assertion
        const playerList = Object.values(players) as any[];
        $(this)
          .find('.player')
          .each(function() {
            // tslint:disable-next-line: no-unnecessary-type-assertion
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

  $('#print-scores').on('click', function(event) {
    event.preventDefault();
    const scoreElement = $(this) as any;
    printScores(scoreElement.attr('href'));
  });
});
