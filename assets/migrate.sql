drop table if exists users cascade;
create table users(
    name varchar(255) not null unique,
    password varchar(255) not null,
    profile_picture text not null,
    money float not null default 10,
    created_at timestamptz not null default now(),
    id serial unique
);

drop table if exists posts;
create table posts
(
    name       varchar(255) not null unique,
    body       text not null,
    created_at timestamptz  not null default now(),
    id         serial unique,
    author_id int,
    primary key (id),
    foreign key (author_id) references users(id)
);

explain analyze select * from users;

SELECT posts.name as posts$name, posts.body as post__body, users.id as users$id, users.name as users$name FROM posts INNER JOIN users ON users.id = posts.author_id WHERE posts.id = 3