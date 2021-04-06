<?php

namespace App\Controller\Admin;

use App\Entity\Stats;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class StatsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stats::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
