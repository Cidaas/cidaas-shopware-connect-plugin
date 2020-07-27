<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Token;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(TokenEntity $entity)
 * @method void              set(string $key, TokenEntity $entity)
 * @method TokenEntity[]    getIterator()
 * @method TokenEntity[]    getElements()
 * @method TokenEntity|null get(string $key)
 * @method TokenEntity|null first()
 * @method TokenEntity|null last()
 */
class TokenCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TokenEntity::class;
    }
}
