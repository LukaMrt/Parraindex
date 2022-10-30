<?php

namespace App\model\association;

use App\model\utils\Id;

class Association {

    private Siret $id;
    private AssociationName $name;

    public function __construct(Siret $id, AssociationName $name) {
        $this->id = $id;
        $this->name = $name;
    }

}