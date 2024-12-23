<?php

namespace b1u3s7\bedwars\game\utils;


class TeamUpgrade
{
    private Upgrade $upgrade;
    private int $tier;

    public function __construct(Upgrade $upgrade)
    {
        $this->upgrade = $upgrade;
        $this->tier = 0;
    }

    public function getUpgrade(): Upgrade
    {
        return $this->upgrade;
    }

    public function setUpgrade(Upgrade $upgrade): void
    {
        $this->upgrade = $upgrade;
    }

    public function getTier(): int
    {
        return $this->tier;
    }

    public function increaseTier(): void
    {
        $this->tier++;
    }

    public function setTier(int $tier): void
    {
        $this->tier = $tier;
    }
}