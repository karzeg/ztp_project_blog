<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * Class TagFixtures.
 */
class TagFixtures extends Fixture
{
    /**
     * Faker.
     */
    protected Generator $faker;

    /**
     * Persistence object manager.
     */
    protected ObjectManager $manager;

    /**
     * Load.
     *
     * @param ObjectManager $manager Persistence object manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create();

        for ($i = 0; $i < 30; ++$i) {
            $tag = new Tag();
            $tag->setTitle($this->faker->word);

            $manager->persist($tag);
        }

        $manager->flush();
    }
}
