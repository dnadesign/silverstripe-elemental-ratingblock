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
}
