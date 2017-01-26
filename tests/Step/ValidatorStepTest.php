<?php

namespace Ddeboer\DataImport\Tests\Step;

use Ddeboer\DataImport\Step\ValidatorStep;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorStepTest extends \PHPUnit_Framework_TestCase
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var ValidatorStep */
    private $filter;

    protected function setUp()
    {
        $this->validator = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $this->filter = new ValidatorStep($this->validator);
    }

    public function testProcess()
    {
        $data = ['title' => null];

        $this->filter->add('title', $constraint = new Constraints\NotNull());

        $list = new ConstraintViolationList();
        $list->add($this->buildConstraintViolation());

        $this->validator->expects($this->once())
                        ->method('validate')
                        ->willReturn($list);

        $this->assertFalse($this->filter->process($data));

        $this->assertEquals([1 => $list], $this->filter->getViolations());
    }

    /**
     * @expectedException \Ddeboer\DataImport\Exception\ValidationException
     */
    public function testProcessWithExceptions()
    {
        $data = ['title' => null];

        $this->filter->add('title', $constraint = new Constraints\NotNull());
        $this->filter->throwExceptions();

        $list = new ConstraintViolationList();
        $list->add($this->buildConstraintViolation());

        $this->validator->expects($this->once())
                        ->method('validate')
                        ->willReturn($list);

        $this->assertFalse($this->filter->process($data));
    }

    public function testProcessWithAllowedExtraFields()
    {
        $this->filter->addOption('allowExtraFields', true);

        $data = ['title' => null, 'telephone' => '0155/555-555'];

        $this->filter->add('title', $constraint = new Constraints\NotNull());

        $list = new ConstraintViolationList();
        $list->add($this->buildConstraintViolation());

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($list);

        $this->assertFalse($this->filter->process($data));

        $this->assertEquals([1 => $list], $this->filter->getViolations());
    }

    public function testPriority()
    {
        $this->assertEquals(128, $this->filter->getPriority());
    }

    private function buildConstraintViolation()
    {
        return $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
