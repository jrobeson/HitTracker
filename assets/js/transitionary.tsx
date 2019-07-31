import React from 'react';
import ReactDOM from 'react-dom';
import useAxios from 'axios-hooks';
import { SSEProvider, useSSE } from 'react-hooks-sse';

import { GameScores } from './components/GameScores';

const activePage = document.body.dataset.currentPage!;
const eventSourceUrl = document.body.dataset.gameEventsSubscribeUrl;

if (['hittracker-game-active', 'hittracker-game-scoreboard', 'hittracker-game-show'].includes(activePage)) {
  const gameScoresElement = document.getElementById('game-scores-react');
  const gameId = parseInt((gameScoresElement as HTMLElement).dataset.gameId as string);

  const GamePage = () => {
    const currentGameId = useSSE('game.start', {
      initialState: gameId,
      stateReducer(_state: any, changes: any) {
        return changes.data.gameId;
      },
    });
    const [{ data, loading, error }] = useAxios(`/api/games/${currentGameId}`);
    const game = data;
    if (loading) return <p>Loading...</p>;
    if (error) return <p>{error.message}</p>;

    return <GameScores game={game} />;
  };
  const App = () => {
    return (
      <React.StrictMode>
        <SSEProvider endpoint={eventSourceUrl}>
          <GamePage />
        </SSEProvider>
      </React.StrictMode>
    );
  };

  ReactDOM.render(<App />, gameScoresElement);
}
