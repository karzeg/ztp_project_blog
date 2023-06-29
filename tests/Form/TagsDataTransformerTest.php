<?php
/**
 * Tags Data Transformer Test.
 */

namespace App\Tests\Form\DataTransformer;

use App\Entity\Tag;
use App\Form\DataTransformer\TagsDataTransformer;
use App\Service\TagServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class TagsDataTransformerTest extends TestCase
{
    /**
     * @return void
     */
    public function testTransform(): void
    {
        $tag1 = new Tag();
        $tag1->setTitle('TestTag1');
        $tag2 = new Tag();
        $tag2->setTitle('TestTag2');

        $tags = new ArrayCollection([$tag1, $tag2]);

        $tagService = $this->createMock(TagServiceInterface::class);
        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->transform($tags);

        $this->assertStringContainsString('TestTag1, TestTag2', $result);
    }

    /**
     * @return void
     */
    public function testReverseTransform(): void
    {
        $tagTitle1 = 'TestTag1';
        $tagTitle2 = 'TestTag2';

        $tagService = $this->createMock(TagServiceInterface::class);
        $tagService->expects($this->exactly(2))
            ->method('findOneByTitle')
            ->willReturn(null);

        $tagService->expects($this->exactly(2))
            ->method('save');

        $transformer = new TagsDataTransformer($tagService);

        $result = $transformer->reverseTransform("$tagTitle1,$tagTitle2");

        $this->assertCount(2,$result);
        $this->assertInstanceOf(Tag::class,$result[0]);
        $this->assertInstanceOf(Tag::class,$result[1]);
        $this->assertEquals($tagTitle1,$result[0]->getTitle());
        $this->assertEquals($tagTitle2,$result[1]->getTitle());
    }
}