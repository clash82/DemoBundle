<?php
/**
 * File containing the PlaceController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace EzSystems\DemoBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;

class PlaceController extends Controller
{
    /**
     * Displays all the places contained in a place list
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location of a place_list
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listPlaceListAction( Location $location )
    {
        /** @var \EzSystems\DemoBundle\Helper\PlaceHelper $placeHelper */
        $placeHelper = $this->get( 'ezdemo.place_helper' );

        $places = $placeHelper->getPlaceList(
            $location,
            $this->container->getParameter( 'ezdemo.places.place_list.content_types' ),
            $this->getConfigResolver()->getParameter( 'languages' )
        );

        return $this->render(
            'eZDemoBundle:parts/place:place_list.html.twig',
            array(
                'places' => $places,
            )
        );
    }

    /**
     * Displays place content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $viewType
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPlaceAction( Location $location, $viewType )
    {
        $placeHelper = $this->get( 'ezdemo.place_helper' );
        $contentService = $this->get( 'ezpublish.api.service.content' );

        return $this->get( 'ez_content' )->viewLocation(
            $location->id,
            $viewType,
            true,
            [ 'relatedImage' => $placeHelper->getImageFromRelatedObjects( $contentService, $location ) ]
        );
    }

    /**
     * Displays all the places sorted by proximity contained in a place list
     * The max distance of the places displayed can be modified in the default config
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location of a place_list
     * @param float $latitude
     * @param float $longitude
     * @param int $maxDist Maximum distance for the search in km
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function listPlaceListSortedAction( Location $location, $latitude, $longitude, $maxDist )
    {
        // The Symfony router is configured (routing.yml) not to check for keys needed to generate URL
        // template from twig (without calling the controller).
        // We need to make sure those keys can't be used here.
        if ( $latitude == "key_lat" || $longitude == "key_lon" || $maxDist == "key_dist" )
        {
            throw $this->createNotFoundException( "Invalid parameters" );
        }

        /** @var \EzSystems\DemoBundle\Helper\PlaceHelper $placeHelper */
        $placeHelper = $this->get( 'ezdemo.place_helper' );

        $languages = $this->getConfigResolver()->getParameter( 'languages' );
        $language = ( !empty( $languages ) ) ? $languages[0] : null;
        $sortClauses = array(
            new SortClause\MapLocationDistance(
                "place",
                "location",
                $latitude,
                $longitude,
                Query::SORT_ASC,
                $language
            )
        );

        $places = $placeHelper->getPlaceListSorted(
            $location,
            $latitude,
            $longitude,
            $this->container->getParameter( 'ezdemo.places.place_list.content_types' ),
            $maxDist,
            $sortClauses,
            $languages
        );

        return $this->render(
            'eZDemoBundle:parts/place:place_list.html.twig',
            array( 'places' => $places )
        );
    }
}
