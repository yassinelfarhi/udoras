<?php

namespace FS\TrainingProgramsBundle\Util;


use Doctrine\ORM\EntityManager;
use FS\TrainingProgramsBundle\Entity\Payment;
use FS\TrainingProgramsBundle\Entity\Request;
use FS\TrainingProgramsBundle\Entity\TrainingProgram;
use FS\TrainingProgramsBundle\Model\Manager\AccessManager;
use FS\TrainingProgramsBundle\Model\Manager\RequestManager;
use FS\UserBundle\Entity\Employee;
use FS\UserBundle\Entity\User;
use FS\UserBundle\Entity\Vendor;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Symfony\Component\Config\Definition\Exception\Exception;
use FS\TrainingProgramsBundle\Entity\Access;


class StripeService
{
    protected $em;
    protected $accessManager;
    protected $stripeSecretKey;

    public function __construct(EntityManager $em, AccessManager $accessManager, $stripeSecretKey) {
        $this->em = $em;
        $this->accessManager = $accessManager;
        $this->stripeSecretKey = $stripeSecretKey;
    }

    /**
     * @param Vendor $vendor
     * @param Request $request
     * @param $token
     * @param $amount
     * @return null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function buyTrainingsAsVendor(Vendor $vendor, Request &$request, $token, $amount)
    {
        $totalPrice = $request->getTrainingProgram()->getPrice() * $amount;

        try {
            $this->makeStripePayment($vendor, $token, $totalPrice * 100);
        } catch (Exception $exception) {
            return false;
        }

        $request->addToAmountOfTrainings($amount);
        $this->accessManager->updateAccessesForVendor($vendor, $request);
        $this->newPaymentEntity($vendor, $request->getTrainingProgram(), $totalPrice);
        $this->em->flush();

        return true;
    }

    /**
     * @param Employee $employee
     * @param Access $access
     * @param $token
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function buyTrainingAsEmployee(Employee $employee, Access &$access, $token) {

        if ($access->getTrainingState() != null) {
            return false;
        }

        $totalPrice = $access->getTrainingProgram()->getPrice();

        try {
            $this->makeStripePayment($employee, $token, $totalPrice * 100);
        } catch (Exception $exception) {
            return false;
        }

        $this->accessManager->addNewTrainingStateToAccess($access);
        $access->setState(Access::PAID);
        $this->newPaymentEntity($employee, $access->getTrainingProgram(), $totalPrice);
        $this->em->flush();

        return true;
    }


    /**
     * @param User $user
     * @param TrainingProgram $trainingProgram
     * @param $totalPrice
     * @return Payment
     */
    private function newPaymentEntity(User $user, TrainingProgram $trainingProgram, $totalPrice)
    {
        $payment = new Payment();
        $payment->setUser($user);
        $payment->setTrainingProgram($trainingProgram);
        $payment->setCurrentDate();
        $payment->setTotalPrice($totalPrice);
        $this->em->persist($payment);

        return $payment;
    }

    /**
     * @param User $user
     * @param $token
     * @param $amount
     * @return Charge
     */
    private function makeStripePayment(User $user, $token, $amount)
    {
        Stripe::setApiKey($this->stripeSecretKey);

        $customer = Customer::create([
            'email' => $user->getEmail(),
            'source' => $token
        ]);

        Charge::create([
            'customer' => $customer->id,
            'amount' => $amount,
            'currency' => 'usd'
        ]);
    }
}