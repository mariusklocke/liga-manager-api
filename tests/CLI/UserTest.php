<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\CLI;

use PHPUnit\Framework\Attributes\Depends;
use HexagonalPlayground\Tests\Framework\CommandTest;
use HexagonalPlayground\Tests\Framework\DataGenerator;

class UserTest extends CommandTest
{
    public function testCanBeCreated(): void
    {
        $inputs = ['mary.poppins@example.com', DataGenerator::generatePassword(), 'Mary', 'Poppins', 'admin', 'en'];
        $result = $this->runCommand('app:user:create', [], $inputs);
        self::assertExecutionSuccess($result);

        $result = $this->runCommand('app:user:create', ['--default' => null]);
        self::assertExecutionSuccess($result);
    }

    /**
     * @return array
     */
    #[Depends("testCanBeCreated")]
    public function testCanBeListed(): array
    {
        $result = $this->runCommand('app:user:list');
        $users = [];
        foreach (explode("\n", trim($result->output)) as $line) {
            if (str_contains($line, '@')) {
                $columns = array_values(array_filter(explode(' ', $line)));
                $users[] = [
                    'id' => $columns[0],
                    'email' => $columns[1]
                ];
            }
        }

        self::assertExecutionSuccess($result);

        return $users;
    }

    /**
     * @param array $users
     * @return void
     */
    #[Depends("testCanBeListed")]
    public function testCanBeDeleted(array $users): void
    {
        $deletable = array_filter($users, function (array $user) {
            return $user['email'] !== getenv('ADMIN_EMAIL');
        });
        self::assertNotEmpty($deletable);
        $user = array_shift($deletable);
        $result = $this->runCommand('app:user:delete', ['userId' => $user['id']]);
        self::assertExecutionSuccess($result);
    }
}