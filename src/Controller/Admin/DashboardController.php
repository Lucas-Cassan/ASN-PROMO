<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MamyStérik - Back');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', UserCrudController::getEntityFqcn());
		yield MenuItem::linkToCrud('Statistiques utilisateurs', 'fas fa-chart-bar', StatsCrudController::getEntityFqcn());
		yield MenuItem::linkToCrud('Cartes', 'fas fa-puzzle-piece', CardCrudController::getEntityFqcn());
		yield MenuItem::linkToCrud('Parties', 'fas fa-gamepad', GameCrudController::getEntityFqcn());


	}
}
