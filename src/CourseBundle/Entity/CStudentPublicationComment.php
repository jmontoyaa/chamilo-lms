<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CourseBundle\Entity;

use Chamilo\CoreBundle\Entity\AbstractResource;
use Chamilo\CoreBundle\Entity\ResourceInterface;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * CStudentPublicationComment.
 *
 * @ORM\Table(
 *  name="c_student_publication_comment",
 *  indexes={
 *      @ORM\Index(name="course", columns={"c_id"}),
 *      @ORM\Index(name="user", columns={"user_id"}),
 *      @ORM\Index(name="work", columns={"work_id"})
 *  }
 * )
 * @ORM\Entity
 */
class CStudentPublicationComment extends AbstractResource implements ResourceInterface
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
     * @ORM\Column(name="work_id", type="integer", nullable=false)
     */
    protected int $workId;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected ?string $comment;

    /**
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    protected ?string $file;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected int $userId;

    /**
     * @ORM\Column(name="sent_at", type="datetime", nullable=false)
     */
    protected DateTime $sentAt;

    public function __construct()
    {
        $this->sentAt = new DateTime();
    }

    public function __toString(): string
    {
        return (string) $this->getIid();
    }

    public function getIid(): int
    {
        return $this->iid;
    }

    /**
     * Set workId.
     *
     * @param int $workId
     *
     * @return CStudentPublicationComment
     */
    public function setWorkId($workId)
    {
        $this->workId = $workId;

        return $this;
    }

    /**
     * Get workId.
     *
     * @return int
     */
    public function getWorkId()
    {
        return $this->workId;
    }

    /**
     * Set cId.
     *
     * @param int $cId
     *
     * @return CStudentPublicationComment
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
     * Set comment.
     *
     * @param string $comment
     *
     * @return CStudentPublicationComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set file.
     *
     * @param string $file
     *
     * @return CStudentPublicationComment
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CStudentPublicationComment
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set sentAt.
     *
     * @param DateTime $sentAt
     *
     * @return CStudentPublicationComment
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt.
     *
     * @return DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    public function getResourceIdentifier(): int
    {
        return $this->getIid();
    }

    public function getResourceName(): string
    {
        $text = strip_tags($this->getComment());
        $slugify = new Slugify();
        $text = $slugify->slugify($text);

        return (string) substr($text, 0, 40);
    }

    public function setResourceName(string $name): self
    {
        return $this->setComment($name);
    }
}
