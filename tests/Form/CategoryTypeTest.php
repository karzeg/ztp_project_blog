<?php

namespace App\Tests\Forms;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use DateTime;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoryTypeTest extends TypeTestCase
{

    public function testSubmitValidDate()
    {
        $formatData = [
            'title' => 'TestCategory',
        ];

        $model = new Category();
        $form = $this->factory->create(CategoryType::class, $model);

        $expected = new Category();
        $expected->setTitle('TestCategory');
        $form->submit($formatData);
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expected->getTitle(), $model->getTitle());
        $this->assertEquals($expected->getId(), $model->getId());
    }

}