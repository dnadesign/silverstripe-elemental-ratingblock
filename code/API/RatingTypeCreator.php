<?php

namespace DNADesign\Elemental\API;

use SilverStripe\GraphQL\TypeCreator;
use GraphQL\Type\Definition\Type;

class RatingTypeCreator extends TypeCreator
{
    public function attributes()
    {
        return [
            'name' => 'rating'
        ];
    }

    public function fields()
    {
        return [
            'ID' => ['type' => Type::nonNull(Type::id())],
            'Rating' => ['type' => Type::int()],
            'Comments' => ['type' => Type::string()],
            'Tags' => ['type' => Type::string()],
            'PageName' => ['type' => Type::string()],
            'PageID' => ['type' => Type::int()],
            'URL' => ['type' => Type::string()],
            'Error' => ['type' => Type::string()],
        ];
    }
}
