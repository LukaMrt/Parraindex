# Parraindex

<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-1-orange.svg?style=flat-square)](#contributors-)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=coverage)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)

![GitHub language count](https://img.shields.io/github/languages/count/lukamrt/parraindex)
![GitHub](https://img.shields.io/github/license/lukamrt/parraindex)

## About the project

a

This project is a school project made for the third semester of the Bachelor's degree in Computer Science at the
University of Lyon 1. The objective of this project is to create a web application with a visual interface and
an administration interface used to manage the application.

We decided to create a website which presents the relationship existing between students of the Institute of
Technology. There are many sponsor relationships between students, and we wanted to create a website that would
allow students to see all the links between people.

## Built with

The school imposed to not use any framework, so we decided to use the following technologies to create the website:

* Twig for front-end
* SCSS for styling
* JavaScript for front-end interactions
* PHP with some libraries for back-end
* MariaDB for database

## Installation

### Requirements

To launch the project, you need to have the following software installed on your computer:

* PHP
* MariaDB
* Composer
* SCSS

### Launching the project

To launch the project, you need to follow these steps:

1. Clone the repository
2. Install the dependencies with `composer install`
3. Create a database and import the `creation.sql` file located in the `database` folder
4. Create a `.env` file in the root folder of the project and fill it with the following information (follow the
   example of the `.env.example` file):

    ```
    # Environment
    
    DEBUG="false"               # Set to "true" to enable debug mode
    
    # Database
    
    DRIVER="mysql"              # Database driver (mysql, pgsql, sqlite, ...)
    HOST="host"                 # Host of the database
    PORT="3306"                 # Port of the database (default: 3306)
    DATABASE="database"         # Name of the database
    USERNAME="user"             # Username of the database
    PASSWORD="password"         # Password of the database
    
    # Mail
    
    MAIL_USERNAME="username"   # Username of the mail account
    MAIL_PASSWORD="password"   # Password of the mail account
    MAIL_HOST="host"           # Host of the mail account
    MAIL_PORT="587"            # Port of the mail account (default: 587)
    ```

5. Build the CSS files with `sass --update scss:public/css'`
6. Launch the project with `php -S localhost:8000 -t public` or `composer server`

## Contributing

Feel free to contribute to the project by creating a pull request or by opening an issue. If you want to contribute
to the project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Contributors

* **[Lilian BAUDRY](https://github.com/Irophin)** - *Initial work*
* **[Vincent CHAVOT]()** - *Initial work*
* **[Melvyn DELPREE](https://github.com/Melvyn27)** - *Initial work*
* **[Luka MARET](https://github.com/LukaMrt)** - *Initial work*

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center"><a href="http://lukamaret.com"><img src="https://avatars.githubusercontent.com/u/48085295?v=4?s=100" width="100px;" alt="LukaMrt"/><br /><sub><b>LukaMrt</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Code">💻</a> <a href="#infra-LukaMrt" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#projectManagement-LukaMrt" title="Project Management">📆</a> <a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Tests">⚠️</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

## License

This repository is under [MIT license](LICENSE).
