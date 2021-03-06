<?php
/**
 * File containing the PlaceHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace EzSystems\DemoBundle\Helper;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Helper for places
 */
class PlaceHelper
{
    /**
     * @var  \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \EzSystems\DemoBundle\Helper\SearchHelper
     */
    private $searchHelper;

    /**
     * Min distance in kilometers to display items in the place list
     *
     * @var int
     */
    private $placeListMinDist;

    /**
     * Max distance in kilometers to display items in the place list
     *
     * @var int
     */
    private $placeListMaxDist;

    public function __construct(
        SearchService $searchService,
        SearchHelper $searchHelper,
        $placeListMinDist,
        $placeListMaxDist
    )
    {
        $this->searchService = $searchService;
        $this->searchHelper = $searchHelper;
        $this->placeListMinDist = $placeListMinDist;
        $this->placeListMaxDist = $placeListMaxDist;
    }

    /**
     * Returns all places contained in a place_list
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location of a place_list
     * @param string|string[] $contentTypes to be retrieved
     * @param string|string[] $languages to be retrieved
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getPlaceList( Location $location, $contentTypes, $languages = array() )
    {
        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( $contentTypes ),
                new Criterion\Subtree( $location->pathString ),
                new Criterion\LanguageCode( $languages )
            )
        );
        $query->performCount = false;

        $searchResults = $this->searchService->findContent( $query );

        return $this->searchHelper->buildListFromSearchResult( $searchResults );
    }

    /**
     * Returns all places contained in a place_list that are located between the range defined in
     * the default configuration. A sort clause array can be provided in order to sort the results.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location of a place_list
     * @param float $latitude
     * @param float $longitude
     * @param string|string[] $contentTypes to be retrieved
     * @param int $maxDist Maximum distance for the search in km
     * @param array $sortClauses
     * @param string|string[] $languages to be retrieved
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]
     */
    public function getPlaceListSorted( Location $location, $latitude, $longitude, $contentTypes, $maxDist = null, $sortClauses = array(), $languages = array() )
    {
        if ( $maxDist === null )
        {
            $maxDist = $this->placeListMaxDist;
        }

        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\ContentTypeIdentifier( $contentTypes ),
                new Criterion\Subtree( $location->pathString ),
                new Criterion\LanguageCode( $languages ),
                new Criterion\MapLocationDistance(
                    "location",
                    Criterion\Operator::BETWEEN,
                    array(
                        $this->placeListMinDist,
                        $maxDist
                    ),
                    $latitude,
                    $longitude
                )
            )
        );
        $query->sortClauses = $sortClauses;
        $query->performCount = false;

        $searchResults = $this->searchService->findContent( $query );

        return $this->searchHelper->buildListFromSearchResult( $searchResults );
    }
}
