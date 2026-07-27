<?php

namespace DNADesign\Elemental\GraphQL\Resolvers;

use GraphQL\Type\Definition\ResolveInfo;

class RatingUtilityResolver
{
    public static function resolveErrorField($object, array $args, $context, ResolveInfo $info)
    {
        return $object->Error;
    }

    public static function resolveLinkField($object, array $args, $context, $info)
    {
        return $object->AbsoluteLink();
    }

    /**
     * Placeholder root query. GraphQL requires a schema to have a root Query type
     * with at least one field; this module otherwise only defines a mutation.
     */
    public static function resolveRatingBlockStatus(): string
    {
        return 'ok';
    }
}
