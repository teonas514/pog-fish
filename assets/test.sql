select posts.title as posts___title, tags.name as tags___name, users.name as users___name from posts
    join users on posts.author_id = users.id /*STATIC*/

    left join post_tags on posts.id = post_tags.post_id /*IN BETWEEN*/
    left join tags on post_tags.tag_id = tags.id

where posts.id = 12
