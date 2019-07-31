import React from 'react';
import PropTypes from 'prop-types';

import * as _ from 'lodash';
import Countdown from 'react-countdown-now';
import { useTranslation } from 'react-i18next';
import { useSSE } from 'react-hooks-sse';

import { GameInterface, TeamInterface, PlayerInterface } from '../interfaces';
import { formatGameTotalTime } from '../ui-util';
import { TimedTextColorChange } from './TimedTextColorChange';

export interface TeamScoresProps {
  team: TeamInterface;
  game: GameInterface;
}

const TeamScores = ({ game, team }: TeamScoresProps) => {
  const { t } = useTranslation();
  const zoneCount = _.maxBy(team.players, player => player.unit.zones)!.unit.zones;
  const columnCount = 3 + zoneCount;
  let showHp = false;
  let showScores = true;
  if (game.gameType !== 'target') {
    showHp = true;
    showScores = false;
  }

  return (
    <table className={`table scores team ${team.name.toLowerCase().replace(/ /g, '-')}`}>
      <thead>
        <tr className={`team-color-${team.color}`}>
          <th className="text-center font-weight-bold" colSpan={columnCount}>
            {team.name}
          </th>
        </tr>
        <tr>
          <th>#</th>
          <th>{t('hittracker.game.player_name')}</th>
          {_.range(2).map(zone => {
            const zoneNo = zone + 1;
            return <th key={zoneNo}>{t('hittracker.game.zone_no', { zoneNo: zoneNo })}</th>;
          })}
          {showHp && <th className="player-total">{t('hittracker.game.hit_points')}</th>}
          {showScores && <th className="player-score">{t('hittracker.game.score')}</th>}
        </tr>
      </thead>
      <tbody>
        {team.players.map(player => (
          <tr className={`player-${player.id} scores-player`} key={player.id}>
            <td className="player-id">{player.unit.id}</td>
            <td>{player.name}</td>
            {_.range(player.unit.zones).map(zone => {
              const zoneNo = zone + 1;
              return (
                <td key={zoneNo}>
                  <TimedTextColorChange colorClass="game-hit-color">{player.zoneHits[zoneNo]}</TimedTextColorChange>
                </td>
              );
            })}
            {showHp && (
              <td className="player-hit-points">
                <TimedTextColorChange colorClass="game-hit-color">{player.hitPoints}</TimedTextColorChange>
              </td>
            )}
            {showScores && (
              <td className="player-score">
                <TimedTextColorChange colorClass="game-hit-color">{player.score}</TimedTextColorChange>
              </td>
            )}
          </tr>
        ))}
      </tbody>
      <tfoot>
        <tr>
          <td colSpan={zoneCount} />
          <td colSpan={2} className="text-right font-weight-bold">
            {t('hittracker.game.team_total')}
          </td>
          {showScores && (
            <td className="team-total-score">
              <TimedTextColorChange colorClass="game-hit-color">{team.score}</TimedTextColorChange>
            </td>
          )}
          {showHp && (
            <td className="team-total-hp">
              <TimedTextColorChange colorClass="game-hit-color">{team.hitPoints}</TimedTextColorChange>
            </td>
          )}
        </tr>
      </tfoot>
    </table>
  );
};

TeamScores.propTypes = {
  game: PropTypes.object,
  team: PropTypes.object,
};

interface GameScoresProps {
  game: GameInterface;
}
export const GameScores = ({ game }: GameScoresProps) => {
  const { t } = useTranslation();
  const onCountdownFinish = () => {
    const gameMusicSelector = document.getElementById('active-game-music');
    if (gameMusicSelector) {
      (gameMusicSelector as HTMLAudioElement).pause();
    }
  };

  const updatedTeams = useSSE('game.hit', {
    initialState: _.cloneDeep(game.teams),
    stateReducer(state: any, changes: any) {
      const { teamData, playerData } = changes.data;
      if (teamData) {
        return state.map((team: TeamInterface) => {
          if (teamData.id === team.id) {
            team = { ...team, ...teamData };
            team.players = team.players.map((player: PlayerInterface) => {
              if (playerData.id === player.id) {
                player = { ...player, ...playerData };
              }
              return player;
            });
          }
          return team;
        });
      }
      return changes;
    },
  });

  return (
    <React.Fragment>
      <div className="row w-100">
        <div className="col-lg-6 col-12">
          <h2 className="text-center">
            {t('hittracker.game.length_total', { time: formatGameTotalTime(game.timeTotal) })}
          </h2>
        </div>

        <div className="col-lg-6 col-12">
          {game.active && (
            <h2 className="text-center">
              {t('hittracker.game.time_left')}&nbsp;
              <Countdown date={game.endsAt} daysInHours={true} onComplete={onCountdownFinish} />
            </h2>
          )}
        </div>
      </div>

      <div className="row w-100">
        {game.teams.map((team, index) => {
          return (
            <div key={team.id} className="col-md-6 col-12">
              <TeamScores game={game} team={updatedTeams[index]} />
            </div>
          );
        })}
      </div>
    </React.Fragment>
  );
};
