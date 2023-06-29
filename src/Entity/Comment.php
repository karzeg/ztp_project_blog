<?php
/**
 * Comment entity.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class Comment.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
class Comment
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Content.
     */
    #[ORM\Column(type: 'text')]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 65000)]
    private ?string $content = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $date;

    /**
     * Post.
     *
     * @var Post|null
     */
    #[ORM\ManyToOne(targetEntity: Post::class, fetch: 'EXTRA_LAZY')]
    #[Assert\Type(Post::class)]
    private $post;

    /**
     * Author.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[Assert\Type(User::class)]
    private ?User $author;

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
     *
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Setter for date.
     *
     * @param \DateTimeImmutable $date Date
     */
    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    /**
     * Getter for Post.
     *
     * @return Post|null Post
     */
    public function getPost(): ?Post
    {
        return $this->post;
    }

    /**
     * Setter for Post.
     *
     * @param Post|null $post
     *
     * @return $this
     */
    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Getter for Author.
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Setter for Author.
     *
     * @param User|null $author
     *
     * @return $this
     */
    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
