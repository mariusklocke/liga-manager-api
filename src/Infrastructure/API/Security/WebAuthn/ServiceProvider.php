<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Cose\Algorithm\Manager;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Infrastructure\Environment;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[ChallengeGenerator::class] = function () {
            return new ChallengeGenerator();
        };
        $container[OptionsStoreInterface::class] = function () use ($container) {
            return new RedisOptionsStore($container[\Redis::class], 60);
        };
        $container[CreationOptionsFactory::class] = function () use ($container) {
            return new CreationOptionsFactory(
                $container[ChallengeGenerator::class],
                30000,
                $container[Manager::class]
            );
        };
        $container[RequestOptionsFactory::class] = function () use ($container) {
            return new RequestOptionsFactory(
                $container[ChallengeGenerator::class],
                30000
            );
        };
        $container[Manager::class] = function () {
            return new Manager();
        };
        $container[PublicKeyCredentialLoader::class] = function () use ($container) {
            $attestationObjectLoader = new AttestationObjectLoader($container[AttestationStatementSupportManager::class]);
            return new PublicKeyCredentialLoader($attestationObjectLoader);
        };
        $container[AttestationStatementSupportManager::class] = function () {
            return new AttestationStatementSupportManager();
        };
        $container[AuthenticatorAttestationResponseValidator::class] = function () use ($container) {
            return new AuthenticatorAttestationResponseValidator(
                $container[AttestationStatementSupportManager::class],
                $container['orm.repository.publicKeyCredential'],
                new IgnoreTokenBindingHandler(),
                new ExtensionOutputCheckerHandler()
            );
        };
        $container[AuthenticatorAssertionResponseValidator::class] = function () use ($container) {
            return new AuthenticatorAssertionResponseValidator(
                $container['orm.repository.publicKeyCredential'],
                null,
                new IgnoreTokenBindingHandler(),
                new ExtensionOutputCheckerHandler(),
                $container[Manager::class]
            );
        };
        $container[FakeCredentialDescriptorFactory::class] = function () {
            return new FakeCredentialDescriptorFactory(Environment::get('JWT_SECRET'));
        };
        $container[CredentialController::class] = function () use ($container) {
            return new CredentialController(
                $container['orm.repository.publicKeyCredential'],
                $container[PublicKeyCredentialLoader::class],
                $container[AuthenticatorAttestationResponseValidator::class],
                $container[OptionsStoreInterface::class],
                $container[CreationOptionsFactory::class]
            );
        };
        $container[AuthController::class] = function () use ($container) {
            return new AuthController(
                $container['orm.repository.publicKeyCredential'],
                $container[RequestOptionsFactory::class],
                $container[OptionsStoreInterface::class],
                $container[PublicKeyCredentialLoader::class],
                $container[AuthenticatorAssertionResponseValidator::class],
                $container[FakeCredentialDescriptorFactory::class],
                $container['orm.repository.user'],
                $container[TokenFactoryInterface::class]
            );
        };
    }
}
