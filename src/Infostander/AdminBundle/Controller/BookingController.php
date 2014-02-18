<?php
/**
 * @file
 * This file is a part of the Infostander AdminBundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infostander\AdminBundle\Controller;

use Infostander\AdminBundle\Entity\Booking;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BookingController
 *
 * Controller for bookings.
 *
 * @package Infostander\AdminBundle\Controller
 */
class BookingController extends Controller
{

    /**
     * Handler for the index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // Get all bookings sorted by sortOrder.
        $bookings = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Booking')
            ->findBy(
                array(),
                array('sortOrder' => 'asc')
            );

        // Return the rendering of the Booking:index template.
        return $this->render('InfostanderAdminBundle:Booking:index.html.twig', array(
            'bookings' => $bookings,
        ));
    }

    /**
     * Handler for the add action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        // Make a new Booking.
        $booking = new Booking();

        // Find all slides that are not archived.
        $slides = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Slide')
            ->findBy(
                array('archived' => false),
                array('title' => 'asc')
            );

        // Create the form for the booking, with the non-archived slides as option.
        $form = $this->createForm('booking', $booking, array(
            'choice_options' => $slides,
        ));

        // Handle the request.
        $form->handleRequest($request);

        // If this is a submit of the form, persist the new booking.
        if ($form->isValid()) {
            // Find the largest sort order number in the db.
            $largestSortOrderBooking = $this->getDoctrine()
                ->getRepository('InfostanderAdminBundle:Booking')
                ->findOneBy(
                    array(),
                    array('sortOrder' => 'desc')
                );

            $newSortOrder = 1;

            // If other bookings exist, set sort order to one higher.
            if ($largestSortOrderBooking) {
                $newSortOrder = $largestSortOrderBooking->getSortOrder() + 1;
            }

            // Set sortOrder to 1 higher than previous largest sortOrder.
            $booking->setSortOrder($newSortOrder);

            // Change dates to DateTime.
            $booking->setStartDate(new \DateTime($booking->getStartDate()));
            $booking->setEndDate(new \DateTime($booking->getEndDate()));

            // Persist to the db.
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($booking);
            $manager->flush();

            // Redirect to Booking:index page.
            return $this->redirect($this->generateUrl("infostander_admin_booking"));
        }

        // Return the rendering of the Booking:add template.
        return $this->render('InfostanderAdminBundle:Booking:add.html.twig', array(
            'form' => $form->createView(),
            'slides' => $slides,
        ));
    }

    /**
     * Handler for the edit action.
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        // Get booking with $id.
        $booking = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Booking')->findOneById($id);

        // If no booking is found, redirect to Booking:index page.
        if (!$booking) {
            return $this->redirect($this->generateUrl("infostander_admin_booking"));
        }

        // Change format from DateTime to "d-m-Y H:i".
        $booking->setStartDate(date("d-m-Y H:i", date_timestamp_get($booking->getStartDate())));
        $booking->setEndDate(date("d-m-Y H:i", date_timestamp_get($booking->getEndDate())));

        // Get all slides that are not archived.
        $slides = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Slide')
            ->findBy(
                array('archived' => false),
                array('title' => 'asc')
            );

        // Create the form for the booking.
        $form = $this->createForm('booking', $booking, array(
            'choice_options' => $slides,
        ));

        // Handle the request.
        $form->handleRequest($request);

        // If this is a submit, persist the changes.
        if ($form->isValid()) {
            // Change dates to DateTime.
            $booking->setStartDate(new \DateTime($booking->getStartDate()));
            $booking->setEndDate(new \DateTime($booking->getEndDate()));

            // Persist to the db.
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            // Redirect to the Booking:index page.
            return $this->redirect($this->generateUrl("infostander_admin_booking"));
        }

        // Return the rendering of the Booking:edit template.
        return $this->render('InfostanderAdminBundle:Booking:edit.html.twig', array(
            'form' => $form->createView(),
            'slides' => $slides,
            'id' => $id,
            'slide_id' => $booking->getSlideId(),
        ));
    }

    /**
     * Handler for the changeSortOrder action.
     *
     * @param $id
     * @param $updown
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeSortOrderAction($id, $updown)
    {
        // Get the booking with $id.
        $booking = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Booking')->findOneById($id);

        // Make sure booking exists.
        if (!$booking) {
            return $this->redirect($this->generateUrl("infostander_admin_booking"));
        }

        // Get the current sort order of the booking.
        $bookingSortOrder = $booking->getSortOrder();

        // Get the next booking in the given sort order
        $em = $this->getDoctrine()->getManager();
        if ($updown == 'up') {
            $query = $em->createQuery(
                'SELECT p
                FROM InfostanderAdminBundle:Booking p
                WHERE p.sortOrder > :sort_order
                ORDER BY p.sortOrder ASC'
            )->setParameter('sort_order', $bookingSortOrder)
            ->setMaxResults(1);
        } else {
            $query = $em->createQuery(
                'SELECT p
                FROM InfostanderAdminBundle:Booking p
                WHERE p.sortOrder < :sort_order
                ORDER BY p.sortOrder DESC'
            )->setParameter('sort_order', $bookingSortOrder)
            ->setMaxResults(1);
        }
        $otherBooking = $query->getSingleResult();

        // Make sure booking exists.
        if (!$otherBooking) {
            return $this->redirect($this->generateUrl("infostander_admin_booking"));
        }

        $otherBookingSortOrder = $otherBooking->getSortOrder();

        // If there is a booking to change order with, do it.
        if ($otherBooking) {
            // Set the sortOrder of the booking to the sort order of the other booking + the change.
            $booking->setSortOrder($otherBookingSortOrder);

            // Persist the change to the booking.
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            // Persist the change to the other booking.
            $otherBooking->setSortOrder($bookingSortOrder);
            $manager->flush();
        }

        // Redirect to the Booking:index page.
        return $this->redirect($this->generateUrl("infostander_admin_booking"));
    }

    /**
     * Handler for the delete action.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        // Get the booking with $id.
        $booking = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Booking')->findOneById($id);

        // Remove the booking, if it exists
        if ($booking) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($booking);
            $manager->flush();
        }

        // Redirect to the Booking:index page.
        return $this->redirect($this->generateUrl("infostander_admin_booking"));
    }

    /**
     * Pushes channels to middleware
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function pushChannelsAction()
    {
        // Build default channel array.
        $channel = array(
            'channelID' => '1',
            'channelContent' => array(
                'logo' => '',
            ),
            'groups' => array(
                'infostander',
            ),
        );

        $now = date_timestamp_get(date_create());

        // Get bookings where present time is between the start and end date
        $bookings = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Booking')->findAll();

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $slides = array();

        foreach ($bookings as $booking) {
            $start = date_timestamp_get($booking->getStartDate());
            $end =   date_timestamp_get($booking->getEndDate());


            // If the the slide should be shown now, add it to the bookings that should be sent to the middleware
            if ($start <= $now && $now <= $end) {
                // Load slide.
                $slide = $this->getDoctrine()
                    ->getRepository('InfostanderAdminBundle:Slide')
                    ->findOneById($booking->getSlideId());

                // Set basic slide information.
                $channelEntry = array(
                    'slideID' => $booking->getSlideId(),
                    'title' => $booking->getTitle(),
                    'layout' => 'infostander',
                );

                // Add image to slide information.
                $request = Request::createFromGlobals();
                $path = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() .
                    $helper->asset($slide, 'image');

                $imgArray = array(
                    'image' => array(
                        $path,
                    ),
                );

                $channelEntry['media'] = $imgArray;
                $slides[] = $channelEntry;
            }
        }

        // Add slide to channel.
        $channel['channelContent']['slides'] = $slides;

        // Encode the channel as JSON data.
        $json = json_encode($channel);

        // Send  post request to middleware (/push/channel).
        $url = $this->container->getParameter("middleware_host") . "/push/channel";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json',
            'Content-Length: ' . strlen($json),
        ));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);

        return $this->redirect($this->generateUrl("infostander_admin_booking"));
    }
}
