<?php
/**
 * Created by PhpStorm.
 * User: Vladislav
 * Date: 11.11.2016
 * Time: 17:53
 * Autor: <vladislav@fora-soft.com>
 */

namespace FS\UdorasBundle\Form\Type;

use FS\TrainingProgramsBundle\Entity\EmployeeTrainingState;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TrainingReportFilterType
 * @package FS\UdorasBundle\Form\Type
 * @author <vladislav@fora-soft.com>
 */
class TrainingReportFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendor', Filters\TextFilterType::class, [
                    'condition_pattern' => FilterOperands::STRING_CONTAINS,
                    'label' => 'Vendor',
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (!empty($values['value'])) {
                            /** @var \Doctrine\ORM\QueryBuilder $qb */
                            $qb = $filterQuery->getQueryBuilder();

                            $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                            $qb->andWhere(
                                $filterQuery->getExpr()->like('vendor.fullName', $value)
                            );
                        }
                    }
                ]
            )->add('employee', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'label' => 'Employee',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {
                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                        $qb->andWhere(
                            $filterQuery->getExpr()->like('employee.fullName', $value)
                        );
                    }
                }
            ])->add('dateBetween', Filters\DateRangeFilterType::class, [
                    'left_date_options' => [
                        'widget' => 'single_text',
                        'format' => 'MM/dd/yyyy'
                    ],
                    'right_date_options' => ['widget' => 'single_text', 'format' => 'MM/dd/yyyy'],
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (!empty($values['value'])) {

                            /** @var \Doctrine\ORM\QueryBuilder $qb */
                            $qb = $filterQuery->getQueryBuilder();

                            $from = $values['value']['left_date'][0];
                            $to = $values['value']['right_date'][0];

                            if ($from <= $to || $from == null || $to == null) {
                                if (!empty($from)) {
                                    $qb->andWhere('ts.endTime >= :from')
                                        ->setParameter('from', $from->format('Y-m-d 0:00:00'));
                                }
                                if (!empty($to)) {
                                    $qb->andWhere('ts.endTime <= :to')
                                        ->setParameter('to', $to->format('Y-m-d 23:59:59'));
                                }
                            }
                        }
                    },
                ]
            )->add('expiresBetween', Filters\DateRangeFilterType::class, [
                    'left_date_options' => [
                        'widget' => 'single_text',
                        'format' => 'MM/dd/yyyy'
                    ],
                    'right_date_options' => ['widget' => 'single_text', 'format' => 'MM/dd/yyyy'],
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (!empty($values['value'])) {

                            /** @var \Doctrine\ORM\QueryBuilder $qb */
                            $qb = $filterQuery->getQueryBuilder();

                            $from = $values['value']['left_date'][0];
                            $to = $values['value']['right_date'][0];

                            if ($from <= $to || $from == null || $to == null) {
                                if (!empty($from)) {
                                    $qb->andWhere('ts.validUntil >= :fromExpires')
                                        ->setParameter('fromExpires', $from->format('Y-m-d 0:00:00'));
                                }
                                if (!empty($to)) {
                                    $qb->andWhere('ts.validUntil <= :toExpires')
                                        ->setParameter('toExpires', $to->format('Y-m-d 23:59:59'));
                                }
                            }
                        }
                    },
                ]
            )->add('customer', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {
                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                        $qb->andWhere(
                            $filterQuery->getExpr()->like('customer.company', $value)
                        );
                    }
                }
            ])->add('title', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {
                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                        $qb->andWhere(
                            $filterQuery->getExpr()->like('training.title', $value)
                        );
                    }
                }
            ])->add('status', Filters\ChoiceFilterType::class, [
                'choices' => [
                    'all' => 'All',
                    EmployeeTrainingState::FINAL_STATUS_PASSED => 'Passed',
                    EmployeeTrainingState::FINAL_STATUS_FAILED => 'Failed',
                    EmployeeTrainingState::FINAL_STATUS_IN_PROGRESS => 'Pending',
                ],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value']) && $values['value'] !== 'all') {

                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                        $qb->andWhere(
                            $filterQuery->getExpr()->like('ts.passedStatus', $value)
                        );
                    }
                }
            ])->add('vendors', Filters\CheckboxFilterType::class, [
                'label' => 'Search for Employees without a Vendor',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if ($values['value']) {
                        $qb = $filterQuery->getQueryBuilder();

                        $qb->andWhere(
                            $filterQuery->getExpr()->isNull('request.vendor')
                        );
                    }
                }
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => [
                'filtering',
            ]
        ]);
    }


    /**
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'training_report_filter';
    }
}