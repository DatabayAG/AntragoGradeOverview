<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Plugin\AntragoGradeOverview\Model;

use DateTime;

class ImportHistory
{
    protected int $id;
    protected int $userId;
    protected DateTime $date;
    protected int $datasetsAdded;
    protected int $datasetsChanged;
    protected int $datasetsUnchanged;
    protected string $firstName;
    protected string $lastName;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getDatasetsAdded(): int
    {
        return $this->datasetsAdded;
    }

    public function setDatasetsAdded(int $datasetsAdded): self
    {
        $this->datasetsAdded = $datasetsAdded;
        return $this;
    }

    public function getDatasetsChanged(): int
    {
        return $this->datasetsChanged;
    }

    public function setDatasetsChanged(int $datasetsChanged): self
    {
        $this->datasetsChanged = $datasetsChanged;
        return $this;
    }

    public function getDatasetsUnchanged(): int
    {
        return $this->datasetsUnchanged;
    }

    public function setDatasetsUnchanged(int $datasetsUnchanged): self
    {
        $this->datasetsUnchanged = $datasetsUnchanged;
        return $this;
    }
}
