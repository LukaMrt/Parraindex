drop table if exists HeartSponsor;
drop table if exists ClassicSponsor;
drop table if exists EditSponsor;
drop table if exists EditPerson;
drop table if exists Characteristic;
drop table if exists FamilyMember;
drop table if exists Family;
drop table if exists Privilege;
drop table if exists Student;
drop table if exists Promotion;
drop table if exists Degree;
drop table if exists School;
drop table if exists AssociationMember;
drop table if exists Association;
drop table if exists TypeCharacteristic;
drop table if exists Sponsor;
drop table if exists Ticket;
drop table if exists ResetPassword;
drop table if exists Account;
drop table if exists TemporaryAccount;
drop table if exists Person;

/*==============================================================*/
/* Table : Account                                              */
/*==============================================================*/
create table Account
(
    id_account int          not null auto_increment,
    id_person  int,
    password   varchar(254) not null,
    email      varchar(254) not null,
    primary key (id_account)
);

/*==============================================================*/
/* Table : Association                                          */
/*==============================================================*/
create table Association
(
    siret            bigint       not null,
    association_name varchar(254) not null,
    primary key (siret)
);

/*==============================================================*/
/* Table : AssociationMember                                    */
/*==============================================================*/
create table AssociationMember
(
    id_person      int          not null,
    siret          bigint       not null,
    arrival_date   date         not null,
    departure_date date,
    role           varchar(254) not null,
    primary key (id_person, siret, arrival_date)
);

/*==============================================================*/
/* Table : Characteristic                                       */
/*==============================================================*/
create table Characteristic
(
    id_characteristic int  not null auto_increment,
    id_person         int  not null,
    id_network        int  not null,
    value             varchar(254),
    visibility        bool not null,
    primary key (id_characteristic)
);

/*==============================================================*/
/* Table : ClassicSponsor                                       */
/*==============================================================*/
create table ClassicSponsor
(
    id_sponsor int not null,
    reason     text,
    primary key (id_sponsor)
);

/*==============================================================*/
/* Table : Degree                                               */
/*==============================================================*/
create table Degree
(
    id_degree   int          not null auto_increment,
    degree_name varchar(254) not null,
    level       int,
    total_ects  int,
    duration    int,
    official    bool,
    primary key (id_degree)
);

/*==============================================================*/
/* Table : EditSponsor                                             */
/*==============================================================*/
create table EditSponsor
(
    id_ticket    int not null,
    id_sponsor   int,
    id_godfather int,
    id_godson    int,
    date         date,
    description  text,
    type         int,
    primary key (id_ticket)
);

/*==============================================================*/
/* Table : EditPerson                                           */
/*==============================================================*/
create table EditPerson
(
    id_ticket  int          not null,
    id_person  int,
    last_name  varchar(254) not null,
    first_name varchar(254) not null,
    birthdate  date,
    biography  text,
    entry_year int,
    primary key (id_ticket)
);

/*==============================================================*/
/* Table : Family                                               */
/*==============================================================*/
create table Family
(
    id_family   int          not null auto_increment,
    id_creator  int          not null,
    family_name varchar(254) not null,
    primary key (id_family)
);

/*==============================================================*/
/* Table : FamilyMember                                         */
/*==============================================================*/
create table FamilyMember
(
    id_person int not null,
    id_family int not null,
    primary key (id_person, id_family)
);

/*==============================================================*/
/* Table : HeartSponsor                                         */
/*==============================================================*/
create table HeartSponsor
(
    id_sponsor  int not null,
    description text,
    primary key (id_sponsor)
);

/*==============================================================*/
/* Table : Person                                               */
/*==============================================================*/
create table Person
(
    id_person    int          not null auto_increment,
    uuid_person  varchar(254) not null default UUID(),
    last_name    varchar(254) not null default '?',
    first_name   varchar(254) not null,
    birthdate    date,
    biography    text,
    description  varchar(254),
    banner_color varchar(7),
    picture      varchar(254),
    primary key (id_person),
    key (first_name, last_name)
);

/*==============================================================*/
/* Table : Privilege                                            */
/*==============================================================*/
create table Privilege
(
    id_account     int not null,
    id_school      int not null,
    privilege_name varchar(254),
    description    varchar(254),
    primary key (id_account, id_school)
);

/*==============================================================*/
/* Table : Promotion                                            */
/*==============================================================*/
create table Promotion
(
    id_promotion   int not null auto_increment,
    id_degree      int not null,
    id_school      int not null,
    year           int,
    speciality     varchar(254),
    desc_promotion text,
    primary key (id_promotion)
);

/*==============================================================*/
/* Table : ResetPassword                                        */
/*==============================================================*/
create table ResetPassword
(
    id_account int          not null,
    password   varchar(254) not null,
    link       varchar(254) not null
);

/*==============================================================*/
/* Table : School                                               */
/*==============================================================*/
create table School
(
    id_school   int not null auto_increment,
    id_director int,
    school_name varchar(254),
    address     varchar(254),
    creation    date,
    city        varchar(254),
    primary key (id_school)
);

/*==============================================================*/
/* Table : Sponsor                                              */
/*==============================================================*/
create table Sponsor
(
    id_sponsor   int not null auto_increment,
    id_godfather int not null,
    id_godson    int not null,
    sponsorDate  date,
    primary key (id_sponsor),
    key (id_godfather, id_godson)
);

/*==============================================================*/
/* Table : Student                                              */
/*==============================================================*/
create table Student
(
    id_promotion int not null,
    id_person    int not null,
    primary key (id_promotion, id_person)
);

/*==============================================================*/
/* Table : TemporaryAccount                                     */
/*==============================================================*/
create table TemporaryAccount
(
    id_person int          not null,
    password  varchar(254) not null,
    email     varchar(254) not null,
    link      varchar(254) not null,
    creation  datetime     not null default NOW()
);

/*==============================================================*/
/* Table : Ticket                                               */
/*==============================================================*/
create table Ticket
(
    id_ticket       int          not null auto_increment,
    id_Resolver     int,
    type            int          not null,
    creation_date   datetime     not null,
    contacter_name  varchar(254) not null,
    contacter_email varchar(254) not null,
    resolution_date datetime,
    description     text,
    primary key (id_ticket)
);

/*==============================================================*/
/* Table : TypeCharacteristic                                   */
/*==============================================================*/
create table TypeCharacteristic
(
    id_network           int          not null auto_increment,
    title                varchar(254) not null,
    type                 varchar(254) not null,
    url                  varchar(254),
    image                varchar(254) not null,
    characteristic_order int          not null,
    primary key (id_network)
);

alter table Account
    add constraint FK_DISPOSE foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;

alter table AssociationMember
    add constraint FK_ASSO_PARTICIPATE foreign key (siret)
        references Association (siret) on delete cascade on update restrict;

alter table AssociationMember
    add constraint FK_PARTICIPATE foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;

alter table Characteristic
    add constraint FK_CHARACTERISTIC_TYPE foreign key (id_network)
        references TypeCharacteristic (id_network) on delete restrict on update restrict;

alter table Characteristic
    add constraint FK_CHOOSE foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;

alter table ClassicSponsor
    add constraint FK_CLASSIC_SPONSOR foreign key (id_sponsor)
        references Sponsor (id_sponsor) on delete cascade on update restrict;

alter table EditSponsor
    add constraint FK_EDIT_LINK_TICKET foreign key (id_ticket)
        references Ticket (id_ticket) on delete cascade on update restrict;

alter table EditSponsor
    add constraint FK_EDIT_REFER_TO foreign key (id_sponsor)
        references Sponsor (id_sponsor) on delete set null on update restrict;

alter table EditSponsor
    add constraint FK_GODFATHER_EDIT foreign key (id_godfather)
        references Person (id_person) on delete set null on update restrict;

alter table EditSponsor
    add constraint FK_GODSON_EDIT foreign key (id_godson)
        references Person (id_person) on delete set null on update restrict;

alter table EditPerson
    add constraint FK_CONCERN foreign key (id_person)
        references Person (id_person) on delete set null on update restrict;

alter table EditPerson
    add constraint FK_EDIT_PERSON_TICKET foreign key (id_ticket)
        references Ticket (id_ticket) on delete cascade on update restrict;

alter table Family
    add constraint FK_CREATE_FAMILY foreign key (id_creator)
        references Person (id_person) on delete cascade on update restrict;

alter table FamilyMember
    add constraint FK_BELONG foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;

alter table FamilyMember
    add constraint FK_BELONG_FAMILY foreign key (id_family)
        references Family (id_family) on delete cascade on update restrict;

alter table HeartSponsor
    add constraint FK_HEART_SPONSOR foreign key (id_sponsor)
        references Sponsor (id_sponsor) on delete cascade on update restrict;

alter table Privilege
    add constraint FK_DISPENSE foreign key (id_school)
        references School (id_school) on delete cascade on update restrict;

alter table Privilege
    add constraint FK_MANAGE_ACCOUNT foreign key (id_account)
        references Account (id_account) on delete cascade on update restrict;

alter table Promotion
    add constraint FK_DELIVER foreign key (id_degree)
        references Degree (id_degree) on delete restrict on update restrict;

alter table Promotion
    add constraint FK_TRAIN foreign key (id_school)
        references School (id_school) on delete restrict on update restrict;

alter table ResetPassword
    add constraint FK_RESET foreign key (id_account)
        references Account (id_account) on delete cascade on update restrict;

alter table School
    add constraint FK_LEAD_SCHOOL foreign key (id_director)
        references Person (id_person) on delete cascade on update restrict;

alter table Sponsor
    add constraint FK_FILLOTE foreign key (id_godson)
        references Person (id_person) on delete cascade on update restrict;

alter table Sponsor
    add constraint FK_PARRAINE foreign key (id_godfather)
        references Person (id_person) on delete cascade on update restrict;

alter table Student
    add constraint FK_COMPOSE foreign key (id_promotion)
        references Promotion (id_promotion) on delete cascade on update restrict;

alter table Student
    add constraint FK_STUDY foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;

alter table Ticket
    add constraint FK_SOLUTION foreign key (id_Resolver)
        references Person (id_person) on delete cascade on update restrict;

alter table TemporaryAccount
    add constraint FK_TEMPORARY_ACCOUNT foreign key (id_person)
        references Person (id_person) on delete cascade on update restrict;
