
drop table if exists roles cascade;
create table roles
(
    name varchar(16),
    rank int2,
    id serial unique
);

drop table if exists users cascade;
create table users(
    name varchar(255) not null unique,
    password varchar(255) not null,
    profile_picture varchar(255),
    bio varchar(1023),
    money float not null default 10,
    created_at timestamptz not null default now(),
    id serial unique,
    role_id int default 1,

    primary key (id),
    foreign key (role_id) references roles(id)
);

drop table if exists posts cascade ;
create table posts
(
    title       varchar(255) not null,
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

drop table if exists tags cascade;
create table tags
(
    name varchar(16),
    description varchar(255),
    id serial unique
);

drop table if exists awards cascade;
create table awards
(
    name varchar(16),
    cost float not null default 16,
    id serial unique
);

drop table if exists post_tags cascade;
create table post_tags
(
    post_id int,
    tag_id int,
    foreign key (post_id) references posts(id),
    foreign key (tag_id) references tags(id)
);

drop table if exists post_awards cascade;
create table post_awards
(
    post_id int,
    award_id int,
    foreign key (post_id) references posts(id),
    foreign key (award_id) references tags(id)
);

select *
from users where id=2;