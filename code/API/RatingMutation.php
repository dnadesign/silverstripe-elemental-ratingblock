<?php

namespace DNADesign\Elemental\API;

use DNADesign\Elemental\Models\Rating;
use SilverStripe\View\ArrayData;
use SilverStripe\GraphQL\OperationResolver;
use SilverStripe\GraphQL\MutationCreator;
use SilverStripe\Core\Convert;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;

class RatingMutation extends MutationCreator implements OperationResolver
{

    public function attributes()
    {
        return [
            'name' => 'ratingMutation',
            'description' => 'create a page rating'
        ];
    }

    public function type()
    {
        return $this->manager->getType('rating');
    }

    /**
     * @return array
     */
    public function args()
    {
        return [
            'Rating' => ['type' => Type::int()],
            'Comments' => ['type' => Type::string()],
            'Tags' => ['type' => Type::string()],
            'PageName' => ['type' => Type::string()],
            'PageID' => ['type' => Type::int()],
            'URL' => ['type' => Type::string()]
        ];
    }

    /**
     * create a new Rating
     */
    public function resolve($object, array $args, $context, ResolveInfo $info)
    {
        // Opt out of caching
        HTTPCacheControlMiddleware::singleton()
            ->disableCache();

        // Too quick. slow it down so the user feels like something is happening and to
        // give enough time to show a loading spinner
        sleep(1);

        try {
            if (!isset($args['Rating'])) {
                throw new \InvalidArgumentException(
                    'Please provide a rating',
                    500
                );
            }

            if (!isset($args['PageName'])) {
                throw new \InvalidArgumentException(
                    'Please provide a page name',
                    500
                );
            }

            $rating = Rating::create([
                'Rating' => Convert::raw2sql($args['Rating']),
                'Comments' => Convert::raw2sql($args['Comments']),
                'Tags' => Convert::raw2sql($args['Tags']),
                'PageName' => Convert::raw2sql($args['PageName']),
                'PageID' => Convert::raw2sql($args['PageID']),
                'URL' => Convert::raw2sql($args['URL']),
            ]);
            $rating->write();

            return $rating;
        } catch (\Exception $e) {
            return ArrayData::create([
                'Error' => $e->getMessage()
            ]);
        }
    }
}
