<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Hache plainPassword avant persistance Doctrine (Post / Put / Patch).
 */
final readonly class UserPasswordHasherProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User) {
            $plain = $data->getPlainPassword();
            if (null !== $plain && '' !== $plain) {
                $data->setPassword($this->passwordHasher->hashPassword($data, $plain));
                $data->setPlainPassword(null);
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
