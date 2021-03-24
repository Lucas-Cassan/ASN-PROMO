<?php

namespace App\Entity;

use App\Repository\StatsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StatsRepository::class)
 */
class Stats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="integer")
     */
    private $victoires;

    /**
     * @ORM\Column(type="integer")
     */
    private $defaites;

    /**
     * @ORM\Column(type="integer")
     */
    private $parties;

    /**
     * @ORM\Column(type="integer")
     */
    private $rang;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getVictoires(): ?int
    {
        return $this->victoires;
    }

    public function setVictoires(int $victoires): self
    {
        $this->victoires = $victoires;

        return $this;
    }

    public function getDefaites(): ?int
    {
        return $this->defaites;
    }

    public function setDefaites(int $defaites): self
    {
        $this->defaites = $defaites;

        return $this;
    }

	public function getParties(): ?int
	{
		return $this->parties;
	}

	public function setParties(int $parties): self
	{
		$this->parties = $parties;

		return $this;
	}

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }
}
