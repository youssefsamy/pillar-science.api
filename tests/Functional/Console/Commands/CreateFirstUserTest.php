<?php

namespace App\Functional\Console\Commands;

use App\Console\Commands\CreateFirstUser;
use App\Models\User;
use App\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\MockObject\MockObject;

class CreateFirstUserTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @group RV-10
     * @throws \ReflectionException
     */
    public function testHandle()
    {
        /** @var MockObject $command */
        $command = $this->createPartialMockWithConstructor(CreateFirstUser::class, ['ask', 'secret']);

        $command->expects($this->exactly(2))
            ->method('ask')
            ->withConsecutive(
                ['Name'],
                ['Email']
            )
            ->willReturnOnConsecutiveCalls('Functional Testing', 'test@pillar.science');

        $command->expects($this->exactly(2))
            ->method('secret')
            ->withConsecutive(
                ['Password'],
                ['Confirm password']
            )
            ->willReturnOnConsecutiveCalls('secret-password', 'secret-password');

        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($command);

        $this->artisan('pillar:create-first-user', ['--no-interaction' => true]);
    }

    /**
     * @group RV-10
     * @throws \ReflectionException
     */
    public function testHandlePasswordNotMatchingOnFirstTry()
    {
        /** @var MockObject $command */
        $command = $this->createPartialMockWithConstructor(CreateFirstUser::class, ['ask', 'secret']);

        $command->expects($this->exactly(2))
            ->method('ask')
            ->withConsecutive(
                ['Name'],
                ['Email']
            )
            ->willReturnOnConsecutiveCalls('Functional Testing', 'test@pillar.science');

        $command->expects($this->exactly(4))
            ->method('secret')
            ->withConsecutive(
                ['Password'],
                ['Confirm password'],
                ['Password'],
                ['Confirm password']
            )
            ->willReturnOnConsecutiveCalls('secret-password', 'oups', '123', '123');

        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($command);

        $this->artisan('pillar:create-first-user', ['--no-interaction' => true]);
    }

    /**
     * @group RV-10
     * @throws \ReflectionException
     */
    public function testHandleUserCancels()
    {
        /** @var MockObject $command */
        $command = $this->createPartialMockWithConstructor(CreateFirstUser::class, ['ask', 'secret', 'confirm']);

        $command->expects($this->exactly(2))
            ->method('ask')
            ->withConsecutive(
                ['Name'],
                ['Email']
            )
            ->willReturnOnConsecutiveCalls('Functional Testing', 'test@pillar.science');

        $command->expects($this->exactly(2))
            ->method('secret')
            ->withConsecutive(
                ['Password'],
                ['Confirm password']
            )
            ->willReturnOnConsecutiveCalls('secret-password', 'secret-password');

        $command->expects($this->exactly(1))
            ->method('confirm')
            ->willReturn(false);

        $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($command);

        $return = $this->artisan('pillar:create-first-user', ['--no-interaction' => true]);

        $this->assertEquals(0, $return);
    }

    /**
     * @group RV-10
     */
    public function testHandleFirstUserAlreadyCreated()
    {
        factory(User::class)->create();

        $return = $this->artisan('pillar:create-first-user', ['--no-interaction' => true]);

        $this->assertEquals(-1, $return);
    }
}
