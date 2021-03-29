drop table if exists users cascade;
create table users(
    name varchar(255) not null unique,
    password varchar(255) not null,
    profile_picture text,
    money float not null default 10,
    created_at timestamptz not null default now(),
    id serial unique
);

drop table if exists posts cascade ;
create table posts
(
    title       varchar(255) not null unique,
    body       text not null,
    created_at timestamptz  not null default now(),
    id         serial unique,
    author_id int,
    primary key (id),
    foreign key (author_id) references users(id)
);

drop table if exists "groups" cascade;
create table "groups"
(
    name       varchar(255) not null unique,
    body       text not null,
    created_at timestamptz  not null default now(),
    id         serial unique,
    author_id int,
    primary key (id),
    foreign key (author_id) references users(id)
);