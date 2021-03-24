drop table if exists users cascade;
create table users(
    name varchar(255) not null unique,
    password varchar(255) not null,
    profile_picture text not null,
    created_at timestamptz not null default now(),
    id serial unique
);

drop table if exists posts;
create table posts
(
    name       varchar(255) not null unique,
    body       varchar(255) not null,
    created_at timestamptz  not null default now(),
    id         serial unique,
    author_id int,
    primary key (id),
    foreign key (author_id) references users(id)
);

explain analyze select * from users;