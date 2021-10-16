<?php

namespace FS\UdorasBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;


class PaymentsReportsFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', Filters\TextFilterType::class, [
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
            ])
            ->add('title', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {
                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $value = $filterQuery->getExpr()->literal('%' . $values['value'] . '%');
                        $qb->andWhere(
                            $filterQuery->getExpr()->like('trainingProgram.title', $value)
                        );
                    }
                }
            ])
            ->add('dateBetween', Filters\DateRangeFilterType::class, [
                    'left_date_options' => [
                        'widget' => 'single_text',
                        'format' => 'MM/dd/yyyy',
                        'view_timezone' => 'UTC',
                        'model_timezone' => 'UTC',
                    ],
                    'right_date_options' => [
                        'widget' => 'single_text',
                        'format' => 'MM/dd/yyyy',
                        'view_timezone' => 'UTC',
                        'model_timezone' => 'UTC',
                    ],
                    'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                        if (!empty($values['value'])) {

                            /** @var \Doctrine\ORM\QueryBuilder $qb */
                            $qb = $filterQuery->getQueryBuilder();

                            $from = $values['value']['left_date'][0];
                            $to = $values['value']['right_date'][0];

                            if ($from <= $to || $from == null || $to == null) {
                                if (!empty($from)) {
                                    $qb->andWhere('payment.date >= :from')
                                        ->setParameter('from', $from->format('Y-m-d 0:00:00'));
                                }

                                if (!empty($to)) {
                                    $qb->andWhere('payment.date <= :to')
                                        ->setParameter('to', $to->format('Y-m-d 23:59:59'));
                                }
                            }
                        }
                    },
            ])
            ->add('priceBetween', TextRangeFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {

                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $from = floatval($values['value']['left_text']);
                        $to = floatval($values['value']['right_text']);

                        if ($from < $to || $from == 0 || $to == 0) {
                            if ($from > 0) {
                                $qb->andWhere('trainingProgram.price >= :fromPrice')
                                    ->setParameter('fromPrice', $from);
                            }

                            if ($to > 0) {
                                $qb->andWhere('trainingProgram.price <= :toPrice')
                                    ->setParameter('toPrice', $to);
                            }
                        }
                    }
                },
            ])
            ->add('totalBetween', TextRangeFilterType::class, [
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (!empty($values['value'])) {

                        /** @var \Doctrine\ORM\QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();

                        $from = floatval($values['value']['left_text']);
                        $to = floatval($values['value']['right_text']);

                        if ($from < $to || $from == 0 || $to == 0) {
                            if ($from > 0) {
                                $qb->andHaving('SUM(payment.totalPrice) >= :fromTotalPrice')
                                    ->setParameter('fromTotalPrice', $from);
                            }

                            if ($to > 0) {
                                $qb->andHaving('SUM(payment.totalPrice) <= :toTotalPrice')
                                    ->setParameter('toTotalPrice', $to);
                            }
                        }
                    }
                },
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
        return 'payments_reports_filter';
    }
}