<?php

namespace FS\UdorasBundle\Menu;

use FS\UserBundle\Entity\Customer;
use Knp\Menu\FactoryInterface;

/**
 * Class Builder
 * @package FS\UdorasBundle\Menu
 * @author <vladislav@fora-soft.com>
 */
class Builder
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $mainMenu = $factory->createItem('root', [
            'navbar' => true,
        ]);

        $login = $mainMenu->addChild('Login', [
            'route' => 'fos_user_security_login',

        ]);

        $login->setLinkAttributes([
            'class' => 'ajax-modal',
            'data-op' => 'redirect'
        ]);

        $login = $mainMenu->addChild('Registration', [
            'route' => 'fs_basic_select_registration',

        ]);


        return $mainMenu;
    }

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $adminMenu = $factory->createItem('root', [
            'navbar' => true,
        ]);

        $adminMenu->addChild('Users', [
            'route' => 'index_admin',
        ])->setExtra('routes', [
            'admin_vendors',
            'admin_employee',
            'index_admin'
        ]);

        $adminMenu->addChild('Payments', [
            'route' => 'payments'
        ]);
        $adminMenu->addChild('Certificates', [
            'route' => 'admin_view_certificates_list'
        ]);
        $adminMenu->addChild('Reports', [
            'route' => 'admin_reports_training_action'
        ])->setExtra('routes', [
            'admin_reports_training_action',
            'admin_reports_payment_action',
        ]);

        return $adminMenu;
    }

    public function customerMenu(FactoryInterface $factory, array $options)
    {
        $customerMenu = $factory->createItem('root', [
            'navbar' => true,
        ]);

        $customerMenu->addChild('Vendors', [
            'route' => 'index_customer',
        ]);
        $customerMenu->addChild('Trainings', [
            'route' => 'customer_training_programs',
            'routeParameters' => ['customer' => $options['customer']]
        ]);
        $customerMenu->addChild('Reports', [
            'route' => 'customer_reports_training_action',
        ]);

        return $customerMenu;
    }

    public function vendorMenu(FactoryInterface $factory, array $options)
    {
        $vendorMenu = $factory->createItem('root', [
            'navbar' => true,
        ]);

        $vendorMenu->addChild('Employees', [
            'route' => 'index_vendor',
        ]);
        $vendorMenu->addChild('Trainings', [
            'route' => 'fs_trainings_requests_show_vendor_training_requests'
        ]);

        return $vendorMenu;
    }

    public function employeeMenu(FactoryInterface $factory, array $options)
    {
        $vendorMenu = $factory->createItem('root', [
            'navbar' => true,
        ]);

        $vendorMenu->addChild('Trainings', [
            'route' => 'index_employee',
        ]);

        $vendorMenu->addChild('Certificates', [
            'route' => 'index_employee_certificate',
            'routeParameters' => [$options['parameterName'] => $options['user']->getId()],
        ]);

        return $vendorMenu;
    }

    public function logoutMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('other', array(
            'navbar' => true,
            'pull-right' => true,
        ));

        $menu->addChild('<i class="svg-icon svg-icon-user"></i> ' . (ucfirst($options['user']->getUsername())), [
            'uri' => '#',
            'pull-right' => true,
            'extras' => [
                'safe_label' => true
            ]
        ]);
        $logout = $menu->addChild('<i class="svg-icon svg-icon-logout"></i> Logout', [
            'route' => 'get_logout_form',
            'pull-right' => true,
            'extras' => [
                'safe_label' => true
            ]
        ]);

        $logout->setLinkAttributes([
            'class' => 'ajax-modal',
            'data-op' => 'redirect'
        ]);

        return $menu;
    }

    public function profileLogoutMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('other', array(
            'navbar' => true,
            'pull-right' => true,
        ));
        $name = $options['user']->getFullName();
        if ($options['user'] instanceof Customer) {
            $name = $options['user']->getCompany();
        }
        $menu->addChild('<i class="svg-icon svg-icon-user"></i> ' . $name, [
            'route' => $options['route'],
            'routeParameters' => [$options['parameterName'] => $options['user']->getId()],
            'pull-right' => true,
            'extras' => [
                'safe_label' => true
            ]
        ]);
        $logout = $menu->addChild('<i class="svg-icon svg-icon-logout"></i> Logout', [
            'route' => 'get_logout_form',
            'pull-right' => true,
            'extras' => [
                'safe_label' => true
            ]
        ]);

        $logout->setLinkAttributes([
            'class' => 'ajax-modal',
            'data-op' => 'redirect'
        ]);


        return $menu;
    }

}