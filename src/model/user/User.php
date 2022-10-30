<?php

namespace App\model\user;

use DateTime;

class User {

    private int $id;
    private string $lastName;
    private string $firstName;
    private DateTime $birthDate;
    private string $biography;
    private string $picture;

    public function __construct(UserBuilder $builder) {
        $this->id = $builder->getId();
        $this->lastName = $builder->getLastName();
        $this->firstName = $builder->getFirstName();
        $this->birthDate = $builder->getBirthDate();
        $this->biography = $builder->getBiography();
        $this->picture = $builder->getPicture();

        (new UserBuilder())
            ->withId(1)
            ->withLastName("Maret")
            ->withFirstName("Luka")
            ->withBirthDate("16/06/2003")
            ->withBiography("I'm a student")
            ->withPicture("Luka.png")
            ->build();
    }


}