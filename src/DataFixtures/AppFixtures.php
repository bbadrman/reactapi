<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $encoder) {}
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');



        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $chrono = 1;

            $hash = $this->encoder->hashPassword($user, 'password');
            $user->setEmail($faker->email)
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName)
                ->setPassword($hash)
                ->setRoles(['ROLE_USER']);

            $manager->persist($user);
            for ($c = 0; $c < mt_rand(5, 20); $c++) {
                $customer = new Customer();
                $customer->setFirstName($faker->firstName())
                    ->setLastName($faker->lastName)
                    ->setCompany($faker->company)
                    ->setEmail($faker->email);

                $manager->persist($customer);

                for ($i = 0; $i < mt_rand(3, 10); $i++) {
                    $invoice = new Invoice();
                    $invoice->setAmount($faker->randomFloat(2, 250, 5000))
                        ->setSentAt($faker->dateTimeBetween('-6 months'))
                        ->setStatus($faker->randomElement(['SENT', 'PAID', 'CANCELLED']))
                        ->setCustomer($customer)
                        ->setChrono($chrono++);


                    $manager->persist($invoice);
                }
            }
        }


        $manager->flush();
    }
}
