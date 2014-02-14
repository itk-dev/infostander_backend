<?php
/**
 * @file
 * This file is a part of the Infostander AdminBundle.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infostander\AdminBundle\Controller;

use Infostander\AdminBundle\Entity\Slide;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class SlideController
 *
 * Controller for slides.
 *
 * @package Infostander\AdminBundle\Controller
 */
class SlideController extends Controller
{

    /**
     * Handler for the index action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        // Get all entities from the db, sorted by title.
        $slides = $this->getDoctrine()
            ->getRepository('InfostanderAdminBundle:Slide')->findBy(array(), array('title' => 'asc'));

        // Get show archived cookie.
        $show_archived = 0;
        $show_archived_cookie = $request->cookies->get('SHOW_ARCHIVED');
        if (isset($show_archived_cookie)) {
            $show_archived = $show_archived_cookie;
        }

        // Return the result of rendering the Slide:index template.
        return $this->render(
            'InfostanderAdminBundle:Slide:index.html.twig',
            array(
                'slides' => $slides,
                'showarchived' => $show_archived
            )
        );
    }

    /**
     * Handler for the toggle show archived action.
     *
     * Sets the cookie "SHOW_ARCHIVED", that is toggled between 0 and 1,
     * depending on whether the archived slides should be shown in the slide index page or not.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function toggleShowArchivedAction(Request $request)
    {
        // Get SHOW_ARCHIVED cookie.
        $show_archived_cookie = $request->cookies->get('SHOW_ARCHIVED');

        // Find the new value for the cookie by toggling the previous set value.
        $show_archived = 1;
        if (isset($show_archived_cookie)) {
            if ($show_archived_cookie == 1) {
                $show_archived = 0;
            } else {
                $show_archived = 1;
            }
        }

        // Make the cookie.
        $cookie = new Cookie('SHOW_ARCHIVED', $show_archived, 0, '/', null, false, false);

        // Set the cookie and redirect to Slide:index page.
        $response = new RedirectResponse($this->generateUrl("infostander_admin_slide"));
        $response->headers->setCookie($cookie);
        return $response;
    }

    /**
     * Handler for the add action.
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        // Make a new Slide.
        $slide = new Slide();

        // Create the form for the slide.
        $form = $this->createForm('slide', $slide);

        // Handle the request.
        $form->handleRequest($request);

        // If this is a submit of the form.
        if ($form->isValid()) {
            // Set the archived value of the slide.
            $slide->setArchived(false);

            // Persist the slide to the db.
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($slide);
            $manager->flush();

            // Redirect to the Slide:index page.
            return $this->redirect($this->generateUrl("infostander_admin_slide"));
        }

        // Return the rendering of the index template.
        return $this->render('InfostanderAdminBundle:Slide:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Handler for the delete action.
     *
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAction($id)
    {
        // Get the slide with $id.
        $slide = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Slide')->find($id);

        // If the slide exists, remove the slide.
        if ($slide != null) {
            // Delete the slide from the db.
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($slide);
            $manager->flush();
        }

        // Redirect to the Slide:index page.
        return $this->redirect($this->generateUrl("infostander_admin_slide"));
    }

    /**
     * Handler for the toggle archived action.
     *
     * @param $id
     * @return RedirectResponse
     */
    public function toggleArchivedAction($id)
    {
        // Get the slide with $id.
        $slide = $this->getDoctrine()->getRepository('InfostanderAdminBundle:Slide')->find($id);

        // If the exists, toggle archived.
        if ($slide != null) {
            // Set the archived to the toggled value.
            $slide->setArchived(!$slide->getArchived());

            // Persist the change to the db.
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();
        }

        // Redirect to the Slide:index page.
        return $this->redirect($this->generateUrl("infostander_admin_slide"));
    }
}
