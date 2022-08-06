<?php
/**
 * Comment entity.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Comment.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
class Comment
{
    /**
     * Primary key.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Content.
     *
     * @var string|null
     */
    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    /**
     * @var DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $date;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'comments')]
    private $post;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for content.
     *
     * @return string|null Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Setter for content.
     *
     * @param string $content Content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Getter for date.
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Setter for date.
     */
    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
