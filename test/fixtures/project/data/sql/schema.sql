CREATE TABLE blog (id INTEGER PRIMARY KEY AUTOINCREMENT, title VARCHAR(255), body LONGTEXT);
CREATE TABLE blog_product (blog_id INTEGER, product_id INTEGER, PRIMARY KEY(blog_id, product_id));
CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT, title VARCHAR(255), price DOUBLE, slug VARCHAR(255));
CREATE UNIQUE INDEX product_sluggable_idx ON product (slug);
