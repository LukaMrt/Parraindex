# Parraindex

[![Version](https://img.shields.io/badge/version-1.0.0-blue)]()
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=coverage)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=LukaMrt_Parraindex&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=LukaMrt_Parraindex)
![GitHub language count](https://img.shields.io/github/languages/count/lukamrt/parraindex)
![GitHub](https://img.shields.io/github/license/lukamrt/parraindex)
<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
[![All Contributors](https://img.shields.io/badge/all_contributors-4-orange.svg?style=flat)](#contributors)
<!-- ALL-CONTRIBUTORS-BADGE:END -->

## About the project

This project is a school project made for the third semester of the Bachelor's degree in Computer Science at the
University of Lyon 1. The objective of this project is to create a web application with a visual interface and
an administration interface used to manage the application.

We decided to create a website which presents the relationship existing between students of the Institute of
Technology. There are many sponsor relationships between students, and we wanted to create a website that would
allow students to see all the links between people. This website will also allow students to create their own
profile and to add their own sponsor relationships (through a contact form because administrators need to validate
those creations). Administrators are able to manage the website and the users (add, delete, modify, etc.) with an
admin panel which is accessible only to them and gathers all the requests made by users with the contact form.

This project is kind of a social network. You can see the relationships between people and customize your profile.
You can't post anything, you can't follow people, you can't chat with people, etc...

You can visit the final website at this address : [https://parraindex.com](https://parraindex.com)
You can also find the source code of the website at this
address : [https://github.com/LukaMrt/Parraindex](https://github.com/LukaMrt/Parraindex)

## Built with

The school imposed to not use any framework, so we decided to use the following technologies to create the website:

* Twig for the front-end
* SCSS for styling
* JavaScript for front-end interactions
* PHP and some libraries for back-end with Composer to manage these dependencies
* MariaDB for the database

## Installation

### Requirements

To launch the project, you need to have the following software installed on your computer (or on a server and reachable
from your computer, for example the MariaDB database):

* PHP
* MariaDB
* Composer
* SCSS

You also need to have a working mail server to send emails.

### Launching the project

To launch the project, you need to follow these steps:

1. Clone the repository
2. Install the dependencies with `composer install`
3. Create a database and import the `creation.sql` file located in the `database` folder
4. Create a `.env` file in the root folder of the project and fill it with the following information (follow the
   example of the `.env.example` file):

   ```properties
    # Environment
   DEBUG="false"               # Set to "true" to enable debug mode
   
   # Database
   DRIVER="mysql"              # Database driver (mysql, pgsql, sqlite, ...)
   HOST="host"                 # Host of the database (ip or domain name)
   PORT="3306"                 # Port of the database (default for mariadb: 3306)
   DATABASE="database"         # Name of the used database
   USERNAME="user"             # Username used to connect to the database
   PASSWORD="password"         # Password of the user used to connect to the database
   
   # Mail
   MAIL_HOST="host"            # Host of the mail server (ip or domain name)
   MAIL_PORT="587"             # Port of the mail server (default: 587)
   MAIL_USERNAME="username"    # Username used to connect to the mail server
   MAIL_PASSWORD="password"    # Password of the user used to connect to the mail server
   ```

5. Build the CSS files with `sass --update scss:public/css` or `composer sass` if you want to watch the changes
   in the SCSS files and automatically rebuild the CSS files
6. Launch the project with `php -S localhost:8000 -t public` or `composer server`

## Contributing

Feel free to contribute to the project by creating a pull request or by opening an issue. If you want to contribute
to the project, please read the [CONTRIBUTING.md](CONTRIBUTING.md) file.

## Contributors

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tbody>
    <tr>
      <td align="center"><a href="https://lukamaret.com"><img src="https://avatars.githubusercontent.com/u/48085295?v=4?s=100" width="100px;" alt="Luka Maret"/><br /><sub><b>Luka Maret</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Code">💻</a> <a href="#infra-LukaMrt" title="Infrastructure (Hosting, Build-Tools, etc)">🚇</a> <a href="#projectManagement-LukaMrt" title="Project Management">📆</a> <a href="https://github.com/LukaMrt/Parraindex/commits?author=LukaMrt" title="Tests">⚠️</a> <a href="#" title="Documentation">📖</a></td>
      <td align="center"><a href="https://irophin.github.io/CV-Web/"><img src="https://avatars.githubusercontent.com/u/62310861?v=4?s=100" width="100px;" alt="Lilian Baudry"/><br /><sub><b>Lilian Baudry</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=Irophin" title="Code">💻</a> <a href="#" title="Review">👀</a> <a href="#" title="Ideas">🤔</a> <a href="#" title="Design">🎨</a></td>
      <td align="center"><a href="https://github.com/Melvyn27"><img src="https://avatars.githubusercontent.com/u/93776074?v=4?s=100" width="100px;" alt="Melvyn Delpree"/><br /><sub><b>Melvyn Delpree</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=Melvyn27" title="Code">💻</a> <a href="#" title="Design">🎨</a> <a href="#" title="Documentation">📖</a></td>
      <td align="center"><a href="https://github.com/415K7467"><img src="https://avatars.githubusercontent.com/u/93972726?v=4?s=100" width="100px;" alt="Vincent Chavot-Dambrun"/><br /><sub><b>Vincent Chavot-Dambrun</b></sub></a><br /><a href="https://github.com/LukaMrt/Parraindex/commits?author=415K7467" title="Code">💻</a></td>
    </tr>
  </tbody>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification.
Contributions of any kind welcome!

## License

The Parraindex is licensed under the [MIT License](LICENSE).
