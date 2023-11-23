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

namespace Spipu\ConfigurationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\TimestampableInterface;
use Spipu\UiBundle\Entity\TimestampableTrait;

#[ORM\Entity(repositoryClass: 'Spipu\ConfigurationBundle\Repository\ConfigurationRepository')]
#[ORM\Table(name: "spipu_configuration")]
#[ORM\UniqueConstraint(name: "UNIQ_CODE_SCOPE", columns: ["code", "scope"])]
#[ORM\HasLifecycleCallbacks]
class Configuration implements EntityInterface, TimestampableInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 128, options: ["default" => ""])]
    private string $scope = '';

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getScope(): ?string
    {
        if ($this->scope === '') {
            return null;
        }

        return $this->scope;
    }

    public function setScope(?string $scope): self
    {
        if ($scope === null) {
            $scope = '';
        }

        $this->scope = $scope;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
