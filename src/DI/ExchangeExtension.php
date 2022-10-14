<?php declare(strict_types = 1);

/**
 * ExchangeExtension.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:Exchange!
 * @subpackage     DI
 * @since          0.1.0
 *
 * @date           19.12.20
 */

namespace FastyBird\Exchange\DI;

use FastyBird\Exchange\Consumer;
use FastyBird\Exchange\Entities;
use FastyBird\Exchange\Publisher;
use Nette;
use Nette\DI;
use function assert;
use function is_bool;

/**
 * Exchange plugin extension container
 *
 * @package        FastyBird:Exchange!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class ExchangeExtension extends DI\CompilerExtension
{

	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'fbExchange',
	): void
	{
		$config->onCompile[] = static function (
			Nette\Configurator $config,
			DI\Compiler $compiler,
		) use ($extensionName): void {
			$compiler->addExtension($extensionName, new ExchangeExtension());
		};
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('consumer'), new DI\Definitions\ServiceDefinition())
			->setType(Consumer\Container::class);

		$builder->addDefinition($this->prefix('publisher'), new DI\Definitions\ServiceDefinition())
			->setType(Publisher\Container::class);

		$builder->addDefinition($this->prefix('entityFactory'), new DI\Definitions\ServiceDefinition())
			->setType(Entities\EntityFactory::class);
	}

	/**
	 * @throws Nette\DI\MissingServiceException
	 */
	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		/**
		 * CONSUMERS PROXY
		 */

		$consumerProxyServiceName = $builder->getByType(Consumer\Container::class);

		if ($consumerProxyServiceName !== null) {
			$consumerProxyService = $builder->getDefinition($consumerProxyServiceName);
			assert($consumerProxyService instanceof DI\Definitions\ServiceDefinition);

			$consumerServices = $builder->findByType(Consumer\Consumer::class);

			foreach ($consumerServices as $consumerService) {
				if (
					$consumerService->getType() !== Consumer\Container::class
					&& (
						$consumerService->getAutowired() !== false
						|| !is_bool($consumerService->getAutowired())
					)
				) {
					// Container is not allowed to be autowired
					$consumerService->setAutowired(false);

					$consumerProxyService->addSetup('?->register(?)', [
						'@self',
						$consumerService,
					]);
				}
			}
		}

		/**
		 * PUBLISHERS PROXY
		 */

		$publisherProxyServiceName = $builder->getByType(Publisher\Container::class);

		if ($publisherProxyServiceName !== null) {
			$publisherProxyService = $builder->getDefinition($publisherProxyServiceName);
			assert($publisherProxyService instanceof DI\Definitions\ServiceDefinition);

			$publisherServices = $builder->findByType(Publisher\Publisher::class);

			foreach ($publisherServices as $publisherService) {
				if (
					$publisherService->getType() !== Publisher\Container::class
					&& (
						$publisherService->getAutowired() !== false
						|| !is_bool($publisherService->getAutowired())
					)
				) {
					// Container is not allowed to be autowired
					$publisherService->setAutowired(false);

					$publisherProxyService->addSetup('?->register(?)', [
						'@self',
						$publisherService,
					]);
				}
			}
		}
	}

}
