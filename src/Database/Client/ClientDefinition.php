<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Client;

use Cidaas\OauthConnect\Database\Token\TokenDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class ClientDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'cidaas_oauth_connect_client';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ClientEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ClientCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('client_id', 'clientId'))->addFlags(new Required()),
            (new StringField('client_secret', 'clientSecret'))->addFlags(new Required()),
            (new StringField('redirect_uri', 'redirectUri'))->addFlags(new Required()),
            (new StringField('token_url', 'tokenUrl'))->addFlags(new Required()),
            (new StringField('authorization_url', 'authorizationUrl'))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            new CreatedAtField(),
            new UpdatedAtField(),

            new OneToManyAssociationField('users', UserDefinition::class, 'client_id', 'id'),
            new OneToManyAssociationField('tokens', TokenDefinition::class, 'client_id', 'id'),
        ]);
    }
}
