<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\ServiceFactory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

/**
 * Abstract service factory capable of instantiating services whose names match the
 * pattern <code>doctrine.$serviceType.$serviceName</doctrine>
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AbstractDoctrineServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== $this->getFactoryMapping($container, $requestedName);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mappings = $this->getFactoryMapping($container, $requestedName);

        if (! $mappings) {
            throw new ServiceNotFoundException();
        }

        $factoryClass = $mappings['factoryClass'];
        /* @var $factory \DoctrineModule\Service\AbstractFactory */
        $factory = new $factoryClass($mappings['serviceName']);

        return $factory->createService($container);
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $container
     * @param string                                       $$requestedName
     *
     * @return bool|array
     */
    private function getFactoryMapping(ContainerInterface $container, $requestedName)
    {
        $matches = array();

        if (! preg_match('/^doctrine\.(?P<serviceType>[a-z0-9_]+)\.(?P<serviceName>[a-z0-9_]+)$/', $requestedName, $matches)) {
            return false;
        }

        $config      = $serviceLocator->get('Config');
        $serviceType = $matches['serviceType'];
        $serviceName = $matches['serviceName'];

        if (! isset($config['doctrine_factories'][$serviceType])
            || ! isset($config['doctrine'][$serviceType][$serviceName])
        ) {
            return false;
        }

        return array(
            'serviceType'  => $serviceType,
            'serviceName'  => $serviceName,
            'factoryClass' => $config['doctrine_factories'][$serviceType],
        );
    }
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->__invoke($serviceLocator, $requestedName);
    }



}
