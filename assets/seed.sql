insert into roles (name, rank) values
    ('user', 0),
    ('admin', 20),
    ('mod', 10);

insert into tags (name, description) values
    ('spooky O_O', 'THIS IS RESERVERED FOR THE SCARIEST CREEPYPASTA OF POSTS. USE THE ON OWN RISK.'),
    ('/!\NSFW/!\', 'O_o (FLUSHING moji)'),
    ('FUNNY', 'ACTUALLY FUNNY ACTUALLY.'),
    ('r/reddit', 'reddit moments and reddit moments only'),
    ('pog-fish', 'fish pog moment'),
    ('FUCK YU', 'F-F-F-FUCK YOU!');

insert into awards (name, cost) values
    ('pog fish gold', 10),
    ('pog fish silver', 5),
    ('pog fish bronze', 2),
    ('wholesome', 1);

insert into layouts (template_areas) values
    ('["dlr pst usr"]'),
    ('["dlr dlr dlr", "pst usr usr"]'),
    ('["dlr pst pst", "dlr usr usr"]');

insert into users (name, password, profile_picture, bio) VALUES ('bengt', 'bengt', null, 'jag är benght');

insert into posts (title, body, author_id) values
    ('title', 'body', 1);

insert into post_tags (post_id, tag_id) values
    (2, 2),
    (2, 3),
    (2, 4);
