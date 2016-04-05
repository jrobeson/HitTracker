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
 * @copyright 2016 <johnny@localmomentum.net>
 * @license AGPL-3
 */

namespace LazerBall\HitTracker\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20160315044635 extends AbstractMigration
{
    use Helpers;

    public function up(Schema $schema)
    {
        $stmts = [];

        $stmts[] = 'ALTER TABLE game_players ADD COLUMN holding BOOLEAN NOT NULL DEFAULT FALSE';

        $this->addStmts($stmts);
    }

    public function down(Schema $schema)
    {
        $stmts = [];

        $stmts[] = 'ALTER TABLE game_players DROP COLUMN holding';

        $this->addStmts($stmts);
    }
}
