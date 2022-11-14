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

/**
 * @ORM\Entity(repositoryClass="Spipu\ConfigurationBundle\Repository\ConfigurationRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(
 *     name="spipu_configuration",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UNIQ_CODE_SCOPE", columns={"code", "scope"})}
 * )
 */
class Configuration implements EntityInterface, TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $scope;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Configuration
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string|null $scope
     * @return Configuration
     */
    public function setScope(?string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Configuration
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
