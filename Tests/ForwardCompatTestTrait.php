<?php

namespace Exercise\HTMLPurifierBundle\Tests;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

// Auto-adapt to PHPUnit 8 that added a `void` return-type to the setUp/tearDown methods

if (method_exists(\ReflectionMethod::class, 'hasReturnType') && (new \ReflectionMethod(FormIntegrationTestCase::class, 'tearDown'))->hasReturnType()) {
    eval('
    namespace Exercise\HTMLPurifierBundle\Tests;

    /**
     * @internal
     */
    trait ForwardCompatTestTrait
    {
        private function doSetUp(): void
        {
        }

        private function doTearDown(): void
        {
        }

        protected function setUp(): void
        {
            $this->doSetUp();
        }
        protected function tearDown(): void
        {
            $this->doTearDown();
        }
    }
');
} else {
    /**
     * @internal
     */
    trait ForwardCompatTestTrait
    {
        /**
         * @return void
         */
        private function doSetUp()
        {
        }

        /**
         * @return void
         */
        private function doTearDown()
        {
        }

        /**
         * @return void
         */
        protected function setUp()
        {
            $this->doSetUp();
        }

        /**
         * @return void
         */
        protected function tearDown()
        {
            $this->doTearDown();
        }
    }
}
