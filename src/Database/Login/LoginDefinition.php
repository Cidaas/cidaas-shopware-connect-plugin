<?php declare(strict_types=1);

namespace Cidaas\OauthConnect\Database\Login;

use Cidaas\OauthConnect\Database\Client\ClientDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\User\UserDefinition;

class LoginDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'cidaas_oauth_connect_login';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return LoginEntity::class;
    }

    public function getCollectionClass(): string
    {
        return LoginCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new IdField('state', 'state'))->addFlags(new Required()),
            new CreatedAtField(),
            new UpdatedAtField(),

            (new FkField('client_id', 'clientId', ClientDefinition::class))->addFlags(new Required()),
            new FkField('user_id', 'userId', UserDefinition::class),

            new ManyToOneAssociationField('client', 'client_id', ClientDefinition::class, 'id', false),
            new ManyToOneAssociationField('user', 'user_id', UserDefinition::class, 'id', false),
        ]);
    }
}
