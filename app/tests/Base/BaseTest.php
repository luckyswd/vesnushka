<?php

namespace Base;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->container = static::getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();

        self::ensureKernelShutdown();
    }

    public function testDatabaseConnection(): void
    {
        try {
            $connection = $this->entityManager->getConnection();
            $connection->connect();

            $result = $connection->executeQuery('SELECT 1')->fetchOne();
            $this->assertEquals(1, $result, 'Simple query should return 1');
        } catch (\Exception $e) {
            $this->fail("Failed to connect to the database: " . $e->getMessage());
        }
    }
}
