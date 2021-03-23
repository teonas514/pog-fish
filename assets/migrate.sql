drop table if exists users;
create table users(
    name text not null unique,
    password text not null,
    profile_picture text not null,
    created_at timestamptz not null default now(),
    id serial unique
);

create index index_name
    on users(id);

explain analyze select * from users;