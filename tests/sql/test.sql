create table emails
(
    id    bigint unsigned auto_increment
        primary key,
    email varchar(100) null
);

create table users
(
    id    bigint unsigned auto_increment
        primary key,
    login varchar(100) null
);

create table user_profiles
(
    id            bigint unsigned auto_increment
        primary key,
    user_id       bigint unsigned null,
    registered_at datetime        null,
    constraint user_profiles_users_id_fk
        foreign key (user_id) references users (id)
            on update cascade on delete set null
);

create table user_wallets
(
    id               bigint unsigned auto_increment
        primary key,
    user_id          bigint unsigned null,
    currency         varchar(100)    null,
    value_calculated bigint          null,
    calculated_at    datetime        null,
    constraint user_emails_users_id_fk
        foreign key (user_id) references users (id)
            on update cascade on delete set null
);

create table users_emails
(
    id       bigint unsigned auto_increment
        primary key,
    user_id  bigint unsigned null,
    email_id bigint unsigned null,
    constraint users_emails_emails_id_fk
        foreign key (email_id) references emails (id)
            on update cascade on delete set null,
    constraint users_emails_users_id_fk
        foreign key (user_id) references users (id)
            on update cascade on delete set null
);

create index users_emails_email_id_index
    on users_emails (email_id);

create index users_emails_user_id_index
    on users_emails (user_id);
