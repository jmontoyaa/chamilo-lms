<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SequenceCondition.
 *
 * @ORM\Table(name="sequence_condition")
 * @ORM\Entity
 */
class SequenceCondition
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue()
     */
    protected int $id;

    /**
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    protected string $description;

    /**
     * @ORM\Column(name="mat_op", type="string")
     */
    protected string $mathOperation;

    /**
     * @ORM\Column(name="param", type="float")
     */
    protected string $param;

    /**
     * @ORM\Column(name="act_true", type="integer")
     */
    protected string $actTrue;

    /**
     * @ORM\Column(name="act_false", type="string")
     */
    protected string $actFalse;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return SequenceCondition
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getMathOperation()
    {
        return $this->mathOperation;
    }

    /**
     * @param string $mathOperation
     *
     * @return SequenceCondition
     */
    public function setMathOperation($mathOperation)
    {
        $this->mathOperation = $mathOperation;

        return $this;
    }

    /**
     * @return string
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param string $param
     *
     * @return SequenceCondition
     */
    public function setParam($param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * @return string
     */
    public function getActTrue()
    {
        return $this->actTrue;
    }

    /**
     * @param string $actTrue
     *
     * @return SequenceCondition
     */
    public function setActTrue($actTrue)
    {
        $this->actTrue = $actTrue;

        return $this;
    }

    /**
     * @return string
     */
    public function getActFalse()
    {
        return $this->actFalse;
    }

    /**
     * @param string $actFalse
     *
     * @return SequenceCondition
     */
    public function setActFalse($actFalse)
    {
        $this->actFalse = $actFalse;

        return $this;
    }
}
