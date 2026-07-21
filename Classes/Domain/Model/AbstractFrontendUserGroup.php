<?php

declare(strict_types=1);

namespace JwTue\FeUserManager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Base properties of a frontend user group (table fe_groups).
 *
 * Counterpart to AbstractFrontendUser: TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
 * was marked as deprecated in v11 and is removed in TYPO3 v12. When porting, the
 * inheritance was switched to AbstractEntity without carrying over the properties — which
 * caused, among other things, $group->getTitle() to fail in the ShowFeUserController.
 *
 * The field list corresponds to the TCA of fe_groups (title, description, subgroup).
 * The extension's own additional field "image" is located in FrontendUserGroup.
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
     * Called by Extbase after reconstructing the object from the database.
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
