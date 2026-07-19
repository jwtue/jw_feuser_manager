<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Basis-Eigenschaften einer Frontend-Benutzergruppe (Tabelle fe_groups).
 *
 * Gegenstueck zu AbstractFrontendUser: TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
 * war in v11 als deprecated markiert und ist in TYPO3 v12 entfernt. Beim Port wurde die
 * Vererbung auf AbstractEntity umgestellt, ohne die Eigenschaften mitzunehmen — dadurch
 * schlug u. a. $group->getTitle() im ShowFeUserController fehl.
 *
 * Die Feldliste entspricht der TCA von fe_groups (title, description, subgroup).
 * Das extensionseigene Zusatzfeld "image" steht in FrontendUserGroup.
 */
abstract class AbstractFrontendUserGroup extends AbstractEntity
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var ObjectStorage<AbstractFrontendUserGroup>
     */
    protected $subgroup;

    public function __construct(string $title = '')
    {
        $this->setTitle($title);
        $this->subgroup = new ObjectStorage();
    }

    /**
     * Wird von Extbase nach dem Rekonstruieren aus der Datenbank aufgerufen.
     */
    public function initializeObject(): void
    {
        $this->subgroup = $this->subgroup ?? new ObjectStorage();
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param ObjectStorage<AbstractFrontendUserGroup> $subgroup
     */
    public function setSubgroup(ObjectStorage $subgroup): void
    {
        $this->subgroup = $subgroup;
    }

    /**
     * @return ObjectStorage<AbstractFrontendUserGroup>
     */
    public function getSubgroup(): ObjectStorage
    {
        return $this->subgroup;
    }

    public function addSubgroup(AbstractFrontendUserGroup $subgroup): void
    {
        $this->subgroup->attach($subgroup);
    }

    public function removeSubgroup(AbstractFrontendUserGroup $subgroup): void
    {
        $this->subgroup->detach($subgroup);
    }
}
