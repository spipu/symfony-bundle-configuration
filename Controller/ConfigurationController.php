<?php
declare(strict_types = 1);

namespace Spipu\ConfigurationBundle\Controller;

use Spipu\ConfigurationBundle\Ui\ConfigurationForm;
use Spipu\ConfigurationBundle\Ui\ConfigurationGrid;
use Spipu\UiBundle\Exception\UiException;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UiBundle\Service\Ui\GridFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/configuration")
 */
class ConfigurationController extends AbstractController
{
    /**
     * @Route(
     *     "/",
     *     name="spipu_configuration_admin_list",
     *     methods="GET"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_CONFIGURATION_SHOW')")
     * @param GridFactory $gridFactory
     * @param ConfigurationGrid $configurationGrid
     * @return Response
     * @throws UiException
     */
    public function index(GridFactory $gridFactory, ConfigurationGrid $configurationGrid): Response
    {
        $manager = $gridFactory->create($configurationGrid);
        $manager->setRoute('spipu_configuration_admin_list');
        $manager->validate();

        return $this->render('@SpipuConfiguration/index.html.twig', ['manager' => $manager]);
    }

    /**
     * @Route(
     *     "/{code}",
     *     name="spipu_configuration_admin_edit",
     *     methods="GET|POST"
     * )
     * @Security("is_granted('ROLE_ADMIN_MANAGE_CONFIGURATION_EDIT')")
     * @param FormFactory $formFactory
     * @param ConfigurationForm $configurationForm
     * @param string $code
     * @return Response
     * @throws UiException
     */
    public function edit(FormFactory $formFactory, ConfigurationForm $configurationForm, string $code): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $configurationForm->setConfigurationCode($code);

        $manager = $formFactory->create($configurationForm);
        $manager->setSubmitButton('spipu.ui.action.update');
        if ($manager->validate()) {
            return $this->redirectToRoute('spipu_configuration_admin_list');
        }

        return $this->render('@SpipuConfiguration/show.html.twig', ['manager' => $manager]);
    }
}
