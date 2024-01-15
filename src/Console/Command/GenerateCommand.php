<?php

declare(strict_types=1);

namespace TomasVotruba\SymfonyConfigGenerator\Console\Command;

use Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TomasVotruba\SymfonyConfigGenerator\Enum\SymfonyClass;

final class GenerateCommand extends Command
{
    /**
     * @var string[]
     */
    private const EXTENSION_CLASSES = [
        SymfonyClass::DOCTRINE_EXTENSION,
        SymfonyClass::MONOLOG_EXTENSION,
        SymfonyClass::SECURITY_EXTENSION,
        SymfonyClass::TWIG_EXTENSION,
        SymfonyClass::DOCTRINE_EXTENSION,
    ];

    private SymfonyStyle $symfonyStyle;

    protected function configure(): void
    {
        $this->setName('generate-config-classes');
        $this->setDescription(
            'Generate Symfony config classes to /var/cache/Symfony directory, see https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);

        $configBuilderGenerator = new ConfigBuilderGenerator(getcwd() . '/var/cache');
        $this->symfonyStyle->newLine();

        foreach (self::EXTENSION_CLASSES as $extensionClass) {
            // skip for non-existing classes
            if (! class_exists($extensionClass)) {
                continue;
            }

            $configuration = $this->createExtensionConfiguration($extensionClass);
            if (! $configuration instanceof ConfigurationInterface) {
                continue;
            }

            $configBuilderGenerator->build($configuration);
        }

        return self::SUCCESS;
    }

    /**
     * @param class-string $extensionClass
     */
    private function createExtensionConfiguration(string $extensionClass): ?ConfigurationInterface
    {
        $containerBuilderClass = SymfonyClass::CONTAINER_BUILDER;

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', false);

        /** @var Extension $extension */
        $extension = new $extensionClass();

        return $extension->getConfiguration([], $containerBuilder);
    }
}
