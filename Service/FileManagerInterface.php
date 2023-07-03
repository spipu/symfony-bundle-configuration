<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\ConfigurationBundle\Service;

use Spipu\ConfigurationBundle\Entity\Definition;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileManagerInterface
{
    public function isAllowed(): bool;

    public function saveFile(Definition $definition, string $scope, UploadedFile $file): string;

    public function loadFile(Definition $definition, string $scope, string $filename): ?string;
}
