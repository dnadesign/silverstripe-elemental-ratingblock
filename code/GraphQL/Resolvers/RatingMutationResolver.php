<?php

namespace DNADesign\Elemental\GraphQL\Resolvers;

use DNADesign\Elemental\Models\Rating;
use GraphQL\Type\Definition\ResolveInfo;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Convert;
use SilverStripe\View\ArrayData;

class RatingMutationResolver
{
    public static function resolve($rootValue, $args, $context, ResolveInfo $info)
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
                'RatingScore' => Convert::raw2sql($args['Rating']),
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
