<?php
/**
 * Comment fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use DateTimeImmutable;

/**
 * Class CommentFixtures.
 */
class CommentFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     */
    public function loadData(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $comment = new Comment();
            $comment->setContent($this->faker->sentence);
            $comment->setDate(
                DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-100 days', '-1 days'))
            );

            $this->manager->persist($comment);
        }

        $this->manager->flush();
    }
}
