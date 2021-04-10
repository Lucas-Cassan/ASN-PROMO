<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GameCrudController extends AbstractCrudController
{
	public static function getEntityFqcn(): string
	{
		return Game::class;

	}


	public function configureFields(string $pageName): iterable
	{
		return [
			IntegerField::new('id'),


			DateField::new('created'),
			DateField::new('ended'),

		];
	}

}
