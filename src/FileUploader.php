<?php declare(strict_types=1);
/**
 * Copyright (C) 2017 Johnny Robeson <johnny@localmomentum.net>
 *
 * This program is free software: you can redistribute it and/or modify
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace LazerBall\HitTracker;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    const EXTENSION_MAP = [
      'mp4a' => 'mp4'
    ];

    /** @var string */
    private $baseDir;
    private $basePrefix;

    public function __construct(string $baseDir, string $basePrefix = '')
    {
        $this->baseDir = realpath($baseDir);
        $this->basePrefix = $basePrefix;
    }

    public function getUploadBaseDir(): string
    {
        return $this->baseDir;
    }

    public function upload(UploadedFile $file, string $prefix = ''): string
    {
        $fileName = md5(uniqid()) . '.' . $this->getFileExtension($file);

        $prefix = $this->cleanPrefix($prefix);
        $dir = $this->baseDir . $prefix;

        $file->move($dir, $fileName);

        return $this->basePrefix . $prefix . $fileName;
    }

    public function uploadFileWithName(UploadedFile $file, string $fileName, string $prefix = ''): string
    {
        $prefix = $this->cleanPrefix($prefix);
        $dir = $this->baseDir .  $prefix;

        $file->move($dir, $fileName);

        return $this->basePrefix . $prefix . $fileName;
    }

    private function getFileExtension(UploadedFile $file): string
    {
        $original = $file->getClientOriginalExtension();
        $guessed = $file->guessExtension() ?: '';
        if (array_key_exists($guessed, self::EXTENSION_MAP)) {
            return self::EXTENSION_MAP[$guessed];
        }

        return $guessed;
    }

    public function cleanPrefix(string $prefix): string
    {
        if (empty($prefix)) {
            return '';
        }

        return '/' . preg_replace(['|^/|', '|/$|'], '', $prefix) . '/';
    }
}
