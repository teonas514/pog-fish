select posts.title as posts___title, tags.name as tags___name, users.name as users___name from posts
    join users on posts.author_id = users.id /*STATIC*/

    left join post_tags on posts.id = post_tags.post_id /*IN BETWEEN*/
    left join tags on post_tags.tag_id = tags.id

where posts.id = 12;


SELECT users.money as users____money, users.bio as users____bio, lay.layout as users____layout, roles.rank as roles____rank, roles.name as roles____name, layouts.template_area as layouts____template_area FROM users JOIN roles ON users.role_id = roles.id JOIN layouts ON users.layout_id = layouts.id WHERE users.id = 1 LIMIT 500

SELECT id FROM users WHERE name = 'bengt'