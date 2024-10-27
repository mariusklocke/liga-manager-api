<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use CBOR\Decoder;
use Cose\Algorithm\Manager as CoseManager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\RSA\RS1;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;
use DI;
use HexagonalPlayground\Application\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;
use Webauthn\TokenBinding\TokenBindingHandler;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            FakeCredentialDescriptorFactory::class => DI\autowire(),

            AuthenticatorAssertionResponseValidator::class => DI\autowire(),

            AuthenticatorAttestationResponseValidator::class => DI\autowire(),

            PackedAttestationStatementSupport::class => DI\autowire(),

            AttestationStatementSupportManager::class => DI\factory(function (ContainerInterface $container) {
                $manager = new AttestationStatementSupportManager();
                $manager->add(new FidoU2FAttestationStatementSupport());
                $manager->add(new AndroidKeyAttestationStatementSupport());
                $manager->add(new NoneAttestationStatementSupport());
                $manager->add(new TPMAttestationStatementSupport());
                $manager->add($container->get(PackedAttestationStatementSupport::class));

                return $manager;
            }),

            PublicKeyCredentialLoader::class => DI\autowire(),

            CoseManager::class => DI\factory(function() {
                $manager = new CoseManager();
                $manager->add(new ES256());
                $manager->add(new ES384());
                $manager->add(new ES512());
                $manager->add(new RS1());
                $manager->add(new RS256());
                $manager->add(new RS384());
                $manager->add(new RS512());

                return $manager;
            }),

            RequestOptionsFactory::class => DI\autowire(),

            CreationOptionsFactory::class => DI\autowire(),

            RedisOptionsStore::class => DI\autowire(),

            OptionsStoreInterface::class => DI\get(RedisOptionsStore::class),

            ChallengeGenerator::class => DI\autowire(),

            Decoder::class => null,

            TokenBindingHandler::class => DI\get(IgnoreTokenBindingHandler::class),

            IgnoreTokenBindingHandler::class => DI\autowire(),

            ExtensionOutputCheckerHandler::class => DI\autowire(),

            AttestationObjectLoader::class => DI\autowire(),

            \Webauthn\PublicKeyCredentialSourceRepository::class => DI\get(PublicKeyCredentialSourceRepository::class)
        ];
    }
}
