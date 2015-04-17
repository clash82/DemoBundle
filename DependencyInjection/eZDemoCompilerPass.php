<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\DemoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class eZDemoCompilerPass implements CompilerPassInterface
{
    protected $container;

    private function addPrivacyCookieBanner()
    {
        if ( $this->container->hasDefinition( 'ez_privacy_cookie.twig.extension' ) === false ) {
            return;
        }

        if ( $this->container->hasDefinition( 'ezdemo.ez_content_banner_factory' ) === false ) {
            return;
        }

        $definition = $this->container->getDefinition( 'ez_privacy_cookie.twig.extension' );
        $eZBannerFactory = $this->container->getDefinition( 'ezdemo.ez_content_banner_factory' );
        $definition->replaceArgument( 2, $eZBannerFactory );
    }

    public function process( ContainerBuilder $container )
    {
        $this->container = $container;

        $this->addPrivacyCookieBanner();
    }
}
