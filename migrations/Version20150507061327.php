<?php
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
namespace LazerBall\HitTracker\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150507061327 extends AbstractMigration
{
    use Helpers;

    public function up(Schema $schema)
    {
        $stmts = [];

        $stmts[] = 'CREATE SEQUENCE sylius_settings_parameter_id_seq INCREMENT BY 1 MINVALUE 1 START 1';
        $stmts[] = 'CREATE TABLE sylius_settings_parameter (
                        id INT NOT NULL, namespace VARCHAR(255) NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        value TEXT DEFAULT NULL,
                        PRIMARY KEY(id)
                   )';
        $stmts[] = 'COMMENT ON COLUMN sylius_settings_parameter.value IS \'(DC2Type:object)\'';
        $stmts[] = 'CREATE TABLE sessions (
                        session_id VARCHAR(128) NOT NULL,
                        session_data BYTEA NOT NULL,
                        session_time INT NOT NULL,
                        session_lifetime INT NOT NULL,
                        PRIMARY KEY(session_id)
                  )';
        $stmts[] = 'CREATE TABLE game_players (
                      id SERIAL NOT NULL,
                      game_id INT DEFAULT NULL,
                      vest_id INT DEFAULT NULL,
                      team VARCHAR(255) DEFAULT NULL,
                      name VARCHAR(255) NOT NULL,
                      hit_points INT NOT NULL,
                      zone_1 INT NOT NULL,
                      zone_2 INT NOT NULL,
                      zone_3 INT NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'CREATE INDEX idx_player_game_id ON game_players (game_id)';
        $stmts[] = 'CREATE INDEX idx_player_vest_id ON game_players (vest_id)';
        $stmts[] = 'CREATE UNIQUE INDEX idx_player_game_vest ON game_players (game_id, vest_id)';
        $stmts[] = 'CREATE TABLE vests (
                      id SERIAL NOT NULL,
                      radio_id VARCHAR(8) NOT NULL,
                      active BOOLEAN NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'CREATE UNIQUE INDEX idx_vest_radio_id ON vests (radio_id)';
        $stmts[] = 'CREATE TABLE games (
                      id SERIAL NOT NULL,
                      arena INT NOT NULL,
                      player_hit_points INT NOT NULL,
                      player_hit_points_deducted INT NOT NULL,
                      ends_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                      PRIMARY KEY(id)
                  )';
        $stmts[] = 'ALTER TABLE game_players ADD CONSTRAINT FK_players_games
                  FOREIGN KEY (game_id) REFERENCES games (id)
                  ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE';
        $stmts[] = 'ALTER TABLE game_players ADD CONSTRAINT fk_players_vests
                  FOREIGN KEY (vest_id) REFERENCES vests (id)
                  NOT DEFERRABLE INITIALLY IMMEDIATE';

        $this->addStmts($stmts);
    }

    public function down(Schema $schema)
    {
        $stmts = [];
        $stmts[] = 'CREATE SCHEMA public';
        $stmts[] = 'ALTER TABLE game_players DROP CONSTRAINT fk_players_vests';
        $stmts[] = 'ALTER TABLE game_players DROP CONSTRAINT fk_players_games';
        $stmts[] = 'DROP SEQUENCE sylius_settings_parameter_id_seq CASCADE';
        $stmts[] = 'DROP TABLE sessions';
        $stmts[] = 'DROP TABLE game_players';
        $stmts[] = 'DROP TABLE vests';
        $stmts[] = 'DROP TABLE games';
        $stmts[] = 'DROP TABLE sylius_settings_parameter';

        $this->addStmts($stmts);
    }
}
