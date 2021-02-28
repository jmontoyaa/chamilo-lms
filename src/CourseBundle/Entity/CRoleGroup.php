<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CRoleGroup.
 *
 * @ORM\Table(
 *  name="c_role_group",
 *  indexes={
 *      @ORM\Index(name="course", columns={"c_id"}),
 *      @ORM\Index(name="group", columns={"group_id"})
 *  }
 * )
 * @ORM\Entity
 */
class CRoleGroup
{
    /**
     * @ORM\Column(name="iid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected int $iid;

    /**
     * @ORM\Column(name="c_id", type="integer")
     */
    protected int $cId;

    /**
     * @ORM\Column(name="role_id", type="integer", nullable=false)
     */
    protected int $roleId;

    /**
     * @ORM\Column(name="scope", type="string", length=20, nullable=false)
     */
    protected string $scope;

    /**
     * @ORM\Column(name="group_id", type="integer")
     */
    protected int $groupId;

    /**
     * Set roleId.
     *
     * @param int $roleId
     *
     * @return CRoleGroup
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set scope.
     *
     * @param string $scope
     *
     * @return CRoleGroup
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope.
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set cId.
     *
     * @param int $cId
     *
     * @return CRoleGroup
     */
    public function setCId($cId)
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * Get cId.
     *
     * @return int
     */
    public function getCId()
    {
        return $this->cId;
    }

    /**
     * Set groupId.
     *
     * @param int $groupId
     *
     * @return CRoleGroup
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
