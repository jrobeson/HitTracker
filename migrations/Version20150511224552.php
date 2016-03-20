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

class Version20150511224552 extends AbstractMigration
{
    use Helpers;

    public function up(Schema $schema)
    {
        $this->connection->update('sylius_settings_parameter',
            ['name' => 'player_hit_points'], ['name' => 'player_life_credits']);
        $this->connection->update('sylius_settings_parameter',
            ['name' => 'player_hit_points_deducted'], ['name' => 'life_credits_deducted']);

        $stmts = [];
        $stmts[] = 'ALTER TABLE games RENAME COLUMN player_life_credits TO player_hit_points';
        $stmts[] = 'ALTER TABLE games RENAME COLUMN life_credits_deducted TO player_hit_points_deducted';

        $stmts[] = 'ALTER TABLE game_players RENAME COLUMN life_credits TO hit_points';

        $this->addStmts($stmts);
    }

    public function down(Schema $schema)
    {
        $this->connection->update('sylius_settings_parameter',
            ['name' => 'player_life_credits'], ['name' => 'player_hit_points']);
        $this->connection->update('sylius_settings_parameter',
            ['name' => 'life_credits_deducted'], ['name' => 'player_hit_points_deducted']);

        $stmts = [];
        $stmts[] = 'ALTER TABLE games RENAME COLUMN player_hit_points TO player_life_credits';
        $stmts[] = 'ALTER TABLE games RENAME COLUMN player_hit_points_deducted TO life_credits_deducted';

        $stmts[] = 'ALTER TABLE game_players RENAME COLUMN hit_points TO life_credits';

        $this->addStmts($stmts);
    }
}
