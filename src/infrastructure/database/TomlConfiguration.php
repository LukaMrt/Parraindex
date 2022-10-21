<?php

namespace App\infrastructure\database;

use App\model\database\DatabaseCredentials;
use Toml\Parser;

class TomlConfiguration {

    static function getConfiguration(): DatabaseCredentials {

        $toml = Parser::fromFile(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'configuration.toml');

        return DatabaseCredentials::databaseCredentials()
            ->withDriver($toml['database']['driver'])
            ->withHost($toml['database']['host'])
            ->withPort($toml['database']['port'])
            ->withDatabase($toml['database']['database'])
            ->withUsername($toml['database']['username'])
            ->withPassword($toml['database']['password'])
            ->build();
    }

}
