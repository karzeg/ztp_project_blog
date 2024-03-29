<?php
/**
 * Tag entity.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Tag.
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
class Tag
{
    /**
     * Primary key.
     *
     * @var int|null Id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Title.
     *
     * @var string|null Title
     */
    #[ORM\Column(type: 'string', length: 45)]
    #[Assert\Type('string')]
    #[Assert\length(min: 1, max: 45)]
    private ?string $title = null;

    /**
     * Posts.
     *
     * @var ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private $posts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

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
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string|null $title Title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Add post.
     *
     * @param Post $post Post entity
     */
    public function addPost(Post $post): void
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
        }
    }

    /**
     * Remove post.
     *
     * @param Post $post
     * @return void
     */
    public function removePost(Post $post): void
    {
        $this->posts->removeElement($post);
    }
}
